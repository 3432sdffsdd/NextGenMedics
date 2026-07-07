import { FiFilter, FiX } from 'react-icons/fi'
import { STATUS_OPTIONS } from '../../utils/scheduleStatus'

const selectCls =
  'rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/15'

export default function ScheduleFilters({
  filters,
  onChange,
  courses = [],
  batches = [],
  teachers = [],
  showTeacher = true,
}) {
  const set = (key) => (e) => onChange({ ...filters, [key]: e.target.value })
  const hasActive = Object.values(filters).some((v) => v)

  return (
    <div className="flex flex-wrap items-center gap-2.5">
      <span className="flex items-center gap-1.5 text-sm font-medium text-slate-500">
        <FiFilter size={15} /> Filters
      </span>

      <select className={selectCls} value={filters.course_id || ''} onChange={set('course_id')}>
        <option value="">All courses</option>
        {courses.map((c) => (
          <option key={c.id} value={c.id}>{c.title}</option>
        ))}
      </select>

      <select className={selectCls} value={filters.batch_id || ''} onChange={set('batch_id')} disabled={!batches.length}>
        <option value="">All batches</option>
        {batches.map((b) => (
          <option key={b.id} value={b.id}>{b.name}</option>
        ))}
      </select>

      {showTeacher && (
        <select className={selectCls} value={filters.teacher_id || ''} onChange={set('teacher_id')} disabled={!teachers.length}>
          <option value="">All teachers</option>
          {teachers.map((t) => (
            <option key={t.id} value={t.id}>
              {t.first_name ? `${t.first_name} ${t.last_name}` : t.name}
            </option>
          ))}
        </select>
      )}

      <select className={selectCls} value={filters.status || ''} onChange={set('status')}>
        <option value="">All statuses</option>
        {STATUS_OPTIONS.map((s) => (
          <option key={s.value} value={s.value}>{s.label}</option>
        ))}
      </select>

      <input
        className={selectCls}
        placeholder="Subject…"
        value={filters.subject || ''}
        onChange={set('subject')}
      />

      {hasActive && (
        <button
          type="button"
          onClick={() => onChange({})}
          className="flex items-center gap-1 rounded-xl px-2.5 py-2 text-sm font-medium text-slate-500 transition hover:bg-slate-100 hover:text-slate-700"
        >
          <FiX size={14} /> Clear
        </button>
      )}
    </div>
  )
}
