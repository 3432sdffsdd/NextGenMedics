import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { FiBookOpen, FiChevronRight } from 'react-icons/fi'
import { myCoursesService } from '../../services/api'
import Alert from '../../components/dashboard/Alert'

export default function TeacherCourses() {
  const [courses, setCourses] = useState([])
  const [error, setError] = useState('')

  useEffect(() => {
    myCoursesService
      .list()
      .then(({ data }) => setCourses(data.data || []))
      .catch(() => setError('Failed to load courses. Ensure you are assigned to courses by admin.'))
  }, [])

  return (
    <div>
      <h2 className="font-display text-xl font-bold text-navy">My Courses</h2>
      <p className="text-sm text-slate-500">Courses assigned to you by the administrator</p>

      {error && <div className="mt-4"><Alert>{error}</Alert></div>}

      <div className="mt-6 grid gap-4 sm:grid-cols-2">
        {courses.map((c) => (
          <Link
            key={c.id}
            to={`/teacher/courses/${c.id}`}
            className="group flex items-center justify-between rounded-2xl border border-slate-100 bg-white p-6 shadow-soft transition hover:border-primary/30 hover:shadow-soft-lg"
          >
            <div className="flex items-start gap-4">
              <div className="rounded-xl bg-primary/10 p-3 text-primary">
                <FiBookOpen size={22} />
              </div>
              <div>
                <h3 className="font-semibold text-navy group-hover:text-primary">{c.title}</h3>
                <p className="mt-1 text-xs text-slate-400">{c.category_name || 'Course'}</p>
                <p className="mt-2 text-sm text-slate-500">{c.enrolled_count ?? 0} students enrolled</p>
              </div>
            </div>
            <FiChevronRight className="text-slate-300 group-hover:text-primary" />
          </Link>
        ))}
        {courses.length === 0 && !error && (
          <p className="col-span-2 rounded-2xl border border-dashed border-slate-200 p-8 text-center text-slate-400">
            No courses assigned yet. Ask your administrator to create a course and assign you as teacher.
          </p>
        )}
      </div>
    </div>
  )
}
