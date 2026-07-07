import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { FiPlus, FiEdit2, FiTrash2, FiCheck, FiArchive } from 'react-icons/fi'
import { adminCoursesService } from '../../services/api'
import { useConfirm } from '../../context/ConfirmContext'
import { useToast } from '../../context/ToastContext'
import Alert from '../../components/dashboard/Alert'

export default function AdminCourses() {
  const confirm = useConfirm()
  const toast = useToast()
  const [courses, setCourses] = useState([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')

  const load = () => {
    setLoading(true)
    adminCoursesService
      .list({ per_page: 50 })
      .then(({ data }) => setCourses(data.data || []))
      .catch(() => setError('Failed to load courses'))
      .finally(() => setLoading(false))
  }

  useEffect(() => { load() }, [])

  const handlePublish = async (id) => {
    await adminCoursesService.publish(id)
    load()
  }

  const handleArchive = async (id) => {
    await adminCoursesService.archive(id)
    load()
  }

  const handleDelete = async (id) => {
    if (!(await confirm({ title: 'Delete course', message: 'Are you sure you want to delete this course?', confirmText: 'Yes, Delete', tone: 'danger' }))) return
    try {
      await adminCoursesService.delete(id)
      toast.success('Deleted successfully')
      load()
    } catch {
      toast.error('Could not delete course')
    }
  }

  return (
    <div>
      <div className="flex items-center justify-between">
        <div>
          <h2 className="font-display text-xl font-bold text-navy">Courses</h2>
          <p className="text-sm text-slate-500">Create courses and assign teachers</p>
        </div>
        <Link to="/admin/courses/new" className="btn-primary text-sm">
          <FiPlus /> Create Course
        </Link>
      </div>

      {error && <div className="mt-4"><Alert>{error}</Alert></div>}

      <div className="mt-6 overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-soft">
        {loading ? (
          <p className="p-6 text-sm text-slate-500">Loading...</p>
        ) : (
          <table className="w-full text-left text-sm">
            <thead className="border-b border-slate-100 bg-slate-50 text-xs uppercase text-slate-500">
              <tr>
                <th className="px-4 py-3">Title</th>
                <th className="px-4 py-3">Teacher</th>
                <th className="px-4 py-3">Status</th>
                <th className="px-4 py-3">Enrolled</th>
                <th className="px-4 py-3 text-right">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {courses.map((c) => (
                <tr key={c.id} className="hover:bg-slate-50">
                  <td className="px-4 py-3">
                    <p className="font-medium text-navy">{c.title}</p>
                    <p className="text-xs text-slate-400">{c.category_name || 'Uncategorized'}</p>
                  </td>
                  <td className="px-4 py-3 text-slate-600">{c.teacher_names || c.teacher_name || '—'}</td>
                  <td className="px-4 py-3">
                    <span className={`rounded-full px-2 py-0.5 text-xs font-medium ${
                      c.status === 'published' ? 'bg-green-100 text-green-700' :
                      c.status === 'draft' ? 'bg-slate-100 text-slate-600' : 'bg-amber-100 text-amber-700'
                    }`}>
                      {c.status}
                    </span>
                  </td>
                  <td className="px-4 py-3 text-slate-600">{c.enrolled_count ?? 0}</td>
                  <td className="px-4 py-3">
                    <div className="flex justify-end gap-1">
                      <Link to={`/admin/courses/${c.id}`} className="rounded-lg p-2 text-slate-500 hover:bg-slate-100" title="Manage">
                        <FiEdit2 size={16} />
                      </Link>
                      {c.status !== 'published' && (
                        <button type="button" onClick={() => handlePublish(c.id)} className="rounded-lg p-2 text-green-600 hover:bg-green-50" title="Publish">
                          <FiCheck size={16} />
                        </button>
                      )}
                      {c.status !== 'archived' && (
                        <button type="button" onClick={() => handleArchive(c.id)} className="rounded-lg p-2 text-amber-600 hover:bg-amber-50" title="Archive">
                          <FiArchive size={16} />
                        </button>
                      )}
                      <button type="button" onClick={() => handleDelete(c.id)} className="rounded-lg p-2 text-red-500 hover:bg-red-50" title="Delete">
                        <FiTrash2 size={16} />
                      </button>
                    </div>
                  </td>
                </tr>
              ))}
              {courses.length === 0 && (
                <tr><td colSpan={5} className="px-4 py-8 text-center text-slate-400">No courses yet.</td></tr>
              )}
            </tbody>
          </table>
        )}
      </div>
    </div>
  )
}
