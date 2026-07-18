import { useCallback, useEffect, useRef, useState } from 'react'
import { FiCheck, FiClock, FiLoader, FiPlay, FiRefreshCw, FiX, FiZap } from 'react-icons/fi'
import { aiEngineService } from '../../services/api'
import { useToast } from '../../context/ToastContext'
import Alert from './Alert'

function StageIcon({ status }) {
  if (status === 'completed' || status === 'skipped') {
    return <span className="grid h-6 w-6 place-items-center rounded-full bg-emerald-500 text-white"><FiCheck size={14} /></span>
  }
  if (status === 'running') {
    return <span className="grid h-6 w-6 place-items-center rounded-full bg-primary text-white"><FiLoader size={14} className="animate-spin" /></span>
  }
  if (status === 'failed') {
    return <span className="grid h-6 w-6 place-items-center rounded-full bg-red-500 text-white"><FiX size={14} /></span>
  }
  return <span className="grid h-6 w-6 place-items-center rounded-full bg-slate-200 text-slate-500"><FiClock size={14} /></span>
}

function EngineProgress({ job }) {
  const stages = job?.stages || []
  const pct = job?.progress ?? 0
  return (
    <div className="mt-4 rounded-2xl border border-primary/20 bg-gradient-to-br from-primary/5 via-white to-slate-50 p-5">
      <div className="flex items-center justify-between gap-3">
        <div>
          <p className="text-xs font-semibold uppercase tracking-wide text-primary">AI Generation Engine</p>
          <p className="mt-1 font-display text-lg font-bold text-navy">{job?.stage_label || 'Preparing…'}</p>
          <p className="mt-1 text-xs text-slate-500">
            Model: {job?.model || 'gemini'} · Tokens: {job?.total_tokens || 0}
            {job?.estimated_cost != null ? ` · Est. $${Number(job.estimated_cost).toFixed(4)}` : ''}
          </p>
        </div>
        <span className="rounded-full bg-white px-3 py-1 text-sm font-bold text-primary shadow-sm">{pct}%</span>
      </div>
      <div className="mt-3 h-2 overflow-hidden rounded-full bg-slate-100">
        <div className="h-full rounded-full bg-primary transition-all" style={{ width: `${pct}%` }} />
      </div>
      <ul className="mt-4 grid gap-2 sm:grid-cols-2">
        {stages.map((s) => (
          <li key={s.stage_key} className="flex items-center gap-2 rounded-xl border border-slate-100 bg-white px-3 py-2 text-sm">
            <StageIcon status={s.status} />
            <span className="min-w-0 flex-1 truncate text-slate-700">{s.title}</span>
            {s.target > 0 && (
              <span className="shrink-0 text-xs text-slate-400">{s.done || 0}/{s.target}</span>
            )}
          </li>
        ))}
      </ul>
      {job?.error && <p className="mt-3 text-sm text-red-600">{job.error}</p>}
    </div>
  )
}

function MarkdownBlock({ text }) {
  if (!text) return <p className="text-sm text-slate-400">No content yet.</p>
  return <pre className="whitespace-pre-wrap rounded-xl bg-slate-50 p-4 text-sm text-slate-700">{text}</pre>
}

const REVIEW_TABS = [
  { id: 'detailed_notes', label: 'Detailed Notes' },
  { id: 'summary', label: 'Summary' },
  { id: 'high_yield', label: 'High Yield' },
  { id: 'drugs', label: 'Drug Table' },
  { id: 'comparisons', label: 'Disease Comparison' },
  { id: 'mnemonics', label: 'Mnemonics' },
  { id: 'flashcards', label: 'Flashcards' },
  { id: 'viva', label: 'Viva' },
  { id: 'mcqs', label: 'MCQs' },
  { id: 'cases', label: 'Clinical Cases' },
  { id: 'revision', label: 'Revision Sheet' },
  { id: 'video', label: 'Video Simulation' },
]

export default function AiEnginePanel({ lectures = [], focusLectureId = null, autoPoll = false, onAutoPollConsumed }) {
  const toast = useToast()
  const [lectureId, setLectureId] = useState(focusLectureId || lectures[0]?.id || '')
  const [status, setStatus] = useState(null)
  const [job, setJob] = useState(null)
  const [review, setReview] = useState(null)
  const [tab, setTab] = useState('detailed_notes')
  const [manualText, setManualText] = useState('')
  const [running, setRunning] = useState(false)
  const pollRef = useRef(null)
  const autoStartedRef = useRef(false)

  useEffect(() => {
    if (focusLectureId) {
      setLectureId(Number(focusLectureId))
      autoStartedRef.current = false
    }
  }, [focusLectureId])

  useEffect(() => {
    if (lectures.length && !lectureId) setLectureId(lectures[0].id)
  }, [lectures, lectureId])

  useEffect(() => {
    aiEngineService.status()
      .then(({ data }) => setStatus(data.data))
      .catch(() => setStatus({ ready: false }))
  }, [])

  const loadJob = useCallback(async (id) => {
    if (!id) return null
    const { data } = await aiEngineService.jobStatus(id)
    const j = data.data?.job || null
    setJob(j)
    return j
  }, [])

  const loadReview = useCallback(async (id) => {
    if (!id) return
    const { data } = await aiEngineService.review(id)
    setReview(data.data || null)
  }, [])

  const stopPoll = useCallback(() => {
    if (pollRef.current) {
      clearTimeout(pollRef.current)
      pollRef.current = null
    }
    setRunning(false)
  }, [])

  const poll = useCallback(async (jobId) => {
    try {
      const { data } = await aiEngineService.process(jobId)
      const j = data.data?.job
      if (j) {
        unsetSource(j)
        setJob(j)
      }
      if (!j || ['completed', 'failed', 'cancelled'].includes(j.status)) {
        stopPoll()
        if (j?.status === 'completed') {
          toast.success('AI study pack ready for review')
          loadReview(lectureId)
        }
        return
      }
      pollRef.current = setTimeout(() => poll(jobId), 800)
    } catch (e) {
      stopPoll()
      toast.error(e.response?.data?.message || 'Generation step failed')
    }
  }, [lectureId, loadReview, toast, stopPoll])

  const beginPolling = useCallback((jobId) => {
    if (!jobId) return
    stopPoll()
    setRunning(true)
    poll(jobId)
  }, [poll, stopPoll])

  useEffect(() => {
    if (!lectureId) return
    let cancelled = false
    ;(async () => {
      const j = await loadJob(lectureId)
      if (cancelled) return
      await loadReview(lectureId)
      if (cancelled) return
      // Resume/continue any in-progress job automatically when this panel opens.
      if (j && ['pending', 'processing'].includes(j.status)) {
        beginPolling(j.id)
      }
    })()
    return () => { cancelled = true }
  }, [lectureId, loadJob, loadReview, beginPolling])

  // Explicit auto-poll after PPT upload (switches lecture + forces poll restart).
  useEffect(() => {
    if (!autoPoll || !lectureId || autoStartedRef.current) return
    autoStartedRef.current = true
    ;(async () => {
      const j = await loadJob(lectureId)
      if (j?.id && ['pending', 'processing', 'failed'].includes(j.status)) {
        if (j.status === 'failed') {
          try {
            const { data } = await aiEngineService.resume(j.id)
            const nj = data.data?.job
            setJob(nj)
            if (nj?.id) beginPolling(nj.id)
          } catch {
            beginPolling(j.id)
          }
        } else {
          beginPolling(j.id)
        }
      }
      onAutoPollConsumed?.()
    })()
  }, [autoPoll, lectureId, loadJob, beginPolling, onAutoPollConsumed])

  useEffect(() => () => stopPoll(), [stopPoll])

  const start = async () => {
    if (!lectureId) return
    try {
      setRunning(true)
      const body = manualText.trim() ? { text: manualText.trim() } : {}
      const { data } = await aiEngineService.generate(lectureId, body)
      const j = data.data?.job
      setJob(j)
      if (j?.id) beginPolling(j.id)
      else setRunning(false)
    } catch (e) {
      setRunning(false)
      toast.error(e.response?.data?.message || 'Could not start generation')
    }
  }

  const resume = async () => {
    if (!job?.id) return
    try {
      setRunning(true)
      const { data } = await aiEngineService.resume(job.id)
      const j = data.data?.job
      setJob(j)
      if (j?.id && !['completed', 'cancelled'].includes(j.status)) beginPolling(j.id)
      else setRunning(false)
    } catch (e) {
      setRunning(false)
      toast.error(e.response?.data?.message || 'Resume failed')
    }
  }

  const cancel = async () => {
    if (!job?.id) return
    await aiEngineService.cancel(job.id)
    stopPoll()
    loadJob(lectureId)
  }

  const approve = async () => {
    await aiEngineService.approve(lectureId)
    toast.success('Approved')
    loadReview(lectureId)
  }

  const publish = async () => {
    await aiEngineService.publish(lectureId)
    toast.success('Published to students')
    loadReview(lectureId)
  }

  const content = review?.content || {}
  const busy = running || job?.status === 'processing' || job?.status === 'pending'

  return (
    <div className="space-y-6">
      <div>
        <h3 className="font-display text-xl font-bold text-navy">AI Generation Engine</h3>
        <p className="text-sm text-slate-500">
          Upload a PPT/PDF on the Content tab and generation starts automatically.
          Or start manually here. After it finishes, Approve → Publish so students can see it.
        </p>
      </div>

      {!status?.ready && (
        <Alert type="warning">
          {status?.hint || 'Set GEMINI_API_KEY in backend/.env to enable the engine.'}
        </Alert>
      )}

      <div className="flex flex-wrap items-end gap-3">
        <label className="block min-w-[220px] flex-1 text-sm">
          <span className="mb-1 block font-medium text-slate-600">Lecture</span>
          <select
            className="w-full rounded-xl border border-slate-200 px-3 py-2"
            value={lectureId}
            onChange={(e) => setLectureId(Number(e.target.value))}
          >
            {lectures.map((l) => (
              <option key={l.id} value={l.id}>{l.title}</option>
            ))}
          </select>
        </label>
        <button
          type="button"
          disabled={!status?.ready || !lectureId || busy}
          onClick={start}
          className="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2 text-sm font-semibold text-white disabled:opacity-50"
        >
          <FiPlay /> Generate full pack
        </button>
        {job?.status === 'failed' && (
          <button type="button" onClick={resume} className="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-navy">
            <FiRefreshCw /> Resume
          </button>
        )}
        {busy && job?.id && (
          <button type="button" onClick={cancel} className="inline-flex items-center gap-2 rounded-xl border border-red-200 px-4 py-2 text-sm font-semibold text-red-600">
            <FiX /> Cancel
          </button>
        )}
      </div>

      <label className="block text-sm">
        <span className="mb-1 block font-medium text-slate-600">Optional: paste lecture text (skips file extraction)</span>
        <textarea
          rows={4}
          className="w-full rounded-xl border border-slate-200 px-3 py-2"
          value={manualText}
          onChange={(e) => setManualText(e.target.value)}
          placeholder="Leave empty to extract from uploaded PPT/PDF…"
        />
      </label>

      {job && <EngineProgress job={job} />}

      {review && (
        <div className="rounded-2xl border border-slate-100 bg-white p-5 shadow-soft">
          <div className="flex flex-wrap items-center justify-between gap-3">
            <h4 className="font-display text-lg font-bold text-navy">Review pack</h4>
            <div className="flex gap-2">
              <button type="button" onClick={approve} className="rounded-xl border border-slate-200 px-3 py-1.5 text-sm font-semibold">Approve</button>
              <button type="button" onClick={publish} className="inline-flex items-center gap-1 rounded-xl bg-navy px-3 py-1.5 text-sm font-semibold text-white">
                <FiZap /> Publish
              </button>
            </div>
          </div>
          <div className="mt-4 flex flex-wrap gap-2">
            {REVIEW_TABS.map((t) => (
              <button
                key={t.id}
                type="button"
                onClick={() => setTab(t.id)}
                className={`rounded-full px-3 py-1 text-xs font-semibold ${tab === t.id ? 'bg-primary text-white' : 'bg-slate-100 text-slate-600'}`}
              >
                {t.label}
              </button>
            ))}
          </div>
          <div className="mt-4">
            {tab === 'detailed_notes' && <MarkdownBlock text={content.detailed_notes || content.revision_notes} />}
            {tab === 'summary' && <MarkdownBlock text={content.summary} />}
            {tab === 'high_yield' && (
              <div className="space-y-3">
                <MarkdownBlock text={content.high_yield_notes} />
                {(content.high_yield_points || []).length > 0 && (
                  <ul className="list-disc space-y-1 pl-5 text-sm text-slate-700">
                    {content.high_yield_points.map((p, i) => <li key={i}>{p}</li>)}
                  </ul>
                )}
              </div>
            )}
            {tab === 'drugs' && (
              <div className="overflow-x-auto">
                <table className="min-w-full text-left text-sm">
                  <thead><tr className="border-b text-slate-500"><th className="py-2 pr-3">Drug</th><th className="py-2 pr-3">Class</th><th className="py-2">Mechanism</th></tr></thead>
                  <tbody>
                    {(review.drugs || []).map((d) => (
                      <tr key={d.id} className="border-b border-slate-50"><td className="py-2 pr-3 font-medium">{d.drug_name}</td><td className="py-2 pr-3">{d.drug_class}</td><td className="py-2">{d.mechanism}</td></tr>
                    ))}
                  </tbody>
                </table>
                {!review.drugs?.length && <p className="text-sm text-slate-400">No drugs extracted.</p>}
              </div>
            )}
            {tab === 'comparisons' && (review.disease_comparisons || []).map((c) => (
              <div key={c.id} className="mb-4">
                <p className="font-semibold text-navy">{c.title}</p>
                <p className="text-xs text-slate-500">{(c.diseases || []).join(' vs ')}</p>
              </div>
            ))}
            {tab === 'mnemonics' && (
              <ul className="space-y-3">
                {(review.mnemonics || []).map((m) => (
                  <li key={m.id} className="rounded-xl bg-slate-50 p-3 text-sm">
                    <p className="font-semibold text-navy">{m.topic || 'Mnemonic'}</p>
                    <p className="mt-1">{m.mnemonic}</p>
                    {m.explanation && <p className="mt-1 text-slate-500">{m.explanation}</p>}
                  </li>
                ))}
              </ul>
            )}
            {tab === 'flashcards' && <p className="text-sm text-slate-600">{(review.flashcards || []).length} flashcards generated.</p>}
            {tab === 'viva' && (
              <ul className="space-y-3">
                {(review.viva_questions || []).map((q) => (
                  <li key={q.id} className="rounded-xl border border-slate-100 p-3 text-sm">
                    <p className="font-medium">{q.question}</p>
                    <p className="mt-1 text-slate-600">{q.answer}</p>
                  </li>
                ))}
              </ul>
            )}
            {tab === 'mcqs' && <p className="text-sm text-slate-600">{(review.mcqs || []).length} MCQs generated.</p>}
            {tab === 'cases' && (
              <ul className="space-y-3">
                {(review.clinical_cases || []).map((c) => (
                  <li key={c.id} className="rounded-xl border border-slate-100 p-3 text-sm">
                    <p className="font-semibold text-navy">{c.title}</p>
                    <p className="mt-1 whitespace-pre-wrap">{c.scenario}</p>
                  </li>
                ))}
              </ul>
            )}
            {tab === 'revision' && <MarkdownBlock text={review.revision_sheet?.content} />}
            {tab === 'video' && (
              <div className="space-y-3 text-sm">
                <p className="font-semibold text-navy">{review.video_simulation?.title}</p>
                <MarkdownBlock text={review.video_simulation?.teaching_script} />
              </div>
            )}
          </div>
        </div>
      )}
    </div>
  )
}

function unsetSource(j) {
  if (j) delete j.source_text
}
