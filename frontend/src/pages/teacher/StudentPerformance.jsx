import { useEffect, useState } from 'react'
import {
  FiArrowLeft, FiSearch, FiUsers, FiCheckCircle, FiXCircle,
  FiVideo, FiCalendar, FiAlertCircle, FiTarget, FiClipboard,
  FiClock, FiPlay, FiEye, FiDownload,
} from 'react-icons/fi'
import {
  studentPerformanceService,
  quizService,
  assignmentService,
  downloadMedia,
} from '../../services/api'
import { formatWatchTime } from '../../utils/videoTrackingClient'
import { useToast } from '../../context/ToastContext'
import { Modal, Button } from '../../components/ui'
import ViewFileButton from '../../components/dashboard/ViewFileButton'

function parseSelectedIds(selected) {
  if (Array.isArray(selected)) return selected.map(Number)
  if (typeof selected === 'string' && selected.trim()) {
    try {
      const parsed = JSON.parse(selected)
      return Array.isArray(parsed) ? parsed.map(Number) : []
    } catch {
      return []
    }
  }
  return []
}

function QuizAttemptReviewModal({ open, loading, data, onClose }) {
  const [wrongOnly, setWrongOnly] = useState(false)
  const attempt = data?.attempt
  const review = data?.review || []
  const right = review.filter((q) => q.is_correct === true).length
  const wrong = review.filter((q) => q.is_correct === false).length
  const visible = wrongOnly ? review.filter((q) => q.is_correct === false) : review

  useEffect(() => {
    if (!open) setWrongOnly(false)
  }, [open])

  return (
    <Modal
      open={open}
      onClose={onClose}
      title="Quiz attempt review"
      subtitle={data?.quiz?.title || 'Question-by-question results'}
      size="xl"
      footer={<Button variant="outline" onClick={onClose}>Close</Button>}
    >
      {loading && !data ? (
        <p className="py-8 text-center text-sm text-slate-400">Loading attempt…</p>
      ) : data ? (
        <div className="space-y-4">
          <div className="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-100 bg-slate-50 px-4 py-3 text-sm">
            <div className="flex flex-wrap items-center gap-2">
              <span className="font-semibold text-navy">{data.student?.student_name || 'Student'}</span>
              <span className="text-slate-300">·</span>
              <span className="text-slate-600">
                Score {attempt?.score ?? '—'} ({attempt?.percentage ?? '—'}%)
              </span>
              <span className={`font-semibold ${attempt?.passed ? 'text-emerald-600' : 'text-rose-600'}`}>
                {attempt?.passed ? 'Passed' : 'Not passed'}
              </span>
              <span className="rounded-md bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700">{right} right</span>
              <span className="rounded-md bg-rose-50 px-2 py-0.5 text-xs font-semibold text-rose-700">{wrong} wrong</span>
            </div>
            <label className="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-sm">
              <input
                type="checkbox"
                className="rounded border-slate-300 text-primary focus:ring-primary"
                checked={wrongOnly}
                onChange={(e) => setWrongOnly(e.target.checked)}
              />
              Show wrong only
            </label>
          </div>

          <div className="max-h-[55vh] space-y-3 overflow-y-auto pr-1">
            {visible.map((q, i) => {
              const selected = parseSelectedIds(q.selected)
              const originalIndex = review.indexOf(q) + 1
              const tone = q.is_correct === true
                ? 'border-l-4 border-l-emerald-500 border-slate-100'
                : q.is_correct === false
                  ? 'border-l-4 border-l-rose-500 border-slate-100'
                  : 'border-l-4 border-l-slate-200 border-slate-100'
              return (
                <div key={`${q.question_id}-${i}`} className={`rounded-xl border bg-white p-3.5 ${tone}`}>
                  <div className="flex flex-wrap items-start justify-between gap-2">
                    <p className="text-sm font-semibold text-navy">
                      {originalIndex}. {q.question_text}
                    </p>
                    <span className={`shrink-0 rounded-md px-2 py-0.5 text-[11px] font-bold uppercase tracking-wide ${
                      q.is_correct === true
                        ? 'bg-emerald-50 text-emerald-700'
                        : q.is_correct === false
                          ? 'bg-rose-50 text-rose-700'
                          : 'bg-slate-100 text-slate-500'
                    }`}>
                      {q.is_correct === true ? 'Right' : q.is_correct === false ? 'Wrong' : '—'}
                    </span>
                  </div>
                  <div className="mt-2.5 space-y-1.5">
                    {(q.options || []).map((o) => {
                      const isSelected = selected.includes(Number(o.id))
                      let cls = 'rounded-lg border border-slate-100 bg-slate-50/80 px-3 py-2 text-xs text-slate-600'
                      if (o.is_correct) cls = 'rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-medium text-emerald-800'
                      else if (isSelected) cls = 'rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-medium text-rose-800'
                      return (
                        <div key={o.id} className={cls}>
                          {o.option_text}
                          {o.is_correct ? ' ✓ Correct' : ''}
                          {isSelected && !o.is_correct ? ' · Student chose' : ''}
                          {isSelected && o.is_correct ? ' · Student chose' : ''}
                        </div>
                      )
                    })}
                    {q.text_answer && (
                      <p className="rounded-lg bg-slate-50 px-3 py-2 text-xs text-slate-600">
                        Written answer: {q.text_answer}
                      </p>
                    )}
                  </div>
                  {q.explanation && (
                    <p className="mt-2 text-xs text-slate-500">
                      <span className="font-semibold text-slate-600">Explanation: </span>
                      {q.explanation}
                    </p>
                  )}
                </div>
              )
            })}
            {!visible.length && (
              <p className="py-6 text-center text-sm text-slate-400">
                {wrongOnly ? 'No wrong answers in this attempt.' : 'No question answers found for this attempt.'}
              </p>
            )}
          </div>
        </div>
      ) : null}
    </Modal>
  )
}

function AssignmentDetailModal({ open, loading, data, onClose }) {
  const assignment = data?.assignment
  const submission = data?.submission
  const listState = data?.listState
  const files = submission?.files?.length
    ? submission.files
    : submission?.file_path
      ? [{ id: 0, title: submission.original_filename || 'Uploaded file', file_path: submission.file_path }]
      : []

  return (
    <Modal
      open={open}
      onClose={onClose}
      title="Assignment overview"
      subtitle={assignment?.title || listState?.title || 'Details'}
      size="lg"
      footer={<Button variant="outline" onClick={onClose}>Close</Button>}
    >
      {loading && !data ? (
        <p className="py-8 text-center text-sm text-slate-400">Loading assignment…</p>
      ) : (
        <div className="space-y-4">
          <div className="rounded-2xl border border-slate-100 bg-slate-50/80 p-4">
            <div className="flex flex-wrap items-center gap-2">
              <StatusBadge state={listState?.state || submission?.status || 'pending'} />
              {assignment?.course_title && (
                <span className="text-xs text-slate-500">{assignment.course_title}</span>
              )}
            </div>
            <h4 className="mt-2 font-display text-lg font-bold text-navy">
              {assignment?.title || listState?.title}
            </h4>
            <div className="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-500">
              {(assignment?.due_date || listState?.due_date) && (
                <span>Due {new Date(assignment?.due_date || listState.due_date).toLocaleString()}</span>
              )}
              {assignment?.max_marks != null && <span>Max marks {assignment.max_marks}</span>}
              {assignment?.assignment_type && <span className="capitalize">{assignment.assignment_type.replace(/_/g, ' ')}</span>}
            </div>
          </div>

          {(assignment?.description || assignment?.instructions) && (
            <div className="space-y-3">
              {assignment.description && (
                <div>
                  <p className="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Description</p>
                  <p className="mt-1 whitespace-pre-wrap text-sm text-slate-700">{assignment.description}</p>
                </div>
              )}
              {assignment.instructions && (
                <div>
                  <p className="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Instructions</p>
                  <p className="mt-1 whitespace-pre-wrap text-sm text-slate-700">{assignment.instructions}</p>
                </div>
              )}
            </div>
          )}

          {(assignment?.attachments || []).length > 0 && (
            <div>
              <p className="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Teacher attachments</p>
              <ul className="mt-2 space-y-1.5">
                {assignment.attachments.map((att) => (
                  <li key={att.id || att.file_path} className="flex flex-wrap items-center gap-3 text-xs">
                    <span className="font-medium text-slate-600">{att.title || 'Attachment'}</span>
                    <ViewFileButton
                      title={att.title}
                      filePath={att.file_path}
                      className="inline-flex items-center gap-1 text-primary hover:underline"
                      size={13}
                    />
                  </li>
                ))}
              </ul>
            </div>
          )}

          <div className="rounded-2xl border border-slate-100 p-4">
            <p className="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Student submission</p>
            {!submission ? (
              <p className="mt-2 text-sm text-slate-400">
                {listState?.state === 'overdue'
                  ? 'Not submitted — overdue.'
                  : 'No submission from this student yet.'}
              </p>
            ) : (
              <div className="mt-2 space-y-3">
                <div className="flex flex-wrap gap-x-4 gap-y-1 text-sm">
                  <span className="text-slate-600">
                    Status: <strong className="capitalize text-navy">{submission.status}</strong>
                  </span>
                  {submission.submitted_at && (
                    <span className="text-slate-500">
                      Submitted {new Date(submission.submitted_at).toLocaleString()}
                    </span>
                  )}
                  {(submission.marks != null || listState?.marks != null) && (
                    <span className="font-semibold text-navy">
                      Marks {submission.marks ?? listState.marks}
                      {assignment?.max_marks != null ? ` / ${assignment.max_marks}` : ''}
                      {submission.percentage != null ? ` (${submission.percentage}%)` : ''}
                    </span>
                  )}
                </div>
                {submission.remarks && (
                  <p className="rounded-xl bg-amber-50 px-3 py-2 text-xs text-amber-900">
                    <span className="font-semibold">Remarks: </span>{submission.remarks}
                  </p>
                )}
                {submission.submission_text && (
                  <p className="whitespace-pre-wrap rounded-xl bg-slate-50 px-3 py-2 text-sm text-slate-700">
                    {submission.submission_text}
                  </p>
                )}
                {files.length > 0 ? (
                  <div className="space-y-1.5">
                    {files.map((f) => (
                      <div key={f.id || f.file_path} className="flex flex-wrap items-center gap-2 text-xs">
                        <span className="font-medium text-slate-500">{f.title || f.original_filename || 'File'}</span>
                        <ViewFileButton
                          title={f.title}
                          filePath={f.file_path}
                          className="inline-flex items-center gap-1 text-primary hover:underline"
                        />
                        <button
                          type="button"
                          onClick={() => downloadMedia(f.file_path, f.title || f.original_filename)}
                          className="inline-flex items-center gap-1 text-slate-500 hover:underline"
                        >
                          <FiDownload size={14} /> Download
                        </button>
                      </div>
                    ))}
                  </div>
                ) : (
                  !submission.submission_text && (
                    <p className="text-xs text-slate-400">No files attached to this submission.</p>
                  )
                )}
              </div>
            )}
          </div>
        </div>
      )}
    </Modal>
  )
}

function StatChip({ label, value, tone = 'slate' }) {
  const tones = {
    slate: 'bg-slate-50 text-slate-700',
    green: 'bg-emerald-50 text-emerald-700',
    amber: 'bg-amber-50 text-amber-700',
    red: 'bg-red-50 text-red-700',
    blue: 'bg-sky-50 text-sky-700',
  }
  return (
    <span className={`inline-flex items-center gap-1 rounded-lg px-2 py-1 text-[11px] font-semibold ${tones[tone] || tones.slate}`}>
      <span className="opacity-70 font-normal">{label}</span> {value}
    </span>
  )
}

function OverviewCard({ label, value, icon: Icon, hint, tone = 'teal' }) {
  const themes = {
    teal: { border: 'border-l-teal-500', icon: 'bg-teal-50 text-teal-600' },
    navy: { border: 'border-l-slate-700', icon: 'bg-slate-100 text-navy' },
    green: { border: 'border-l-emerald-500', icon: 'bg-emerald-50 text-emerald-600' },
    amber: { border: 'border-l-amber-500', icon: 'bg-amber-50 text-amber-600' },
    rose: { border: 'border-l-rose-500', icon: 'bg-rose-50 text-rose-600' },
    sky: { border: 'border-l-sky-500', icon: 'bg-sky-50 text-sky-600' },
  }
  const t = themes[tone] || themes.teal

  return (
    <div className={`rounded-xl border border-slate-100 border-l-4 bg-white p-4 shadow-card ${t.border}`}>
      <div className="flex items-start justify-between gap-3">
        <div className="min-w-0">
          <p className="text-[11px] font-semibold uppercase tracking-wide text-slate-400">{label}</p>
          <p className="mt-1.5 font-display text-2xl font-bold tabular-nums text-navy">{value}</p>
          {hint && <p className="mt-1 text-xs text-slate-500">{hint}</p>}
        </div>
        {Icon && (
          <div className={`grid h-9 w-9 shrink-0 place-items-center rounded-lg ${t.icon}`}>
            <Icon size={16} />
          </div>
        )}
      </div>
    </div>
  )
}

function Section({ title, subtitle, children, empty }) {
  return (
    <section className="mt-6 rounded-2xl border border-slate-100 bg-white p-5 shadow-soft">
      <div className="flex flex-wrap items-end justify-between gap-2">
        <div>
          <h3 className="font-display text-lg font-bold text-navy">{title}</h3>
          {subtitle && <p className="mt-0.5 text-xs text-slate-400">{subtitle}</p>}
        </div>
      </div>
      {empty ? <p className="mt-3 text-sm text-slate-400">{empty}</p> : <div className="mt-4">{children}</div>}
    </section>
  )
}

function ProgressBar({ value, tone = 'teal' }) {
  const pct = Math.max(0, Math.min(100, Number(value) || 0))
  const colors = {
    teal: 'bg-teal-500',
    green: 'bg-emerald-500',
    amber: 'bg-amber-400',
    red: 'bg-red-400',
    slate: 'bg-slate-300',
  }
  return (
    <div className="h-1.5 w-full overflow-hidden rounded-full bg-slate-100">
      <div className={`h-full rounded-full transition-all ${colors[tone] || colors.teal}`} style={{ width: `${pct}%` }} />
    </div>
  )
}

function StatusBadge({ state }) {
  const map = {
    completed: 'bg-emerald-50 text-emerald-700',
    watching: 'bg-sky-50 text-sky-700',
    not_started: 'bg-slate-100 text-slate-500',
    submitted: 'bg-sky-50 text-sky-700',
    graded: 'bg-emerald-50 text-emerald-700',
    pending: 'bg-amber-50 text-amber-700',
    overdue: 'bg-red-50 text-red-700',
  }
  const label = String(state || '—').replace(/_/g, ' ')
  return (
    <span className={`inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-semibold capitalize ${map[state] || map.not_started}`}>
      {label}
    </span>
  )
}

function MetricTile({ label, value, tone = 'slate', icon: Icon }) {
  const tones = {
    slate: 'from-slate-50 to-white border-slate-100 text-navy',
    green: 'from-emerald-50 to-white border-emerald-100 text-emerald-800',
    amber: 'from-amber-50 to-white border-amber-100 text-amber-800',
    red: 'from-red-50 to-white border-red-100 text-red-800',
    blue: 'from-sky-50 to-white border-sky-100 text-sky-800',
    teal: 'from-teal-50 to-white border-teal-100 text-teal-800',
  }
  return (
    <div className={`rounded-2xl border bg-gradient-to-br p-4 ${tones[tone] || tones.slate}`}>
      <div className="flex items-center justify-between gap-2">
        <p className="text-[10px] font-semibold uppercase tracking-wide opacity-60">{label}</p>
        {Icon && <Icon size={14} className="opacity-40" />}
      </div>
      <p className="mt-1 text-2xl font-bold tabular-nums">{value}</p>
    </div>
  )
}

function videoStatusTone(status) {
  if (status === 'completed') return 'green'
  if (status === 'watching') return 'amber'
  return 'slate'
}

export default function StudentPerformance() {
  const toast = useToast()
  const [courses, setCourses] = useState([])
  const [courseId, setCourseId] = useState('')
  const [q, setQ] = useState('')
  const [search, setSearch] = useState('')
  const [students, setStudents] = useState([])
  const [loading, setLoading] = useState(true)
  const [detail, setDetail] = useState(null)
  const [detailLoading, setDetailLoading] = useState(false)
  const [quizReview, setQuizReview] = useState(null)
  const [quizReviewLoading, setQuizReviewLoading] = useState(false)
  const [assignmentDetail, setAssignmentDetail] = useState(null)
  const [assignmentDetailLoading, setAssignmentDetailLoading] = useState(false)

  useEffect(() => {
    const t = setTimeout(() => setSearch(q.trim()), 300)
    return () => clearTimeout(t)
  }, [q])

  useEffect(() => {
    setLoading(true)
    studentPerformanceService
      .list({
        ...(courseId ? { course_id: courseId } : {}),
        ...(search ? { q: search } : {}),
      })
      .then(({ data: res }) => {
        setStudents(res.data?.students || [])
        setCourses(res.data?.courses || [])
      })
      .catch((err) => toast.error(err.response?.data?.message || err.message))
      .finally(() => setLoading(false))
  }, [courseId, search])

  const openStudent = async (id) => {
    setDetailLoading(true)
    setQuizReview(null)
    setAssignmentDetail(null)
    try {
      const { data: res } = await studentPerformanceService.show(id)
      setDetail(res.data)
    } catch (err) {
      toast.error(err.response?.data?.message || err.message)
    } finally {
      setDetailLoading(false)
    }
  }

  const openQuizAttempt = async (attemptId) => {
    setQuizReviewLoading(true)
    setQuizReview(null)
    try {
      const { data: res } = await quizService.teacherReview(attemptId)
      setQuizReview(res.data)
    } catch (err) {
      toast.error(err.response?.data?.message || 'Could not load quiz attempt')
    } finally {
      setQuizReviewLoading(false)
    }
  }

  const openAssignmentDetail = async (item) => {
    const studentId = detail?.student?.id
    setAssignmentDetailLoading(true)
    setAssignmentDetail({ listState: item, assignment: null, submission: null })
    try {
      const [asgRes, subsRes] = await Promise.all([
        assignmentService.get(item.id),
        assignmentService.submissions(item.id),
      ])
      const assignment = asgRes.data?.data || null
      const subs = subsRes.data?.data || []
      const submission = studentId
        ? subs.find((s) => Number(s.student_id) === Number(studentId)) || null
        : null
      setAssignmentDetail({ listState: item, assignment, submission })
    } catch (err) {
      toast.error(err.response?.data?.message || 'Could not load assignment')
      setAssignmentDetail(null)
    } finally {
      setAssignmentDetailLoading(false)
    }
  }

  if (detail || detailLoading) {
    const o = detail?.overview
    const s = detail?.student
    const asg = detail?.assignments || {}
    const va = detail?.video_analytics || {}
    const vs = va.summary || {}
    const videos = va.videos || []
    const qs = detail?.quizzes?.summary || {}
    const given = asg.given || 0
    const submitPct = given > 0 ? Math.round(((asg.submitted || 0) / given) * 100) : 0
    const videoPct = (vs.total_videos || 0) > 0
      ? Math.round(((vs.completed || 0) / vs.total_videos) * 100)
      : 0

    return (
      <div className="pb-10">
        <button
          type="button"
          className="mb-4 inline-flex items-center gap-1 text-sm font-semibold text-primary hover:underline"
          onClick={() => setDetail(null)}
        >
          <FiArrowLeft /> Back to all students
        </button>

        {detailLoading && <p className="text-center text-slate-400">Loading student profile…</p>}

        {detail && (
          <>
            <div className="flex flex-wrap items-start justify-between gap-4 rounded-xl border border-slate-100 bg-white p-5 shadow-card">
              <div>
                <h2 className="font-display text-2xl font-bold text-navy">{s.name}</h2>
                <p className="mt-0.5 text-sm text-slate-500">{s.email}</p>
                <div className="mt-2.5 flex flex-wrap gap-1.5">
                  {(detail.courses || []).map((c) => (
                    <span key={c.id} className="rounded-md bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600">
                      {c.title}
                    </span>
                  ))}
                </div>
              </div>
              <div className="flex gap-3">
                <div className="min-w-[88px] rounded-xl bg-emerald-50 px-4 py-3 text-center">
                  <p className="text-[10px] font-semibold uppercase tracking-wide text-emerald-600/80">Right</p>
                  <p className="mt-0.5 font-display text-2xl font-bold tabular-nums text-emerald-700">{o.right_answers ?? 0}</p>
                </div>
                <div className="min-w-[88px] rounded-xl bg-rose-50 px-4 py-3 text-center">
                  <p className="text-[10px] font-semibold uppercase tracking-wide text-rose-600/80">Mistakes</p>
                  <p className="mt-0.5 font-display text-2xl font-bold tabular-nums text-rose-700">{o.mistakes ?? o.wrong_answers ?? 0}</p>
                </div>
              </div>
            </div>

            <div className="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
              <OverviewCard
                label="Quiz average"
                value={`${o.quiz_avg_score ?? 0}%`}
                icon={FiTarget}
                tone="teal"
                hint={`${o.quiz_attempts ?? 0} attempts · ${o.quiz_answered ?? 0} answered`}
              />
              <OverviewCard
                label="Quiz right / wrong"
                value={`${o.quiz_correct ?? 0} / ${o.quiz_incorrect ?? 0}`}
                icon={FiCheckCircle}
                tone="green"
                hint={`${o.quiz_accuracy ?? 0}% accuracy`}
              />
              <OverviewCard
                label="MCQ right / wrong"
                value={`${o.mcq_correct ?? 0} / ${o.mcq_incorrect ?? 0}`}
                icon={FiXCircle}
                tone={(o.mcq_attempted ?? 0) > 0 ? 'amber' : 'navy'}
                hint={`${o.mcq_accuracy ?? 0}% · ${o.mcq_attempted ?? 0} practiced`}
              />
              <OverviewCard
                label="Mistakes"
                value={o.mistakes ?? o.wrong_answers ?? 0}
                icon={FiAlertCircle}
                tone={(o.mistakes ?? o.wrong_answers ?? 0) > 0 ? 'rose' : 'green'}
                hint="All wrong answers (quiz + MCQ)"
              />
              <OverviewCard
                label="Assignments"
                value={`${o.assignments_submitted ?? 0}/${o.assignments_given ?? 0}`}
                icon={FiClipboard}
                tone={(o.assignments_overdue ?? 0) > 0 ? 'rose' : 'sky'}
                hint={`${o.assignments_pending ?? 0} pending · ${o.assignments_overdue ?? 0} overdue`}
              />
              <OverviewCard
                label="Attendance"
                value={`${o.attendance_pct ?? 0}%`}
                icon={FiCalendar}
                tone="navy"
                hint="Marked sessions in your courses"
              />
              <OverviewCard
                label="Videos"
                value={`${o.videos_completed}/${o.videos_total}`}
                icon={FiVideo}
                tone="sky"
                hint={`${o.avg_watch_pct}% avg · ${o.study_hours_videos}h`}
              />
              <OverviewCard
                label="Total right"
                value={o.right_answers ?? 0}
                icon={FiTarget}
                tone="teal"
                hint="Quizzes + MCQ practice"
              />
            </div>

            <Section
              title="Past quiz history"
              subtitle="Click a row to review every question — right and wrong"
              empty={!detail.quizzes?.recent_attempts?.length ? 'No completed quiz attempts in your courses yet.' : null}
            >
              <div className="mb-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <MetricTile label="Attempts" value={qs.attempts ?? 0} tone="blue" icon={FiClipboard} />
                <MetricTile label="Right" value={qs.correct ?? 0} tone="green" icon={FiCheckCircle} />
                <MetricTile label="Wrong" value={qs.incorrect ?? 0} tone="red" icon={FiXCircle} />
                <MetricTile label="Accuracy" value={`${qs.accuracy ?? 0}%`} tone="teal" icon={FiTarget} />
              </div>
              <div className="overflow-x-auto">
                <table className="w-full text-left text-sm">
                  <thead>
                    <tr className="border-b text-xs uppercase text-slate-400">
                      <th className="py-2 pr-3">Quiz</th>
                      <th className="py-2 pr-3">Course</th>
                      <th className="py-2 pr-3">Right</th>
                      <th className="py-2 pr-3">Wrong</th>
                      <th className="py-2 pr-3">Score</th>
                      <th className="py-2 pr-3">Date</th>
                      <th className="py-2"></th>
                    </tr>
                  </thead>
                  <tbody>
                    {detail.quizzes.recent_attempts.map((a) => (
                      <tr
                        key={a.id}
                        className="cursor-pointer border-b border-slate-50 transition hover:bg-teal-50/50"
                        onClick={() => openQuizAttempt(a.id)}
                      >
                        <td className="py-2.5 pr-3 font-medium text-navy">{a.quiz_title}</td>
                        <td className="py-2.5 pr-3 text-slate-500">{a.course_title}</td>
                        <td className="py-2.5 pr-3 font-semibold text-emerald-600">{a.correct ?? 0}</td>
                        <td className="py-2.5 pr-3 font-semibold text-red-500">{a.incorrect ?? 0}</td>
                        <td className="py-2.5 pr-3">
                          <span className={a.passed ? 'text-emerald-600 font-semibold' : 'text-red-500 font-semibold'}>
                            {a.percentage != null ? `${a.percentage}%` : '—'}
                          </span>
                        </td>
                        <td className="py-2.5 pr-3 text-slate-400 text-xs">
                          {a.submitted_at ? new Date(a.submitted_at).toLocaleString() : '—'}
                        </td>
                        <td className="py-2.5 text-right">
                          <span className="inline-flex items-center gap-1 text-xs font-semibold text-primary">
                            <FiEye size={13} /> Review
                          </span>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </Section>

            <Section
              title="MCQ practice history"
              subtitle="Past practice from the study tools bank"
              empty={!(detail.mcq_practice?.total_questions > 0) ? 'No MCQ practice history yet.' : null}
            >
              <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <MetricTile label="Sessions" value={detail.mcq_practice?.attempts ?? 0} tone="blue" icon={FiClipboard} />
                <MetricTile label="Right" value={detail.mcq_practice?.correct ?? 0} tone="green" icon={FiCheckCircle} />
                <MetricTile label="Wrong" value={detail.mcq_practice?.incorrect ?? 0} tone="red" icon={FiXCircle} />
                <MetricTile label="Accuracy" value={`${detail.mcq_practice?.accuracy ?? 0}%`} tone="teal" icon={FiTarget} />
              </div>
            </Section>

            <div className="grid gap-0 lg:grid-cols-2 lg:gap-4">
              <Section
                title="Wrong quiz questions"
                empty={!detail.quizzes?.wrong_questions?.length ? 'No incorrect quiz answers recorded.' : null}
              >
                <ul className="space-y-3">
                  {detail.quizzes.wrong_questions.map((w, i) => (
                    <li key={`${w.question_id}-${i}`} className="rounded-xl bg-red-50/60 p-3 text-sm">
                      <p className="font-medium text-navy line-clamp-2">{w.question_text}</p>
                      <p className="mt-1 text-xs text-slate-500">
                        {w.quiz_title} · {w.course_title}
                      </p>
                      <div className="mt-2 space-y-1 text-xs">
                        <p>
                          <span className="text-slate-500">Student chose: </span>
                          {w.selected_option ? (
                            <span className="font-semibold text-red-600">
                              {w.selected_option}
                              {w.selected_option_text ? ` — ${w.selected_option_text}` : ''}
                            </span>
                          ) : (
                            <span className="font-semibold text-slate-500">No answer selected</span>
                          )}
                        </p>
                        <p>
                          <span className="text-slate-500">Correct answer: </span>
                          <span className="font-semibold text-emerald-600">
                            {w.correct_option || '—'}
                            {w.correct_option_text ? ` — ${w.correct_option_text}` : ''}
                          </span>
                        </p>
                      </div>
                    </li>
                  ))}
                </ul>
              </Section>

              <Section
                title="Practice mistake bank"
                subtitle="Separate redo list from study tools (not the same as quiz wrongs)"
                empty={!detail.mistakes?.items?.length ? 'No items in the practice mistake bank.' : null}
              >
                <p className="mb-3 text-xs text-slate-500">
                  {detail.mistakes.stats.remaining} remaining · {detail.mistakes.stats.mastered} mastered
                  {(o.mistakes ?? 0) > 0 && (
                    <span className="ml-2 text-rose-600">· Profile mistakes total: {o.mistakes}</span>
                  )}
                </p>
                <ul className="space-y-3">
                  {detail.mistakes.items.map((m) => (
                    <li key={m.id} className="rounded-xl bg-amber-50/70 p-3 text-sm">
                      <p className="font-medium text-navy line-clamp-2">{m.question}</p>
                      <p className="mt-1 text-xs text-slate-500">
                        {[m.subject, m.chapter, m.topic].filter(Boolean).join(' · ') || '—'}
                      </p>
                      <p className="mt-1 text-xs text-slate-400">
                        Wrong {m.wrong_count || 1}× · last {m.last_wrong_at ? new Date(m.last_wrong_at).toLocaleDateString() : '—'}
                      </p>
                    </li>
                  ))}
                </ul>
              </Section>
            </div>

            <Section title="Attendance" empty={!detail.attendance?.sessions ? 'No attendance records yet.' : null}>
              <div className="flex flex-wrap gap-2 text-sm">
                <StatChip label="Present" value={detail.attendance.present} tone="green" />
                <StatChip label="Late" value={detail.attendance.late} tone="amber" />
                <StatChip label="Absent" value={detail.attendance.absent} tone="red" />
                <StatChip label="Overall" value={`${detail.attendance.attendance_pct}%`} tone="blue" />
              </div>
              {(detail.attendance.by_course || []).length > 0 && (
                <div className="mt-4 overflow-x-auto">
                  <table className="w-full text-left text-sm">
                    <thead>
                      <tr className="border-b text-xs uppercase text-slate-400">
                        <th className="py-2">Course</th>
                        <th className="py-2">Attended</th>
                        <th className="py-2">%</th>
                      </tr>
                    </thead>
                    <tbody>
                      {detail.attendance.by_course.map((c) => (
                        <tr key={c.course_id} className="border-b border-slate-50">
                          <td className="py-2 font-medium text-navy">{c.course_title}</td>
                          <td className="py-2 text-slate-500">{c.attended}/{c.marked}</td>
                          <td className="py-2 font-semibold">{c.attendance_pct}%</td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              )}
            </Section>

            <div className="grid gap-0 lg:grid-cols-2 lg:gap-4">
              <Section title="Weak subjects" empty={!detail.weak_subjects?.length ? 'Not enough past quiz / MCQ history yet.' : null}>
                <ul className="space-y-2">
                  {detail.weak_subjects.map((w, i) => (
                    <li key={i} className="flex items-center justify-between gap-2 text-sm">
                      <span className="text-navy">{w.subject || w.name || '—'}</span>
                      <span className="shrink-0 text-xs text-slate-400">
                        {w.correct ?? 0}/{w.total ?? 0}
                        <span className={`ml-2 font-semibold ${(Number(w.accuracy) < 70) ? 'text-red-500' : 'text-amber-600'}`}>
                          {w.accuracy}%
                        </span>
                      </span>
                    </li>
                  ))}
                </ul>
              </Section>
              <Section title="Weak topics" empty={!detail.weak_topics?.length ? 'Not enough past quiz / MCQ history yet.' : null}>
                <ul className="space-y-2">
                  {detail.weak_topics.map((w, i) => (
                    <li key={i} className="flex items-center justify-between gap-2 text-sm">
                      <span className="text-navy">{w.topic || w.name || '—'}</span>
                      <span className="shrink-0 text-xs text-slate-400">
                        {w.correct ?? 0}/{w.total ?? 0}
                        <span className={`ml-2 font-semibold ${(Number(w.accuracy) < 70) ? 'text-red-500' : 'text-amber-600'}`}>
                          {w.accuracy}%
                        </span>
                      </span>
                    </li>
                  ))}
                </ul>
              </Section>
            </div>

            {/* Assignments */}
            <Section
              title="Assignment status"
              subtitle="Click an assignment to open overview, files, and this student’s submission"
              empty={!given ? 'No published assignments for this student yet.' : null}
            >
              <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                <MetricTile label="Given" value={asg.given ?? 0} tone="slate" icon={FiClipboard} />
                <MetricTile label="Submitted" value={asg.submitted ?? 0} tone="blue" icon={FiCheckCircle} />
                <MetricTile label="Pending" value={asg.pending ?? 0} tone="amber" icon={FiClock} />
                <MetricTile label="Overdue" value={asg.overdue ?? 0} tone="red" icon={FiAlertCircle} />
                <MetricTile label="Graded" value={asg.graded ?? 0} tone="green" icon={FiTarget} />
              </div>

              <div className="mt-4 rounded-xl bg-slate-50 px-4 py-3">
                <div className="mb-1.5 flex items-center justify-between text-xs">
                  <span className="font-semibold text-slate-600">Submission rate</span>
                  <span className="font-bold text-navy">{submitPct}%</span>
                </div>
                <ProgressBar value={submitPct} tone={submitPct >= 70 ? 'green' : submitPct >= 40 ? 'amber' : 'red'} />
                {asg.avg_marks != null && (
                  <p className="mt-2 text-xs text-slate-500">Average graded marks: <strong className="text-navy">{asg.avg_marks}</strong></p>
                )}
              </div>

              {(asg.items || []).length > 0 && (
                <ul className="mt-4 max-h-80 space-y-2 overflow-y-auto">
                  {asg.items.map((a) => (
                    <li key={a.id}>
                      <button
                        type="button"
                        onClick={() => openAssignmentDetail(a)}
                        className="flex w-full flex-wrap items-center justify-between gap-2 rounded-xl border border-slate-100 px-3 py-2.5 text-left transition hover:border-primary/30 hover:bg-sky-50/50"
                      >
                        <div className="min-w-0 flex-1">
                          <p className="truncate font-medium text-navy">{a.title}</p>
                          <p className="text-xs text-slate-400">
                            {a.course_title}
                            {a.due_date ? ` · Due ${new Date(a.due_date).toLocaleString()}` : ''}
                            {a.submitted_at ? ` · Submitted ${new Date(a.submitted_at).toLocaleDateString()}` : ''}
                          </p>
                        </div>
                        <div className="flex items-center gap-2">
                          {a.marks != null && (
                            <span className="text-xs font-semibold text-slate-600">
                              {a.marks}{a.max_marks != null ? `/${a.max_marks}` : ''}
                            </span>
                          )}
                          <StatusBadge state={a.state} />
                          <span className="inline-flex items-center gap-1 text-xs font-semibold text-primary">
                            <FiEye size={13} /> View
                          </span>
                        </div>
                      </button>
                    </li>
                  ))}
                </ul>
              )}
            </Section>

            {/* Video analytics — redesigned */}
            <Section
              title="Video analytics"
              subtitle="Watch progress and engagement for this student"
              empty={!videos.length ? 'No course videos for this student.' : null}
            >
              <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <MetricTile label="Completed" value={vs.completed ?? 0} tone="green" icon={FiCheckCircle} />
                <MetricTile label="Watching" value={vs.watching ?? 0} tone="blue" icon={FiPlay} />
                <MetricTile label="Not started" value={vs.not_started ?? 0} tone="slate" icon={FiClock} />
                <MetricTile label="Watch time" value={`${vs.total_watch_hours ?? 0}h`} tone="teal" icon={FiVideo} />
              </div>

              <div className="mt-4 rounded-xl bg-slate-50 px-4 py-3">
                <div className="mb-1.5 flex items-center justify-between text-xs">
                  <span className="font-semibold text-slate-600">Lecture completion</span>
                  <span className="font-bold text-navy">
                    {vs.completed ?? 0}/{vs.total_videos ?? 0} · {videoPct}%
                  </span>
                </div>
                <ProgressBar value={videoPct} tone={videoPct >= 70 ? 'green' : videoPct >= 30 ? 'amber' : 'red'} />
                <p className="mt-2 text-xs text-slate-500">
                  Average watch across videos: <strong className="text-navy">{vs.average_watch_pct ?? 0}%</strong>
                </p>
              </div>

              {(va.by_course || []).length > 0 && (
                <div className="mt-4 grid gap-3 sm:grid-cols-2">
                  {va.by_course.map((c) => (
                    <div key={c.course_id} className="rounded-2xl border border-slate-100 p-4">
                      <p className="text-sm font-semibold text-navy line-clamp-2">{c.subject_name}</p>
                      <div className="mt-3 flex flex-wrap gap-1.5">
                        <StatChip label="Done" value={c.completed_videos} tone="green" />
                        <StatChip label="Left" value={c.remaining_videos} tone="amber" />
                        <StatChip label="Avg" value={`${c.average_watch_pct}%`} tone="blue" />
                      </div>
                      <div className="mt-3">
                        <div className="mb-1 flex justify-between text-[11px] text-slate-500">
                          <span>Completion</span>
                          <span className="font-semibold text-navy">{c.completion_pct}%</span>
                        </div>
                        <ProgressBar value={c.completion_pct} tone={c.completion_pct >= 70 ? 'green' : 'amber'} />
                      </div>
                    </div>
                  ))}
                </div>
              )}

              <div className="mt-4 grid gap-3 lg:grid-cols-3">
                {[
                  {
                    label: 'Most replayed',
                    title: va.most_replayed?.lecture_title || va.most_replayed?.video_title,
                    meta: `${va.most_replayed?.replay_count ?? 0} replays`,
                  },
                  {
                    label: 'Least watched',
                    title: va.least_watched?.lecture_title || va.least_watched?.video_title,
                    meta: `${Math.round(va.least_watched?.completion_pct ?? 0)}% complete`,
                  },
                  {
                    label: 'Last viewed',
                    title: va.last_viewed?.lecture_title || va.last_viewed?.video_title,
                    meta: va.last_viewed?.last_watched_at
                      ? new Date(va.last_viewed.last_watched_at).toLocaleString()
                      : '—',
                  },
                ].map((card) => (
                  <div key={card.label} className="rounded-2xl border border-slate-100 bg-gradient-to-br from-slate-50/80 to-white p-4">
                    <p className="text-[10px] font-semibold uppercase tracking-wide text-slate-400">{card.label}</p>
                    <p className="mt-1 line-clamp-2 text-sm font-semibold text-navy">{card.title || '—'}</p>
                    <p className="mt-1 text-xs text-slate-500">{card.meta}</p>
                  </div>
                ))}
              </div>

              <div className="mt-5">
                <p className="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-400">All videos</p>
                <div className="max-h-[28rem] space-y-2 overflow-y-auto pr-1">
                  {videos.map((v) => {
                    const pct = Math.round(v.completion_pct || 0)
                    const status = v.status || 'not_started'
                    return (
                      <div key={v.resource_id} className="rounded-2xl border border-slate-100 px-3.5 py-3 transition hover:border-slate-200">
                        <div className="flex flex-wrap items-start justify-between gap-2">
                          <div className="min-w-0 flex-1">
                            <p className="truncate text-sm font-semibold text-navy">
                              {v.lecture_title || v.video_title}
                            </p>
                            <p className="truncate text-xs text-slate-400">
                              {v.course_title}
                              {v.video_title && v.lecture_title ? ` · ${v.video_title}` : ''}
                            </p>
                          </div>
                          <div className="flex items-center gap-2">
                            <span className="text-xs font-bold tabular-nums text-navy">{pct}%</span>
                            <StatusBadge state={status} />
                          </div>
                        </div>
                        <div className="mt-2">
                          <ProgressBar value={pct} tone={videoStatusTone(status)} />
                        </div>
                        <div className="mt-2 flex flex-wrap gap-x-3 gap-y-1 text-[11px] text-slate-500">
                          <span>▶ {v.play_count || 0}</span>
                          <span>⏸ {v.pause_count || 0}</span>
                          <span>⇄ {(v.seek_forward_count || 0) + (v.seek_backward_count || 0)}</span>
                          <span>↺ {v.replay_count || 0}</span>
                          <span>⏱ {formatWatchTime(v.watched_seconds || 0)}</span>
                          {v.last_watched_at && (
                            <span>Last {new Date(v.last_watched_at).toLocaleDateString()}</span>
                          )}
                        </div>
                      </div>
                    )
                  })}
                </div>
              </div>

              {(va.timeline || []).length > 0 && (
                <div className="mt-5">
                  <p className="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-400">Recent activity</p>
                  <ul className="max-h-48 divide-y divide-slate-50 overflow-y-auto rounded-xl border border-slate-100">
                    {va.timeline.slice(0, 25).map((e, i) => (
                      <li key={i} className="flex flex-wrap items-baseline justify-between gap-2 px-3 py-2 text-sm">
                        <span>
                          <span className="font-medium capitalize text-navy">{e.event_type}</span>
                          <span className="text-slate-400"> · </span>
                          <span className="text-slate-600">{e.video_title || e.lecture_title || 'Video'}</span>
                        </span>
                        <span className="text-[11px] text-slate-400">
                          {e.created_at ? new Date(e.created_at).toLocaleString() : ''}
                        </span>
                      </li>
                    ))}
                  </ul>
                </div>
              )}
            </Section>
          </>
        )}

        <QuizAttemptReviewModal
          open={!!quizReview || quizReviewLoading}
          loading={quizReviewLoading}
          data={quizReview}
          onClose={() => { setQuizReview(null); setQuizReviewLoading(false) }}
        />
        <AssignmentDetailModal
          open={!!assignmentDetail || assignmentDetailLoading}
          loading={assignmentDetailLoading}
          data={assignmentDetail}
          onClose={() => { setAssignmentDetail(null); setAssignmentDetailLoading(false) }}
        />
      </div>
    )
  }

  return (
    <div className="pb-10">
      <div className="flex flex-wrap items-start justify-between gap-3">
        <div>
          <h2 className="font-display text-2xl font-bold text-navy">Student Performance</h2>
          <p className="text-sm text-slate-500">
            Monitor quizzes, assignments, attendance, videos, and activity for each student.
          </p>
        </div>
        <div className="flex flex-wrap gap-2">
          <select className="input-field text-sm" value={courseId} onChange={(e) => setCourseId(e.target.value)}>
            <option value="">All my courses</option>
            {courses.map((c) => (
              <option key={c.id} value={c.id}>{c.title}</option>
            ))}
          </select>
          <div className="relative">
            <FiSearch className="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" size={14} />
            <input
              className="input-field pl-9 text-sm"
              placeholder="Search name or email…"
              value={q}
              onChange={(e) => setQ(e.target.value)}
            />
          </div>
        </div>
      </div>

      {loading && <p className="mt-8 text-center text-slate-400">Loading students…</p>}

      {!loading && students.length === 0 && (
        <div className="mt-10 rounded-2xl border border-dashed border-slate-200 py-12 text-center">
          <FiUsers className="mx-auto text-slate-300" size={32} />
          <p className="mt-2 text-sm text-slate-500">No enrolled students found.</p>
        </div>
      )}

      {!loading && students.length > 0 && (
        <div className="mt-6 grid gap-3">
          {students.map((st) => (
            <button
              key={st.id}
              type="button"
              onClick={() => openStudent(st.id)}
              className="w-full rounded-2xl border border-slate-100 bg-white p-4 text-left shadow-soft transition hover:border-primary/30 hover:shadow-md"
            >
              <div className="flex flex-wrap items-start justify-between gap-2">
                <div>
                  <p className="font-display text-lg font-bold text-navy">{st.name}</p>
                  <p className="text-xs text-slate-500">{st.email}</p>
                  <p className="mt-1 text-xs text-slate-400 line-clamp-1">{st.courses || '—'}</p>
                </div>
                <span className="rounded-full bg-primary/10 px-2.5 py-0.5 text-[11px] font-semibold text-primary">
                  View profile →
                </span>
              </div>
              <div className="mt-3 flex flex-wrap gap-1.5">
                <StatChip label="Quiz" value={`${st.stats?.quiz_avg_score ?? 0}%`} tone="blue" />
                <StatChip
                  label="Right/Wrong"
                  value={`${st.stats?.quiz_correct ?? 0}/${st.stats?.quiz_incorrect ?? 0}`}
                  tone={(st.stats?.quiz_incorrect ?? 0) > 0 ? 'amber' : 'green'}
                />
                <StatChip label="Attendance" value={`${st.stats?.attendance_pct ?? 0}%`} tone="green" />
                <StatChip
                  label="Assignments"
                  value={`${st.stats?.assignments_submitted ?? 0}/${st.stats?.assignments_given ?? 0}`}
                  tone={(st.stats?.assignments_overdue ?? 0) > 0 ? 'red' : 'amber'}
                />
                <StatChip
                  label="Videos"
                  value={`${st.stats?.videos_completed ?? 0}/${st.stats?.videos_total ?? 0}`}
                  tone="blue"
                />
                <StatChip
                  label="Mistakes"
                  value={st.stats?.mistakes ?? st.stats?.active_mistakes ?? 0}
                  tone={(st.stats?.mistakes ?? st.stats?.active_mistakes ?? 0) > 0 ? 'red' : 'slate'}
                />
              </div>
            </button>
          ))}
        </div>
      )}
    </div>
  )
}
