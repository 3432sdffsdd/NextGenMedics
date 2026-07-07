import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { FiZap, FiAward } from 'react-icons/fi'
import { progressService } from '../../services/api'
import { badgeEmoji } from '../../utils/badges'

const DAY_LABELS = ['S', 'M', 'T', 'W', 'T', 'F', 'S']

export default function StreakWidget() {
  const [data, setData] = useState(null)

  useEffect(() => {
    progressService.streak().then(({ data }) => setData(data.data)).catch(() => {})
  }, [])

  if (!data) return null

  const activeDates = new Set((data.week || []).map((d) => d.activity_date))
  const days = []
  for (let i = 6; i >= 0; i--) {
    const d = new Date()
    d.setDate(d.getDate() - i)
    const iso = d.toISOString().slice(0, 10)
    days.push({ iso, label: DAY_LABELS[d.getDay()], active: activeDates.has(iso) })
  }
  const earned = (data.badges || []).filter((b) => b.earned)

  return (
    <div className="rounded-2xl border border-slate-100 bg-gradient-to-br from-orange-50 to-amber-50 p-5 shadow-soft">
      <div className="flex items-center justify-between">
        <div className="flex items-center gap-3">
          <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-orange-500/10 text-orange-500"><FiZap size={24} /></div>
          <div>
            <p className="text-2xl font-bold text-navy">{data.current_streak} <span className="text-sm font-medium text-slate-500">day streak</span></p>
            <p className="text-xs text-slate-400">Longest: {data.longest_streak} days</p>
          </div>
        </div>
        <Link to="/student/progress" className="text-xs font-semibold text-primary hover:underline">Analytics →</Link>
      </div>

      <div className="mt-4 flex justify-between">
        {days.map((d, i) => (
          <div key={i} className="flex flex-col items-center gap-1">
            <div className={`flex h-8 w-8 items-center justify-center rounded-full text-xs font-semibold ${d.active ? 'bg-orange-500 text-white' : 'bg-white text-slate-300'}`}>
              {d.active ? '🔥' : ''}
            </div>
            <span className="text-[10px] text-slate-400">{d.label}</span>
          </div>
        ))}
      </div>

      {earned.length > 0 && (
        <div className="mt-4 flex items-center gap-2 border-t border-orange-100 pt-3">
          <FiAward className="text-amber-500" />
          <span className="text-xs text-slate-500">{earned.length} badge{earned.length > 1 ? 's' : ''} earned</span>
          <div className="ml-auto flex -space-x-1">
            {earned.slice(0, 5).map((b) => (
              <span key={b.id} title={b.name} className="flex h-6 w-6 items-center justify-center rounded-full border-2 border-white bg-amber-100 text-xs">{badgeEmoji(b.icon)}</span>
            ))}
          </div>
        </div>
      )}
    </div>
  )
}
