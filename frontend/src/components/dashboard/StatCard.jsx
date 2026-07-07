import { FiArrowUpRight, FiArrowDownRight } from 'react-icons/fi'
import useCountUp from '../../hooks/useCountUp'

const TONES = {
  blue: 'from-blue-500 to-blue-700 shadow-blue-500/30',
  emerald: 'from-emerald-500 to-emerald-600 shadow-emerald-500/30',
  amber: 'from-amber-500 to-orange-500 shadow-amber-500/30',
  violet: 'from-violet-500 to-purple-600 shadow-violet-500/30',
  rose: 'from-rose-500 to-red-500 shadow-rose-500/30',
  slate: 'from-slate-600 to-slate-800 shadow-slate-500/30',
}

export default function StatCard({ label, value, icon: Icon, tone = 'blue', trend, hint, index = 0 }) {
  const [count, ref] = useCountUp(Number(value) || 0)
  const trendUp = typeof trend === 'number' ? trend >= 0 : null

  return (
    <div
      ref={ref}
      className="group animate-slide-up rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_2px_16px_rgba(15,23,42,0.05)] transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_12px_32px_rgba(15,23,42,0.10)]"
      style={{ animationDelay: `${index * 60}ms` }}
    >
      <div className="flex items-start justify-between">
        <p className="text-sm font-medium text-slate-500">{label}</p>
        {Icon && (
          <span
            className={`grid h-11 w-11 place-items-center rounded-xl bg-gradient-to-br text-white shadow-lg transition-transform duration-300 group-hover:scale-110 ${TONES[tone] || TONES.blue}`}
          >
            <Icon size={20} />
          </span>
        )}
      </div>
      <p className="mt-3 font-display text-3xl font-extrabold tracking-tight text-slate-800">
        {count.toLocaleString()}
      </p>
      <div className="mt-1.5 flex items-center gap-2">
        {trendUp !== null && (
          <span
            className={`inline-flex items-center gap-0.5 text-xs font-semibold ${
              trendUp ? 'text-emerald-600' : 'text-red-500'
            }`}
          >
            {trendUp ? <FiArrowUpRight size={13} /> : <FiArrowDownRight size={13} />}
            {Math.abs(trend)}%
          </span>
        )}
        {hint && <span className="text-xs text-slate-400">{hint}</span>}
      </div>
    </div>
  )
}
