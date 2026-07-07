import { useEffect, useRef, useState } from 'react'
import { FiZap, FiCheckCircle, FiUploadCloud, FiTrash2, FiPlus, FiEdit2, FiSave, FiAlertCircle, FiFileText, FiLayers } from 'react-icons/fi'
import { aiService } from '../../services/api'
import { useToast } from '../../context/ToastContext'
import QuizFormatGuide from './QuizFormatGuide'
import Alert from './Alert'

const REVIEW_TABS = ['summary', 'notes', 'flashcards', 'mcqs', 'challenge']
const DIFFICULTIES = ['easy', 'moderate', 'difficult']

function hasStudyContent(review) {
  if (!review) return false
  const c = review.content || {}
  const counts = review.counts || {}
  return !!(
    (c.summary && String(c.summary).trim()) ||
    (c.revision_notes && String(c.revision_notes).trim()) ||
    Number(counts.flashcards) > 0 ||
    Number(counts.mcqs) > 0 ||
    (review.flashcards?.length > 0) ||
    (review.mcqs?.length > 0)
  )
}

function studyContentSummary(review) {
  if (!review) return ''
  const c = review.content || {}
  const counts = review.counts || {}
  const parts = []
  const summaryLen = (c.summary && String(c.summary).trim()) ? String(c.summary).trim().length : 0
  const notesLen = (c.revision_notes && String(c.revision_notes).trim()) ? String(c.revision_notes).trim().length : 0
  if (summaryLen) parts.push(`Summary (${summaryLen.toLocaleString()} chars)`)
  if (notesLen) parts.push(`Notes (${notesLen.toLocaleString()} chars)`)
  const fc = Number(counts.flashcards) || review.flashcards?.length || 0
  const mc = Number(counts.mcqs) || review.mcqs?.length || 0
  if (fc) parts.push(`${fc} flashcard${fc === 1 ? '' : 's'}`)
  if (mc) parts.push(`${mc} MCQ${mc === 1 ? '' : 's'}`)
  return parts.join(' · ')
}

const GENERATION_STEPS = [
  { id: 'extract', label: 'Reading your lecture file', active: 'Reading your lecture file…', done: 'Lecture file ready' },
  { id: 'summary', label: 'Lecture summary', active: 'Creating your lecture summary…', done: 'Summary complete' },
  { id: 'notes', label: 'Revision notes', active: 'Hold on — we are generating revision notes…', done: 'Revision notes ready' },
  { id: 'flashcards', label: 'Flashcards', active: 'Hold on — we are generating flashcards…', done: 'Flashcards ready' },
  { id: 'mcqs', label: 'MCQs', active: 'Hold on — we are generating MCQs…', done: 'MCQs ready' },
]

const WAITING_TIPS = [
  'Your content is being saved as you go — nothing is lost if one step pauses.',
  'FCPS-style questions are tailored from your lecture material.',
  'Please keep this tab open until all steps finish.',
  'You will review and approve everything before students see it.',
  'Large lectures take a few minutes — this is normal.',
]

function stepIndex(step) {
  const i = GENERATION_STEPS.findIndex((s) => s.id === step)
  return i >= 0 ? i : 0
}

function GenerationProgress({ job, running }) {
  const [tipIdx, setTipIdx] = useState(0)
  const current = job?.current_step || 'extract'
  const curIdx = stepIndex(current === 'done' ? 'mcqs' : current)

  useEffect(() => {
    if (!running) return undefined
    const t = setInterval(() => setTipIdx((i) => (i + 1) % WAITING_TIPS.length), 5000)
    return () => clearInterval(t)
  }, [running])

  const activeStep = GENERATION_STEPS.find((s) => s.id === current) || GENERATION_STEPS[0]
  const headline = running
    ? (current === 'extract' ? activeStep.active : activeStep.active)
    : 'Preparing…'

  return (
    <div className="mt-4 rounded-2xl border border-primary/20 bg-gradient-to-br from-primary/5 via-white to-violet-50 p-5">
      <div className="flex items-start gap-3">
        <span className="relative mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center">
          <span className="absolute inline-flex h-full w-full animate-ping rounded-full bg-primary/20 opacity-75" />
          <span className="relative grid h-10 w-10 place-items-center rounded-full bg-primary text-white">
            <FiZap size={18} className={running ? 'animate-pulse' : ''} />
          </span>
        </span>
        <div className="min-w-0 flex-1">
          <p className="text-xs font-semibold uppercase tracking-wide text-primary">AI generation in progress</p>
          <p className="mt-1 font-display text-lg font-bold text-navy">{headline}</p>
          {(current === 'flashcards' || current === 'mcqs') && (
            <p className="mt-1 text-sm text-slate-600">
              {current === 'flashcards' && `Flashcards ${job.flashcard_done || 0} / ${job.flashcard_target || 0}`}
              {current === 'mcqs' && `MCQs ${job.mcq_done || 0} / ${job.mcq_target || 0}`}
            </p>
          )}
          <p className="mt-2 text-xs text-slate-500 italic">{WAITING_TIPS[tipIdx]}</p>
        </div>
        <span className="shrink-0 rounded-full bg-white px-3 py-1 text-sm font-bold text-primary shadow-sm">
          {job.progress || 0}%
        </span>
      </div>

      <div className="mt-4 h-2 w-full overflow-hidden rounded-full bg-slate-200">
        <div
          className="h-full rounded-full bg-gradient-to-r from-primary to-violet-500 transition-all duration-700"
          style={{ width: `${job.progress || 0}%` }}
        />
      </div>

      <ol className="mt-5 space-y-2">
        {GENERATION_STEPS.filter((s) => {
          if (s.id === 'extract') return true
          if (s.id === 'flashcards' && !(job.flashcard_target > 0)) return false
          if (s.id === 'mcqs' && !(job.mcq_target > 0)) return false
          return true
        }).map((step) => {
          const idx = stepIndex(step.id)
          const done = curIdx > idx || job.status === 'completed'
          const active = step.id === current && running
          return (
            <li
              key={step.id}
              className={`flex items-center gap-3 rounded-xl px-3 py-2 text-sm transition-colors ${
                active ? 'bg-primary/10 font-medium text-primary' : done ? 'text-green-700' : 'text-slate-400'
              }`}
            >
              <span className={`grid h-6 w-6 shrink-0 place-items-center rounded-full text-xs ${
                done ? 'bg-green-100 text-green-600' : active ? 'bg-primary text-white' : 'bg-slate-100'
              }`}>
                {done ? <FiCheckCircle size={14} /> : active ? '…' : '○'}
              </span>
              <span>{done ? step.done : active ? step.active : step.label}</span>
            </li>
          )
        })}
      </ol>
    </div>
  )
}

/**
 * Teacher learning assistant panel — generate, review, approve, publish.
 * Generate → review/edit → approve → publish. Nothing reaches students until
 * the teacher publishes.
 */
export default function AiTutorPanel({ lectures = [] }) {
  const toast = useToast()
  const [lectureId, setLectureId] = useState('')
  const [options, setOptions] = useState({ flashcards: 30, mcqs: 30 })
  const [pasteText, setPasteText] = useState('')
  const [showPaste, setShowPaste] = useState(false)

  const [job, setJob] = useState(null)
  const [running, setRunning] = useState(false)
  const [review, setReview] = useState(null)
  const [reviewLoading, setReviewLoading] = useState(false)
  const [tab, setTab] = useState('summary')
  const [error, setError] = useState('')
  const [busy, setBusy] = useState(false)
  const [aiStatus, setAiStatus] = useState(null)
  const [mode, setMode] = useState('ai') // 'ai' | 'manual'
  const [quizFile, setQuizFile] = useState(null)
  const [excelFile, setExcelFile] = useState(null)
  const [importing, setImporting] = useState('')

  useEffect(() => {
    aiService.status()
      .then(({ data }) => setAiStatus(data.data))
      .catch((e) => {
        const status = e.response?.status
        const msg = e.response?.data?.message
        let hint = 'Could not reach the AI status check. '
        if (status === 404) {
          hint += 'Upload the latest backend/ folder (missing /ai/status route).'
        } else if (status === 401 || status === 403) {
          hint += 'Log out and log back in as teacher/admin.'
        } else if (msg) {
          hint += msg
        } else {
          hint += 'Confirm backend/.env exists on the server with AI_API_KEY set.'
        }
        setAiStatus({ ready: false, hint })
      })
  }, [])

  useEffect(() => {
    if (error) toast.error(error)
  }, [error, toast])

  const cancelledRef = useRef(false)
  const reviewSeqRef = useRef(0)
  const reviewSectionRef = useRef(null)

  useEffect(() => () => { cancelledRef.current = true }, [])

  const scrollToReview = () => {
    requestAnimationFrame(() => {
      reviewSectionRef.current?.scrollIntoView({ behavior: 'smooth', block: 'start' })
    })
  }

  const loadReview = async (lid) => {
    if (!lid) {
      setReview(null)
      setReviewLoading(false)
      return null
    }
    const seq = ++reviewSeqRef.current
    setReviewLoading(true)
    try {
      const { data } = await aiService.review(lid)
      if (seq !== reviewSeqRef.current) return null
      const payload = data.data
      setReview(payload)
      return payload
    } catch (e) {
      if (seq !== reviewSeqRef.current) return null
      setReview(null)
      setError(e.response?.data?.message || 'Could not load study content for this lecture')
      return null
    } finally {
      if (seq === reviewSeqRef.current) setReviewLoading(false)
    }
  }

  const selectLecture = async (lid) => {
    setLectureId(lid)
    setReview(null)
    setJob(null)
    setError('')
    if (!lid) return
    try {
      const { data } = await aiService.jobStatus(lid)
      if (data.data?.job) setJob(data.data.job)
    } catch { /* no job yet */ }
    await loadReview(lid)
  }

  const runJob = async (jobId) => {
    setRunning(true)
    cancelledRef.current = false
    try {
      let current = null
      do {
        const { data } = await aiService.process(jobId)
        current = data.data.job
        setJob(current)
        if (cancelledRef.current) return
        // Groq free tier: space out steps to stay under tokens-per-minute limit.
        if (current && !['completed', 'failed'].includes(current.status)) {
          await new Promise((r) => setTimeout(r, 3000))
        }
      } while (current && !['completed', 'failed'].includes(current.status))

      if (current?.status === 'completed') {
        toast.success('All study resources are ready — review below, then approve and publish.')
        const payload = await loadReview(lectureId)
        if (payload) {
          const fc = Number(payload.counts?.flashcards) || payload.flashcards?.length || 0
          const mc = Number(payload.counts?.mcqs) || payload.mcqs?.length || 0
          setTab(fc > 0 && mc === 0 ? 'flashcards' : mc > 0 ? 'mcqs' : 'summary')
          scrollToReview()
        }
      } else if (current?.status === 'failed') {
        setError(current.error || 'Generation failed')
      }
    } catch (e) {
      setError(e.response?.data?.message || 'Generation failed')
    } finally {
      setRunning(false)
    }
  }

  const handleGenerate = async () => {
    if (!lectureId) return
    setError('')
    setBusy(true)
    try {
      const payload = { flashcards: Number(options.flashcards) || 0, mcqs: Number(options.mcqs) || 0 }
      if (showPaste && pasteText.trim()) payload.text = pasteText.trim()
      const { data } = await aiService.generate(lectureId, payload)
      const newJob = data.data.job
      setJob(newJob)
      await runJob(newJob.id)
    } catch (e) {
      setError(e.response?.data?.message || 'Could not start generation')
    } finally {
      setBusy(false)
    }
  }

  const handleResume = async () => {
    if (!job?.id) return
    setError('')
    await runJob(job.id)
  }

  const handleApprove = async () => {
    setBusy(true); setError('')
    try {
      await aiService.approve(lectureId)
      toast.success('All content approved. Click Publish to release it to students.')
      loadReview(lectureId)
    } catch { setError('Approve failed') } finally { setBusy(false) }
  }

  const handlePublish = async () => {
    setBusy(true); setError('')
    try {
      await aiService.publish(lectureId)
      toast.success('Published! Students can now access these resources.')
      loadReview(lectureId)
    } catch { setError('Publish failed') } finally { setBusy(false) }
  }

  const handleManualSaved = async () => {
    try {
      await aiService.publish(lectureId)
      toast.success('Saved and published to students.')
      loadReview(lectureId)
    } catch {
      toast.error('Saved, but publish failed — use Publish in the review section.')
      loadReview(lectureId)
    }
  }

  const importMcqs = async () => {
    if (!lectureId || !quizFile) return toast.error('Select a lecture and quiz file first')
    setImporting('quiz')
    setError('')
    try {
      const fd = new FormData()
      fd.append('file', quizFile)
      const { data } = await aiService.importMcqs(lectureId, fd)
      toast.success(data.message || 'Quiz imported and published to students')
      setQuizFile(null)
      const payload = await loadReview(lectureId)
      setTab('mcqs')
      if (payload) scrollToReview()
    } catch (e) {
      setError(e.response?.data?.message || 'Quiz import failed')
      toast.error(e.response?.data?.message || 'Quiz import failed')
    } finally {
      setImporting('')
    }
  }

  const importFlashcards = async () => {
    if (!lectureId || !excelFile) return toast.error('Select a lecture and Excel file first')
    setImporting('excel')
    setError('')
    try {
      const fd = new FormData()
      fd.append('file', excelFile)
      const { data } = await aiService.importFlashcards(lectureId, fd)
      toast.success(data.message || 'Flashcards imported and published to students')
      setExcelFile(null)
      const payload = await loadReview(lectureId)
      setTab('flashcards')
      if (payload) scrollToReview()
    } catch (e) {
      setError(e.response?.data?.message || 'Excel import failed')
      toast.error(e.response?.data?.message || 'Excel import failed')
    } finally {
      setImporting('')
    }
  }

  const excelTopicHint = excelFile?.name
    ? excelFile.name.replace(/\.[^.]+$/, '').replace(/[_-]+/g, ' ')
    : ''

  const status = review?.content?.status
  const contentLoaded = hasStudyContent(review)
  const contentSummary = studyContentSummary(review)

  return (
    <div className="mt-6 space-y-6">
      <section className="rounded-2xl border border-slate-100 bg-white p-6 shadow-soft">
        <div className="flex items-center gap-2">
          <span className="grid h-9 w-9 place-items-center rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 text-white shadow">
            <FiZap size={18} />
          </span>
          <div>
            <h3 className="font-semibold text-navy">Learning Assistant</h3>
            <p className="text-xs text-slate-500">Generate FCPS Part-I study resources from a lecture. You review &amp; approve everything before students see it.</p>
          </div>
        </div>

        {aiStatus && !aiStatus.ready && (
          <div className="mt-4">
            <Alert
              type="warning"
              title={
                aiStatus.hint?.includes('Could not reach')
                  ? 'AI status check failed'
                  : aiStatus.key_configured === false
                    ? 'API key required'
                    : 'AI not ready'
              }
            >
              {aiStatus.hint || 'Add AI_API_KEY in backend/.env on your server, then try again.'}
              {aiStatus.hint?.includes('Could not reach') && (
                <p className="mt-2 text-xs opacity-90">
                  Open browser DevTools → Network → reload this page → click the failed <strong>/ai/status</strong> request.
                  Common fixes: upload latest <strong>backend/</strong>, ensure <strong>backend/.env</strong> exists (not inside public/), log out and back in.
                </p>
              )}
              {!aiStatus.hint?.includes('Could not reach') && (
                <p className="mt-2 text-xs opacity-90">
                  Edit <strong>backend/.env</strong> → set <code className="rounded bg-white/60 px-1">AI_API_KEY=gsk_...</code>
                  {' '}(Groq) or see <strong>backend/docs/AI_SETUP.md</strong>.
                  You can still add flashcards and MCQs manually below without an API key.
                </p>
              )}
            </Alert>
          </div>
        )}

        <div className="mt-5 grid gap-3 sm:grid-cols-2">
          <div>
            <label className="mb-1 block text-xs font-medium text-slate-500">Lecture</label>
            <select
              value={lectureId}
              onChange={(e) => selectLecture(e.target.value)}
              className="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"
            >
              <option value="">Select a lecture…</option>
              {lectures.map((lec) => (
                <option key={lec.id} value={lec.id}>{lec.chapterTitle ? `${lec.chapterTitle} → ` : ''}{lec.title}</option>
              ))}
            </select>
          </div>
          <div>
            <label className="mb-1 block text-xs font-medium text-slate-500">Mode</label>
            <div className="flex rounded-xl border border-slate-200 p-1">
              <button
                type="button"
                onClick={() => setMode('ai')}
                className={`flex flex-1 items-center justify-center gap-1.5 rounded-lg px-3 py-2 text-sm font-medium ${mode === 'ai' ? 'bg-primary text-white' : 'text-slate-600 hover:bg-slate-50'}`}
              >
                <FiZap size={15} /> AI generate
              </button>
              <button
                type="button"
                onClick={() => setMode('manual')}
                className={`flex flex-1 items-center justify-center gap-1.5 rounded-lg px-3 py-2 text-sm font-medium ${mode === 'manual' ? 'bg-primary text-white' : 'text-slate-600 hover:bg-slate-50'}`}
              >
                <FiUploadCloud size={15} /> Manual upload
              </button>
            </div>
          </div>
        </div>

        {lectureId && mode === 'manual' && (
          <div className="mt-4 space-y-4">
            <p className="text-xs text-slate-500">
              Paste summary and notes directly, upload flashcards from Excel, and upload quizzes from a file.
              Flashcard and quiz imports <strong>replace</strong> existing items of that type and publish immediately.
            </p>

            <div className="rounded-xl border border-violet-100 bg-violet-50/40 p-4">
              <p className="text-sm font-semibold text-navy">Summary</p>
              <p className="mt-1 text-xs text-slate-500">Paste your lecture summary below and save.</p>
              <div className="mt-3">
                <SummaryEditor
                  lectureId={lectureId}
                  review={review}
                  rows={14}
                  onSaved={handleManualSaved}
                />
              </div>
            </div>

            <div className="rounded-xl border border-sky-100 bg-sky-50/40 p-4">
              <p className="text-sm font-semibold text-navy">Revision notes</p>
              <p className="mt-1 text-xs text-slate-500">Paste your full revision notes below and save.</p>
              <div className="mt-3">
                <SimpleNotesEditor
                  lectureId={lectureId}
                  review={review}
                  rows={18}
                  onSaved={handleManualSaved}
                />
              </div>
            </div>

            <div className="rounded-xl border border-emerald-100 bg-emerald-50/40 p-4">
              <div className="flex items-center gap-2">
                <FiLayers className="text-emerald-600" />
                <p className="text-sm font-semibold text-navy">Excel file → Flashcards</p>
              </div>
              <p className="mt-1 text-xs text-slate-500">
                <strong>.xlsx</strong> or older <strong>.xls</strong> supported. Column A = <strong>Front</strong>, column B = <strong>Back</strong>.
                Row 1 can be headers (Front / Back). File name becomes the topic
                {excelTopicHint ? <> (e.g. <em>{excelTopicHint}</em>)</> : ''}.
              </p>
              <input
                type="file"
                accept=".xlsx,.xls,.csv"
                className="mt-3 block w-full text-sm"
                onChange={(e) => setExcelFile(e.target.files?.[0] || null)}
              />
              <button
                type="button"
                onClick={importFlashcards}
                disabled={!excelFile || !!importing}
                className="btn-primary mt-3 text-sm disabled:opacity-60"
              >
                {importing === 'excel' ? 'Importing…' : 'Import flashcards'}
              </button>
            </div>

            <div className="rounded-xl border border-indigo-100 bg-indigo-50/40 p-4">
              <div className="flex items-center gap-2">
                <FiFileText className="text-indigo-600" />
                <p className="text-sm font-semibold text-navy">Quiz file → MCQs (.txt or .docx)</p>
              </div>
              <p className="mt-1 text-xs text-slate-500">
                Upload the same FCPS-style quiz as a <strong>.txt</strong> or <strong>.docx</strong> file (optional <strong>.html</strong>).
              </p>
              <div className="mt-3">
                <QuizFormatGuide compact />
              </div>
              <input
                type="file"
                accept=".docx,.doc,.txt,.html,.htm"
                className="mt-3 block w-full text-sm"
                onChange={(e) => setQuizFile(e.target.files?.[0] || null)}
              />
              <button
                type="button"
                onClick={importMcqs}
                disabled={!quizFile || !!importing}
                className="btn-primary mt-3 text-sm disabled:opacity-60"
              >
                {importing === 'quiz' ? 'Importing…' : 'Import quiz MCQs'}
              </button>
            </div>
          </div>
        )}

        {lectureId && mode === 'ai' && (
          <div className="mt-4 rounded-xl bg-slate-50 p-4">
            <p className="mb-3 text-xs text-slate-500">
              Clicking <strong>Generate</strong> always creates a <strong>summary</strong> and <strong>revision notes</strong> first,
              then flashcards and MCQs. You review everything before students see it.
            </p>
            <div className="grid gap-4 sm:grid-cols-2">
              <div className="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-600">
                <span className="font-medium text-navy">Summary</span>
                <span className="ml-2 text-xs text-green-600">Always included</span>
              </div>
              <div className="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-600">
                <span className="font-medium text-navy">Revision notes</span>
                <span className="ml-2 text-xs text-green-600">Always included</span>
              </div>
              <div>
                <label className="mb-1 block text-xs font-medium text-slate-500">Flashcards</label>
                <input type="number" min="0" max="100" value={options.flashcards}
                  onChange={(e) => setOptions({ ...options, flashcards: e.target.value })}
                  className="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-sm" />
              </div>
              <div>
                <label className="mb-1 block text-xs font-medium text-slate-500">MCQs</label>
                <input type="number" min="0" max="200" value={options.mcqs}
                  onChange={(e) => setOptions({ ...options, mcqs: e.target.value })}
                  className="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-sm" />
              </div>
            </div>

            <button type="button" onClick={() => setShowPaste(!showPaste)} className="mt-3 text-xs font-medium text-primary hover:underline">
              {showPaste ? 'Use uploaded file instead' : 'Paste lecture text manually (optional)'}
            </button>
            {showPaste && (
              <textarea
                value={pasteText}
                onChange={(e) => setPasteText(e.target.value)}
                rows={5}
                placeholder="Paste lecture text here if the file cannot be read automatically (e.g. scanned PDF)…"
                className="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"
              />
            )}

            <div className="mt-4 flex flex-wrap items-center gap-3">
              <button type="button" onClick={handleGenerate} disabled={busy || running || !aiStatus?.ready}
                className="btn-primary text-sm disabled:opacity-60">
                <FiZap /> {running ? 'Generating…' : 'Generate study resources'}
              </button>
              {!aiStatus?.ready && (
                <span className="flex items-center gap-1 text-xs text-amber-600"><FiAlertCircle size={14} /> Configure API key first</span>
              )}
              {aiStatus?.ready && !showPaste && <span className="text-xs text-slate-400">Reads the PowerPoint/PDF/document attached to this lecture.</span>}
            </div>

            {job && job.status === 'failed' && (
              <div className="mt-4 rounded-xl border border-red-100 bg-red-50 p-4">
                <p className="text-sm text-red-700">{job.error || 'Generation stopped.'}</p>
                <p className="mt-1 text-xs text-red-600">Summary and notes are saved. Resume to continue flashcards and MCQs.</p>
                <button type="button" onClick={handleResume} disabled={running || busy}
                  className="btn-primary mt-3 text-sm disabled:opacity-60">
                  <FiZap /> Resume generation
                </button>
              </div>
            )}

            {job && (running || job.status === 'processing' || job.status === 'pending') && (
              <GenerationProgress job={job} running={running} />
            )}
          </div>
        )}
      </section>

      {lectureId && (
        <section ref={reviewSectionRef} className="rounded-2xl border border-slate-100 bg-white p-6 shadow-soft">
          {reviewLoading && (
            <div className="flex items-center gap-3 rounded-xl bg-slate-50 px-4 py-6 text-sm text-slate-600">
              <span className="inline-block h-5 w-5 animate-spin rounded-full border-2 border-primary border-t-transparent" />
              Loading study content for this lecture…
            </div>
          )}

          {!reviewLoading && !review && (
            <Alert type="warning" title="Could not load content">
              Select the lecture again or refresh the page. If you just imported a file, check that the backend is up to date.
            </Alert>
          )}

          {!reviewLoading && review && (
            <>
              {status !== 'published' && contentLoaded && (
                <Alert type="warning" title="Not visible to students yet">
                  Content is saved as <strong>{status || 'draft'}</strong>. Students only see material after you click
                  {' '}<strong>Publish to students</strong> below (Revision Center, Flashcards, MCQs).
                </Alert>
              )}

              <div className={`flex flex-wrap items-center justify-between gap-3 ${status !== 'published' && contentLoaded ? 'mt-4' : ''}`}>
                <div>
                  <div className="flex items-center gap-2">
                    <h3 className="font-semibold text-navy">Review &amp; Publish</h3>
                    <StatusBadge status={status} />
                  </div>
                  {contentSummary ? (
                    <p className="mt-1 text-xs text-slate-500">Loaded: {contentSummary}</p>
                  ) : (
                    <p className="mt-1 text-xs text-slate-400">No study content yet — generate or upload above.</p>
                  )}
                </div>
                <div className="flex gap-2">
                  <button type="button" onClick={handleApprove} disabled={busy || !contentLoaded}
                    className="btn-secondary text-sm disabled:opacity-60"><FiCheckCircle /> Approve all</button>
                  <button type="button" onClick={handlePublish} disabled={busy || !contentLoaded || status === 'published'}
                    className="btn-primary text-sm disabled:opacity-60"><FiUploadCloud /> {status === 'published' ? 'Published' : 'Publish to students'}</button>
                </div>
              </div>

              {!contentLoaded && (
                <p className="mt-4 rounded-xl bg-slate-50 px-4 py-3 text-sm text-slate-500">
                  After you import or generate content, it will appear in the tabs below. Excel flashcards are under the <strong>Flashcards</strong> tab; Word MCQs are under <strong>MCQs</strong>.
                </p>
              )}

              <div className="mt-4 flex flex-wrap gap-2 border-b border-slate-200">
                {REVIEW_TABS.map((t) => (
                  <button key={t} type="button" onClick={() => setTab(t)}
                    className={`px-3 py-2 text-sm font-medium capitalize ${tab === t ? 'border-b-2 border-primary text-primary' : 'text-slate-500'}`}>
                    {t}{t === 'flashcards' ? ` (${review.counts?.flashcards || review.flashcards?.length || 0})` : t === 'mcqs' ? ` (${review.counts?.mcqs || review.mcqs?.length || 0})` : ''}
                  </button>
                ))}
              </div>

              <div className="mt-5">
                {tab === 'summary' && <SummaryEditor lectureId={lectureId} review={review} onSaved={() => loadReview(lectureId)} />}
                {tab === 'notes' && <NotesEditor lectureId={lectureId} review={review} onSaved={() => loadReview(lectureId)} />}
                {tab === 'flashcards' && <FlashcardsEditor lectureId={lectureId} cards={review.flashcards || []} onChanged={() => loadReview(lectureId)} />}
                {tab === 'mcqs' && <McqsEditor lectureId={lectureId} mcqs={review.mcqs || []} onChanged={() => loadReview(lectureId)} />}
                {tab === 'challenge' && <ChallengeEditor lectureId={lectureId} challenge={review.challenge} mcqCount={review.counts?.mcqs || 0} onSaved={() => loadReview(lectureId)} />}
              </div>
            </>
          )}
        </section>
      )}
    </div>
  )
}

function StatusBadge({ status }) {
  const map = {
    published: 'bg-emerald-100 text-emerald-700',
    approved: 'bg-amber-100 text-amber-700',
    draft: 'bg-slate-100 text-slate-600',
  }
  const label = status || 'draft'
  return <span className={`rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize ${map[label] || map.draft}`}>{label}</span>
}

function SummaryEditor({ lectureId, review, onSaved, rows = 10 }) {
  const [value, setValue] = useState(review?.content?.summary || '')
  const [saving, setSaving] = useState(false)
  useEffect(() => { setValue(review?.content?.summary || '') }, [review?.content?.summary])

  const save = async () => {
    setSaving(true)
    try { await aiService.updateContent(lectureId, { summary: value }); onSaved() } finally { setSaving(false) }
  }
  return (
    <div>
      <textarea rows={rows} value={value} onChange={(e) => setValue(e.target.value)}
        className="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" placeholder="Lecture summary…" />
      <button type="button" onClick={save} disabled={saving} className="btn-primary mt-3 text-sm"><FiSave /> Save summary</button>
    </div>
  )
}

function SimpleNotesEditor({ lectureId, review, onSaved, rows = 10 }) {
  const [value, setValue] = useState(review?.content?.revision_notes || '')
  const [saving, setSaving] = useState(false)
  useEffect(() => { setValue(review?.content?.revision_notes || '') }, [review?.content?.revision_notes])

  const save = async () => {
    setSaving(true)
    try {
      await aiService.updateContent(lectureId, { revision_notes: value })
      onSaved()
    } finally { setSaving(false) }
  }
  return (
    <div>
      <textarea rows={rows} value={value} onChange={(e) => setValue(e.target.value)}
        className="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm font-mono" placeholder="Revision notes…" />
      <button type="button" onClick={save} disabled={saving} className="btn-primary mt-3 text-sm"><FiSave /> Save notes</button>
    </div>
  )
}

function NotesEditor({ lectureId, review, onSaved }) {
  const c = review?.content || {}
  const [notes, setNotes] = useState(c.revision_notes || '')
  const [lists, setLists] = useState({
    high_yield_points: (c.high_yield_points || []).join('\n'),
    clinical_pearls: (c.clinical_pearls || []).join('\n'),
    common_mistakes: (c.common_mistakes || []).join('\n'),
    memory_tricks: (c.memory_tricks || []).join('\n'),
    key_takeaways: (c.key_takeaways || []).join('\n'),
    key_definitions: (c.key_definitions || []).map((d) => `${d.term} :: ${d.definition}`).join('\n'),
  })
  const [saving, setSaving] = useState(false)

  useEffect(() => {
    const cc = review?.content || {}
    setNotes(cc.revision_notes || '')
    setLists({
      high_yield_points: (cc.high_yield_points || []).join('\n'),
      clinical_pearls: (cc.clinical_pearls || []).join('\n'),
      common_mistakes: (cc.common_mistakes || []).join('\n'),
      memory_tricks: (cc.memory_tricks || []).join('\n'),
      key_takeaways: (cc.key_takeaways || []).join('\n'),
      key_definitions: (cc.key_definitions || []).map((d) => `${d.term} :: ${d.definition}`).join('\n'),
    })
  }, [review?.content])

  const save = async () => {
    setSaving(true)
    const toList = (s) => s.split('\n').map((x) => x.trim()).filter(Boolean)
    const defs = toList(lists.key_definitions).map((line) => {
      const [term, ...rest] = line.split('::')
      return { term: (term || '').trim(), definition: rest.join('::').trim() }
    })
    try {
      await aiService.updateContent(lectureId, {
        revision_notes: notes,
        high_yield_points: toList(lists.high_yield_points),
        clinical_pearls: toList(lists.clinical_pearls),
        common_mistakes: toList(lists.common_mistakes),
        memory_tricks: toList(lists.memory_tricks),
        key_takeaways: toList(lists.key_takeaways),
        key_definitions: defs,
      })
      onSaved()
    } finally { setSaving(false) }
  }

  const listFields = [
    ['high_yield_points', 'High-Yield Points'],
    ['clinical_pearls', 'Clinical Pearls'],
    ['common_mistakes', 'Common Mistakes'],
    ['memory_tricks', 'Memory Tricks / Mnemonics'],
    ['key_takeaways', 'Key Takeaways'],
  ]

  return (
    <div className="space-y-4">
      <div>
        <label className="mb-1 block text-xs font-medium text-slate-500">Revision Notes (markdown)</label>
        <textarea rows={10} value={notes} onChange={(e) => setNotes(e.target.value)}
          className="w-full rounded-xl border border-slate-200 px-3 py-2 font-mono text-xs" />
      </div>
      <div className="grid gap-4 sm:grid-cols-2">
        {listFields.map(([key, label]) => (
          <div key={key}>
            <label className="mb-1 block text-xs font-medium text-slate-500">{label} <span className="text-slate-400">(one per line)</span></label>
            <textarea rows={4} value={lists[key]} onChange={(e) => setLists({ ...lists, [key]: e.target.value })}
              className="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" />
          </div>
        ))}
        <div>
          <label className="mb-1 block text-xs font-medium text-slate-500">Key Definitions <span className="text-slate-400">(term :: definition)</span></label>
          <textarea rows={4} value={lists.key_definitions} onChange={(e) => setLists({ ...lists, key_definitions: e.target.value })}
            className="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" />
        </div>
      </div>
      <button type="button" onClick={save} disabled={saving} className="btn-primary text-sm"><FiSave /> Save notes</button>
    </div>
  )
}

function FlashcardsEditor({ lectureId, cards, onChanged }) {
  const [adding, setAdding] = useState({ front: '', back: '', topic: '', difficulty: 'moderate' })
  const add = async () => {
    if (!adding.front.trim() || !adding.back.trim()) return
    await aiService.addFlashcard(lectureId, adding)
    setAdding({ front: '', back: '', topic: '', difficulty: 'moderate' })
    onChanged()
  }
  return (
    <div className="space-y-3">
      <div className="rounded-xl bg-slate-50 p-4">
        <h4 className="text-sm font-semibold text-navy">Add flashcard</h4>
        <div className="mt-2 grid gap-2 sm:grid-cols-2">
          <input placeholder="Front (question)" value={adding.front} onChange={(e) => setAdding({ ...adding, front: e.target.value })} className="rounded-lg border border-slate-200 px-3 py-2 text-sm" />
          <input placeholder="Back (answer)" value={adding.back} onChange={(e) => setAdding({ ...adding, back: e.target.value })} className="rounded-lg border border-slate-200 px-3 py-2 text-sm" />
          <input placeholder="Topic" value={adding.topic} onChange={(e) => setAdding({ ...adding, topic: e.target.value })} className="rounded-lg border border-slate-200 px-3 py-2 text-sm" />
          <select value={adding.difficulty} onChange={(e) => setAdding({ ...adding, difficulty: e.target.value })} className="rounded-lg border border-slate-200 px-3 py-2 text-sm">
            {DIFFICULTIES.map((d) => <option key={d} value={d}>{d}</option>)}
          </select>
        </div>
        <button type="button" onClick={add} className="btn-secondary mt-3 text-sm"><FiPlus /> Add flashcard</button>
      </div>

      {cards.length === 0 ? (
        <p className="text-sm text-slate-400">No flashcards yet. Generate some or add manually.</p>
      ) : cards.map((card) => <FlashcardRow key={card.id} card={card} onChanged={onChanged} />)}
    </div>
  )
}

function FlashcardRow({ card, onChanged }) {
  const [edit, setEdit] = useState(false)
  const [f, setF] = useState(card)
  useEffect(() => setF(card), [card])

  const save = async () => { await aiService.updateFlashcard(card.id, { front: f.front, back: f.back, topic: f.topic, difficulty: f.difficulty }); setEdit(false); onChanged() }
  const remove = async () => { await aiService.deleteFlashcard(card.id); onChanged() }

  return (
    <div className="rounded-xl border border-slate-100 p-3">
      {edit ? (
        <div className="grid gap-2 sm:grid-cols-2">
          <input value={f.front} onChange={(e) => setF({ ...f, front: e.target.value })} className="rounded-lg border border-slate-200 px-2 py-1.5 text-sm" />
          <input value={f.back} onChange={(e) => setF({ ...f, back: e.target.value })} className="rounded-lg border border-slate-200 px-2 py-1.5 text-sm" />
          <div className="flex gap-2 sm:col-span-2">
            <button type="button" onClick={save} className="btn-primary text-xs"><FiSave /> Save</button>
            <button type="button" onClick={() => setEdit(false)} className="text-xs text-slate-500">Cancel</button>
          </div>
        </div>
      ) : (
        <div className="flex items-start justify-between gap-3">
          <div className="text-sm">
            <p className="font-medium text-navy">{card.front}</p>
            <p className="text-slate-600">{card.back}</p>
            {card.topic && <span className="mt-1 inline-block rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-500">{card.topic} · {card.difficulty}</span>}
          </div>
          <div className="flex shrink-0 gap-2">
            <button type="button" onClick={() => setEdit(true)} className="text-slate-400 hover:text-primary"><FiEdit2 size={15} /></button>
            <button type="button" onClick={remove} className="text-slate-400 hover:text-red-500"><FiTrash2 size={15} /></button>
          </div>
        </div>
      )}
    </div>
  )
}

function McqsEditor({ lectureId, mcqs, onChanged }) {
  const blank = { question: '', option_a: '', option_b: '', option_c: '', option_d: '', option_e: '', correct_option: 'A', explanation: '', topic: '', difficulty: 'moderate' }
  const [adding, setAdding] = useState(blank)
  const [showAdd, setShowAdd] = useState(false)

  const add = async () => {
    if (!adding.question.trim() || !adding.option_a.trim() || !adding.option_b.trim()) return
    await aiService.addMcq(lectureId, adding)
    setAdding(blank); setShowAdd(false); onChanged()
  }

  return (
    <div className="space-y-3">
      <button type="button" onClick={() => setShowAdd(!showAdd)} className="btn-secondary text-sm"><FiPlus /> Add MCQ manually</button>
      {showAdd && (
        <div className="rounded-xl bg-slate-50 p-4 space-y-2">
          <textarea placeholder="Question / clinical vignette" value={adding.question} onChange={(e) => setAdding({ ...adding, question: e.target.value })} rows={2} className="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" />
          {['a', 'b', 'c', 'd', 'e'].map((k) => (
            <input key={k} placeholder={`Option ${k.toUpperCase()}`} value={adding[`option_${k}`]} onChange={(e) => setAdding({ ...adding, [`option_${k}`]: e.target.value })} className="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" />
          ))}
          <div className="flex flex-wrap gap-2">
            <select value={adding.correct_option} onChange={(e) => setAdding({ ...adding, correct_option: e.target.value })} className="rounded-lg border border-slate-200 px-3 py-2 text-sm">
              {['A', 'B', 'C', 'D', 'E'].map((o) => <option key={o} value={o}>Correct: {o}</option>)}
            </select>
            <select value={adding.difficulty} onChange={(e) => setAdding({ ...adding, difficulty: e.target.value })} className="rounded-lg border border-slate-200 px-3 py-2 text-sm">
              {DIFFICULTIES.map((d) => <option key={d} value={d}>{d}</option>)}
            </select>
            <input placeholder="Topic" value={adding.topic} onChange={(e) => setAdding({ ...adding, topic: e.target.value })} className="flex-1 rounded-lg border border-slate-200 px-3 py-2 text-sm" />
          </div>
          <textarea placeholder="Explanation" value={adding.explanation} onChange={(e) => setAdding({ ...adding, explanation: e.target.value })} rows={2} className="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" />
          <button type="button" onClick={add} className="btn-primary text-sm"><FiSave /> Save MCQ</button>
        </div>
      )}

      {mcqs.length === 0 ? (
        <p className="text-sm text-slate-400">No MCQs yet. Generate some or add manually.</p>
      ) : mcqs.map((q, i) => <McqRow key={q.id} index={i + 1} mcq={q} onChanged={onChanged} />)}
    </div>
  )
}

function McqRow({ index, mcq, onChanged }) {
  const [edit, setEdit] = useState(false)
  const [q, setQ] = useState(mcq)
  useEffect(() => setQ(mcq), [mcq])

  const save = async () => {
    await aiService.updateMcq(mcq.id, {
      question: q.question, option_a: q.option_a, option_b: q.option_b, option_c: q.option_c,
      option_d: q.option_d, option_e: q.option_e, correct_option: q.correct_option,
      explanation: q.explanation, topic: q.topic, difficulty: q.difficulty,
    })
    setEdit(false); onChanged()
  }
  const remove = async () => { await aiService.deleteMcq(mcq.id); onChanged() }

  const opts = [['A', mcq.option_a], ['B', mcq.option_b], ['C', mcq.option_c], ['D', mcq.option_d], ['E', mcq.option_e]].filter(([, v]) => v)

  return (
    <div className="rounded-xl border border-slate-100 p-3">
      {edit ? (
        <div className="space-y-2">
          <textarea value={q.question} onChange={(e) => setQ({ ...q, question: e.target.value })} rows={2} className="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-sm" />
          {['a', 'b', 'c', 'd', 'e'].map((k) => (
            <input key={k} placeholder={`Option ${k.toUpperCase()}`} value={q[`option_${k}`] || ''} onChange={(e) => setQ({ ...q, [`option_${k}`]: e.target.value })} className="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-sm" />
          ))}
          <div className="flex flex-wrap gap-2">
            <select value={q.correct_option} onChange={(e) => setQ({ ...q, correct_option: e.target.value })} className="rounded-lg border border-slate-200 px-2 py-1.5 text-sm">
              {['A', 'B', 'C', 'D', 'E'].map((o) => <option key={o} value={o}>Correct: {o}</option>)}
            </select>
            <select value={q.difficulty} onChange={(e) => setQ({ ...q, difficulty: e.target.value })} className="rounded-lg border border-slate-200 px-2 py-1.5 text-sm">
              {DIFFICULTIES.map((d) => <option key={d} value={d}>{d}</option>)}
            </select>
          </div>
          <textarea placeholder="Explanation" value={q.explanation || ''} onChange={(e) => setQ({ ...q, explanation: e.target.value })} rows={2} className="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-sm" />
          <div className="flex gap-2">
            <button type="button" onClick={save} className="btn-primary text-xs"><FiSave /> Save</button>
            <button type="button" onClick={() => setEdit(false)} className="text-xs text-slate-500">Cancel</button>
          </div>
        </div>
      ) : (
        <div className="flex items-start justify-between gap-3">
          <div className="text-sm">
            <p className="font-medium text-navy">{index}. {mcq.question}</p>
            <ul className="mt-1 space-y-0.5">
              {opts.map(([label, text]) => (
                <li key={label} className={label === mcq.correct_option ? 'font-semibold text-emerald-600' : 'text-slate-600'}>
                  {label}. {text} {label === mcq.correct_option && '✓'}
                </li>
              ))}
            </ul>
            {mcq.explanation && <p className="mt-1 text-xs text-slate-500"><span className="font-medium">Explanation:</span> {mcq.explanation}</p>}
            <span className="mt-1 inline-block rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-500">{mcq.topic || 'General'} · {mcq.difficulty}</span>
          </div>
          <div className="flex shrink-0 gap-2">
            <button type="button" onClick={() => setEdit(true)} className="text-slate-400 hover:text-primary"><FiEdit2 size={15} /></button>
            <button type="button" onClick={remove} className="text-slate-400 hover:text-red-500"><FiTrash2 size={15} /></button>
          </div>
        </div>
      )}
    </div>
  )
}

function ChallengeEditor({ lectureId, challenge, mcqCount, onSaved }) {
  const [form, setForm] = useState({
    enabled: challenge?.enabled ? true : false,
    mcqs_per_day: challenge?.mcqs_per_day || 10,
    start_date: (challenge?.start_date || new Date().toISOString().slice(0, 10)).slice(0, 10),
  })
  const [saving, setSaving] = useState(false)
  useEffect(() => {
    setForm({
      enabled: challenge?.enabled ? true : false,
      mcqs_per_day: challenge?.mcqs_per_day || 10,
      start_date: (challenge?.start_date || new Date().toISOString().slice(0, 10)).slice(0, 10),
    })
  }, [challenge])

  const save = async () => {
    setSaving(true)
    try {
      await aiService.saveChallenge(lectureId, {
        enabled: form.enabled ? 1 : 0,
        mcqs_per_day: Number(form.mcqs_per_day),
        start_date: form.start_date,
      })
      onSaved()
    } finally { setSaving(false) }
  }

  const days = form.mcqs_per_day > 0 ? Math.ceil(mcqCount / form.mcqs_per_day) : 0

  return (
    <div className="max-w-lg space-y-4">
      <p className="text-sm text-slate-500">Release approved MCQs to students gradually as a daily challenge.</p>
      <label className="flex items-center gap-2 text-sm text-slate-700">
        <input type="checkbox" checked={form.enabled} onChange={(e) => setForm({ ...form, enabled: e.target.checked })} />
        Enable Daily MCQ Challenge for this lecture
      </label>
      <div className="grid gap-4 sm:grid-cols-2">
        <div>
          <label className="mb-1 block text-xs font-medium text-slate-500">MCQs per day</label>
          <input type="number" min="1" value={form.mcqs_per_day} onChange={(e) => setForm({ ...form, mcqs_per_day: e.target.value })} className="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" />
        </div>
        <div>
          <label className="mb-1 block text-xs font-medium text-slate-500">Start date</label>
          <input type="date" value={form.start_date} onChange={(e) => setForm({ ...form, start_date: e.target.value })} className="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" />
        </div>
      </div>
      <p className="text-xs text-slate-400">{mcqCount} MCQs → about {days} day(s) of challenges.</p>
      <button type="button" onClick={save} disabled={saving} className="btn-primary text-sm"><FiSave /> Save challenge settings</button>
    </div>
  )
}
