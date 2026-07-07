import { FiChevronLeft, FiChevronRight } from 'react-icons/fi'

function range(start, end) {
  return Array.from({ length: end - start + 1 }, (_, i) => start + i)
}

function getPages(current, total) {
  if (total <= 7) return range(1, total)
  if (current <= 4) return [...range(1, 5), '…', total]
  if (current >= total - 3) return [1, '…', ...range(total - 4, total)]
  return [1, '…', current - 1, current, current + 1, '…', total]
}

export default function Pagination({ page, totalPages, onChange, className = '' }) {
  if (!totalPages || totalPages <= 1) return null
  const pages = getPages(page, totalPages)

  const btn =
    'grid h-9 min-w-9 place-items-center rounded-lg px-2 text-sm font-medium transition-colors'

  return (
    <nav className={`flex items-center gap-1.5 ${className}`} aria-label="Pagination">
      <button
        type="button"
        onClick={() => onChange(page - 1)}
        disabled={page <= 1}
        className={`${btn} border border-slate-200 text-slate-500 hover:bg-slate-50 disabled:opacity-40 disabled:hover:bg-transparent`}
        aria-label="Previous page"
      >
        <FiChevronLeft size={16} />
      </button>

      {pages.map((p, i) =>
        p === '…' ? (
          <span key={`gap-${i}`} className="px-1 text-slate-400">…</span>
        ) : (
          <button
            key={p}
            type="button"
            onClick={() => onChange(p)}
            aria-current={p === page ? 'page' : undefined}
            className={`${btn} ${
              p === page
                ? 'bg-blue-600 text-white shadow-sm'
                : 'text-slate-600 hover:bg-slate-100'
            }`}
          >
            {p}
          </button>
        )
      )}

      <button
        type="button"
        onClick={() => onChange(page + 1)}
        disabled={page >= totalPages}
        className={`${btn} border border-slate-200 text-slate-500 hover:bg-slate-50 disabled:opacity-40 disabled:hover:bg-transparent`}
        aria-label="Next page"
      >
        <FiChevronRight size={16} />
      </button>
    </nav>
  )
}
