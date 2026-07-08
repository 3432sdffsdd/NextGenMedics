import { useCallback, useEffect, useState } from 'react'
import { Link, useParams } from 'react-router-dom'
import ViewFileButton from '../../components/dashboard/ViewFileButton'
import { myCoursesService, adminCoursesService, quizService, assignmentService, attendanceService, notificationsService, downloadMedia } from '../../services/api'
import TabBadge from '../../components/dashboard/TabBadge'
import { useToast } from '../../context/ToastContext'
import CourseDiscussion from '../../components/dashboard/CourseDiscussion'
import CourseMonthSchedule from '../../components/dashboard/CourseMonthSchedule'
import StudentLearnMaterials from '../../components/dashboard/StudentLearnMaterials'
import LiveClassManager from '../teacher/LiveClassManager'
import QuizReview from '../../components/dashboard/QuizReview'
import AssignmentTestRunner, { AssignmentTestResult, AssignmentTestReview } from '../../components/dashboard/AssignmentTestRunner'
import { EmptyState } from '../../components/ui'
import Alert from '../../components/dashboard/Alert'
import MultiFileUploadField from '../../components/dashboard/MultiFileUploadField'
import { ACCEPT_ALL, appendTitledFiles, normalizeExternalUrl } from '../../utils/files'
import { FiHelpCircle, FiFileText, FiDownload, FiLink } from 'react-icons/fi'

const TABS = ['learn', 'quizzes', 'assignments', 'live class', 'discussions', 'schedule', 'attendance']
const BADGE_TABS = new Set(['quizzes', 'assignments', 'discussions'])

export default function StudentCourseHub() {
  const { id } = useParams()
  const courseId = Number(id)
  const toast = useToast()
  const [tab, setTab] = useState('learn')
  const [course, setCourse] = useState(null)
  const [structure, setStructure] = useState([])
  const [quizzes, setQuizzes] = useState([])
  const [assignments, setAssignments] = useState([])
  const [attendance, setAttendance] = useState([])
  const [loading, setLoading] = useState(true)
  const [structureError, setStructureError] = useState('')

  const [activeQuiz, setActiveQuiz] = useState(null)
  const [attemptId, setAttemptId] = useState(null)
  const [answers, setAnswers] = useState({})
  const [quizResult, setQuizResult] = useState(null)
  const [quizAttempts, setQuizAttempts] = useState({})
  const [quizStartedAt, setQuizStartedAt] = useState(null)

  const [submitFiles, setSubmitFiles] = useState([])
  const [submitText, setSubmitText] = useState('')
  const [submitAssignmentId, setSubmitAssignmentId] = useState(null)
  const [activeAssignmentTest, setActiveAssignmentTest] = useState(null)
  const [assignmentTestResult, setAssignmentTestResult] = useState(null)
  const [assignmentTestReview, setAssignmentTestReview] = useState(null)
  const [submittingTest, setSubmittingTest] = useState(false)
  const [tabBadges, setTabBadges] = useState({ assignments: 0, quizzes: 0, discussions: 0 })

  const loadTabBadges = useCallback(() => {
    notificationsService.courseTabBadges(courseId)
      .then(({ data }) => setTabBadges(data.data || { assignments: 0, quizzes: 0, discussions: 0 }))
      .catch(() => {})
  }, [courseId])

  useEffect(() => {
    loadTabBadges()
    const t = setInterval(loadTabBadges, 30000)
    return () => clearInterval(t)
  }, [loadTabBadges])

  const selectTab = async (t) => {
    setTab(t)
    if (BADGE_TABS.has(t) && tabBadges[t] > 0) {
      try {
        const { data } = await notificationsService.markCourseTabRead(courseId, t)
        setTabBadges(data.data?.badges || { assignments: 0, quizzes: 0, discussions: 0 })
      } catch {
        loadTabBadges()
      }
    }
  }
  useEffect(() => {
    setLoading(true)
    setStructureError('')

    myCoursesService.list()
      .then(({ data }) => setCourse((data.data || []).find((c) => c.id === courseId)))
      .catch(() => toast.error('Could not load your courses'))

    adminCoursesService.structure(courseId)
      .then(({ data }) => {
        setStructure(data.data || [])
        setStructureError('')
      })
      .catch((err) => {
        setStructure([])
        if (err.response?.status === 403) {
          setStructureError('You are not enrolled in this course. Ask your administrator to enroll you, then open the course again.')
        } else {
          setStructureError(err.response?.data?.message || 'Could not load lecture materials.')
        }
      })

    quizService.list(courseId)
      .then(({ data }) => setQuizzes(data.data || []))
      .catch(() => setQuizzes([]))

    assignmentService.list(courseId)
      .then(({ data }) => setAssignments(data.data || []))
      .catch(() => setAssignments([]))

    attendanceService.my(courseId)
      .then(({ data }) => setAttendance(data.data || []))
      .catch(() => setAttendance([]))
      .finally(() => setLoading(false))
  }, [courseId, toast])

  const loadQuizAttempts = useCallback((quizId) => {
    quizService.myAttempts(quizId).then(({ data }) => {
      setQuizAttempts((prev) => ({ ...prev, [quizId]: data.data || [] }))
    }).catch(() => {})
  }, [])

  const startQuiz = async (quizId) => {
    setQuizResult(null)
    try {
      const { data: startData } = await quizService.start(quizId)
      const { data: quizData } = await quizService.get(quizId)
      setAttemptId(startData.data.attempt_id)
      setActiveQuiz(quizData.data)
      setAnswers({})
      setQuizStartedAt(Date.now())
    } catch (err) {
      toast.error(err.response?.data?.message || 'Could not start quiz')
    }
  }

  const submitQuiz = async () => {
    const quizId = activeQuiz?.id
    const quizTitle = activeQuiz?.title
    const timeTaken = quizStartedAt ? Math.round((Date.now() - quizStartedAt) / 1000) : undefined
    try {
      const { data } = await quizService.submit(attemptId, answers, timeTaken)
      setQuizResult({ ...data.data, title: quizTitle })
      setActiveQuiz(null)
      setQuizStartedAt(null)
      toast.success('Quiz submitted successfully')
      if (quizId) loadQuizAttempts(quizId)
    } catch {
      toast.error('Quiz submit failed')
    }
  }

  const reviewPastAttempt = async (attemptId, title) => {
    try {
      const { data } = await quizService.reviewAttempt(attemptId)
      setQuizResult({
        percentage: data.data.attempt.percentage,
        score: data.data.attempt.score,
        passed: !!data.data.attempt.passed,
        review: data.data.review,
        title: title || data.data.quiz?.title,
        time_taken_seconds: data.data.attempt.time_taken_seconds,
        submitted_at: data.data.attempt.submitted_at,
      })
      setActiveQuiz(null)
    } catch {
      toast.error('Could not load review')
    }
  }

  const handleAssignmentSubmit = async (e) => {
    e.preventDefault()
    if (!submitFiles.length && !submitText.trim()) {
      return toast.error('Upload at least one file or add notes')
    }
    const missingTitle = submitFiles.find((x) => !x.title?.trim())
    if (missingTitle) return toast.error('Please enter a title for each file')

    const fd = new FormData()
    appendTitledFiles(fd, submitFiles)
    if (submitText.trim()) fd.append('submission_text', submitText.trim())
    try {
      await assignmentService.submit(submitAssignmentId, fd)
      toast.success('Assignment submitted successfully')
      setSubmitAssignmentId(null)
      setSubmitFiles([])
      setSubmitText('')
      const { data } = await assignmentService.list(courseId)
      setAssignments(data.data || [])
    } catch {
      toast.error('Submission failed')
    }
  }

  const startAssignmentTest = async (assignmentId) => {
    try {
      const { data } = await assignmentService.getTest(assignmentId)
      const payload = data.data
      if (payload.already_submitted && payload.submission) {
        setActiveAssignmentTest(null)
        setAssignmentTestResult({
          score: payload.submission.marks,
          max_marks: payload.assignment.max_marks,
          percentage: payload.submission.percentage,
          passed: payload.submission.passed,
          review: payload.submission.review,
          assignment: payload.assignment,
        })
        setAssignmentTestReview(null)
        return
      }
      setActiveAssignmentTest(payload)
      setAssignmentTestResult(null)
      setAssignmentTestReview(null)
    } catch (err) {
      toast.error(err.response?.data?.message || 'Could not start test')
    }
  }

  const submitAssignmentTest = async (answers) => {
    if (!activeAssignmentTest?.assignment?.id) return
    setSubmittingTest(true)
    try {
      const { data } = await assignmentService.submitTest(activeAssignmentTest.assignment.id, answers)
      setAssignmentTestResult({ ...data.data, assignment: activeAssignmentTest.assignment })
      setActiveAssignmentTest(null)
      const listRes = await assignmentService.list(courseId)
      setAssignments(listRes.data.data || [])
      toast.success('Test submitted')
    } catch (err) {
      toast.error(err.response?.data?.message || 'Could not submit test')
    } finally {
      setSubmittingTest(false)
    }
  }

  const reloadAssignments = async () => {
    setActiveAssignmentTest(null)
    setAssignmentTestResult(null)
    setAssignmentTestReview(null)
    const { data } = await assignmentService.list(courseId)
    setAssignments(data.data || [])
  }

  return (
    <div>
      <Link to="/student/courses" className="text-sm font-semibold text-primary hover:underline">← Back to courses</Link>
      <h2 className="mt-4 font-display text-xl font-bold text-navy">{course?.title || 'Course'}</h2>

      {loading && <p className="mt-4 text-sm text-slate-400">Loading course…</p>}

      {structureError && (
        <div className="mt-4">
          <Alert type="warning" title="Materials unavailable">{structureError}</Alert>
        </div>
      )}

      {quizResult && !activeQuiz && (
        <div className="mt-4">
          <QuizReview result={quizResult} title={quizResult.title} onClose={() => setQuizResult(null)} />
        </div>
      )}

      <div className="mt-6 flex flex-wrap gap-2 border-b border-slate-200">
        {TABS.map((t) => (
          <button key={t} type="button" onClick={() => selectTab(t)}
            className={`inline-flex items-center px-4 py-2 text-sm font-medium capitalize ${tab === t ? 'border-b-2 border-primary text-primary' : 'text-slate-500'}`}>
            {t}
            {BADGE_TABS.has(t) && <TabBadge count={tabBadges[t]} />}
          </button>
        ))}
      </div>

      {tab === 'learn' && (
        <div className="mt-6">
          <StudentLearnMaterials structure={structure} courseId={courseId} />
        </div>
      )}

      {tab === 'quizzes' && !activeQuiz && !quizResult && (
        <div className="mt-6 space-y-3">
          {quizzes.map((q) => {
            const attempts = quizAttempts[q.id]
            const atMax = q.max_attempts && attempts?.length >= q.max_attempts
            return (
              <div key={q.id} className="rounded-xl border border-slate-100 bg-white p-4 shadow-soft">
                <div className="flex flex-wrap items-center justify-between gap-3">
                  <div>
                    <p className="font-medium text-navy">{q.title}</p>
                    <p className="text-xs text-slate-400">{q.duration_minutes} min · Pass: {q.passing_marks}% · {q.max_attempts} attempt(s)</p>
                  </div>
                  <div className="flex flex-wrap gap-2">
                    {!atMax && <button type="button" onClick={() => startQuiz(q.id)} className="btn-primary text-sm">Start Quiz</button>}
                    <button type="button" onClick={() => loadQuizAttempts(q.id)} className="btn-secondary text-sm">Past attempts</button>
                  </div>
                </div>
                {attempts?.length > 0 && (
                  <ul className="mt-3 space-y-1 border-t border-slate-100 pt-3 text-sm">
                    {attempts.map((a) => (
                      <li key={a.id} className="flex items-center justify-between gap-2">
                        <span className="text-slate-500">Attempt {a.attempt_number} · {a.percentage ?? a.score}% · {a.passed ? 'Passed' : 'Failed'}</span>
                        <button type="button" onClick={() => reviewPastAttempt(a.id, q.title)} className="text-xs font-semibold text-primary hover:underline">Review answers</button>
                      </li>
                    ))}
                  </ul>
                )}
              </div>
            )
          })}
          {quizzes.length === 0 && (
            <EmptyState icon={FiHelpCircle} title="No quizzes available" description="Published quizzes will appear here." />
          )}
        </div>
      )}

      {tab === 'quizzes' && activeQuiz && (
        <div className="mt-6 rounded-2xl border border-slate-100 bg-white p-6 shadow-soft">
          <h3 className="font-bold text-navy">{activeQuiz.title}</h3>
          <div className="mt-6 space-y-6">
            {(activeQuiz.questions || []).map((q) => (
              <div key={q.id} className="border-b border-slate-100 pb-4">
                <p className="font-medium text-navy">{q.question_text}</p>
                {q.question_type === 'essay' ? (
                  <textarea className="mt-2 w-full rounded-xl border border-slate-200 p-3 text-sm" rows={3}
                    onChange={(e) => setAnswers({ ...answers, [q.id]: e.target.value })} />
                ) : (
                  <div className="mt-2 space-y-2">
                    {(q.options || []).map((opt) => (
                      <label key={opt.id} className="flex cursor-pointer items-center gap-2 rounded-lg px-2 py-1 hover:bg-slate-50">
                        <input type="radio" name={`q-${q.id}`}
                          onChange={() => setAnswers({ ...answers, [q.id]: opt.id })} />
                        <span className="text-sm">{opt.option_text}</span>
                      </label>
                    ))}
                  </div>
                )}
              </div>
            ))}
          </div>
          <button type="button" onClick={submitQuiz} className="btn-primary mt-6 text-sm">Submit Quiz</button>
        </div>
      )}

      {tab === 'assignments' && activeAssignmentTest?.questions && !assignmentTestResult && (
        <AssignmentTestRunner
          testData={activeAssignmentTest}
          onSubmit={submitAssignmentTest}
          onCancel={reloadAssignments}
          submitting={submittingTest}
        />
      )}

      {tab === 'assignments' && assignmentTestReview && (
        <AssignmentTestReview
          review={assignmentTestReview}
          assignment={assignmentTestResult?.assignment}
          onBack={() => setAssignmentTestReview(null)}
        />
      )}

      {tab === 'assignments' && assignmentTestResult && !assignmentTestReview && (
        <AssignmentTestResult
          result={assignmentTestResult}
          assignment={assignmentTestResult.assignment}
          onBack={reloadAssignments}
          onReview={() => setAssignmentTestReview(assignmentTestResult.review)}
        />
      )}

      {tab === 'assignments' && !activeAssignmentTest && !assignmentTestResult && !assignmentTestReview && (
        <div className="mt-6 space-y-4">
          {assignments.map((a) => {
            const sub = a.my_submission
            const isInteractive = a.assignment_type === 'interactive_test'
            return (
              <div key={a.id} className="rounded-xl border border-slate-100 bg-white p-5 shadow-soft">
                <div className="flex flex-wrap items-start justify-between gap-3">
                  <div className="min-w-0 flex-1">
                    <div className="flex flex-wrap items-center gap-2">
                      <p className="font-medium text-navy">{a.title}</p>
                      {isInteractive && (
                        <span className="rounded-full bg-violet-50 px-2 py-0.5 text-xs font-semibold text-violet-700">
                          MCQ test · {a.question_count || 0} questions
                        </span>
                      )}
                    </div>
                    {a.description && <p className="mt-1 text-sm text-slate-500">{a.description}</p>}
                    {a.instructions && <p className="mt-1 text-sm text-slate-600">{a.instructions}</p>}
                    <p className="mt-2 text-xs text-slate-400">Due: {new Date(a.due_date).toLocaleString()} · Max: {a.max_marks} marks</p>
                    {!isInteractive && (a.attachments?.length > 0 || a.attachment_path) && (
                      <div className="mt-3 space-y-1">
                        {(a.attachments?.length ? a.attachments : [{
                          id: 0,
                          title: 'Assignment file',
                          file_path: a.attachment_path,
                        }]).map((att) => (
                          <div key={att.id || att.file_path} className="flex flex-wrap items-center gap-3">
                            <span className="text-xs font-medium text-slate-500">{att.title}</span>
                            <ViewFileButton
                              title={att.title}
                              filePath={att.file_path}
                              className="inline-flex items-center gap-1.5 text-sm font-medium text-primary hover:underline"
                            />
                            <button
                              type="button"
                              onClick={() => downloadMedia(att.file_path, att.title)}
                              className="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:underline"
                            >
                              <FiDownload size={14} /> Download
                            </button>
                          </div>
                        ))}
                      </div>
                    )}
                    {!isInteractive && a.external_url && (
                      <a
                        href={normalizeExternalUrl(a.external_url)}
                        target="_blank"
                        rel="noreferrer"
                        className="mt-3 inline-flex items-center gap-1.5 text-sm font-medium text-violet-600 hover:underline"
                      >
                        <FiLink size={14} /> Open external resource
                      </a>
                    )}
                    {sub && (
                      <div className="mt-3 rounded-xl border border-slate-100 bg-slate-50/80 p-3 text-sm">
                        <p className="font-medium text-navy">
                          {isInteractive ? 'Your test result' : 'Your submission'}
                          {sub.status && <span className="ml-2 text-xs font-normal uppercase text-slate-400">{sub.status}</span>}
                        </p>
                        {!isInteractive && sub.submission_text && <p className="mt-1 text-slate-600">{sub.submission_text}</p>}
                        {!isInteractive && (sub.files?.length > 0 || sub.file_path) && (
                          <div className="mt-2 space-y-1">
                            {(sub.files?.length ? sub.files : [{
                              id: 0,
                              title: sub.original_filename || 'Uploaded file',
                              file_path: sub.file_path,
                            }]).map((f) => (
                              <div key={f.id || f.file_path} className="flex flex-wrap items-center gap-3">
                                <span className="text-xs font-medium text-slate-500">{f.title}</span>
                                <ViewFileButton title={f.title} filePath={f.file_path} className="inline-flex items-center gap-1.5 text-primary hover:underline" />
                                <button
                                  type="button"
                                  onClick={() => downloadMedia(f.file_path, f.title)}
                                  className="inline-flex items-center gap-1.5 text-slate-500 hover:underline"
                                >
                                  <FiDownload size={14} /> Download
                                </button>
                              </div>
                            ))}
                          </div>
                        )}
                        {(sub.marks != null && sub.marks !== '') && (
                          <p className="mt-2 text-xs text-slate-500">
                            Score: {sub.marks}{sub.percentage != null ? ` (${sub.percentage}%)` : ''}{sub.passed != null ? (sub.passed ? ' · Passed' : ' · Not passed') : ''}
                            {!isInteractive && sub.remarks ? ` · ${sub.remarks}` : ''}
                          </p>
                        )}
                      </div>
                    )}
                  </div>
                  {isInteractive ? (
                    a.status !== 'closed' && !sub && (
                      <button type="button" onClick={() => startAssignmentTest(a.id)} className="btn-primary shrink-0 text-sm">
                        Start test
                      </button>
                    )
                  ) : (
                    a.status !== 'closed' && (
                      <button type="button" onClick={() => { setSubmitAssignmentId(a.id); setSubmitFiles([]); setSubmitText('') }} className="btn-primary shrink-0 text-sm">
                        {sub ? 'Resubmit' : 'Submit'}
                      </button>
                    )
                  )}
                  {isInteractive && sub && (
                    <button type="button" onClick={() => startAssignmentTest(a.id)} className="btn-secondary shrink-0 text-sm">
                      View result
                    </button>
                  )}
                </div>
                {!isInteractive && submitAssignmentId === a.id && (
                  <form onSubmit={handleAssignmentSubmit} className="mt-4 border-t border-slate-100 pt-4">
                    <textarea placeholder="Notes (optional)" value={submitText} onChange={(e) => setSubmitText(e.target.value)}
                      className="w-full rounded-xl border border-slate-200 p-3 text-sm" rows={2} />
                    <div className="mt-3">
                      <MultiFileUploadField
                        items={submitFiles}
                        onChange={setSubmitFiles}
                        accept={ACCEPT_ALL}
                        hint="Upload one or more files — give each a title"
                      />
                    </div>
                    <button type="submit" className="btn-secondary mt-3 text-sm">Upload submission</button>
                  </form>
                )}
              </div>
            )
          })}
          {assignments.length === 0 && (
            <EmptyState icon={FiFileText} title="No assignments yet" description="Assignments will appear here when your teacher posts them." />
          )}
        </div>
      )}

      {tab === 'live class' && <div className="mt-6"><LiveClassManager courseId={courseId} /></div>}

      {tab === 'discussions' && <div className="mt-6"><CourseDiscussion courseId={courseId} /></div>}

      {tab === 'schedule' && <div className="mt-6"><CourseMonthSchedule courseId={courseId} /></div>}

      {tab === 'attendance' && (
        <div className="mt-6 overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-soft">
          <table className="w-full text-left text-sm">
            <thead className="bg-slate-50 text-xs uppercase text-slate-500">
              <tr><th className="px-4 py-3">Date</th><th className="px-4 py-3">Session</th><th className="px-4 py-3">Status</th></tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {attendance.map((r) => (
                <tr key={r.id}>
                  <td className="px-4 py-3">{new Date(r.session_date).toLocaleDateString()}</td>
                  <td className="px-4 py-3">{r.session_title || 'Class'}</td>
                  <td className="px-4 py-3 capitalize">{r.status}</td>
                </tr>
              ))}
              {attendance.length === 0 && (
                <tr><td colSpan={3} className="px-4 py-8 text-center text-slate-400">No attendance records yet</td></tr>
              )}
            </tbody>
          </table>
        </div>
      )}
    </div>
  )
}
