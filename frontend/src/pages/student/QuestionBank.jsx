import { useEffect, useState } from 'react'
import { FiSearch, FiFilter, FiPlay, FiClock } from 'react-icons/fi'
import { premiumStudyService } from '../../services/api'
import Alert from '../../components/dashboard/Alert'
import McqPlayer from '../../components/dashboard/McqPlayer'

/** Response B: All / Unattempted / Attempted only */
const ATTEMPT_FILTERS = [
  { value: '', label: 'All questions' },
  { value: 'unattempted', label: 'Unattempted' },
  { value: 'attempted', label: 'Attempted' },
]

export default function QuestionBank() {
  const [filters, setFilters] = useState({ topic: '', search: '', attempt_filter: '' })
  const [options, setOptions] = useState({ topics: [], quizzes: [] })
  const [items, setItems] = useState([])
  const [total, setTotal] = useState(0)
  const [page, setPage] = useState(1)
  const [loading, setLoading] = useState(true)
  const [practice, setPractice] = useState(null)

  useEffect(() => {
    premiumStudyService.questionBankFilters()
      .then(({ data }) => setOptions(data.data || { topics: [], quizzes: [] }))
      .catch(() => {})
  }, [])

  const load = (p = page) => {
    setLoading(true)
    premiumStudyService.questionBank({ ...filters, page: p, per_page: 20 })
      .then(({ data }) => {
        setItems(data.data?.items || [])
        setTotal(data.data?.total || 0)
      })
      .finally(() => setLoading(false))
  }

  useEffect(() => {
    load(1)
    setPage(1)
  }, [filters.topic, filters.attempt_filter])

  const startPractice = async (timed = false) => {
    const { data } = await premiumStudyService.questionBankPractice({
      topic: filters.topic || undefined,
      attempt_filter: filters.attempt_filter || undefined,
      limit: 20,
      timed,
      time_limit_minutes: timed ? 20 : undefined,
    })
    const qs = data.data?.questions || []
    if (!qs.length) return
    setPractice({
      questions: qs,
      timed,
      timeLimit: data.data?.time_limit_sec,
    })
  }

  if (practice) {
    return (
      <div className="rounded-2xl border border-slate-100 bg-white p-6 shadow-soft">
        <McqPlayer
          questions={practice.questions}
          source="bank"
          timeLimitSeconds={practice.timed ? practice.timeLimit : null}
          title={practice.timed ? 'Timed Practice · 20 MCQs' : 'Question Bank Practice'}
          onClose={() => { setPractice(null); load(page) }}
        />
      </div>
    )
  }

  const pages = Math.max(1, Math.ceil(total / 20))
  const topicOptions = options.quizzes?.length
    ? options.quizzes.map((q) => q.title)
    : (options.topics || [])

  return (
    <div>
      <h2 className="font-display text-2xl font-bold text-navy">Question Bank</h2>
      <p className="text-sm text-slate-500">
        Practice MCQs from your teachers&apos; published quizzes. Wrong answers also appear in My Mistakes.
      </p>

      <div className="mt-6 grid gap-4 rounded-2xl border border-slate-100 bg-white p-5 shadow-soft lg:grid-cols-3">
        <select
          value={filters.topic}
          onChange={(e) => setFilters((f) => ({ ...f, topic: e.target.value }))}
          className="input-field text-sm"
        >
          <option value="">All topics</option>
          {topicOptions.map((t) => (
            <option key={t} value={t}>{t}</option>
          ))}
        </select>
        <select
          value={filters.attempt_filter}
          onChange={(e) => setFilters((f) => ({ ...f, attempt_filter: e.target.value }))}
          className="input-field text-sm"
        >
          {ATTEMPT_FILTERS.map((f) => (
            <option key={f.value || 'all'} value={f.value}>{f.label}</option>
          ))}
        </select>
        <div className="relative">
          <FiSearch className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
          <input
            type="search"
            placeholder="Search questions…"
            value={filters.search}
            onChange={(e) => setFilters((f) => ({ ...f, search: e.target.value }))}
            onKeyDown={(e) => e.key === 'Enter' && load(1)}
            className="input-field w-full pl-10 text-sm"
          />
        </div>
      </div>

      <div className="mt-4 flex flex-wrap gap-2">
        <button type="button" onClick={() => startPractice(false)} className="btn-primary text-sm">
          <FiPlay className="inline mr-1" /> Practice (20)
        </button>
        <button type="button" onClick={() => startPractice(true)} className="btn-secondary text-sm">
          <FiClock className="inline mr-1" /> Timed (20 min)
        </button>
        <button type="button" onClick={() => load(1)} className="btn-secondary text-sm">
          <FiFilter className="inline mr-1" /> Apply search
        </button>
      </div>

      {loading ? (
        <p className="mt-8 text-center text-slate-400">Loading questions…</p>
      ) : items.length === 0 ? (
        <div className="mt-6">
          <Alert>No questions match your filters. Ask your teacher to publish course quizzes.</Alert>
        </div>
      ) : (
        <div className="mt-6 space-y-3">
          <p className="text-sm text-slate-500">{total} question{total !== 1 ? 's' : ''} found</p>
          {items.map((q, i) => (
            <div key={q.id} className="rounded-xl border border-slate-100 bg-white p-4 shadow-soft">
              <div className="flex flex-wrap items-start justify-between gap-2">
                <p className="flex-1 text-sm font-medium text-navy">
                  <span className="text-slate-400">{(page - 1) * 20 + i + 1}. </span>
                  {q.question}
                </p>
                <div className="flex flex-wrap gap-2 text-xs">
                  {q.attempted ? (
                    <span className="rounded-full bg-amber-50 px-2 py-0.5 font-semibold text-amber-700">Attempted</span>
                  ) : (
                    <span className="rounded-full bg-slate-100 px-2 py-0.5 text-slate-500">Unattempted</span>
                  )}
                  {q.topic && (
                    <span className="rounded-full bg-primary/10 px-2 py-0.5 text-primary">{q.topic}</span>
                  )}
                </div>
              </div>
            </div>
          ))}
          {pages > 1 && (
            <div className="flex justify-center gap-2 pt-4">
              <button
                type="button"
                disabled={page <= 1}
                onClick={() => { const p = page - 1; setPage(p); load(p) }}
                className="btn-secondary text-sm disabled:opacity-40"
              >
                Previous
              </button>
              <span className="px-3 py-2 text-sm text-slate-500">Page {page} of {pages}</span>
              <button
                type="button"
                disabled={page >= pages}
                onClick={() => { const p = page + 1; setPage(p); load(p) }}
                className="btn-secondary text-sm disabled:opacity-40"
              >
                Next
              </button>
            </div>
          )}
        </div>
      )}
    </div>
  )
}
