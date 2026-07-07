import {
  FiClock,
  FiRadio,
  FiCheckCircle,
  FiXCircle,
  FiPauseCircle,
  FiRefreshCw,
} from 'react-icons/fi'

// Central status config — colors match the requested calendar legend.
export const STATUS_CONFIG = {
  upcoming: { label: 'Upcoming', color: '#2563EB', tone: 'blue', icon: FiClock },
  live: { label: 'Live Now', color: '#F59E0B', tone: 'warning', icon: FiRadio },
  completed: { label: 'Completed', color: '#22C55E', tone: 'success', icon: FiCheckCircle },
  cancelled: { label: 'Cancelled', color: '#EF4444', tone: 'danger', icon: FiXCircle },
  postponed: { label: 'Postponed', color: '#6B7280', tone: 'neutral', icon: FiPauseCircle },
  rescheduled: { label: 'Rescheduled', color: '#8B5CF6', tone: 'purple', icon: FiRefreshCw },
}

export const STATUS_OPTIONS = Object.entries(STATUS_CONFIG).map(([value, c]) => ({ value, label: c.label }))

export function statusColor(status) {
  return (STATUS_CONFIG[status] || STATUS_CONFIG.upcoming).color
}

export function statusLabel(status) {
  return (STATUS_CONFIG[status] || STATUS_CONFIG.upcoming).label
}

export function statusTone(status) {
  return (STATUS_CONFIG[status] || STATUS_CONFIG.upcoming).tone
}

/** Build an ISO-ish local datetime string FullCalendar understands. */
export function toIso(dateStr, timeStr) {
  if (!dateStr) return null
  const t = (timeStr || '00:00:00').slice(0, 8)
  return `${dateStr}T${t}`
}

export function fmtTime(timeStr) {
  if (!timeStr) return ''
  const [h, m] = timeStr.split(':')
  const hour = Number(h)
  const ampm = hour >= 12 ? 'PM' : 'AM'
  const h12 = hour % 12 || 12
  return `${h12}:${m} ${ampm}`
}

export function fmtDate(dateStr) {
  if (!dateStr) return ''
  return new Date(dateStr + 'T00:00:00').toLocaleDateString(undefined, {
    weekday: 'short',
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  })
}
