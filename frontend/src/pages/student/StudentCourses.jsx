import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { FiBookOpen, FiChevronRight } from 'react-icons/fi'
import { myCoursesService } from '../../services/api'
import Alert from '../../components/dashboard/Alert'

export default function StudentCourses() {
  const [courses, setCourses] = useState([])
  const [error, setError] = useState('')

  useEffect(() => {
    myCoursesService
      .list()
      .then(({ data }) => setCourses(data.data || []))
      .catch(() => setError('No courses found. Ask admin to enroll you in a course.'))
  }, [])

  return (
    <div>
      <h2 className="font-display text-xl font-bold text-navy">My Courses</h2>
      <p className="text-sm text-slate-500">Courses you are enrolled in</p>

      {error && <div className="mt-4"><Alert type="info">{error}</Alert></div>}

      <div className="mt-6 grid gap-4 sm:grid-cols-2">
        {courses.map((c) => (
          <Link
            key={c.id}
            to={`/student/courses/${c.id}`}
            className="group flex items-center justify-between rounded-2xl border border-slate-100 bg-white p-6 shadow-soft hover:border-primary/30"
          >
            <div className="flex items-start gap-4">
              <div className="rounded-xl bg-primary/10 p-3 text-primary"><FiBookOpen size={22} /></div>
              <div>
                <h3 className="font-semibold text-navy group-hover:text-primary">{c.title}</h3>
                <p className="mt-1 text-xs text-slate-400">Teacher: {c.teacher_name || '—'}</p>
                <p className="mt-2 text-sm text-slate-500">Progress: {c.progress ?? 0}%</p>
              </div>
            </div>
            <FiChevronRight className="text-slate-300 group-hover:text-primary" />
          </Link>
        ))}
        {courses.length === 0 && !error && (
          <p className="col-span-2 rounded-2xl border border-dashed border-slate-200 p-8 text-center text-slate-400">
            You are not enrolled in any course yet.
          </p>
        )}
      </div>
    </div>
  )
}
