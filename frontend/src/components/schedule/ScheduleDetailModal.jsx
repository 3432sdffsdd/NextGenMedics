import { useState } from 'react'
import {
  FiVideo, FiFilm, FiPaperclip, FiEdit2, FiTrash2, FiCheckCircle,
  FiXCircle, FiRefreshCw, FiCopy, FiUpload, FiClock, FiUser, FiBookOpen, FiUsers,
} from 'react-icons/fi'
import { Modal, Button } from '../ui'
import StatusBadge from './StatusBadge'
import { fmtDate, fmtTime } from '../../utils/scheduleStatus'

const API_ORIGIN = (import.meta.env.VITE_API_URL || '/api').replace(/\/api\/?$/, '')

function Row({ icon: Icon, label, children }) {
  if (!children) return null
  return (
    <div className="flex items-start gap-3 py-2">
      <Icon className="mt-0.5 shrink-0 text-slate-400" size={16} />
      <div className="min-w-0">
        <p className="text-xs font-medium uppercase tracking-wide text-slate-400">{label}</p>
        <div className="text-sm text-slate-700">{children}</div>
      </div>
    </div>
  )
}

export default function ScheduleDetailModal({
  schedule, open, onClose, canEdit = false,
  onEdit, onCancel, onComplete, onDelete, onReschedule, onDuplicate, onUpload,
}) {
  const [uploading, setUploading] = useState(false)
  if (!schedule) return null

  const s = schedule
  const mediaUrl = (p) => (p?.startsWith('http') ? p : `${API_ORIGIN}/media?path=${encodeURIComponent(p)}`)

  const handleFile = (field) => async (e) => {
    const file = e.target.files?.[0]
    if (!file) return
    setUploading(true)
    const fd = new FormData()
    fd.append('file', file)
    fd.append('field', field)
    await onUpload?.(s, fd)
    setUploading(false)
    e.target.value = ''
  }

  const footer = canEdit ? (
    <div className="flex flex-wrap justify-end gap-2">
      <Button variant="ghost" size="sm" onClick={() => onEdit?.(s)}><FiEdit2 size={14} /> Edit</Button>
      <Button variant="ghost" size="sm" onClick={() => onDuplicate?.(s)}><FiCopy size={14} /> Duplicate</Button>
      <Button variant="ghost" size="sm" onClick={() => onReschedule?.(s)}><FiRefreshCw size={14} /> Reschedule</Button>
      {s.status !== 'completed' && (
        <Button variant="success" size="sm" onClick={() => onComplete?.(s)}><FiCheckCircle size={14} /> Complete</Button>
      )}
      {s.status !== 'cancelled' && (
        <Button variant="outline" size="sm" onClick={() => onCancel?.(s)}><FiXCircle size={14} /> Cancel</Button>
      )}
      <Button variant="danger" size="sm" onClick={() => onDelete?.(s)}><FiTrash2 size={14} /> Delete</Button>
    </div>
  ) : (
    <Button variant="secondary" size="sm" onClick={onClose}>Close</Button>
  )

  return (
    <Modal open={open} onClose={onClose} size="lg" title={s.lecture_title} subtitle={s.course_title} footer={footer}>
      <div className="mb-3 flex flex-wrap items-center gap-3">
        <StatusBadge status={s.status} size="md" />
        {s.lecture_number ? <span className="text-sm text-slate-500">Lecture #{s.lecture_number}</span> : null}
      </div>

      <div className="grid gap-x-6 sm:grid-cols-2">
        <Row icon={FiClock} label="When">
          {fmtDate(s.class_date)} · {fmtTime(s.start_time)}–{fmtTime(s.end_time)}
          {s.duration_minutes ? <span className="text-slate-400"> ({s.duration_minutes} min)</span> : null}
        </Row>
        <Row icon={FiUser} label="Teacher">{s.teacher_name || '—'}</Row>
        <Row icon={FiBookOpen} label="Subject">{s.subject}</Row>
        <Row icon={FiUsers} label="Batch">{s.batch_name}</Row>
        <Row icon={FiBookOpen} label="Topic covered">{s.topic_covered}</Row>
        <Row icon={FiEdit2} label="Remarks">{s.remarks}</Row>
      </div>

      {s.description && (
        <div className="mt-2 rounded-xl bg-slate-50 p-3 text-sm text-slate-600">{s.description}</div>
      )}

      <div className="mt-4 flex flex-wrap gap-2">
        {s.meeting_link && (
          <a href={s.meeting_link} target="_blank" rel="noreferrer">
            <Button variant="primary" size="sm"><FiVideo size={14} /> Join class</Button>
          </a>
        )}
        {s.recording_link && (
          <a href={s.recording_link.startsWith('http') ? s.recording_link : mediaUrl(s.recording_link)} target="_blank" rel="noreferrer">
            <Button variant="secondary" size="sm"><FiFilm size={14} /> Recording</Button>
          </a>
        )}
        {s.attachment_path && (
          <a href={mediaUrl(s.attachment_path)} target="_blank" rel="noreferrer">
            <Button variant="secondary" size="sm"><FiPaperclip size={14} /> {s.attachment_name || 'Attachment'}</Button>
          </a>
        )}
      </div>

      {canEdit && (
        <div className="mt-4 flex flex-wrap gap-3 border-t border-slate-100 pt-4">
          <label className="cursor-pointer">
            <span className="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50">
              <FiUpload size={14} /> {uploading ? 'Uploading…' : 'Upload notes'}
            </span>
            <input type="file" className="hidden" onChange={handleFile('attachment')} disabled={uploading} />
          </label>
          <label className="cursor-pointer">
            <span className="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50">
              <FiFilm size={14} /> Upload recording
            </span>
            <input type="file" className="hidden" onChange={handleFile('recording')} disabled={uploading} />
          </label>
        </div>
      )}
    </Modal>
  )
}
