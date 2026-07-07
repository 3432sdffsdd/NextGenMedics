import { useEffect, useState } from 'react'
import {
  ResponsiveContainer, AreaChart, Area, BarChart, Bar, XAxis, YAxis, Tooltip, CartesianGrid, LineChart, Line, Cell,
} from 'recharts'
import { FiTarget, FiClock, FiBookOpen, FiLayers, FiTrendingUp, FiTrendingDown } from 'react-icons/fi'
import { progressService } from '../../services/api'
import { badgeEmoji } from '../../utils/badges'
import Alert from '../../components/dashboard/Alert'

const DIFF_COLORS = { easy: '#16a34a', moderate: '#f59e0b', difficult: '#ef4444' }

export default function StudentProgress() {
  const [data, setData] = useState(null)
  const [badges, setBadges] = useState([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    Promise.all([progressService.analytics(), progressService.badges()])
      .then(([a, b]) => { setData(a.data.data); setBadges(b.data.data || []) })
      .finally(() => setLoading(false))
  }, [])

  if (loading) return <p className="text-slate-400">Loading your analytics…</p>
  if (!data) return <Alert>Analytics unavailable.</Alert>

  const o = data.overall
  const dailySeries = (data.daily_activity || []).map((d) => ({ date: d.activity_date.slice(5), total: Number(d.total) }))
  const weeklySeries = (data.weekly_progress || []).map((w) => ({ week: w.week_start?.slice(5), score: Number(w.avg_score) }))
  const topicSeries = (data.topic_accuracy || []).map((t) => ({ topic: t.topic, accuracy: Number(t.accuracy) }))
  const diffSeries = (data.difficulty_accuracy || []).map((d) => ({ difficulty: d.difficulty, accuracy: Number(d.accuracy) }))

  return (
    <div>
      <h2 className="font-display text-2xl font-bold text-navy">Performance Analytics</h2>
      <p className="text-sm text-slate-500">Track your FCPS preparation progress across all activities.</p>

      <div className="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <Metric icon={<FiTarget />} label="Avg. MCQ Score" value={`${o.average_score}%`} tone="text-primary" />
        <Metric icon={<FiBookOpen />} label="Questions Answered" value={o.questions_answered} tone="text-navy" />
        <Metric icon={<FiClock />} label="Time Studied" value={`${Math.floor(o.time_studied_min / 60)}h ${o.time_studied_min % 60}m`} tone="text-teal-600" />
        <Metric icon={<FiLayers />} label="Flashcards Mastered" value={o.flashcards_mastered} tone="text-green-600" />
      </div>

      <div className="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <Metric label="Attempts" value={o.attempts} tone="text-navy" small />
        <Metric label="Lectures Completed" value={o.lectures_completed} tone="text-navy" small />
        <Metric label="Flashcards Reviewed" value={o.flashcards_reviewed} tone="text-navy" small />
        <Metric label="Revision Sessions" value={o.revision_sessions} tone="text-navy" small />
      </div>

      <div className="mt-6 grid gap-6 lg:grid-cols-2">
        <ChartCard title="Daily Study Activity (30 days)">
          {dailySeries.length ? (
            <ResponsiveContainer width="100%" height={240}>
              <AreaChart data={dailySeries}>
                <defs>
                  <linearGradient id="act" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="5%" stopColor="#0ea5a4" stopOpacity={0.4} />
                    <stop offset="95%" stopColor="#0ea5a4" stopOpacity={0} />
                  </linearGradient>
                </defs>
                <CartesianGrid strokeDasharray="3 3" stroke="#f1f5f9" />
                <XAxis dataKey="date" tick={{ fontSize: 11 }} />
                <YAxis tick={{ fontSize: 11 }} allowDecimals={false} />
                <Tooltip />
                <Area type="monotone" dataKey="total" stroke="#0ea5a4" fill="url(#act)" />
              </AreaChart>
            </ResponsiveContainer>
          ) : <Empty />}
        </ChartCard>

        <ChartCard title="Weekly Average Score">
          {weeklySeries.length ? (
            <ResponsiveContainer width="100%" height={240}>
              <LineChart data={weeklySeries}>
                <CartesianGrid strokeDasharray="3 3" stroke="#f1f5f9" />
                <XAxis dataKey="week" tick={{ fontSize: 11 }} />
                <YAxis domain={[0, 100]} tick={{ fontSize: 11 }} />
                <Tooltip />
                <Line type="monotone" dataKey="score" stroke="#6366f1" strokeWidth={2} dot={{ r: 3 }} />
              </LineChart>
            </ResponsiveContainer>
          ) : <Empty />}
        </ChartCard>

        <ChartCard title="Accuracy by Topic">
          {topicSeries.length ? (
            <ResponsiveContainer width="100%" height={Math.max(240, topicSeries.length * 32)}>
              <BarChart data={topicSeries} layout="vertical" margin={{ left: 20 }}>
                <CartesianGrid strokeDasharray="3 3" stroke="#f1f5f9" />
                <XAxis type="number" domain={[0, 100]} tick={{ fontSize: 11 }} />
                <YAxis type="category" dataKey="topic" width={120} tick={{ fontSize: 11 }} />
                <Tooltip />
                <Bar dataKey="accuracy" radius={[0, 6, 6, 0]}>
                  {topicSeries.map((t, i) => (
                    <Cell key={i} fill={t.accuracy >= 70 ? '#16a34a' : t.accuracy >= 50 ? '#f59e0b' : '#ef4444'} />
                  ))}
                </Bar>
              </BarChart>
            </ResponsiveContainer>
          ) : <Empty />}
        </ChartCard>

        <ChartCard title="Accuracy by Difficulty">
          {diffSeries.length ? (
            <ResponsiveContainer width="100%" height={240}>
              <BarChart data={diffSeries}>
                <CartesianGrid strokeDasharray="3 3" stroke="#f1f5f9" />
                <XAxis dataKey="difficulty" tick={{ fontSize: 11 }} />
                <YAxis domain={[0, 100]} tick={{ fontSize: 11 }} />
                <Tooltip />
                <Bar dataKey="accuracy" radius={[6, 6, 0, 0]}>
                  {diffSeries.map((d, i) => <Cell key={i} fill={DIFF_COLORS[d.difficulty] || '#64748b'} />)}
                </Bar>
              </BarChart>
            </ResponsiveContainer>
          ) : <Empty />}
        </ChartCard>
      </div>

      <div className="mt-6 grid gap-6 lg:grid-cols-2">
        <div className="rounded-2xl border border-slate-100 bg-white p-6 shadow-soft">
          <h3 className="mb-3 flex items-center gap-2 font-bold text-red-500"><FiTrendingDown /> Focus Areas (Weak Topics)</h3>
          {data.weak_topics?.length ? (
            <ul className="space-y-2">
              {data.weak_topics.map((t, i) => (
                <li key={i} className="flex items-center justify-between rounded-xl bg-red-50 px-4 py-2 text-sm">
                  <span className="text-slate-700">{t.topic}</span>
                  <span className="font-semibold text-red-500">{t.accuracy}%</span>
                </li>
              ))}
            </ul>
          ) : <p className="text-sm text-slate-400">Attempt more MCQs to see recommendations.</p>}
        </div>

        <div className="rounded-2xl border border-slate-100 bg-white p-6 shadow-soft">
          <h3 className="mb-3 flex items-center gap-2 font-bold text-green-600"><FiTrendingUp /> Strong Topics</h3>
          {data.strong_topics?.length ? (
            <ul className="space-y-2">
              {data.strong_topics.map((t, i) => (
                <li key={i} className="flex items-center justify-between rounded-xl bg-green-50 px-4 py-2 text-sm">
                  <span className="text-slate-700">{t.topic}</span>
                  <span className="font-semibold text-green-600">{t.accuracy}%</span>
                </li>
              ))}
            </ul>
          ) : <p className="text-sm text-slate-400">No data yet.</p>}
        </div>
      </div>

      <div className="mt-6 rounded-2xl border border-slate-100 bg-white p-6 shadow-soft">
        <h3 className="mb-4 font-bold text-navy">Achievement Badges</h3>
        <div className="grid grid-cols-3 gap-4 sm:grid-cols-6">
          {badges.map((b) => (
            <div key={b.id} title={b.description} className={`flex flex-col items-center rounded-xl border p-3 text-center ${b.earned ? 'border-amber-200 bg-amber-50' : 'border-slate-100 bg-slate-50 opacity-50'}`}>
              <span className="text-3xl">{badgeEmoji(b.icon)}</span>
              <span className="mt-1 text-xs font-semibold text-navy">{b.name}</span>
            </div>
          ))}
        </div>
      </div>

      <div className="mt-6 rounded-2xl border border-slate-100 bg-white p-6 shadow-soft">
        <h3 className="mb-4 font-bold text-navy">Recent Attempts</h3>
        {data.recent_attempts?.length ? (
          <div className="overflow-x-auto">
            <table className="w-full text-left text-sm">
              <thead className="text-xs uppercase text-slate-400">
                <tr><th className="py-2">Lecture</th><th className="py-2">Score</th><th className="py-2">Correct</th><th className="py-2">Type</th><th className="py-2">Date</th></tr>
              </thead>
              <tbody className="divide-y divide-slate-100">
                {data.recent_attempts.map((a) => (
                  <tr key={a.id}>
                    <td className="py-2 pr-2 text-slate-700">{a.lecture_title || '—'}</td>
                    <td className="py-2 font-semibold text-navy">{Math.round(a.score)}%</td>
                    <td className="py-2 text-slate-500">{a.correct_count}/{a.total_questions}</td>
                    <td className="py-2 capitalize text-slate-500">{a.source}</td>
                    <td className="py-2 text-slate-400">{a.submitted_at ? new Date(a.submitted_at).toLocaleDateString() : ''}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        ) : <p className="text-sm text-slate-400">No attempts yet.</p>}
      </div>
    </div>
  )
}

function Metric({ icon, label, value, tone, small }) {
  return (
    <div className="rounded-2xl border border-slate-100 bg-white p-5 shadow-soft">
      {icon && <div className="mb-2 flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100 text-slate-500">{icon}</div>}
      <p className={`font-bold ${small ? 'text-xl' : 'text-2xl'} ${tone}`}>{value}</p>
      <p className="mt-0.5 text-xs text-slate-400">{label}</p>
    </div>
  )
}

function ChartCard({ title, children }) {
  return (
    <div className="rounded-2xl border border-slate-100 bg-white p-6 shadow-soft">
      <h3 className="mb-4 font-bold text-navy">{title}</h3>
      {children}
    </div>
  )
}

function Empty() {
  return <div className="flex h-[240px] items-center justify-center text-sm text-slate-400">Not enough data yet</div>
}
