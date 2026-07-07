import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { FiBookOpen, FiUsers, FiClipboard } from 'react-icons/fi'
import { useAuth } from '../../context/AuthContext'
import { dashboardService, myCoursesService } from '../../services/api'
import StatCard from '../../components/dashboard/StatCard'
import ProgressBar from '../../components/dashboard/ProgressBar'

export default function TeacherDashboard() {
  const { user } = useAuth()
  const [stats, setStats] = useState(null)
  const [courses, setCourses] = useState([])

  useEffect(() => {
    dashboardService.teacher().then(({ data }) => setStats(data.data)).catch(() => {})
    myCoursesService.list().then(({ data }) => setCourses(data.data || [])).catch(() => {})
  }, [])

  return (
    <div>
      <p className="text-slate-500">Welcome, {user?.full_name}. Upload content, quizzes, and assignments for your assigned courses.</p>

      {stats && (
        <div className="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
          <StatCard label="Assigned Courses" value={stats.assigned_courses} icon={FiBookOpen} />
          <StatCard label="Students" value={stats.total_students} icon={FiUsers} tone="emerald" />
          <StatCard label="Pending Assignments" value={stats.pending_assignments} icon={FiClipboard} tone="amber" />
          <StatCard label="Quiz Reviews" value={stats.pending_quiz_reviews} icon={FiClipboard} tone="violet" />
        </div>
      )}

      {courses.length > 0 && (
        <div className="mt-10">
          <h3 className="font-display text-lg font-bold text-navy">Course Progress</h3>
          <div className="mt-4 grid gap-4 sm:grid-cols-2">
            {courses.map((c) => (
              <div key={c.id} className="rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_2px_16px_rgba(15,23,42,0.05)]">
                <div className="flex items-center justify-between">
                  <p className="font-semibold text-navy">{c.title}</p>
                  {typeof c.enrolled_count !== 'undefined' && (
                    <span className="text-xs text-slate-400">{c.enrolled_count} students</span>
                  )}
                </div>
                {c.duration && <p className="text-xs text-slate-400">{c.duration}</p>}
                <div className="mt-4">
                  <ProgressBar
                    label="Course timeline"
                    value={c.time_progress}
                    tone="primary"
                    valueLabel={`${c.time_progress || 0}%`}
                  />
                </div>
              </div>
            ))}
          </div>
        </div>
      )}

      <Link to="/teacher/courses" className="btn-primary mt-10 inline-flex text-sm">
        Go to My Courses
      </Link>
    </div>
  )
}
