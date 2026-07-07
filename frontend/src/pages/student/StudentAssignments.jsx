import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { assignmentService } from '../../services/api'
import Alert from '../../components/dashboard/Alert'

export default function StudentAssignments() {
  const [assignments, setAssignments] = useState([])
  const [error, setError] = useState('')

  useEffect(() => {
    assignmentService
      .my()
      .then(({ data }) => setAssignments(data.data || []))
      .catch(() => setError('Could not load assignments'))
  }, [])

  return (
    <div>
      <h2 className="font-display text-xl font-bold text-navy">Assignments & Grades</h2>
      <p className="text-sm text-slate-500">All assignments across your enrolled courses</p>

      {error && <div className="mt-4"><Alert>{error}</Alert></div>}

      <div className="mt-6 overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-soft">
        <table className="w-full text-left text-sm">
          <thead className="border-b border-slate-100 bg-slate-50 text-xs uppercase text-slate-500">
            <tr>
              <th className="px-4 py-3">Assignment</th>
              <th className="px-4 py-3">Course</th>
              <th className="px-4 py-3">Due</th>
              <th className="px-4 py-3">Status</th>
              <th className="px-4 py-3">Marks</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-slate-100">
            {assignments.map((a) => (
              <tr key={a.id}>
                <td className="px-4 py-3 font-medium text-navy">{a.title}</td>
                <td className="px-4 py-3 text-slate-600">{a.course_title}</td>
                <td className="px-4 py-3 text-slate-600">{new Date(a.due_date).toLocaleDateString()}</td>
                <td className="px-4 py-3">
                  <span className={`rounded-full px-2 py-0.5 text-xs ${
                    a.submission_status === 'graded' ? 'bg-green-100 text-green-700' :
                    a.submission_status ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-600'
                  }`}>
                    {a.submission_status || 'Not submitted'}
                  </span>
                </td>
                <td className="px-4 py-3 font-medium text-navy">
                  {a.marks != null ? `${a.marks} / ${a.max_marks}` : '—'}
                </td>
              </tr>
            ))}
            {assignments.length === 0 && (
              <tr><td colSpan={5} className="px-4 py-8 text-center text-slate-400">No assignments yet</td></tr>
            )}
          </tbody>
        </table>
      </div>

      <Link to="/student/courses" className="btn-secondary mt-6 inline-flex text-sm">Go to courses to submit</Link>
    </div>
  )
}
