import { SkeletonRows } from './Skeleton'
import EmptyState from './EmptyState'

/**
 * Lightweight, reusable table.
 *
 * columns: [{ key, header, render?(row), className?, align? }]
 * rows: array of objects (must have a stable `id` or pass rowKey)
 */
export default function DataTable({
  columns,
  rows = [],
  loading = false,
  rowKey = (row, i) => row.id ?? i,
  onRowClick,
  empty,
  className = '',
}) {
  const align = { left: 'text-left', center: 'text-center', right: 'text-right' }

  return (
    <div className={`overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-[0_2px_16px_rgba(15,23,42,0.05)] ${className}`}>
      <div className="max-h-[70vh] overflow-auto">
        <table className="w-full border-collapse text-sm">
          <thead className="sticky top-0 z-10">
            <tr className="bg-slate-50/95 backdrop-blur">
              {columns.map((col) => (
                <th
                  key={col.key}
                  className={`whitespace-nowrap border-b border-slate-200 px-5 py-3.5 text-xs font-semibold uppercase tracking-wide text-slate-500 ${align[col.align || 'left']} ${col.className || ''}`}
                >
                  {col.header}
                </th>
              ))}
            </tr>
          </thead>
          <tbody>
            {loading ? (
              <tr>
                <td colSpan={columns.length} className="px-5 py-6">
                  <SkeletonRows rows={6} />
                </td>
              </tr>
            ) : rows.length === 0 ? (
              <tr>
                <td colSpan={columns.length} className="px-5 py-4">
                  {empty || <EmptyState />}
                </td>
              </tr>
            ) : (
              rows.map((row, i) => (
                <tr
                  key={rowKey(row, i)}
                  onClick={onRowClick ? () => onRowClick(row) : undefined}
                  className={`border-b border-slate-100 transition-colors last:border-0 odd:bg-white even:bg-slate-50/40 hover:bg-blue-50/50 ${onRowClick ? 'cursor-pointer' : ''}`}
                >
                  {columns.map((col) => (
                    <td
                      key={col.key}
                      className={`px-5 py-3.5 text-slate-700 ${align[col.align || 'left']} ${col.cellClassName || ''}`}
                    >
                      {col.render ? col.render(row) : row[col.key]}
                    </td>
                  ))}
                </tr>
              ))
            )}
          </tbody>
        </table>
      </div>
    </div>
  )
}
