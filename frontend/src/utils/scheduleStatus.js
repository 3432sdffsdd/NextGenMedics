import {
  FiClock,
  FiRadio,
  FiCheckCircle,
  FiXCircle,
  FiPauseCircle,
  FiRefreshCw,
  FiBookOpen,
} from 'react-icons/fi'

// Calendar status colors — brand palette
export const STATUS_CONFIG = {
  upcoming: { label: 'Upcoming', color: '#3B82F6', tone: 'blue', icon: FiClock },
  live: { label: 'Live Now', color: '#10B981', tone: 'success', icon: FiRadio },
  completed: { label: 'Completed', color: '#94A3B8', tone: 'neutral', icon: FiCheckCircle },
  cancelled: { label: 'Cancelled', color: '#EF4444', tone: 'danger', icon: FiXCircle },
  postponed: { label: 'Delayed', color: '#64748B', tone: 'neutral', icon: FiPauseCircle },
  rescheduled: { label: 'Rescheduled', color: '#F59E0B', tone: 'warning', icon: FiRefreshCw },
  assignment: { label: 'Assignment', color: '#8B5CF6', tone: 'purple', icon: FiBookOpen },
}

export const STATUS_OPTIONS = Object.entries(STATUS_CONFIG)
  .filter(([value]) => value !== 'assignment')
  .map(([value, c]) => ({ value, label: c.label }))

const MANUAL_STATUSES = new Set(['cancelled', 'postponed', 'rescheduled'])

function toLocalDateTime(dateStr, timeStr) {
  if (!dateStr || !timeStr) return null
  const datePart = String(dateStr).slice(0, 10)
  const [y, mo, d] = datePart.split('-').map(Number)
  const m = String(timeStr).trim().match(/^(\d{1,2}):(\d{2})(?::(\d{2}))?/)
  if (!y || !mo || !d || !m) return null
  return new Date(y, mo - 1, d, Number(m[1]), Number(m[2]), Number(m[3] || 0))
}

function isLocked(schedule) {
  const v = schedule?.is_status_locked
  return v === 1 || v === true || v === '1'
}

/**
 * Derive Upcoming / Live / Completed from class date & time.
 * Respects locked or manual statuses from the server.
 */
export function resolveScheduleStatus(schedule) {
  if (!schedule) return 'upcoming'

  const apiStatus = schedule.status || 'upcoming'
  const raw = schedule.raw_status

  if (isLocked(schedule)) {
    return apiStatus || raw || 'upcoming'
  }

  if (MANUAL_STATUSES.has(apiStatus)) return apiStatus
  if (raw && MANUAL_STATUSES.has(raw)) return raw

  const startDt = toLocalDateTime(schedule.class_date, schedule.start_time)
  const endDt = toLocalDateTime(schedule.class_date, schedule.end_time)
  if (!startDt || !endDt) {
    return apiStatus || 'upcoming'
  }

  const now = Date.now()
  if (endDt.getTime() < now) return 'completed'
  if (startDt.getTime() <= now && now <= endDt.getTime()) return 'live'
  return 'upcoming'
}

export function statusColor(status) {
  return (STATUS_CONFIG[status] || STATUS_CONFIG.upcoming).color
}

export function statusLabel(status) {
  return (STATUS_CONFIG[status] || STATUS_CONFIG.upcoming).label
}

export function statusTone(status) {
  return (STATUS_CONFIG[status] || STATUS_CONFIG.upcoming).tone
}

export function toIso(dateStr, timeStr) {
  if (!dateStr) return null
  const d = String(dateStr).slice(0, 10)
  const match = String(timeStr || '00:00:00').trim().match(/^(\d{1,2}):(\d{2})(?::(\d{2}))?/)
  if (!match) return `${d}T00:00:00`
  const h = String(Number(match[1])).padStart(2, '0')
  const m = match[2]
  const s = match[3] || '00'
  return `${d}T${h}:${m}:${s}`
}

export function fmtTime(timeStr) {
  if (!timeStr) return ''
  const match = String(timeStr).trim().match(/^(\d{1,2}):(\d{2})/)
  if (!match) return String(timeStr)
  const hour = Number(match[1])
  const mins = match[2]
  const ampm = hour >= 12 ? 'PM' : 'AM'
  const h12 = hour % 12 || 12
  return `${h12}:${mins} ${ampm}`
}

export function fmtDate(dateStr) {
  if (!dateStr) return ''
  return new Date(String(dateStr).slice(0, 10) + 'T00:00:00').toLocaleDateString(undefined, {
    weekday: 'short',
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  })
}
