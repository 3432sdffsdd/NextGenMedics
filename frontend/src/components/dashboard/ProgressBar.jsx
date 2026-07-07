const TONES = {
  primary: 'bg-primary',
  emerald: 'bg-emerald-500',
  amber: 'bg-amber-500',
  rose: 'bg-rose-500',
}

export default function ProgressBar({ label, value, tone = 'primary', valueLabel }) {
  const pct = Math.max(0, Math.min(100, Number(value) || 0))
  return (
    <div>
      {(label || valueLabel) && (
        <div className="mb-1 flex items-center justify-between text-xs text-slate-500">
          {label && <span>{label}</span>}
          {valueLabel && <span className="font-semibold text-slate-600">{valueLabel}</span>}
        </div>
      )}
      <div className="h-2 w-full overflow-hidden rounded-full bg-slate-100">
        <div
          className={`h-full rounded-full transition-all duration-500 ${TONES[tone] || TONES.primary}`}
          style={{ width: `${pct}%` }}
        />
      </div>
    </div>
  )
}
