import { useEffect, useState } from 'react'
import { FiCalendar } from 'react-icons/fi'
import { sessionsService } from '../../services/api'
import { useConfirm } from '../../context/ConfirmContext'
import { useToast } from '../../context/ToastContext'
import { EmptyState } from '../ui'

export default function CourseSchedule({ courseId, canManage = false }) {
  const confirm = useConfirm()
  const toast = useToast()
  const [sessions, setSessions] = useState([])
  const [form, setForm] = useState({ title: '', description: '', meeting_url: '', scheduled_at: '', duration_minutes: 60 })
  const [saving, setSaving] = useState(false)

  const load = () => {
    sessionsService.byCourse(courseId).then(({ data }) => setSessions(data.data || [])).catch(() => {})
  }

  useEffect(() => { load() }, [courseId])

  const isValidUrl = (u) => { try { const p = new URL(u); return p.protocol === 'http:' || p.protocol === 'https:' } catch { return !u } }

  const handleCreate = async (e) => {
    e.preventDefault()
    if (!form.title.trim()) return toast.error('Title is required')
    if (form.meeting_url && !isValidUrl(form.meeting_url)) return toast.error('Invalid meeting URL')
    setSaving(true)
    try {
      await sessionsService.create({ ...form, course_id: courseId, duration_minutes: Number(form.duration_minutes) })
      setForm({ title: '', description: '', meeting_url: '', scheduled_at: '', duration_minutes: 60 })
      toast.success('Class scheduled successfully')
      load()
    } catch {
      toast.error('Could not schedule class')
    } finally {
      setSaving(false)
    }
  }

  const handleCancel = async (id) => {
    if (!(await confirm({ title: 'Cancel class', message: 'Cancel this class?', confirmText: 'Yes, cancel', tone: 'danger' }))) return
    try {
      await sessionsService.cancel(id)
      toast.success('Class cancelled')
      load()
    } catch {
      toast.error('Could not cancel class')
    }
  }

  const active = sessions.filter((s) => s.status !== 'cancelled')

  return (
    <div>
      {canManage && (
        <form onSubmit={handleCreate} className="rounded-2xl border border-slate-100 bg-white p-6 shadow-soft">
          <h3 className="font-semibold text-navy">Schedule Live Class</h3>
          <div className="mt-4 grid gap-3 sm:grid-cols-2">
            <input required placeholder="Class title" value={form.title} onChange={(e) => setForm({ ...form, title: e.target.value })}
              className="rounded-xl border border-slate-200 px-3 py-2 text-sm sm:col-span-2" />
            <textarea placeholder="Description" value={form.description} onChange={(e) => setForm({ ...form, description: e.target.value })}
              rows={2} className="rounded-xl border border-slate-200 px-3 py-2 text-sm sm:col-span-2" />
            <input required type="datetime-local" value={form.scheduled_at} onChange={(e) => setForm({ ...form, scheduled_at: e.target.value })}
              className="rounded-xl border border-slate-200 px-3 py-2 text-sm" />
            <input type="number" placeholder="Duration (min)" value={form.duration_minutes} onChange={(e) => setForm({ ...form, duration_minutes: e.target.value })}
              className="rounded-xl border border-slate-200 px-3 py-2 text-sm" />
            <input placeholder="Meeting URL (Zoom, etc.)" value={form.meeting_url} onChange={(e) => setForm({ ...form, meeting_url: e.target.value })}
              className="rounded-xl border border-slate-200 px-3 py-2 text-sm sm:col-span-2" />
          </div>
          <button type="submit" disabled={saving} className="btn-primary mt-4 text-sm">{saving ? 'Saving…' : 'Schedule Class'}</button>
        </form>
      )}

      <div className="mt-6 overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-soft">
        {active.length === 0 ? (
          <EmptyState icon={FiCalendar} title="No classes scheduled" description={canManage ? 'Schedule a one-off live class above.' : 'No upcoming scheduled classes.'} className="py-10" />
        ) : (
          <table className="w-full text-left text-sm">
            <thead className="bg-slate-50 text-xs uppercase text-slate-500">
              <tr>
                <th className="px-4 py-3">Date & Time</th>
                <th className="px-4 py-3">Title</th>
                <th className="px-4 py-3">Duration</th>
                <th className="px-4 py-3">Status</th>
                <th className="px-4 py-3">Link</th>
                {canManage && <th className="px-4 py-3" />}
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {active.map((s) => (
                <tr key={s.id}>
                  <td className="px-4 py-3">{new Date(s.scheduled_at).toLocaleString()}</td>
                  <td className="px-4 py-3 font-medium text-navy">{s.title}</td>
                  <td className="px-4 py-3">{s.duration_minutes} min</td>
                  <td className="px-4 py-3 capitalize">{s.status}</td>
                  <td className="px-4 py-3">
                    {s.meeting_url ? <a href={s.meeting_url} target="_blank" rel="noreferrer" className="text-primary hover:underline">Join</a> : '—'}
                  </td>
                  {canManage && (
                    <td className="px-4 py-3">
                      {s.status === 'scheduled' && (
                        <button type="button" onClick={() => handleCancel(s.id)} className="text-red-500 hover:underline">Cancel</button>
                      )}
                    </td>
                  )}
                </tr>
              ))}
            </tbody>
          </table>
        )}
      </div>
    </div>
  )
}
