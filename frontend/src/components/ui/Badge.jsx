const TONES = {
  neutral: 'bg-slate-100 text-slate-600',
  blue: 'bg-blue-50 text-blue-700',
  success: 'bg-emerald-50 text-emerald-700',
  warning: 'bg-amber-50 text-amber-700',
  danger: 'bg-red-50 text-red-600',
  purple: 'bg-violet-50 text-violet-700',
}

const DOTS = {
  neutral: 'bg-slate-400',
  blue: 'bg-blue-500',
  success: 'bg-emerald-500',
  warning: 'bg-amber-500',
  danger: 'bg-red-500',
  purple: 'bg-violet-500',
}

// Map common domain statuses to a tone so badges are consistent app-wide.
const STATUS_TONE = {
  active: 'success',
  published: 'success',
  completed: 'success',
  open: 'success',
  approved: 'success',
  draft: 'neutral',
  inactive: 'neutral',
  archived: 'neutral',
  closed: 'neutral',
  pending: 'warning',
  submitted: 'warning',
  waitlist: 'warning',
  suspended: 'danger',
  unpublished: 'danger',
  rejected: 'danger',
}

export default function Badge({ children, tone, status, dot = false, className = '' }) {
  const resolvedTone = tone || (status ? STATUS_TONE[String(status).toLowerCase()] : null) || 'neutral'
  const label = children ?? status
  return (
    <span
      className={`inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold capitalize ${TONES[resolvedTone]} ${className}`}
    >
      {dot && <span className={`h-1.5 w-1.5 rounded-full ${DOTS[resolvedTone]}`} />}
      {label}
    </span>
  )
}
