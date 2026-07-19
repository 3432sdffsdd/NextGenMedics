import { useCallback, useEffect, useRef, useState } from 'react'
import { FiCheck, FiClock, FiLoader, FiPlay, FiRefreshCw, FiUpload, FiX, FiZap } from 'react-icons/fi'
import { aiEngineService, aiService } from '../../services/api'
import { useToast } from '../../context/ToastContext'
import Alert from './Alert'

function StageIcon({ status }) {
  if (status === 'completed' || status === 'skipped') {
    return <span className="grid h-7 w-7 place-items-center rounded-full bg-emerald-500 text-white"><FiCheck size={14} /></span>
  }
  if (status === 'running') {
    return <span className="grid h-7 w-7 place-items-center rounded-full bg-primary text-white"><FiLoader size={14} className="animate-spin" /></span>
  }
  if (status === 'failed') {
    return <span className="grid h-7 w-7 place-items-center rounded-full bg-red-500 text-white"><FiX size={14} /></span>
  }
  return <span className="grid h-7 w-7 place-items-center rounded-full bg-slate-200 text-slate-500"><FiClock size={14} /></span>
}

function GenerationChecklist({ job, running }) {
  const stages = job?.stages || []
  const pct = job?.progress ?? 0
  const current = stages.find((s) => s.status === 'running') || stages.find((s) => s.status === 'pending')

  return (
    <div className="mt-5 rounded-2xl border border-primary/20 bg-gradient-to-br from-primary/5 via-white to-slate-50 p-5">
      <div className="flex items-start justify-between gap-3">
        <div>
          <p className="text-xs font-semibold uppercase tracking-wide text-primary">Generating study pack</p>
          <p className="mt-1 font-display text-lg font-bold text-navy">
            {running ? (current?.title ? `${current.title}…` : (job?.stage_label || 'Working…')) : (job?.stage_label || 'Ready')}
          </p>
          <p className="mt-1 text-xs text-slate-500">
            Keep this page open if you want live progress. Generation also continues in the background and auto-retries after errors.
          </p>
        </div>
        <span className="rounded-full bg-white px-3 py-1 text-sm font-bold text-primary shadow-sm">{pct}%</span>
      </div>

      <div className="mt-4 h-2.5 overflow-hidden rounded-full bg-slate-200">
        <div className="h-full rounded-full bg-primary transition-all duration-500" style={{ width: `${pct}%` }} />
      </div>

      <ol className="mt-5 space-y-2">
        {stages.map((s) => {
          const active = s.status === 'running'
          const done = s.status === 'completed' || s.status === 'skipped'
          return (
            <li
              key={s.stage_key}
              className={`flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm ${
                active ? 'bg-primary/10 font-medium text-primary' : done ? 'text-emerald-700' : 'text-slate-400'
              }`}
            >
              <StageIcon status={s.status} />
              <span className="min-w-0 flex-1">{s.title}</span>
              {s.target > 0 && (
                <span className="shrink-0 text-xs text-slate-400">{s.done || 0}/{s.target}</span>
              )}
            </li>
          )
        })}
      </ol>

      {job?.error && (
        <p className="mt-4 rounded-xl border border-red-100 bg-red-50 px-3 py-2 text-sm text-red-700">{job.error}</p>
      )}
    </div>
  )
}

function MarkdownBlock({ text }) {
  if (!text) return <p className="text-sm text-slate-400">No content yet.</p>
  return <pre className="whitespace-pre-wrap rounded-xl bg-slate-50 p-4 text-sm leading-relaxed text-slate-700">{text}</pre>
}

const REVIEW_TABS = [
  { id: 'summary', label: 'Summary' },
  { id: 'mnemonics', label: 'Mnemonics' },
  { id: 'flashcards', label: 'Flashcards' },
  { id: 'mcqs', label: 'MCQs' },
  { id: 'cases', label: 'Clinical Cases' },
]

/**
 * Study Tools — teacher selects a lecture/topic, clicks Generate,
 * and Gemini builds the full study pack stage by stage.
 */
export default function AiTutorPanel({ lectures = [] }) {
  const toast = useToast()
  const [lectureId, setLectureId] = useState(lectures[0]?.id || '')
  const [status, setStatus] = useState(null)
  const [job, setJob] = useState(null)
  const [review, setReview] = useState(null)
  const [tab, setTab] = useState('summary')
  const [pasteText, setPasteText] = useState('')
  const [showPaste, setShowPaste] = useState(false)
  const [running, setRunning] = useState(false)
  const [importing, setImporting] = useState(false)
  const [savingText, setSavingText] = useState(false)
  const [manualSummary, setManualSummary] = useState('')
  const [manualNotes, setManualNotes] = useState('')
  const pollRef = useRef(null)
  const flashRef = useRef(null)
  const mcqRef = useRef(null)

  useEffect(() => {
    if (lectures.length && !lectureId) setLectureId(lectures[0].id)
  }, [lectures, lectureId])

  useEffect(() => {
    aiEngineService.status()
      .then(({ data }) => setStatus(data.data))
      .catch(() => setStatus({
        ready: false,
        hint: 'Set GEMINI_API_KEY in backend/.env to enable generation.',
      }))
  }, [])

  const stopPoll = useCallback(() => {
    if (pollRef.current) {
      clearTimeout(pollRef.current)
      pollRef.current = null
    }
    setRunning(false)
  }, [])

  const loadReview = useCallback(async (id) => {
    if (!id) return
    const { data } = await aiEngineService.review(id)
    const pack = data.data || null
    setReview(pack)
    const c = pack?.content || {}
    setManualSummary(c.summary || '')
    setManualNotes(c.revision_notes || '')
  }, [])

  const loadJob = useCallback(async (id) => {
    if (!id) return null
    const { data } = await aiEngineService.jobStatus(id)
    const j = data.data?.job || null
    if (j) delete j.source_text
    setJob(j)
    return j
  }, [])

  const poll = useCallback(async (jobId, attempt = 0) => {
    try {
      // Prefer status polling — the background worker advances stages by itself.
      // Still call process occasionally as a backup if the worker is down.
      let j = null
      if (attempt % 3 === 0) {
        try {
          const { data } = await aiEngineService.process(jobId)
          j = data.data?.job
        } catch {
          const { data } = await aiEngineService.jobStatus(lectureId)
          j = data.data?.job
        }
      } else {
        const { data } = await aiEngineService.jobStatus(lectureId)
        j = data.data?.job
      }

      if (j) {
        delete j.source_text
        setJob(j)
      }

      if (!j || j.status === 'cancelled') {
        stopPoll()
        return
      }

      if (j.status === 'completed') {
        stopPoll()
        toast.success('Study pack ready — review, then Approve & Publish')
        loadReview(lectureId)
        return
      }

      const retrying = String(j.stage_label || '').toLowerCase().startsWith('retrying')
        || j.status === 'failed'
      const delay = retrying ? Math.min(12000, 3000 + attempt * 1000) : 1500

      if (j.status === 'failed') {
        const err = String(j.error || '').toLowerCase()
        const permanent = [
          'gemini_api_key',
          'not configured',
          'no powerpoint',
          'upload a file',
          'lecture text is missing',
        ].some((n) => err.includes(n))

        if (permanent) {
          stopPoll()
          toast.error(j.error || 'Generation stopped. Fix setup, then Generate again.')
          return
        }
        // Transient failure: worker will auto-retry; keep watching.
      }

      pollRef.current = setTimeout(() => poll(jobId, attempt + 1), delay)
    } catch {
      pollRef.current = setTimeout(() => poll(jobId, attempt + 1), Math.min(15000, 4000 + attempt * 1500))
    }
  }, [lectureId, loadReview, toast, stopPoll])

  const beginPolling = useCallback((jobId) => {
    if (!jobId) return
    stopPoll()
    setRunning(true)
    poll(jobId, 0)
  }, [poll, stopPoll])

  useEffect(() => {
    if (!lectureId) return
    let cancelled = false
    ;(async () => {
      const j = await loadJob(lectureId)
      if (cancelled) return
      await loadReview(lectureId)
      if (cancelled) return
      // Auto-continue any unfinished job (including previously failed ones).
      if (j && ['pending', 'processing', 'failed'].includes(j.status)) {
        beginPolling(j.id)
      }
    })()
    return () => { cancelled = true }
  }, [lectureId, loadJob, loadReview, beginPolling])

  useEffect(() => () => stopPoll(), [stopPoll])

  const start = async () => {
    if (!lectureId) {
      toast.error('Select a lecture / topic first')
      return
    }
    try {
      setRunning(true)
      const body = pasteText.trim() ? { text: pasteText.trim() } : {}
      const { data } = await aiEngineService.generate(lectureId, body)
      const j = data.data?.job
      if (j) delete j.source_text
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
      if (j) delete j.source_text
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
    toast.success('Published — students can now open Study Pack')
    loadReview(lectureId)
  }

  const uploadManual = async (kind, file) => {
    if (!lectureId) {
      toast.error('Select a lecture / topic first')
      return
    }
    if (!file) return
    const fd = new FormData()
    fd.append('file', file)
    setImporting(true)
    try {
      let res
      if (kind === 'flashcards') res = await aiService.importFlashcards(lectureId, fd)
      else res = await aiService.importMcqs(lectureId, fd)
      const d = res.data?.data || {}
      if (kind === 'flashcards') {
        toast.success(`Imported ${d.flashcards_imported || 0} flashcards (published)`)
      } else {
        toast.success(`Imported ${d.mcqs_imported || 0} MCQs into Question Bank (published)`)
      }
      await loadReview(lectureId)
    } catch (e) {
      toast.error(e.response?.data?.message || 'Import failed')
    } finally {
      setImporting(false)
      if (flashRef.current) flashRef.current.value = ''
      if (mcqRef.current) mcqRef.current.value = ''
    }
  }

  const saveManualText = async (andPublish = false) => {
    if (!lectureId) {
      toast.error('Select a lecture / topic first')
      return
    }
    if (!manualSummary.trim() && !manualNotes.trim()) {
      toast.error('Enter summary and/or notes first')
      return
    }
    setSavingText(true)
    try {
      await aiService.updateContent(lectureId, {
        summary: manualSummary,
        revision_notes: manualNotes,
      })
      if (andPublish) {
        await aiEngineService.approve(lectureId)
        await aiEngineService.publish(lectureId)
        toast.success('Summary & notes saved and published')
      } else {
        toast.success('Summary & notes saved')
      }
      await loadReview(lectureId)
    } catch (e) {
      toast.error(e.response?.data?.message || 'Could not save text')
    } finally {
      setSavingText(false)
    }
  }

  const content = review?.content || {}
  const busy = running || job?.status === 'processing' || job?.status === 'pending' || importing || savingText
  const selected = lectures.find((l) => Number(l.id) === Number(lectureId))
  const reviewMcqs = review?.mcqs || []

  return (
    <div className="space-y-6">
      <div>
        <h3 className="font-display text-xl font-bold text-navy">Study Tools</h3>
        <p className="mt-1 text-sm text-slate-500">
          Use <strong>AI Generate</strong> and/or <strong>Manual</strong>: type summary &amp; notes, upload flashcards (Excel) and MCQs (file). MCQs go to the student Question Bank when published.
        </p>
      </div>

      {!status?.ready && (
        <Alert type="warning">
          {status?.hint || 'Add GEMINI_API_KEY to backend/.env, then refresh this page.'}
        </Alert>
      )}

      {lectures.length === 0 ? (
        <Alert type="info">Add lectures under the Content tab first, then come back here.</Alert>
      ) : (
        <>
          <div className="rounded-2xl border border-slate-100 bg-white p-5 shadow-soft">
            <div className="mb-3 flex flex-wrap items-center justify-between gap-2">
              <div>
                <p className="text-[10px] font-semibold uppercase tracking-wide text-primary">Option A · AI</p>
                <h4 className="font-display text-lg font-bold text-navy">AI Generate</h4>
                <p className="text-xs text-slate-500">Summary, mnemonics, flashcards &amp; cases from lecture file (not MCQs).</p>
              </div>
            </div>
            <div className="flex flex-wrap items-end gap-3">
              <label className="block min-w-[240px] flex-1 text-sm">
                <span className="mb-1 block font-semibold text-slate-700">Select lecture / topic</span>
                <select
                  className="w-full rounded-xl border border-slate-200 px-3 py-2.5"
                  value={lectureId}
                  onChange={(e) => setLectureId(Number(e.target.value))}
                  disabled={busy}
                >
                  {lectures.map((l) => (
                    <option key={l.id} value={l.id}>
                      {l.chapterTitle ? `${l.chapterTitle} — ` : ''}{l.title}
                    </option>
                  ))}
                </select>
              </label>

              <button
                type="button"
                disabled={!status?.ready || !lectureId || busy}
                onClick={start}
                className="inline-flex items-center gap-2 rounded-xl bg-primary px-5 py-2.5 text-sm font-semibold text-white disabled:opacity-50"
              >
                <FiPlay /> Generate with AI
              </button>

              {job?.status === 'failed' && (
                <button type="button" onClick={resume} className="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-navy">
                  <FiRefreshCw /> Resume now
                </button>
              )}
              {busy && job?.id && !importing && !savingText && (
                <button type="button" onClick={cancel} className="inline-flex items-center gap-2 rounded-xl border border-red-200 px-4 py-2.5 text-sm font-semibold text-red-600">
                  <FiX /> Cancel
                </button>
              )}
            </div>

            {selected && (
              <p className="mt-3 text-xs text-slate-500">
                Uses the PPT/PDF uploaded on this lecture in Content.
                {!showPaste && (
                  <>
                    {' '}
                    <button type="button" className="font-semibold text-primary hover:underline" onClick={() => setShowPaste(true)}>
                      Or paste source text for AI
                    </button>
                  </>
                )}
              </p>
            )}

            {showPaste && (
              <label className="mt-3 block text-sm">
                <span className="mb-1 block font-medium text-slate-600">Paste lecture text for AI (optional)</span>
                <textarea
                  rows={5}
                  className="w-full rounded-xl border border-slate-200 px-3 py-2"
                  value={pasteText}
                  onChange={(e) => setPasteText(e.target.value)}
                  placeholder="Paste the lecture content here if there is no PPT/PDF yet…"
                  disabled={busy}
                />
              </label>
            )}
          </div>

          <div className="rounded-2xl border border-dashed border-teal-200 bg-teal-50/40 p-5">
            <div className="flex flex-wrap items-start justify-between gap-3">
              <div>
                <p className="text-[10px] font-semibold uppercase tracking-wide text-teal-700">Option B · Manual</p>
                <h4 className="font-display text-lg font-bold text-navy">Manual content</h4>
                <p className="mt-1 text-sm text-slate-600">
                  Type summary &amp; notes, upload flashcards from Excel, upload MCQs from a quiz file.
                </p>
              </div>
              <span className="inline-flex items-center gap-1 rounded-full bg-white px-2.5 py-1 text-[11px] font-semibold text-teal-700">
                <FiUpload size={12} /> Manual
              </span>
            </div>

            <div className="mt-4 space-y-4 rounded-xl border border-white bg-white p-4 shadow-sm">
              <label className="block text-sm">
                <span className="mb-1 block font-semibold text-navy">Summary</span>
                <textarea
                  rows={8}
                  className="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm leading-relaxed"
                  value={manualSummary}
                  onChange={(e) => setManualSummary(e.target.value)}
                  placeholder="Type or paste the lecture summary here…"
                  disabled={!lectureId || busy}
                />
              </label>
              <label className="block text-sm">
                <span className="mb-1 block font-semibold text-navy">Notes</span>
                <textarea
                  rows={12}
                  className="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm leading-relaxed"
                  value={manualNotes}
                  onChange={(e) => setManualNotes(e.target.value)}
                  placeholder="Type or paste detailed notes / revision notes here…"
                  disabled={!lectureId || busy}
                />
              </label>
              <div className="flex flex-wrap gap-2">
                <button
                  type="button"
                  disabled={!lectureId || busy}
                  onClick={() => saveManualText(false)}
                  className="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-navy disabled:opacity-50"
                >
                  {savingText ? 'Saving…' : 'Save text'}
                </button>
                <button
                  type="button"
                  disabled={!lectureId || busy}
                  onClick={() => saveManualText(true)}
                  className="rounded-xl bg-navy px-4 py-2 text-sm font-semibold text-white disabled:opacity-50"
                >
                  Save &amp; publish
                </button>
              </div>
            </div>

            <div className="mt-4 grid gap-3 md:grid-cols-2">
              <label className="block rounded-xl border border-white bg-white p-4 text-sm shadow-sm">
                <span className="font-semibold text-navy">Flashcards from file</span>
                <p className="mt-0.5 text-xs text-slate-500">Excel .xlsx / .xls — Column A = Front, B = Back. Published on upload.</p>
                <input
                  ref={flashRef}
                  type="file"
                  accept=".xlsx,.xls"
                  disabled={!lectureId || busy}
                  className="mt-3 block w-full text-xs"
                  onChange={(e) => uploadManual('flashcards', e.target.files?.[0])}
                />
              </label>
              <label className="block rounded-xl border border-white bg-white p-4 text-sm shadow-sm">
                <span className="font-semibold text-navy">MCQs from file</span>
                <p className="mt-0.5 text-xs text-slate-500">Word / text / HTML quiz file. Goes to Question Bank when published.</p>
                <input
                  ref={mcqRef}
                  type="file"
                  accept=".doc,.docx,.txt,.html,.htm"
                  disabled={!lectureId || busy}
                  className="mt-3 block w-full text-xs"
                  onChange={(e) => uploadManual('mcqs', e.target.files?.[0])}
                />
              </label>
            </div>
            {(importing || savingText) && (
              <p className="mt-3 inline-flex items-center gap-2 text-xs font-semibold text-teal-700">
                <FiLoader className="animate-spin" /> {savingText ? 'Saving…' : 'Uploading…'}
              </p>
            )}
          </div>
        </>
      )}

      {job && <GenerationChecklist job={job} running={busy && !importing} />}

      {job?.status === 'completed' && (
        <Alert type="success" title="Generation complete">
          Review the pack below, then click Approve and Publish so students can use it in Study Pack.
        </Alert>
      )}

      {review && (
        <div className="rounded-2xl border border-slate-100 bg-white p-5 shadow-soft">
          <div className="flex flex-wrap items-center justify-between gap-3">
            <div>
              <h4 className="font-display text-lg font-bold text-navy">3. Review &amp; publish</h4>
              <p className="text-xs text-slate-500">Students only see this after you publish.</p>
            </div>
            <div className="flex gap-2">
              <button type="button" onClick={approve} className="rounded-xl border border-slate-200 px-3 py-1.5 text-sm font-semibold">
                Approve
              </button>
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
            {tab === 'summary' && <MarkdownBlock text={content.summary} />}
            {tab === 'mnemonics' && (
              <ul className="space-y-2">
                {(review.mnemonics || []).map((m) => (
                  <li key={m.id} className="rounded-xl border border-slate-100 p-3 text-sm">
                    <p className="font-medium text-navy">{m.topic || 'Mnemonic'}</p>
                    <p className="mt-1">{m.mnemonic}</p>
                    {m.explanation && <p className="mt-1 text-slate-500">{m.explanation}</p>}
                  </li>
                ))}
                {!review.mnemonics?.length && <p className="text-sm text-slate-400">No mnemonics yet.</p>}
              </ul>
            )}
            {tab === 'flashcards' && (
              <ul className="space-y-2">
                {(review.flashcards || []).map((c) => (
                  <li key={c.id} className="rounded-xl border border-slate-100 p-3 text-sm">
                    <p className="font-medium text-navy">{c.front}</p>
                    <p className="mt-1 text-slate-600">{c.back}</p>
                  </li>
                ))}
                {!review.flashcards?.length && <p className="text-sm text-slate-400">No flashcards yet.</p>}
              </ul>
            )}
            {tab === 'mcqs' && (
              <ul className="max-h-96 space-y-2 overflow-y-auto">
                {reviewMcqs.map((m, i) => (
                  <li key={m.id || i} className="rounded-xl border border-slate-100 p-3 text-sm">
                    <p className="font-medium text-navy">{i + 1}. {m.question}</p>
                    <p className="mt-1 text-xs text-slate-500">
                      {m.source === 'manual' ? 'Manual upload' : (m.source || 'Study Tools')}
                      {m.status ? ` · ${m.status}` : ''}
                    </p>
                  </li>
                ))}
                {!reviewMcqs.length && (
                  <p className="text-sm text-slate-400">No MCQs yet. Use Manual upload → MCQs only (or Word pack).</p>
                )}
              </ul>
            )}
            {tab === 'cases' && (
              <ul className="space-y-3">
                {(review.clinical_cases || []).map((c) => (
                  <li key={c.id} className="rounded-xl border border-slate-100 p-3 text-sm">
                    <p className="font-semibold text-navy">{c.title}</p>
                    <p className="mt-1 whitespace-pre-wrap text-slate-600">{c.scenario}</p>
                    {c.diagnosis && <p className="mt-2 text-xs text-slate-500">Diagnosis: {c.diagnosis}</p>}
                  </li>
                ))}
                {!review.clinical_cases?.length && <p className="text-sm text-slate-400">No clinical cases yet.</p>}
              </ul>
            )}
          </div>
        </div>
      )}
    </div>
  )
}
