import { useCallback, useEffect, useMemo, useRef, useState } from 'react'
import { FiStar, FiFlag, FiSearch, FiRotateCw, FiChevronLeft, FiChevronRight } from 'react-icons/fi'
import { studentAiService } from '../../services/api'
import Alert from '../../components/dashboard/Alert'

const CARD_THEMES = [
  { front: 'from-violet-600 via-purple-600 to-indigo-700', back: 'from-fuchsia-500 via-pink-500 to-rose-600', accent: 'text-violet-100' },
  { front: 'from-cyan-500 via-sky-500 to-blue-600', back: 'from-teal-400 via-emerald-500 to-green-600', accent: 'text-cyan-50' },
  { front: 'from-orange-500 via-amber-500 to-yellow-500', back: 'from-red-500 via-rose-500 to-pink-600', accent: 'text-orange-50' },
  { front: 'from-indigo-600 via-blue-600 to-cyan-500', back: 'from-violet-500 via-purple-500 to-fuchsia-600', accent: 'text-indigo-50' },
  { front: 'from-emerald-500 via-teal-500 to-cyan-600', back: 'from-lime-500 via-green-500 to-emerald-600', accent: 'text-emerald-50' },
  { front: 'from-rose-500 via-red-500 to-orange-500', back: 'from-purple-600 via-violet-600 to-indigo-600', accent: 'text-rose-50' },
]

export default function FlashcardCenter() {
  const [cards, setCards] = useState([])
  const [filterMeta, setFilterMeta] = useState([])
  const [filters, setFilters] = useState({ search: '', lecture_id: '', favorite: false, difficult: false })
  const [index, setIndex] = useState(0)
  const [flipped, setFlipped] = useState(false)
  const [loading, setLoading] = useState(true)
  const touchStart = useRef(null)

  const load = useCallback(() => {
    setLoading(true)
    const params = {}
    if (filters.search) params.search = filters.search
    if (filters.lecture_id) params.lecture_id = filters.lecture_id
    if (filters.favorite) params.favorite = 1
    if (filters.difficult) params.difficult = 1
    studentAiService.flashcards(params)
      .then(({ data }) => {
        setCards(data.data.cards || [])
        setFilterMeta(data.data.filters || [])
        setIndex(0)
        setFlipped(false)
      })
      .finally(() => setLoading(false))
  }, [filters])

  useEffect(() => {
    const t = setTimeout(load, filters.search ? 300 : 0)
    return () => clearTimeout(t)
  }, [load, filters.search])

  const card = cards[index]
  const theme = CARD_THEMES[index % CARD_THEMES.length]

  const patch = (changes) => {
    if (!card) return
    setCards((prev) => prev.map((c, i) => (i === index ? { ...c, ...changes } : c)))
  }

  const saveProgress = (data) => {
    if (card) studentAiService.flashcardProgress(card.id, data).catch(() => {})
  }

  const next = useCallback(() => {
    setFlipped(false)
    setIndex((i) => Math.min(i + 1, cards.length - 1))
  }, [cards.length])

  const prev = useCallback(() => {
    setFlipped(false)
    setIndex((i) => Math.max(i - 1, 0))
  }, [])

  const toggleFavorite = () => { patch({ is_favorite: card.is_favorite ? 0 : 1 }); saveProgress({ is_favorite: !card.is_favorite }) }
  const toggleDifficult = () => { patch({ is_difficult: card.is_difficult ? 0 : 1 }); saveProgress({ is_difficult: !card.is_difficult }) }

  const rate = (status) => {
    patch({ progress_status: status, review_count: (card.review_count || 0) + 1 })
    saveProgress({ status, reviewed: true })
    next()
  }

  useEffect(() => {
    const onKey = (e) => {
      if (['INPUT', 'SELECT', 'TEXTAREA'].includes(e.target.tagName)) return
      if (e.code === 'Space') { e.preventDefault(); setFlipped((f) => !f) }
      else if (e.code === 'ArrowRight') next()
      else if (e.code === 'ArrowLeft') prev()
      else if (e.key === 'f') toggleFavorite()
      else if (e.key === 'd') toggleDifficult()
    }
    window.addEventListener('keydown', onKey)
    return () => window.removeEventListener('keydown', onKey)
  })

  const mastered = useMemo(() => cards.filter((c) => c.progress_status === 'mastered').length, [cards])
  const reviewed = useMemo(() => cards.filter((c) => c.review_count > 0).length, [cards])

  return (
    <div>
      <div className="flex flex-wrap items-end justify-between gap-4">
        <div>
          <h2 className="font-display text-2xl font-bold text-navy">Flashcard Center</h2>
          <p className="text-sm text-slate-500">Active recall for FCPS high-yield concepts.</p>
        </div>
        <div className="flex gap-6 text-center">
          <div><p className="text-xl font-bold text-primary">{cards.length}</p><p className="text-xs text-slate-400">Cards</p></div>
          <div><p className="text-xl font-bold text-green-600">{mastered}</p><p className="text-xs text-slate-400">Mastered</p></div>
          <div><p className="text-xl font-bold text-navy">{reviewed}</p><p className="text-xs text-slate-400">Reviewed</p></div>
        </div>
      </div>

      <div className="mt-6 flex flex-wrap items-center gap-3 rounded-2xl border border-slate-100 bg-white p-4 shadow-soft">
        <div className="relative flex-1 min-w-[180px]">
          <FiSearch className="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
          <input value={filters.search} onChange={(e) => setFilters((f) => ({ ...f, search: e.target.value }))}
            placeholder="Search flashcards…" className="w-full rounded-xl border border-slate-200 py-2 pl-9 pr-3 text-sm" />
        </div>
        <select value={filters.lecture_id} onChange={(e) => setFilters((f) => ({ ...f, lecture_id: e.target.value }))}
          className="rounded-xl border border-slate-200 px-3 py-2 text-sm">
          <option value="">All lectures</option>
          {filterMeta.map((m) => (
            <option key={m.lecture_id} value={m.lecture_id}>{m.lecture_title} ({m.count})</option>
          ))}
        </select>
        <button type="button" onClick={() => setFilters((f) => ({ ...f, favorite: !f.favorite }))}
          className={`flex items-center gap-1.5 rounded-xl border px-3 py-2 text-sm ${filters.favorite ? 'border-amber-400 bg-amber-50 text-amber-600' : 'border-slate-200 text-slate-500'}`}>
          <FiStar /> Favorites
        </button>
        <button type="button" onClick={() => setFilters((f) => ({ ...f, difficult: !f.difficult }))}
          className={`flex items-center gap-1.5 rounded-xl border px-3 py-2 text-sm ${filters.difficult ? 'border-red-400 bg-red-50 text-red-500' : 'border-slate-200 text-slate-500'}`}>
          <FiFlag /> Difficult
        </button>
      </div>

      {loading ? (
        <p className="mt-10 text-center text-slate-400">Loading flashcards…</p>
      ) : cards.length === 0 ? (
        <div className="mt-6"><Alert>No flashcards match your filters yet.</Alert></div>
      ) : (
        <div className="mt-6">
          <div
            className="mx-auto max-w-2xl [perspective:1200px]"
            onTouchStart={(e) => { touchStart.current = e.changedTouches[0].clientX }}
            onTouchEnd={(e) => {
              if (touchStart.current == null) return
              const dx = e.changedTouches[0].clientX - touchStart.current
              if (dx < -50) next()
              else if (dx > 50) prev()
              touchStart.current = null
            }}
          >
            <button
              type="button"
              aria-label="Flip flashcard"
              onClick={() => setFlipped((f) => !f)}
              className="group relative h-[320px] w-full cursor-pointer border-0 bg-transparent p-0 text-left focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 focus-visible:ring-offset-2"
              style={{ perspective: '1200px' }}
            >
              <div
                className={`relative h-full w-full transition-transform duration-700 ease-in-out [transform-style:preserve-3d] ${flipped ? '[transform:rotateY(180deg)]' : ''}`}
              >
                <div className={`absolute inset-0 flex flex-col items-center justify-center overflow-hidden rounded-3xl bg-gradient-to-br ${theme.front} p-8 text-center shadow-xl [backface-visibility:hidden]`}>
                  <div className="absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(255,255,255,0.18),transparent_45%)]" />
                  <div className="absolute right-4 top-4 z-10 flex gap-2">
                    <span
                      role="button"
                      tabIndex={0}
                      onClick={(e) => { e.stopPropagation(); toggleFavorite() }}
                      onKeyDown={(e) => { if (e.key === 'Enter') { e.stopPropagation(); toggleFavorite() } }}
                      className={`rounded-full bg-white/15 p-2 backdrop-blur-sm transition hover:bg-white/25 ${card.is_favorite ? 'text-amber-300' : 'text-white/70 hover:text-amber-200'}`}
                    >
                      <FiStar />
                    </span>
                    <span
                      role="button"
                      tabIndex={0}
                      onClick={(e) => { e.stopPropagation(); toggleDifficult() }}
                      onKeyDown={(e) => { if (e.key === 'Enter') { e.stopPropagation(); toggleDifficult() } }}
                      className={`rounded-full bg-white/15 p-2 backdrop-blur-sm transition hover:bg-white/25 ${card.is_difficult ? 'text-red-200' : 'text-white/70 hover:text-red-100'}`}
                    >
                      <FiFlag />
                    </span>
                  </div>
                  <span className="relative z-10 mb-4 rounded-full bg-white/20 px-3 py-1 text-xs font-bold uppercase tracking-widest text-white/90">Question</span>
                  <p className="relative z-10 max-h-[160px] overflow-y-auto text-xl font-semibold leading-relaxed text-white">{card.front}</p>
                  {card.topic && <span className="relative z-10 mt-4 rounded-full bg-black/15 px-3 py-1 text-xs text-white/90">{card.topic}</span>}
                  <span className="relative z-10 mt-5 flex items-center gap-1 text-xs text-white/75"><FiRotateCw /> Tap or press Space to flip</span>
                </div>

                <div className={`absolute inset-0 flex flex-col items-center justify-center overflow-hidden rounded-3xl bg-gradient-to-br ${theme.back} p-8 text-center shadow-xl [backface-visibility:hidden] [transform:rotateY(180deg)]`}>
                  <div className="absolute inset-0 bg-[radial-gradient(circle_at_80%_80%,rgba(255,255,255,0.16),transparent_45%)]" />
                  <span className="relative z-10 mb-4 rounded-full bg-white/20 px-3 py-1 text-xs font-bold uppercase tracking-widest text-white/90">Answer</span>
                  <p className="relative z-10 max-h-[180px] overflow-y-auto text-xl font-semibold leading-relaxed text-white">{card.back}</p>
                  {card.topic && <span className="relative z-10 mt-4 rounded-full bg-black/15 px-3 py-1 text-xs text-white/90">{card.topic}</span>}
                </div>
              </div>
            </button>
          </div>

          <div className="mx-auto mt-5 flex max-w-2xl items-center justify-between">
            <button type="button" onClick={prev} disabled={index === 0} className="flex items-center gap-1 rounded-xl border border-slate-200 px-4 py-2 text-sm text-slate-600 disabled:opacity-40"><FiChevronLeft /> Prev</button>
            <span className="text-sm text-slate-400">{index + 1} / {cards.length}</span>
            <button type="button" onClick={next} disabled={index === cards.length - 1} className="flex items-center gap-1 rounded-xl border border-slate-200 px-4 py-2 text-sm text-slate-600 disabled:opacity-40">Next <FiChevronRight /></button>
          </div>

          {flipped && (
            <div className="mx-auto mt-4 flex max-w-2xl gap-3">
              <button type="button" onClick={() => rate('learning')} className="flex-1 rounded-xl border border-amber-300 bg-amber-50 py-2.5 text-sm font-semibold text-amber-600 shadow-sm transition hover:bg-amber-100">Still learning</button>
              <button type="button" onClick={() => rate('mastered')} className="flex-1 rounded-xl border border-green-300 bg-green-50 py-2.5 text-sm font-semibold text-green-600 shadow-sm transition hover:bg-green-100">Mastered it</button>
            </div>
          )}
        </div>
      )}
    </div>
  )
}
