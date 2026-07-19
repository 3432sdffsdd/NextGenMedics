import { useEffect, useState } from 'react'
import { FiAlertCircle, FiCheckCircle, FiPlay } from 'react-icons/fi'
import { premiumStudyService } from '../../services/api'
import Alert from '../../components/dashboard/Alert'
import McqPlayer from '../../components/dashboard/McqPlayer'
import StatCard from '../../components/dashboard/StatCard'
import { useToast } from '../../context/ToastContext'

export default function MyMistakes() {
  const toast = useToast()
  const [stats, setStats] = useState(null)
  const [items, setItems] = useState([])
  const [total, setTotal] = useState(0)
  const [page, setPage] = useState(1)
  const [filters, setFilters] = useState({ subject: '', chapter: '', topic: '', date_from: '', date_to: '' })
  const [loading, setLoading] = useState(true)
  const [practice, setPractice] = useState(null)
  const [startingPractice, setStartingPractice] = useState(false)

  const load = (p = 1) => {
    setLoading(true)
    Promise.all([
      premiumStudyService.mistakeStats(),
      premiumStudyService.mistakes({ ...filters, page: p, status: 'active' }),
    ])
      .then(([st, list]) => {
        setStats(st.data.data)
        setItems(list.data.data?.items || [])
        setTotal(list.data.data?.total || 0)
        setPage(p)
      })
      .catch((err) => toast.error(err.response?.data?.message || 'Could not load mistakes'))
      .finally(() => setLoading(false))
  }

  useEffect(() => { load(1) }, [])

  const startPractice = async () => {
    setStartingPractice(true)
    try {
      const { data } = await premiumStudyService.mistakesPractice({ limit: 20 })
      const qs = data.data?.questions || []
      if (!qs.length) {
        toast.info('No remaining mistakes to practice yet')
        return
      }
      setPractice(qs)
    } catch (err) {
      toast.error(err.response?.data?.message || 'Could not start practice')
    } finally {
      setStartingPractice(false)
    }
  }

  if (practice) {
    return (
      <div className="rounded-2xl border border-slate-100 bg-white p-6 shadow-soft">
        <McqPlayer
          questions={practice}
          source="weak"
          title="Practice My Mistakes"
          onClose={() => { setPractice(null); load(page) }}
        />
      </div>
    )
  }

  return (
    <div>
      <div className="flex flex-wrap items-start justify-between gap-4">
        <div>
          <h2 className="font-display text-2xl font-bold text-navy">My Mistakes</h2>
          <p className="text-sm text-slate-500">Wrong answers from course quizzes and Daily Challenges appear here. Practice them to improve weak topics.</p>
        </div>
        <button type="button" onClick={startPractice} disabled={startingPractice} className="btn-primary text-sm disabled:opacity-50">
          <FiPlay className="inline mr-1" /> {startingPractice ? 'Loading…' : 'Practice My Mistakes'}
        </button>
      </div>

      {stats && (
        <div className="mt-6 grid gap-4 sm:grid-cols-3">
          <StatCard label="Total Mistakes" value={stats.total || 0} icon={FiAlertCircle} tone="amber" />
          <StatCard label="Remaining" value={stats.remaining || 0} icon={FiAlertCircle} tone="red" />
          <StatCard label="Mastered" value={stats.mastered || 0} icon={FiCheckCircle} tone="emerald" />
        </div>
      )}
      {stats && (
        <p className="mt-2 text-xs text-slate-400">
          Remaining = still wrong · Mastered = cleared by practicing · List below shows remaining only
          {total > 0 ? ` · ${total} shown` : ''}
        </p>
      )}

      <div className="mt-6 grid gap-3 rounded-2xl border border-slate-100 bg-white p-4 shadow-soft sm:grid-cols-2 lg:grid-cols-4">
        <input type="text" placeholder="Topic" value={filters.topic || filters.subject} onChange={(e) => setFilters((f) => ({ ...f, topic: e.target.value, subject: e.target.value }))} className="input-field text-sm" />
        <input type="date" value={filters.date_from} onChange={(e) => setFilters((f) => ({ ...f, date_from: e.target.value }))} className="input-field text-sm" />
        <input type="date" value={filters.date_to} onChange={(e) => setFilters((f) => ({ ...f, date_to: e.target.value }))} className="input-field text-sm" />
        <button type="button" onClick={() => load(1)} className="btn-secondary text-sm">Filter</button>
      </div>

      {loading ? (
        <p className="mt-8 text-center text-slate-400">Loading mistakes…</p>
      ) : items.length === 0 ? (
        <div className="mt-6"><Alert>No mistakes yet — wrong answers from course quizzes and Daily Challenges will appear here.</Alert></div>
      ) : (
        <div className="mt-6 space-y-4">
          {items.map((m) => (
            <div key={m.id} className="rounded-2xl border border-slate-100 bg-white p-5 shadow-soft">
              <p className="font-medium text-navy">{m.question}</p>
              <div className="mt-2 flex flex-wrap gap-2 text-xs">
                {(m.topic || m.subject) && <span className="rounded-full bg-primary/10 px-2 py-0.5 text-primary">{m.topic || m.subject}</span>}
                {m.source && (
                  <span className="rounded-full bg-slate-100 px-2 py-0.5 text-slate-600">
                    {m.source === 'daily' ? 'Daily Challenge'
                      : m.source === 'practice' ? 'Question Bank'
                        : m.source === 'weak' ? 'Weak Areas'
                          : 'Course Quiz'}
                  </span>
                )}
              </div>
              <div className="mt-3 space-y-1 rounded-xl bg-slate-50 px-3 py-2 text-sm">
                <p>
                  <span className="text-slate-500">You chose: </span>
                  {m.selected_option ? (
                    <span className="font-semibold text-red-600">
                      {m.selected_option}{m.selected_option_text ? ` — ${m.selected_option_text}` : ''}
                    </span>
                  ) : (
                    <span className="font-semibold text-slate-500">No answer selected</span>
                  )}
                </p>
                <p>
                  <span className="text-slate-500">Correct answer: </span>
                  <span className="font-semibold text-emerald-600">
                    {m.correct_option || '—'}
                    {m.correct_option_text ? ` — ${m.correct_option_text}` : ''}
                  </span>
                </p>
              </div>
              {m.explanation && (
                <p className="mt-3 rounded-xl bg-slate-50 p-3 text-sm text-slate-600"><span className="font-semibold">Explanation:</span> {m.explanation}</p>
              )}
              <p className="mt-2 text-xs text-slate-400">Last wrong: {m.last_wrong_at ? new Date(m.last_wrong_at).toLocaleDateString() : '—'}</p>
            </div>
          ))}
        </div>
      )}
    </div>
  )
}
