import { STATUS_CONFIG } from '../../utils/scheduleStatus'

export default function StatusBadge({ status, size = 'sm' }) {
  const cfg = STATUS_CONFIG[status] || STATUS_CONFIG.upcoming
  const Icon = cfg.icon
  const pad = size === 'sm' ? 'px-2 py-0.5 text-xs' : 'px-2.5 py-1 text-sm'
  return (
    <span
      className={`inline-flex items-center gap-1 rounded-full font-semibold ${pad}`}
      style={{ color: cfg.color, backgroundColor: `${cfg.color}1a` }}
    >
      <Icon size={size === 'sm' ? 12 : 14} />
      {cfg.label}
    </span>
  )
}
