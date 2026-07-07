import { useCallback, useEffect, useState } from 'react'
import { FiPlus, FiEdit2, FiTrash2, FiBell, FiClock, FiBookmark } from 'react-icons/fi'
import { announcementsService } from '../../services/api'
import { useToast } from '../../context/ToastContext'
import { useConfirm } from '../../context/ConfirmContext'
import { Modal, Button, Badge, EmptyState } from '../../components/ui'
import { formatDateTime } from '../../utils/files'

const empty = { title: '', content: '', is_pinned: 0, published_at: '' }

export default function AnnouncementsManager({ courseId }) {
  const toast = useToast()
  const confirm = useConfirm()
  const [items, setItems] = useState([])
  const [loading, setLoading] = useState(true)
  const [editing, setEditing] = useState(null)
  const [form, setForm] = useState(empty)
  const [saving, setSaving] = useState(false)

  const load = useCallback(() => {
    setLoading(true)
    announcementsService.list().then(({ data }) => {
      const rows = Array.isArray(data.data) ? data.data : (data.data?.items || [])
      setItems(rows.filter((a) => Number(a.course_id) === Number(courseId)))
    }).finally(() => setLoading(false))
  }, [courseId])
  useEffect(() => { load() }, [load])

  const openNew = () => { setForm(empty); setEditing({}) }
  const openEdit = (a) => {
    setForm({ title: a.title || '', content: a.content || '', is_pinned: a.is_pinned ? 1 : 0, published_at: a.published_at ? a.published_at.replace(' ', 'T').slice(0, 16) : '' })
    setEditing(a)
  }

  const save = async () => {
    if (!courseId || Number.isNaN(Number(courseId))) {
      return toast.error('Invalid course — open the course from My Courses and try again')
    }
    if (!form.title.trim()) return toast.error('Title is required')
    if (!form.content.trim()) return toast.error('Content is required')
    const payload = {
      title: form.title.trim(), content: form.content.trim(), is_pinned: form.is_pinned,
      published_at: form.published_at ? form.published_at.replace('T', ' ') + ':00' : null,
      course_id: Number(courseId),
    }
    setSaving(true)
    try {
      if (editing.id) { await announcementsService.update(editing.id, payload); toast.success('Announcement updated successfully') }
      else { await announcementsService.create(payload); toast.success('Announcement created successfully') }
      setEditing(null); load()
    } catch (err) {
      const fieldErrors = err.response?.data?.errors
      const detail = fieldErrors ? Object.values(fieldErrors).flat().join(' ') : null
      toast.error(detail || err.response?.data?.message || 'Could not save announcement')
    }
    finally { setSaving(false) }
  }

  const remove = async (a) => {
    if (!(await confirm({ title: 'Delete announcement', message: `Are you sure you want to delete "${a.title}"?` }))) return
    try { await announcementsService.remove(a.id); toast.success('Deleted successfully'); load() }
    catch { toast.error('Could not delete announcement') }
  }

  const togglePin = async (a) => {
    try { await announcementsService.update(a.id, { is_pinned: a.is_pinned ? 0 : 1 }); toast.success(a.is_pinned ? 'Unpinned' : 'Pinned'); load() }
    catch { toast.error('Could not update announcement') }
  }

  const now = Date.now()

  return (
    <div className="space-y-4">
      <div className="flex items-center justify-between">
        <h3 className="font-display text-lg font-bold text-navy">Announcements</h3>
        <Button icon={FiPlus} onClick={openNew}>New announcement</Button>
      </div>

      {loading ? (
        <p className="text-slate-400">Loading…</p>
      ) : items.length === 0 ? (
        <EmptyState icon={FiBell} title="No announcements yet" description="Post an announcement to keep your students informed."
          action={<Button icon={FiPlus} onClick={openNew}>New announcement</Button>} />
      ) : (
        <div className="space-y-3">
          {items.map((a) => {
            const scheduled = a.published_at && new Date(a.published_at.replace(' ', 'T')).getTime() > now
            return (
              <div key={a.id} className={`rounded-2xl border p-5 shadow-soft ${a.is_pinned ? 'border-primary/30 bg-primary/5' : 'border-slate-100 bg-white'}`}>
                <div className="flex flex-wrap items-start justify-between gap-3">
                  <div className="min-w-0">
                    <div className="flex items-center gap-2">
                      {a.is_pinned ? <FiBookmark className="fill-primary text-primary" /> : null}
                      <p className="font-semibold text-navy">{a.title}</p>
                      {scheduled && <Badge tone="warning">Scheduled</Badge>}
                    </div>
                    <p className="mt-1 whitespace-pre-line text-sm text-slate-600">{a.content}</p>
                    <p className="mt-2 flex items-center gap-1.5 text-xs text-slate-400"><FiClock size={12} /> {formatDateTime(a.published_at)}</p>
                  </div>
                  <div className="flex items-center gap-1">
                    <button type="button" onClick={() => togglePin(a)} className={`rounded-lg p-2 hover:bg-slate-100 ${a.is_pinned ? 'text-primary' : 'text-slate-400 hover:text-primary'}`} aria-label="Pin"><FiBookmark size={16} /></button>
                    <button type="button" onClick={() => openEdit(a)} className="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-primary"><FiEdit2 size={16} /></button>
                    <button type="button" onClick={() => remove(a)} className="rounded-lg p-2 text-slate-400 hover:bg-red-50 hover:text-red-500"><FiTrash2 size={16} /></button>
                  </div>
                </div>
              </div>
            )
          })}
        </div>
      )}

      <Modal open={!!editing} onClose={() => !saving && setEditing(null)} title={editing?.id ? 'Edit announcement' : 'New announcement'} size="lg"
        footer={<>
          <Button variant="outline" onClick={() => setEditing(null)} disabled={saving}>Cancel</Button>
          <Button onClick={save} loading={saving}>{editing?.id ? 'Save changes' : 'Publish'}</Button>
        </>}>
        <div className="space-y-4">
          <div>
            <label className="text-sm font-medium text-slate-600">Title *</label>
            <input value={form.title} onChange={(e) => setForm((f) => ({ ...f, title: e.target.value }))} className="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" />
          </div>
          <div>
            <label className="text-sm font-medium text-slate-600">Content *</label>
            <textarea value={form.content} onChange={(e) => setForm((f) => ({ ...f, content: e.target.value }))} rows={4} className="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" />
          </div>
          <div className="grid gap-4 sm:grid-cols-2">
            <div>
              <label className="text-sm font-medium text-slate-600">Schedule for (optional)</label>
              <input type="datetime-local" value={form.published_at} onChange={(e) => setForm((f) => ({ ...f, published_at: e.target.value }))} className="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" />
              <p className="mt-1 text-xs text-slate-400">Leave empty to publish now.</p>
            </div>
            <label className="mt-6 inline-flex items-center gap-2 text-sm text-slate-600">
              <input type="checkbox" checked={!!form.is_pinned} onChange={(e) => setForm((f) => ({ ...f, is_pinned: e.target.checked ? 1 : 0 }))} className="h-4 w-4 rounded border-slate-300" />
              Pin to top
            </label>
          </div>
        </div>
      </Modal>
    </div>
  )
}
