import { useEffect, useRef, useState } from 'react'
import { Link } from 'react-router-dom'
import { FiZap, FiTarget, FiBookOpen, FiAlertCircle, FiCalendar, FiRefreshCw, FiBell, FiExternalLink, FiPackage, FiFilm } from 'react-icons/fi'
import { premiumStudyService, dashboardService, myCoursesService, progressService, notificationsService, studyMaterialService } from '../../services/api'
import StatCard from '../../components/dashboard/StatCard'
import StreakWidget from '../../components/dashboard/StreakWidget'
import ProgressBar from '../../components/dashboard/ProgressBar'
import McqPlayer from '../../components/dashboard/McqPlayer'
import { useAuth } from '../../context/AuthContext'
import { Badge } from '../../components/ui'
import { notificationActionLabel, notificationHref, dedupeNotifications } from '../../utils/notificationLinks'

const ACTIVITY_TYPES = new Set(['new_content', 'ai_content_published', 'new_quiz', 'new_assignment', 'assignment_graded'])

function ActivityCard({ n, onOpened }) {
  const href = notificationHref(n, 'student')
  return (
    <article className={`rounded-lg border px-3 py-2 ${n.is_read ? 'border-slate-100 bg-white' : 'border-emerald-200 bg-emerald-50/60'}`}>
      <div className="flex flex-wrap items-center justify-between gap-2">
        <div className="min-w-0 flex-1">
          <div className="flex flex-wrap items-center gap-1.5">
            <h3 className="truncate text-sm font-semibold text-navy">{n.title}</h3>
            {!n.is_read && <Badge tone="success">New</Badge>}
          </div>
          <p className="mt-0.5 line-clamp-1 text-xs text-slate-500">{n.message}</p>
        </div>
        {href && (
          <Link
            to={href}
            onClick={() => onOpened?.(n)}
            className="inline-flex shrink-0 items-center gap-1 rounded-lg bg-primary px-2.5 py-1 text-xs font-semibold text-white hover:opacity-95"
          >
            <FiExternalLink size={12} /> {notificationActionLabel(n)}
          </Link>
        )}
      </div>
    </article>
  )
}

function Countdown({ seconds }) {
  const [left, setLeft] = useState(seconds)
  useEffect(() => {
    setLeft(seconds)
    const t = setInterval(() => setLeft((s) => Math.max(0, s - 1)), 1000)
    return () => clearInterval(t)
  }, [seconds])
  const h = Math.floor(left / 3600)
  const m = Math.floor((left % 3600) / 60)
  const s = left % 60
  return <span>{h > 0 ? `${h}h ` : ''}{m}m {s}s</span>
}

export default function StudentDashboard() {
  const { user } = useAuth()
  const [stats, setStats] = useState(null)
  const [courses, setCourses] = useState([])
  const [premium, setPremium] = useState(null)
  const [revision, setRevision] = useState(null)
  const [revisionSummary, setRevisionSummary] = useState(null)
  const [startingRevision, setStartingRevision] = useState(false)
  const [activity, setActivity] = useState([])
  const [videoSummary, setVideoSummary] = useState({ total: 0, watched: 0 })
  const revisionStart = useRef(Date.now())

  useEffect(() => {
    dashboardService.student().then(({ data }) => setStats(data.data)).catch(() => {})
    myCoursesService.list().then(({ data }) => setCourses(data.data || [])).catch(() => {})
    premiumStudyService.dashboard().then(({ data }) => setPremium(data.data)).catch(() => {})
    studyMaterialService.summary()
      .then(({ data }) => setVideoSummary(data.data || { total: 0, watched: 0 }))
      .catch(() => {})
    progressService.ping('login').catch(() => {})
    notificationsService.list({ per_page: 40 })
      .then(({ data }) => {
        const rows = Array.isArray(data.data) ? data.data : (data.data?.items || [])
        const filtered = rows.filter((n) => ACTIVITY_TYPES.has(n.type))
        setActivity(dedupeNotifications(filtered, 5))
      })
      .catch(() => {})
  }, [])

  const markActivityOpened = async (n) => {
    if (!n?.id || n.is_read) return
    try {
      await notificationsService.markRead(n.id)
      setActivity((prev) => prev.map((x) => (x.id === n.id ? { ...x, is_read: 1 } : x)))
    } catch { /* ignore */ }
  }

  const startRevision = async () => {
    setStartingRevision(true)
    revisionStart.current = Date.now()
    try {
      const { data } = await premiumStudyService.startRevision()
      setRevision(data.data)
    } finally {
      setStartingRevision(false)
    }
  }

  const finishRevision = async (mcqResult) => {
    if (!revision?.session_id) return
    const duration = Math.round((Date.now() - revisionStart.current) / 1000)
    const { data } = await premiumStudyService.completeRevision(revision.session_id, {
      duration_seconds: duration,
      mcqs_solved: mcqResult?.total || 0,
      mcqs_correct: mcqResult?.correct || 0,
      topics_revised: (revision.flashcards || []).map((f) => f.topic).filter(Boolean),
    })
    setRevisionSummary(data.data)
    setRevision(null)
    premiumStudyService.dashboard().then(({ data: d }) => setPremium(d.data)).catch(() => {})
  }

  const totalPresent = courses.reduce((sum, c) => sum + (c.attendance_present || 0), 0)
  const totalSessions = courses.reduce((sum, c) => sum + (c.attendance_total || 0), 0)
  const overallAttendance = totalSessions > 0 ? Math.round((totalPresent / totalSessions) * 100) : null
  const daily = premium?.daily_challenge
  const weak = premium?.weak_areas || []
  const mistakes = premium?.mistakes || {}
  const weekly = premium?.recent_performance?.weekly || []
  const overall = premium?.recent_performance?.overall || {}

  if (revision?.mcqs?.length) {
    return (
      <div className="rounded-2xl border border-slate-100 bg-white p-6 shadow-soft">
        <McqPlayer
          questions={revision.mcqs}
          source="revision"
          title="Smart Revision Session"
          onClose={() => setRevision(null)}
          onFinished={finishRevision}
        />
      </div>
    )
  }

  if (revisionSummary) {
    return (
      <div className="space-y-6">
        <h2 className="font-display text-2xl font-bold text-navy">Revision Complete</h2>
        <div className="grid gap-4 sm:grid-cols-4">
          <StatCard label="MCQs Solved" value={revisionSummary.mcqs_solved} icon={FiTarget} />
          <StatCard label="Accuracy" value={`${Math.round(revisionSummary.accuracy)}%`} icon={FiTarget} tone="emerald" />
          <StatCard label="Time Spent" value={`${Math.floor(revisionSummary.duration_seconds / 60)}m`} icon={FiCalendar} />
          <StatCard label="Topics" value={(revisionSummary.topics_revised || []).length} icon={FiBookOpen} />
        </div>
        {revisionSummary.remaining_weak_areas?.length > 0 && (
          <div className="rounded-2xl border border-amber-100 bg-amber-50 p-5">
            <p className="font-semibold text-amber-800">Remaining weak areas</p>
            <ul className="mt-2 space-y-1 text-sm text-amber-700">
              {revisionSummary.remaining_weak_areas.map((a) => (
                <li key={a.topic || a.subject}>{a.topic || a.subject} — {a.accuracy}%</li>
              ))}
            </ul>
          </div>
        )}
        <button type="button" onClick={() => setRevisionSummary(null)} className="btn-primary text-sm">Back to dashboard</button>
      </div>
    )
  }

  return (
    <div>
      <p className="text-slate-500">Welcome, {user?.full_name}. Your personalized FCPS study hub.</p>

      {activity.length > 0 && (
        <section className="mt-6 rounded-2xl border border-primary/20 bg-gradient-to-br from-primary/5 via-white to-white p-5 shadow-soft">
          <div className="flex items-center gap-2">
            <FiBell className="text-emerald-600" size={20} />
            <h2 className="font-display text-lg font-bold text-navy">New course updates</h2>
          </div>
          <p className="mt-1 text-sm text-slate-500">
            Materials, quizzes, and assignments from your teachers — open them directly.
          </p>
          <div className="mt-3 space-y-2">
            {activity.map((n) => (
              <ActivityCard key={n.id} n={n} onOpened={markActivityOpened} />
            ))}
          </div>
        </section>
      )}

      <button
        type="button"
        onClick={startRevision}
        disabled={startingRevision}
        className="mt-6 flex w-full items-center justify-center gap-3 rounded-2xl bg-gradient-to-r from-primary to-primary-dark px-8 py-5 text-lg font-bold text-white shadow-lg transition hover:opacity-95 disabled:opacity-60"
      >
        <FiRefreshCw className={startingRevision ? 'animate-spin' : ''} size={24} />
        {startingRevision ? 'Preparing session…' : 'Revise Today'}
      </button>
      <p className="mt-2 text-center text-xs text-slate-400">~20–30 min personalized session from weak topics, mistakes & flashcards</p>

      <Link
        to="/student/study-material"
        className="mt-8 block rounded-2xl border border-slate-100 bg-white p-6 shadow-soft transition hover:border-primary/30 hover:shadow-md"
      >
        <div className="flex items-start justify-between gap-4">
          <div className="flex items-start gap-4">
            <div className="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-rose-50 text-rose-500">
              <FiFilm size={22} />
            </div>
            <div>
              <p className="text-xs font-semibold uppercase text-rose-500">Lecture Videos</p>
              <h3 className="mt-1 font-bold text-navy">Video lecture progress</h3>
              <p className="mt-1 text-sm text-slate-500">Mark videos watched as you complete them.</p>
            </div>
          </div>
          <span className="text-xs font-semibold text-primary">Open →</span>
        </div>
        <div className="mt-4 grid grid-cols-2 gap-3">
          <div className="rounded-xl bg-slate-50 px-4 py-3">
            <p className="text-2xl font-bold text-navy">{videoSummary.total || 0}</p>
            <p className="text-xs text-slate-500">Total video lectures uploaded</p>
          </div>
          <div className="rounded-xl bg-emerald-50 px-4 py-3">
            <p className="text-2xl font-bold text-emerald-700">{videoSummary.watched || 0}</p>
            <p className="text-xs text-slate-500">Total lecture videos watched</p>
          </div>
        </div>
      </Link>

      <div className="mt-8 grid gap-5 lg:grid-cols-2">
        {/* Daily Challenge */}
        <div className="rounded-2xl border border-primary/20 bg-gradient-to-br from-primary/5 to-white p-6 shadow-soft">
          <div className="flex items-start gap-4">
            <div className="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary"><FiZap size={22} /></div>
            <div className="flex-1">
              <p className="text-xs font-semibold uppercase text-primary">Daily Challenge</p>
              <h3 className="mt-1 font-bold text-navy">Today&apos;s Challenge</h3>
              {daily?.completed ? (
                <>
                  <p className="mt-2 flex items-center gap-1 text-sm font-semibold text-green-600">✓ Completed Today</p>
                  {daily.last_score != null && <p className="text-sm text-green-600">Score: {Math.round(daily.last_score)}%</p>}
                  <p className="mt-2 text-sm text-slate-500">Next in <Countdown seconds={daily.seconds_until_next} /></p>
                </>
              ) : daily?.available === false ? (
                <>
                  <p className="mt-2 text-sm text-slate-500">{daily.message || 'Ask your teacher to publish course quizzes.'}</p>
                </>
              ) : (
                <>
                  <p className="mt-1 text-sm text-slate-600">{daily?.total_questions || 10} Questions</p>
                  <Link to="/student/challenge" className="btn-primary mt-3 inline-block text-sm">Start Challenge</Link>
                </>
              )}
            </div>
          </div>
        </div>

        {/* Weak Areas */}
        <Link to="/student/weak-areas" className="block rounded-2xl border border-slate-100 bg-white p-6 shadow-soft transition hover:border-primary/30 hover:shadow-md">
          <div className="flex items-center justify-between gap-2">
            <div className="flex items-center gap-2">
              <FiTarget className="text-amber-500" />
              <h3 className="font-bold text-navy">Weak Areas</h3>
            </div>
            <span className="text-xs font-semibold text-primary">Open →</span>
          </div>
          {weak.length === 0 ? (
            <p className="mt-4 text-sm text-slate-500">Answer course quizzes or Daily Challenges to unlock topic-level weak-area analysis.</p>
          ) : (
            <ul className="mt-4 space-y-3">
              {weak.slice(0, 4).map((a) => {
                const name = a.topic || a.subject
                return (
                  <li key={name}>
                    <div className="flex justify-between text-sm">
                      <span className="font-medium text-navy">{name}</span>
                      <span className={a.accuracy < 70 ? 'text-red-500' : 'text-amber-600'}>{a.accuracy}%</span>
                    </div>
                    <ProgressBar value={a.accuracy} tone={a.accuracy < 70 ? 'rose' : 'amber'} className="mt-1" />
                    <p className="mt-1 text-xs text-slate-500">{a.message}</p>
                  </li>
                )
              })}
            </ul>
          )}
        </Link>

        {/* Study Planner */}
        <div className="rounded-2xl border border-slate-100 bg-white p-6 shadow-soft">
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-2"><FiCalendar className="text-primary" /><h3 className="font-bold text-navy">Study Planner</h3></div>
            <Link to="/student/planner" className="text-xs font-semibold text-primary hover:underline">Open →</Link>
          </div>
          {premium?.study_plan?.today_tasks?.length ? (
            <ul className="mt-4 space-y-2">
              {premium.study_plan.today_tasks.slice(0, 4).map((t) => (
                <li key={t.id} className="flex items-center gap-2 text-sm text-slate-600">
                  <span className={`h-2 w-2 rounded-full ${t.status === 'completed' ? 'bg-green-500' : 'bg-slate-300'}`} />
                  {t.title}
                </li>
              ))}
            </ul>
          ) : (
            <p className="mt-4 text-sm text-slate-500">Set your exam date to get a daily study plan.</p>
          )}
        </div>

        {/* My Mistakes */}
        <div className="rounded-2xl border border-slate-100 bg-white p-6 shadow-soft">
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-2"><FiAlertCircle className="text-red-500" /><h3 className="font-bold text-navy">My Mistakes</h3></div>
            <Link to="/student/mistakes" className="text-xs font-semibold text-primary hover:underline">View all →</Link>
          </div>
          <div className="mt-4 grid grid-cols-3 gap-2 text-center">
            <div><p className="text-2xl font-bold text-navy">{mistakes.total || 0}</p><p className="text-xs text-slate-400">Total</p></div>
            <div><p className="text-2xl font-bold text-red-500">{mistakes.remaining || 0}</p><p className="text-xs text-slate-400">Remaining</p></div>
            <div><p className="text-2xl font-bold text-green-600">{mistakes.mastered || 0}</p><p className="text-xs text-slate-400">Mastered</p></div>
          </div>
        </div>
      </div>

      <div className="mt-6 rounded-2xl border border-primary/20 bg-gradient-to-br from-primary/5 to-white p-6 shadow-soft">
        <div className="flex flex-wrap items-start justify-between gap-4">
          <div className="flex items-start gap-4">
            <div className="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
              <FiPackage size={22} />
            </div>
            <div>
              <p className="text-xs font-semibold uppercase text-primary">Study Pack</p>
              <h3 className="mt-1 font-bold text-navy">Summary · Mnemonics · Flashcards · Cases</h3>
              <p className="mt-1 text-sm text-slate-500">Teacher-published revision material for your lectures.</p>
            </div>
          </div>
          <Link to="/student/study-pack" className="btn-primary shrink-0 text-sm">Open Study Pack</Link>
        </div>
      </div>

      <div className="mt-6 flex flex-wrap gap-3">
        <Link to="/student/study-material" className="btn-secondary text-sm"><FiFilm className="inline mr-1" /> Lecture Videos</Link>
        <Link to="/student/study-pack" className="btn-secondary text-sm"><FiPackage className="inline mr-1" /> Study Pack</Link>
        <Link to="/student/question-bank" className="btn-secondary text-sm"><FiBookOpen className="inline mr-1" /> Question Bank</Link>
        <Link to="/student/planner" className="btn-secondary text-sm"><FiCalendar className="inline mr-1" /> Study Planner</Link>
        <Link to="/student/progress" className="btn-secondary text-sm">Performance Analytics</Link>
      </div>

      <div className="mt-8"><StreakWidget /></div>

      {overall.attempts > 0 && (
        <div className="mt-8 rounded-2xl border border-slate-100 bg-white p-6 shadow-soft">
          <h3 className="font-display text-lg font-bold text-navy">Recent Performance</h3>
          <div className="mt-4 grid gap-4 sm:grid-cols-3">
            <StatCard label="MCQ Attempts" value={overall.attempts} icon={FiTarget} />
            <StatCard label="Avg Score" value={`${Math.round(overall.avg_score || 0)}%`} icon={FiTarget} tone="emerald" />
            <StatCard label="Questions Answered" value={overall.total_questions || 0} icon={FiBookOpen} />
          </div>
          {weekly.length > 0 && (
            <div className="mt-6 flex items-end gap-2 h-24">
              {weekly.map((w) => (
                <div key={w.yw || w.week_start} className="flex flex-1 flex-col items-center gap-1">
                  <div className="flex h-20 w-full items-end rounded-t bg-slate-50">
                    <div className="w-full rounded-t bg-primary transition-all" style={{ height: `${Math.max(4, w.avg_score || 0)}%` }} />
                  </div>
                  <span className="text-[10px] text-slate-400">{w.avg_score}%</span>
                </div>
              ))}
            </div>
          )}
        </div>
      )}

      {stats && (
        <div className="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
          <StatCard label="My Courses" value={stats.enrolled_courses} icon={FiBookOpen} />
          <StatCard label="Pending Assignments" value={stats.pending_assignments} icon={FiCalendar} tone="amber" />
          <StatCard label="Attendance" value={overallAttendance ?? 0} icon={FiTarget} tone="violet" hint={overallAttendance == null ? 'No classes yet' : `${totalPresent}/${totalSessions}`} />
        </div>
      )}
    </div>
  )
}
