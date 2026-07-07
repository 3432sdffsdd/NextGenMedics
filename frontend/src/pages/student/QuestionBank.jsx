import { useEffect, useState } from 'react'
import { FiSearch, FiFilter, FiPlay, FiClock } from 'react-icons/fi'
import { premiumStudyService } from '../../services/api'
import Alert from '../../components/dashboard/Alert'
import McqPlayer from '../../components/dashboard/McqPlayer'

const ATTEMPT_FILTERS = [
  { value: '', label: 'All questions' },
  { value: 'unattempted', label: 'Unattempted' },
  { value: 'correct', label: 'Correct only' },
  { value: 'incorrect', label: 'Incorrect only' },
]

export default function QuestionBank() {
  const [filters, setFilters] = useState({ subject: '', chapter: '', topic: '', difficulty: '', search: '', attempt_filter: '' })
  const [options, setOptions] = useState({ subjects: [], chapters: [], topics: [] })
  const [items, setItems] = useState([])
  const [total, setTotal] = useState(0)
  const [page, setPage] = useState(1)
  const [loading, setLoading] = useState(true)
  const [practice, setPractice] = useState(null)

  useEffect(() => {
    premiumStudyService.questionBankFilters().then(({ data }) => setOptions(data.data || {})).catch(() => {})
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

  useEffect(() => { load(1); setPage(1) }, [filters.subject, filters.chapter, filters.topic, filters.difficulty, filters.attempt_filter])

  const startPractice = async (mode, timed = false) => {
    const { data } = await premiumStudyService.questionBankPractice({
      mode,
      subject: filters.subject || undefined,
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
          onClose={() => setPractice(null)}
        />
      </div>
    )
  }

  const pages = Math.ceil(total / 20)

  return (
    <div>
      <h2 className="font-display text-2xl font-bold text-navy">Question Bank</h2>
      <p className="text-sm text-slate-500">Search and practice thousands of MCQs by subject, topic, and difficulty.</p>

      <div className="mt-6 grid gap-4 rounded-2xl border border-slate-100 bg-white p-5 shadow-soft lg:grid-cols-4">
        <select value={filters.subject} onChange={(e) => setFilters((f) => ({ ...f, subject: e.target.value }))} className="input-field text-sm">
          <option value="">All subjects</option>
          {options.subjects?.map((s) => <option key={s} value={s}>{s}</option>)}
        </select>
        <select value={filters.chapter} onChange={(e) => setFilters((f) => ({ ...f, chapter: e.target.value }))} className="input-field text-sm">
          <option value="">All chapters</option>
          {options.chapters?.map((s) => <option key={s} value={s}>{s}</option>)}
        </select>
        <select value={filters.topic} onChange={(e) => setFilters((f) => ({ ...f, topic: e.target.value }))} className="input-field text-sm">
          <option value="">All topics</option>
          {options.topics?.map((s) => <option key={s} value={s}>{s}</option>)}
        </select>
        <select value={filters.difficulty} onChange={(e) => setFilters((f) => ({ ...f, difficulty: e.target.value }))} className="input-field text-sm">
          <option value="">Any difficulty</option>
          <option value="easy">Easy</option>
          <option value="medium">Medium</option>
          <option value="hard">Hard</option>
        </select>
        <select value={filters.attempt_filter} onChange={(e) => setFilters((f) => ({ ...f, attempt_filter: e.target.value }))} className="input-field text-sm lg:col-span-2">
          {ATTEMPT_FILTERS.map((f) => <option key={f.value} value={f.value}>{f.label}</option>)}
        </select>
        <div className="relative lg:col-span-2">
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
        <button type="button" onClick={() => startPractice('random')} className="btn-primary text-sm"><FiPlay className="inline mr-1" /> Random (20)</button>
        {filters.topic && <button type="button" onClick={() => startPractice('topic')} className="btn-secondary text-sm">Topic-wise</button>}
        {filters.subject && <button type="button" onClick={() => startPractice('subject')} className="btn-secondary text-sm">Subject-wise</button>}
        <button type="button" onClick={() => startPractice('random', true)} className="btn-secondary text-sm"><FiClock className="inline mr-1" /> Timed (20 min)</button>
        <button type="button" onClick={() => load(page)} className="btn-secondary text-sm"><FiFilter className="inline mr-1" /> Apply filters</button>
      </div>

      {loading ? (
        <p className="mt-8 text-center text-slate-400">Loading questions…</p>
      ) : items.length === 0 ? (
        <div className="mt-6"><Alert>No questions match your filters.</Alert></div>
      ) : (
        <div className="mt-6 space-y-3">
          <p className="text-sm text-slate-500">{total} question{total !== 1 ? 's' : ''} found</p>
          {items.map((q, i) => (
            <div key={q.id} className="rounded-xl border border-slate-100 bg-white p-4 shadow-soft">
              <div className="flex flex-wrap items-start justify-between gap-2">
                <p className="flex-1 text-sm font-medium text-navy"><span className="text-slate-400">{(page - 1) * 20 + i + 1}.</span> {q.question}</p>
                <div className="flex gap-2 text-xs">
                  {q.subject && <span className="rounded-full bg-primary/10 px-2 py-0.5 text-primary">{q.subject}</span>}
                  {q.difficulty && <span className="rounded-full bg-slate-100 px-2 py-0.5 capitalize text-slate-500">{q.difficulty}</span>}
                </div>
              </div>
              {q.topic && <p className="mt-1 text-xs text-slate-400">{q.chapter} · {q.topic}</p>}
            </div>
          ))}
          {pages > 1 && (
            <div className="flex justify-center gap-2 pt-4">
              <button type="button" disabled={page <= 1} onClick={() => { setPage(page - 1); load(page - 1) }} className="btn-secondary text-sm disabled:opacity-40">Previous</button>
              <span className="px-3 py-2 text-sm text-slate-500">Page {page} of {pages}</span>
              <button type="button" disabled={page >= pages} onClick={() => { setPage(page + 1); load(page + 1) }} className="btn-secondary text-sm disabled:opacity-40">Next</button>
            </div>
          )}
        </div>
      )}
    </div>
  )
}
