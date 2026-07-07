import { useCallback, useEffect, useState } from 'react'
import { FiVideo, FiPlus, FiEdit2, FiTrash2, FiExternalLink, FiClock } from 'react-icons/fi'
import { sessionsService } from '../../services/api'
import { useToast } from '../../context/ToastContext'
import { useConfirm } from '../../context/ConfirmContext'
import { Modal, Button, Badge, EmptyState } from '../../components/ui'
import { formatDateTime } from '../../utils/files'

const empty = { title: 'Live Class', meeting_url: '', scheduled_at: '', duration_minutes: 60 }

const isValidUrl = (u) => { try { const p = new URL(u); return p.protocol === 'http:' || p.protocol === 'https:' } catch { return false } }

export default function LiveClassManager({ courseId, canManage = false }) {
  const toast = useToast()
  const confirm = useConfirm()
  const [items, setItems] = useState([])
  const [loading, setLoading] = useState(true)
  const [editing, setEditing] = useState(null)
  const [form, setForm] = useState(empty)
  const [saving, setSaving] = useState(false)

  const load = useCallback(() => {
    setLoading(true)
    sessionsService.byCourse(courseId).then(({ data }) => setItems(data.data || [])).finally(() => setLoading(false))
  }, [courseId])
  useEffect(() => { load() }, [load])

  const openNew = () => { setForm(empty); setEditing({}) }
  const openEdit = (s) => {
    setForm({ title: s.title || 'Live Class', meeting_url: s.meeting_url || '', scheduled_at: s.scheduled_at ? s.scheduled_at.replace(' ', 'T').slice(0, 16) : '', duration_minutes: s.duration_minutes || 60 })
    setEditing(s)
  }

  const save = async () => {
    if (!form.title.trim()) return toast.error('Title is required')
    if (!isValidUrl(form.meeting_url)) return toast.error('Invalid Zoom URL')
    setSaving(true)
    try {
      const payload = { ...form, course_id: courseId, scheduled_at: form.scheduled_at || null }
      if (editing.id) { await sessionsService.update(editing.id, payload); toast.success('Live class updated') }
      else { await sessionsService.create(payload); toast.success('Live class added') }
      setEditing(null); load()
    } catch (err) { toast.error(err.response?.data?.message || 'Could not save live class') }
    finally { setSaving(false) }
  }

  const remove = async (s) => {
    if (!(await confirm({ title: 'Delete live class', message: `Delete "${s.title}"?` }))) return
    try { await sessionsService.cancel(s.id); toast.success('Deleted successfully'); load() }
    catch { toast.error('Could not delete live class') }
  }

  const active = items.filter((s) => s.status !== 'cancelled')
  const primary = active[0]

  return (
    <div className="space-y-5">
      <div className="flex items-center justify-between">
        <h3 className="font-display text-lg font-bold text-navy">Live Class</h3>
        {canManage && <Button icon={FiPlus} onClick={openNew}>Add Zoom link</Button>}
      </div>

      {/* Prominent join for students / everyone */}
      {primary && primary.meeting_url && (
        <div className="rounded-2xl border border-primary/20 bg-gradient-to-br from-primary/5 to-transparent p-6 text-center">
          <div className="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-primary/10 text-primary"><FiVideo size={26} /></div>
          <p className="mt-3 font-display text-lg font-bold text-navy">{primary.title}</p>
          {primary.scheduled_at && <p className="text-sm text-slate-400">{formatDateTime(primary.scheduled_at)}</p>}
          <a href={primary.meeting_url} target="_blank" rel="noreferrer" className="btn-primary mt-4 inline-flex items-center gap-2 px-6 py-3 text-base">
            <FiVideo /> Join Live Class
          </a>
        </div>
      )}

      {loading ? (
        <p className="text-slate-400">Loading…</p>
      ) : active.length === 0 ? (
        <EmptyState icon={FiVideo} title="No live class scheduled" description={canManage ? 'Add a Zoom link so students can join live classes.' : 'Your teacher has not scheduled a live class yet.'}
          action={canManage ? <Button icon={FiPlus} onClick={openNew}>Add Zoom link</Button> : null} />
      ) : (
        <div className="space-y-2">
          {active.map((s) => (
            <div key={s.id} className="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-100 bg-white p-4 shadow-soft">
              <div>
                <div className="flex items-center gap-2">
                  <p className="font-medium text-navy">{s.title}</p>
                  {s.status && <Badge status={s.status}>{s.status}</Badge>}
                </div>
                <p className="mt-0.5 flex items-center gap-1.5 text-xs text-slate-400"><FiClock size={12} /> {s.scheduled_at ? formatDateTime(s.scheduled_at) : 'Anytime'} · {s.duration_minutes} min</p>
              </div>
              <div className="flex items-center gap-2">
                {s.meeting_url && <a href={s.meeting_url} target="_blank" rel="noreferrer" className="inline-flex items-center gap-1.5 rounded-lg bg-primary/10 px-3 py-1.5 text-sm font-semibold text-primary hover:bg-primary/20"><FiExternalLink size={14} /> Join</a>}
                {canManage && <>
                  <button type="button" onClick={() => openEdit(s)} className="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-primary"><FiEdit2 size={15} /></button>
                  <button type="button" onClick={() => remove(s)} className="rounded-lg p-2 text-slate-400 hover:bg-red-50 hover:text-red-500"><FiTrash2 size={15} /></button>
                </>}
              </div>
            </div>
          ))}
        </div>
      )}

      <Modal open={!!editing} onClose={() => !saving && setEditing(null)} title={editing?.id ? 'Edit live class' : 'Add live class'}
        footer={<>
          <Button variant="outline" onClick={() => setEditing(null)} disabled={saving}>Cancel</Button>
          <Button onClick={save} loading={saving}>{editing?.id ? 'Save' : 'Add'}</Button>
        </>}>
        <div className="space-y-4">
          <div>
            <label className="text-sm font-medium text-slate-600">Title *</label>
            <input value={form.title} onChange={(e) => setForm((f) => ({ ...f, title: e.target.value }))} className="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" />
          </div>
          <div>
            <label className="text-sm font-medium text-slate-600">Zoom / Meeting URL *</label>
            <input value={form.meeting_url} onChange={(e) => setForm((f) => ({ ...f, meeting_url: e.target.value }))} placeholder="https://zoom.us/j/..." className="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" />
            <p className="mt-1 text-xs text-slate-400">This link can be reused for all classes.</p>
          </div>
          <div className="grid gap-4 sm:grid-cols-2">
            <div>
              <label className="text-sm font-medium text-slate-600">Scheduled at (optional)</label>
              <input type="datetime-local" value={form.scheduled_at} onChange={(e) => setForm((f) => ({ ...f, scheduled_at: e.target.value }))} className="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" />
            </div>
            <div>
              <label className="text-sm font-medium text-slate-600">Duration (min)</label>
              <input type="number" value={form.duration_minutes} onChange={(e) => setForm((f) => ({ ...f, duration_minutes: e.target.value }))} className="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" />
            </div>
          </div>
        </div>
      </Modal>
    </div>
  )
}
