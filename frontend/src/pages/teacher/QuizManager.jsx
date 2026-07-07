import { useCallback, useEffect, useState } from 'react'
import { FiPlus, FiEdit2, FiTrash2, FiCopy, FiEye, FiHelpCircle, FiSend, FiList, FiBarChart2 } from 'react-icons/fi'
import { quizService } from '../../services/api'
import { useToast } from '../../context/ToastContext'
import { useConfirm } from '../../context/ConfirmContext'
import { Modal, Button, Badge, EmptyState } from '../../components/ui'
import QuizWordImport from '../../components/dashboard/QuizWordImport'
import QuizResultsPanel from '../../components/dashboard/QuizResultsPanel'

const empty = {
  title: '', description: '', duration_minutes: 30, passing_marks: 50, total_marks: 100,
  max_attempts: 1, shuffle_questions: 0, show_leaderboard: 0, auto_evaluate: 1, show_review: 1,
}
const STATUS_TONE = { draft: 'neutral', published: 'success', closed: 'warning' }

export default function QuizManager({ courseId }) {
  const toast = useToast()
  const confirm = useConfirm()
  const [items, setItems] = useState([])
  const [loading, setLoading] = useState(true)
  const [editing, setEditing] = useState(null)
  const [form, setForm] = useState(empty)
  const [saving, setSaving] = useState(false)
  const [manageFor, setManageFor] = useState(null)
  const [resultsFor, setResultsFor] = useState(null)
  const [preview, setPreview] = useState(null)

  const load = useCallback(() => {
    setLoading(true)
    quizService.list(courseId).then(({ data }) => setItems(data.data || [])).finally(() => setLoading(false))
  }, [courseId])

  useEffect(() => { load() }, [load])

  const openNew = () => { setForm(empty); setEditing({}) }
  const openEdit = (q) => {
    setForm({
      title: q.title || '', description: q.description || '',
      duration_minutes: q.duration_minutes || 30, passing_marks: q.passing_marks || 50,
      total_marks: q.total_marks || 100, max_attempts: q.max_attempts || 1,
      shuffle_questions: q.shuffle_questions ? 1 : 0, show_leaderboard: q.show_leaderboard ? 1 : 0,
      auto_evaluate: q.auto_evaluate !== 0 && q.auto_evaluate !== '0' ? 1 : 0,
      show_review: q.show_review !== 0 && q.show_review !== '0' ? 1 : 0,
    })
    setEditing(q)
  }

  const save = async () => {
    const title = form.title.trim()
    if (title.length < 3) return toast.error('Title must be at least 3 characters')
    if (!courseId) return toast.error('Invalid course — go back and open the course again')

    const payload = {
      title,
      description: form.description?.trim() || '',
      course_id: courseId,
      duration_minutes: Number(form.duration_minutes) || 30,
      passing_marks: Number(form.passing_marks) || 50,
      total_marks: Number(form.total_marks) || 100,
      max_attempts: Number(form.max_attempts) || 1,
      shuffle_questions: form.shuffle_questions ? 1 : 0,
      show_leaderboard: form.show_leaderboard ? 1 : 0,
      auto_evaluate: form.auto_evaluate ? 1 : 0,
      show_review: form.show_review ? 1 : 0,
    }

    setSaving(true)
    try {
      if (editing.id) {
        await quizService.update(editing.id, payload)
        toast.success('Quiz updated successfully')
      } else {
        await quizService.create(payload)
        toast.success('Quiz created successfully')
      }
      setEditing(null)
      load()
    } catch (err) {
      const fieldErrors = err.response?.data?.errors
      if (fieldErrors) {
        const msg = Object.entries(fieldErrors).map(([k, v]) => `${k.replace(/_/g, ' ')}: ${v[0]}`).join(' · ')
        toast.error(msg)
      } else {
        toast.error(err.response?.data?.message || 'Could not save quiz')
      }
    } finally {
      setSaving(false)
    }
  }

  const remove = async (q) => {
    if (!(await confirm({ title: 'Delete quiz', message: `Are you sure you want to delete "${q.title}"? All questions and attempts will be removed.` }))) return
    try { await quizService.remove(q.id); toast.success('Deleted successfully'); load() }
    catch { toast.error('Could not delete quiz') }
  }

  const duplicate = async (q) => {
    try { await quizService.duplicate(q.id); toast.success('Quiz duplicated'); load() }
    catch { toast.error('Could not duplicate quiz') }
  }

  const togglePublish = async (q) => {
    const next = q.status === 'published' ? 'draft' : 'published'
    try { await quizService.setStatus(q.id, next); toast.success(next === 'published' ? 'Quiz published' : 'Quiz unpublished'); load() }
    catch { toast.error('Could not update status') }
  }

  return (
    <div className="space-y-4">
      <div className="flex items-center justify-between">
        <h3 className="font-display text-lg font-bold text-navy">Quizzes</h3>
        <Button icon={FiPlus} onClick={openNew}>New quiz</Button>
      </div>

      {loading ? (
        <p className="text-slate-400">Loading quizzes…</p>
      ) : items.length === 0 ? (
        <EmptyState icon={FiHelpCircle} title="No quizzes yet" description="Create a quiz and add questions for your students."
          action={<Button icon={FiPlus} onClick={openNew}>New quiz</Button>} />
      ) : (
        <div className="space-y-3">
          {items.map((q) => (
            <div key={q.id} className="rounded-2xl border border-slate-100 bg-white p-5 shadow-soft">
              <div className="flex flex-wrap items-start justify-between gap-3">
                <div>
                  <div className="flex items-center gap-2">
                    <p className="font-semibold text-navy">{q.title}</p>
                    <Badge tone={STATUS_TONE[q.status] || 'neutral'}>{q.status}</Badge>
                  </div>
                  <p className="mt-1 text-xs text-slate-400">{q.duration_minutes} min · Pass {q.passing_marks}% · {q.max_attempts} attempt(s) · {q.question_count || 0} questions</p>
                  {q.status === 'draft' && (
                    <p className="mt-1 text-xs font-medium text-amber-700">
                      Hidden from students until you add questions (auto-publishes) or click Publish
                    </p>
                  )}
                </div>
                <div className="flex flex-wrap items-center gap-2">
                  <Button size="sm" variant="secondary" icon={FiList} onClick={() => setManageFor(q)}>Questions</Button>
                  <Button size="sm" variant="secondary" icon={FiBarChart2} onClick={() => setResultsFor(q)}>Results</Button>
                  <Button size="sm" variant="outline" icon={FiEye} onClick={() => setPreview(q)}>Preview</Button>
                  <Button size="sm" variant={q.status === 'published' ? 'outline' : 'success'} icon={FiSend} onClick={() => togglePublish(q)}>
                    {q.status === 'published' ? 'Unpublish' : 'Publish'}
                  </Button>
                  <button type="button" onClick={() => duplicate(q)} className="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-primary" aria-label="Duplicate"><FiCopy size={16} /></button>
                  <button type="button" onClick={() => openEdit(q)} className="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-primary" aria-label="Edit"><FiEdit2 size={16} /></button>
                  <button type="button" onClick={() => remove(q)} className="rounded-lg p-2 text-slate-400 hover:bg-red-50 hover:text-red-500" aria-label="Delete"><FiTrash2 size={16} /></button>
                </div>
              </div>
            </div>
          ))}
        </div>
      )}

      <Modal open={!!editing} onClose={() => !saving && setEditing(null)} title={editing?.id ? 'Edit quiz' : 'New quiz'} size="lg"
        footer={<>
          <Button variant="outline" onClick={() => setEditing(null)} disabled={saving}>Cancel</Button>
          <Button onClick={save} loading={saving}>{editing?.id ? 'Save changes' : 'Create quiz'}</Button>
        </>}>
        <div className="space-y-4">
          <div>
            <label className="text-sm font-medium text-slate-600">Title *</label>
            <input value={form.title} onChange={(e) => setForm((f) => ({ ...f, title: e.target.value }))} className="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" />
          </div>
          <div>
            <label className="text-sm font-medium text-slate-600">Description</label>
            <textarea value={form.description} onChange={(e) => setForm((f) => ({ ...f, description: e.target.value }))} rows={2} className="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" />
          </div>
          <div className="grid gap-4 sm:grid-cols-2">
            <Num label="Duration (minutes)" value={form.duration_minutes} onChange={(v) => setForm((f) => ({ ...f, duration_minutes: v }))} />
            <Num label="Max attempts" value={form.max_attempts} onChange={(v) => setForm((f) => ({ ...f, max_attempts: v }))} />
            <Num label="Passing %" value={form.passing_marks} onChange={(v) => setForm((f) => ({ ...f, passing_marks: v }))} />
            <Num label="Total marks" value={form.total_marks} onChange={(v) => setForm((f) => ({ ...f, total_marks: v }))} />
          </div>
          <div className="flex flex-wrap gap-4">
            <Check label="Shuffle questions" checked={form.shuffle_questions} onChange={(v) => setForm((f) => ({ ...f, shuffle_questions: v }))} />
            <Check label="Show leaderboard" checked={form.show_leaderboard} onChange={(v) => setForm((f) => ({ ...f, show_leaderboard: v }))} />
            <Check label="Auto evaluate" checked={form.auto_evaluate} onChange={(v) => setForm((f) => ({ ...f, auto_evaluate: v }))} />
            <Check label="Show review to students" checked={form.show_review} onChange={(v) => setForm((f) => ({ ...f, show_review: v }))} />
          </div>
        </div>
      </Modal>

      {manageFor && <QuestionsModal quiz={manageFor} onClose={() => { setManageFor(null); load() }} />}

      {resultsFor && <QuizResultsPanel quiz={resultsFor} onClose={() => setResultsFor(null)} />}

      <Modal open={!!preview} onClose={() => setPreview(null)} title={preview?.title} subtitle="Quiz preview" size="lg">
        {preview && <QuizPreview quizId={preview.id} />}
      </Modal>
    </div>
  )
}

function Num({ label, value, onChange }) {
  return (
    <div>
      <label className="text-sm font-medium text-slate-600">{label}</label>
      <input type="number" min="0" value={value} onChange={(e) => onChange(Number(e.target.value) || 0)} className="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" />
    </div>
  )
}

function Check({ label, checked, onChange }) {
  return (
    <label className="inline-flex items-center gap-2 text-sm text-slate-600">
      <input type="checkbox" checked={!!checked} onChange={(e) => onChange(e.target.checked ? 1 : 0)} className="h-4 w-4 rounded border-slate-300" />
      {label}
    </label>
  )
}

function QuizPreview({ quizId }) {
  const [quiz, setQuiz] = useState(null)
  useEffect(() => { quizService.get(quizId).then(({ data }) => setQuiz(data.data)).catch(() => {}) }, [quizId])
  if (!quiz) return <p className="text-slate-400">Loading…</p>
  const questions = quiz.questions || []
  if (!questions.length) return <p className="text-sm text-slate-400">No questions added yet.</p>
  return (
    <div className="space-y-4">
      {questions.map((q, i) => (
        <div key={q.id} className="rounded-xl border border-slate-100 p-4">
          <p className="text-sm font-medium text-navy">{i + 1}. {q.question_text}</p>
          <div className="mt-2 space-y-1.5">
            {(q.options || []).map((o) => (
              <div key={o.id} className={`rounded-lg px-3 py-1.5 text-sm ${o.is_correct ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-50 text-slate-600'}`}>{o.option_text}</div>
            ))}
          </div>
        </div>
      ))}
    </div>
  )
}

function QuestionsModal({ quiz, onClose }) {
  const toast = useToast()
  const confirm = useConfirm()
  const [mode, setMode] = useState('manual')
  const [questions, setQuestions] = useState([])
  const [loading, setLoading] = useState(true)
  const [text, setText] = useState('')
  const [opts, setOpts] = useState([{ option_text: '', is_correct: true }, { option_text: '', is_correct: false }, { option_text: '', is_correct: false }, { option_text: '', is_correct: false }])
  const [explanation, setExplanation] = useState('')
  const [saving, setSaving] = useState(false)

  const load = useCallback(() => {
    setLoading(true)
    quizService.get(quiz.id).then(({ data }) => setQuestions(data.data.questions || [])).finally(() => setLoading(false))
  }, [quiz.id])
  useEffect(() => { load() }, [load])

  const addQuestion = async () => {
    if (!text.trim()) return toast.error('Question text is required')
    const filled = opts.filter((o) => o.option_text.trim())
    if (filled.length < 2) return toast.error('Add at least two options')
    if (!filled.some((o) => o.is_correct)) return toast.error('Mark the correct option')
    setSaving(true)
    try {
      await quizService.addQuestion({ quiz_id: quiz.id, question_type: 'single_choice', question_text: text.trim(), explanation, options: filled })
      toast.success('Question added')
      setText(''); setExplanation('')
      setOpts([{ option_text: '', is_correct: true }, { option_text: '', is_correct: false }, { option_text: '', is_correct: false }, { option_text: '', is_correct: false }])
      load()
    } catch { toast.error('Could not add question') }
    finally { setSaving(false) }
  }

  const removeQuestion = async (q) => {
    if (!(await confirm({ title: 'Delete question', message: 'Are you sure you want to delete this question?' }))) return
    try { await quizService.deleteQuestion(q.id); toast.success('Deleted successfully'); load() }
    catch { toast.error('Could not delete question') }
  }

  return (
    <Modal open onClose={onClose} title="Manage questions" subtitle={quiz.title} size="xl">
      <div className="mb-4 flex gap-2 border-b border-slate-100 pb-3">
        <button type="button" onClick={() => setMode('manual')}
          className={`rounded-lg px-3 py-1.5 text-sm font-medium ${mode === 'manual' ? 'bg-primary/10 text-primary' : 'text-slate-500 hover:bg-slate-50'}`}>
          Manual
        </button>
        <button type="button" onClick={() => setMode('import')}
          className={`rounded-lg px-3 py-1.5 text-sm font-medium ${mode === 'import' ? 'bg-primary/10 text-primary' : 'text-slate-500 hover:bg-slate-50'}`}>
          Bulk import (Word)
        </button>
      </div>

      {mode === 'import' ? (
        <QuizWordImport quizId={quiz.id} onImported={load} />
      ) : (
      <div className="space-y-4">
        {loading ? <p className="text-slate-400">Loading…</p> : questions.length === 0 ? (
          <p className="text-sm text-slate-400">No questions yet. Add your first below.</p>
        ) : (
          <div className="space-y-2">
            {questions.map((q, i) => (
              <div key={q.id} className="flex items-start justify-between gap-3 rounded-xl border border-slate-100 p-3">
                <div>
                  <p className="text-sm font-medium text-navy">{i + 1}. {q.question_text}</p>
                  <div className="mt-1 flex flex-wrap gap-1.5">
                    {(q.options || []).map((o) => (
                      <span key={o.id} className={`rounded px-2 py-0.5 text-xs ${o.is_correct ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500'}`}>{o.option_text}</span>
                    ))}
                  </div>
                </div>
                <button type="button" onClick={() => removeQuestion(q)} className="rounded-lg p-1.5 text-slate-400 hover:bg-red-50 hover:text-red-500"><FiTrash2 size={15} /></button>
              </div>
            ))}
          </div>
        )}

        <div className="rounded-xl border border-slate-200 bg-slate-50/60 p-4">
          <h4 className="text-sm font-semibold text-navy">Add MCQ question</h4>
          <textarea value={text} onChange={(e) => setText(e.target.value)} rows={2} placeholder="Question text" className="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" />
          <div className="mt-2 space-y-2">
            {opts.map((o, i) => (
              <div key={i} className="flex items-center gap-2">
                <input type="radio" name="correct" checked={o.is_correct} onChange={() => setOpts((arr) => arr.map((x, j) => ({ ...x, is_correct: j === i })))} className="h-4 w-4" />
                <input value={o.option_text} onChange={(e) => setOpts((arr) => arr.map((x, j) => j === i ? { ...x, option_text: e.target.value } : x))}
                  placeholder={`Option ${i + 1}`} className="flex-1 rounded-lg border border-slate-200 px-3 py-1.5 text-sm" />
              </div>
            ))}
          </div>
          <input value={explanation} onChange={(e) => setExplanation(e.target.value)} placeholder="Explanation (optional)" className="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" />
          <Button className="mt-3" icon={FiPlus} onClick={addQuestion} loading={saving}>Add question</Button>
        </div>
      </div>
      )}
    </Modal>
  )
}
