import { useCallback, useEffect, useMemo, useState } from 'react'
import { Link, useParams } from 'react-router-dom'
import { adminCoursesService, myCoursesService, attendanceService, notificationsService } from '../../services/api'
import CourseDiscussion from '../../components/dashboard/CourseDiscussion'
import CourseMonthSchedule from '../../components/dashboard/CourseMonthSchedule'
import AiTutorPanel from '../../components/dashboard/AiTutorPanel'
import ContentManager from './ContentManager'
import QuizManager from './QuizManager'
import AssignmentManager from './AssignmentManager'
import AttendanceManager from './AttendanceManager'
import LiveClassManager from './LiveClassManager'
import AnnouncementsManager from './AnnouncementsManager'
import TabBadge from '../../components/dashboard/TabBadge'

const TABS = ['content', 'study tools', 'quizzes', 'assignments', 'attendance', 'live class', 'announcements', 'discussions', 'schedule']
const BADGE_TABS = new Set(['quizzes', 'assignments', 'discussions'])

export default function TeacherCourseHub() {
  const { id } = useParams()
  const courseId = Number(id)
  const [tab, setTab] = useState('content')
  const [course, setCourse] = useState(null)
  const [structure, setStructure] = useState([])
  const [students, setStudents] = useState([])
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

  const loadStructure = useCallback(() => {
    adminCoursesService.structure(courseId)
      .then(({ data }) => setStructure(data.data || []))
      .catch((err) => {
        setStructure([])
        console.error('Failed to load course structure', err)
      })
  }, [courseId])

  useEffect(() => {
    myCoursesService.list().then(({ data }) => setCourse((data.data || []).find((x) => x.id === courseId))).catch(() => {})
    loadStructure()
    attendanceService.courseStudents(courseId).then(({ data }) => setStudents(data.data || [])).catch(() => {})
  }, [courseId, loadStructure])

  const allLectures = useMemo(() =>
    structure.flatMap((m) => (m.chapters || []).flatMap((ch) => (ch.lectures || []).map((lec) => ({ ...lec, chapterTitle: ch.title })))),
    [structure])

  return (
    <div>
      <Link to="/teacher/courses" className="text-sm font-semibold text-primary hover:underline">← Back to courses</Link>
      <h2 className="mt-4 font-display text-xl font-bold text-navy">{course?.title || 'Course'}</h2>
      <p className="text-sm text-slate-500">Manage content, quizzes, assignments, attendance, live classes and more.</p>

      <div className="mt-6 flex flex-wrap gap-1 overflow-x-auto border-b border-slate-200">
        {TABS.map((t) => (
          <button
            key={t}
            type="button"
            onClick={() => selectTab(t)}
            className={`inline-flex items-center whitespace-nowrap px-4 py-2 text-sm font-medium capitalize transition-colors ${
              tab === t ? 'border-b-2 border-primary text-primary' : 'text-slate-500 hover:text-slate-700'
            }`}
          >
            {t}
            {BADGE_TABS.has(t) && <TabBadge count={tabBadges[t]} />}
          </button>
        ))}
      </div>

      <div className="mt-6">
        {tab === 'content' && <ContentManager courseId={courseId} structure={structure} reload={loadStructure} />}
        {tab === 'study tools' && <AiTutorPanel lectures={allLectures} />}
        {tab === 'quizzes' && <QuizManager courseId={courseId} />}
        <div className={tab === 'assignments' ? '' : 'hidden'}>
          <AssignmentManager courseId={courseId} />
        </div>
        {tab === 'attendance' && <AttendanceManager courseId={courseId} students={students} />}
        {tab === 'live class' && <LiveClassManager courseId={courseId} canManage />}
        {tab === 'announcements' && <AnnouncementsManager courseId={courseId} />}
        {tab === 'discussions' && <CourseDiscussion courseId={courseId} canModerate />}
        {tab === 'schedule' && <CourseMonthSchedule courseId={courseId} canEdit />}
      </div>
    </div>
  )
}
