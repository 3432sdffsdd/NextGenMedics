import { useCallback, useEffect, useMemo, useState } from 'react'
import { FiVideo, FiPlus, FiEdit2, FiTrash2, FiExternalLink, FiClock, FiRadio } from 'react-icons/fi'
import { sessionsService, schedulesService } from '../../services/api'
import { useToast } from '../../context/ToastContext'
import { useConfirm } from '../../context/ConfirmContext'
import { Modal, Button, Badge, EmptyState } from '../../components/ui'
import { formatDateTime } from '../../utils/files'
import { resolveScheduleStatus, fmtTime, fmtDate } from '../../utils/scheduleStatus'

const empty = { title: 'Live Class', meeting_url: '', scheduled_at: '', duration_minutes: 60 }

const isValidUrl = (u) => {
  try {
    const p = new URL(u)
    return p.protocol === 'http:' || p.protocol === 'https:'
  } catch {
    return false
  }
}

function normalizeMeetingUrl(url) {
  const u = String(url || '').trim()
  if (!u) return ''
  if (/^https?:\/\//i.test(u)) return u
  return `https://${u}`
}

function todayYmd() {
  const d = new Date()
  const pad = (n) => String(n).padStart(2, '0')
  return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`
}

export default function LiveClassManager({ courseId, canManage = false }) {
  const toast = useToast()
  const confirm = useConfirm()
  const [items, setItems] = useState([])
  const [schedules, setSchedules] = useState([])
  const [loading, setLoading] = useState(true)
  const [editing, setEditing] = useState(null)
  const [form, setForm] = useState(empty)
  const [saving, setSaving] = useState(false)
  const [tick, setTick] = useState(0)

  useEffect(() => {
    const id = setInterval(() => setTick((t) => t + 1), 60_000)
    return () => clearInterval(id)
  }, [])

  const load = useCallback(() => {
    setLoading(true)
    const day = todayYmd()
    Promise.all([
      sessionsService.byCourse(courseId).then(({ data }) => data.data || []).catch(() => []),
      schedulesService.list({
        course_id: courseId,
        date_from: day,
        date_to: day,
      }).then(({ data }) => data.data || []).catch(() => []),
    ])
      .then(([sessions, rows]) => {
        setItems(sessions)
        setSchedules(rows)
      })
      .finally(() => setLoading(false))
  }, [courseId])

  useEffect(() => { load() }, [load])

  const openNew = () => { setForm(empty); setEditing({}) }
  const openEdit = (s) => {
    setForm({
      title: s.title || 'Live Class',
      meeting_url: s.meeting_url || '',
      scheduled_at: s.scheduled_at ? s.scheduled_at.replace(' ', 'T').slice(0, 16) : '',
      duration_minutes: s.duration_minutes || 60,
    })
    setEditing(s)
  }

  const save = async () => {
    if (!form.title.trim()) return toast.error('Title is required')
    if (!isValidUrl(form.meeting_url)) return toast.error('Invalid Zoom URL')
    setSaving(true)
    try {
      const payload = { ...form, course_id: courseId, scheduled_at: form.scheduled_at || null }
      if (editing.id) {
        await sessionsService.update(editing.id, payload)
        toast.success('Live class updated')
      } else {
        await sessionsService.create(payload)
        toast.success('Live class added')
      }
      setEditing(null)
      load()
    } catch (err) {
      toast.error(err.response?.data?.message || 'Could not save live class')
    } finally {
      setSaving(false)
    }
  }

  const remove = async (s) => {
    if (!(await confirm({ title: 'Delete live class', message: `Delete "${s.title}"?` }))) return
    try {
      await sessionsService.cancel(s.id)
      toast.success('Deleted successfully')
      load()
    } catch {
      toast.error('Could not delete live class')
    }
  }

  const active = items.filter((s) => s.status !== 'cancelled')
  const primary = active[0]

  const liveSchedules = useMemo(() => {
    void tick
    return schedules
      .map((s) => ({ ...s, _status: resolveScheduleStatus(s) }))
      .filter((s) => s._status === 'live' && normalizeMeetingUrl(s.meeting_link))
  }, [schedules, tick])

  const upcomingToday = useMemo(() => {
    void tick
    return schedules
      .map((s) => ({ ...s, _status: resolveScheduleStatus(s) }))
      .filter((s) => s._status === 'upcoming' && normalizeMeetingUrl(s.meeting_link))
  }, [schedules, tick])

  const hasAnything = liveSchedules.length > 0 || upcomingToday.length > 0 || active.length > 0

  return (
    <div className="space-y-5">
      <div className="flex items-center justify-between">
        <h3 className="font-display text-lg font-bold text-navy">Live Class</h3>
        {canManage && <Button icon={FiPlus} onClick={openNew}>Add Zoom link</Button>}
      </div>

      {/* Live now — from monthly schedule */}
      {liveSchedules.map((s) => {
        const url = normalizeMeetingUrl(s.meeting_link)
        return (
          <div
            key={`live-${s.id}`}
            className="rounded-2xl border border-emerald-200 bg-gradient-to-br from-emerald-50 to-white p-6 text-center shadow-soft"
          >
            <div className="mx-auto flex items-center justify-center gap-2">
              <span className="relative flex h-3 w-3">
                <span className="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75" />
                <span className="relative inline-flex h-3 w-3 rounded-full bg-emerald-500" />
              </span>
              <span className="inline-flex items-center gap-1 rounded-full bg-emerald-600 px-2.5 py-0.5 text-xs font-bold uppercase tracking-wide text-white">
                <FiRadio size={12} /> Live now
              </span>
            </div>
            <p className="mt-3 font-display text-lg font-bold text-navy">{s.lecture_title}</p>
            {(s.topic_covered || s.subject) && (
              <p className="mt-1 text-sm text-slate-500">{s.topic_covered || s.subject}</p>
            )}
            <p className="mt-1 text-sm text-slate-400">
              {fmtTime(s.start_time)} – {fmtTime(s.end_time)}
              {s.teacher_name ? ` · ${s.teacher_name}` : ''}
            </p>
            <a
              href={url}
              target="_blank"
              rel="noreferrer"
              className="btn-primary mt-4 inline-flex items-center gap-2 bg-emerald-600 px-6 py-3 text-base hover:bg-emerald-700"
            >
              <FiVideo /> Join Live Class
            </a>
          </div>
        )
      })}

      {/* Standalone Zoom session join */}
      {!liveSchedules.length && primary?.meeting_url && (
        <div className="rounded-2xl border border-primary/20 bg-gradient-to-br from-primary/5 to-transparent p-6 text-center">
          <div className="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-primary/10 text-primary">
            <FiVideo size={26} />
          </div>
          <p className="mt-3 font-display text-lg font-bold text-navy">{primary.title}</p>
          {primary.scheduled_at && (
            <p className="text-sm text-slate-400">{formatDateTime(primary.scheduled_at)}</p>
          )}
          <a
            href={normalizeMeetingUrl(primary.meeting_url)}
            target="_blank"
            rel="noreferrer"
            className="btn-primary mt-4 inline-flex items-center gap-2 px-6 py-3 text-base"
          >
            <FiVideo /> Join Live Class
          </a>
        </div>
      )}

      {/* Today's upcoming classes with meeting links */}
      {upcomingToday.length > 0 && (
        <div className="space-y-2">
          <p className="text-xs font-semibold uppercase tracking-wide text-slate-400">Coming up today</p>
          {upcomingToday.map((s) => {
            const url = normalizeMeetingUrl(s.meeting_link)
            return (
              <div
                key={`up-${s.id}`}
                className="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-100 bg-white p-4 shadow-soft"
              >
                <div>
                  <p className="font-medium text-navy">{s.lecture_title}</p>
                  <p className="mt-0.5 text-xs text-slate-400">
                    <FiClock className="mr-1 inline" size={12} />
                    {fmtDate(s.class_date)} · {fmtTime(s.start_time)} – {fmtTime(s.end_time)}
                    {s.topic_covered ? ` · ${s.topic_covered}` : ''}
                  </p>
                </div>
                <a
                  href={url}
                  target="_blank"
                  rel="noreferrer"
                  className="inline-flex items-center gap-1.5 rounded-lg bg-blue-50 px-3 py-1.5 text-sm font-semibold text-blue-700 hover:bg-blue-100"
                >
                  <FiExternalLink size={14} /> Meeting link
                </a>
              </div>
            )
          })}
        </div>
      )}

      {loading ? (
        <p className="text-slate-400">Loading…</p>
      ) : !hasAnything ? (
        <EmptyState
          icon={FiVideo}
          title="No live class right now"
          description={
            canManage
              ? 'Add a Zoom link, or put a meeting link on today’s schedule so students can join when class is live.'
              : 'When a class goes live, the join link will appear here.'
          }
          action={canManage ? <Button icon={FiPlus} onClick={openNew}>Add Zoom link</Button> : null}
        />
      ) : active.length > 0 ? (
        <div className="space-y-2">
          {canManage && <p className="text-xs font-semibold uppercase tracking-wide text-slate-400">Saved Zoom links</p>}
          {active.map((s) => (
            <div
              key={s.id}
              className="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-100 bg-white p-4 shadow-soft"
            >
              <div>
                <div className="flex items-center gap-2">
                  <p className="font-medium text-navy">{s.title}</p>
                  {s.status && <Badge status={s.status}>{s.status}</Badge>}
                </div>
                <p className="mt-0.5 flex items-center gap-1.5 text-xs text-slate-400">
                  <FiClock size={12} />{' '}
                  {s.scheduled_at ? formatDateTime(s.scheduled_at) : 'Anytime'} · {s.duration_minutes} min
                </p>
              </div>
              <div className="flex items-center gap-2">
                {s.meeting_url && (
                  <a
                    href={normalizeMeetingUrl(s.meeting_url)}
                    target="_blank"
                    rel="noreferrer"
                    className="inline-flex items-center gap-1.5 rounded-lg bg-primary/10 px-3 py-1.5 text-sm font-semibold text-primary hover:bg-primary/20"
                  >
                    <FiExternalLink size={14} /> Join
                  </a>
                )}
                {canManage && (
                  <>
                    <button type="button" onClick={() => openEdit(s)} className="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-primary">
                      <FiEdit2 size={15} />
                    </button>
                    <button type="button" onClick={() => remove(s)} className="rounded-lg p-2 text-slate-400 hover:bg-red-50 hover:text-red-500">
                      <FiTrash2 size={15} />
                    </button>
                  </>
                )}
              </div>
            </div>
          ))}
        </div>
      ) : null}

      <Modal
        open={!!editing}
        onClose={() => !saving && setEditing(null)}
        title={editing?.id ? 'Edit live class' : 'Add live class'}
        footer={
          <>
            <Button variant="outline" onClick={() => setEditing(null)} disabled={saving}>Cancel</Button>
            <Button onClick={save} loading={saving}>{editing?.id ? 'Save' : 'Add'}</Button>
          </>
        }
      >
        <div className="space-y-4">
          <div>
            <label className="text-sm font-medium text-slate-600">Title *</label>
            <input
              value={form.title}
              onChange={(e) => setForm((f) => ({ ...f, title: e.target.value }))}
              className="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"
            />
          </div>
          <div>
            <label className="text-sm font-medium text-slate-600">Zoom / Meeting URL *</label>
            <input
              value={form.meeting_url}
              onChange={(e) => setForm((f) => ({ ...f, meeting_url: e.target.value }))}
              placeholder="https://zoom.us/j/..."
              className="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"
            />
            <p className="mt-1 text-xs text-slate-400">This link can be reused for all classes.</p>
          </div>
          <div className="grid gap-4 sm:grid-cols-2">
            <div>
              <label className="text-sm font-medium text-slate-600">Scheduled at (optional)</label>
              <input
                type="datetime-local"
                value={form.scheduled_at}
                onChange={(e) => setForm((f) => ({ ...f, scheduled_at: e.target.value }))}
                className="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"
              />
            </div>
            <div>
              <label className="text-sm font-medium text-slate-600">Duration (min)</label>
              <input
                type="number"
                value={form.duration_minutes}
                onChange={(e) => setForm((f) => ({ ...f, duration_minutes: e.target.value }))}
                className="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"
              />
            </div>
          </div>
        </div>
      </Modal>
    </div>
  )
}
