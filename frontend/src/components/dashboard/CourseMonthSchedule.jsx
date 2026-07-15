import { useCallback, useEffect, useMemo, useState } from 'react'
import { FiUpload, FiDownload, FiTrash2, FiCalendar, FiList, FiCheck } from 'react-icons/fi'
import * as XLSX from 'xlsx'
import { schedulesService, mediaUrl } from '../../services/api'
import { useConfirm } from '../../context/ConfirmContext'
import { useToast } from '../../context/ToastContext'
import { Button, Modal, EmptyState } from '../ui'
import ScheduleCalendar from '../schedule/ScheduleCalendar'
import ScheduleDetailModal from '../schedule/ScheduleDetailModal'
import StatusBadge from '../schedule/StatusBadge'
import {
  downloadMonthTemplate,
  monthOptions,
  monthRange,
  parseMonthScheduleRows,
  readScheduleWorkbook,
} from '../../utils/scheduleExcel'
import { fmtDate, fmtTime, resolveScheduleStatus, STATUS_CONFIG } from '../../utils/scheduleStatus'

const inputCls =
  'rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/15'

export default function CourseMonthSchedule({ courseId, canEdit = false }) {
  const confirm = useConfirm()
  const toast = useToast()
  const months = useMemo(() => monthOptions(), [])
  const [selectedMonth, setSelectedMonth] = useState(months.find((m) => {
    const now = new Date()
    return m.value === `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`
  })?.value || months[6]?.value)

  const [schedules, setSchedules] = useState([])
  const [uploads, setUploads] = useState([])
  const [loading, setLoading] = useState(true)
  const [importing, setImporting] = useState(false)
  const [preview, setPreview] = useState(null)
  const [previewFile, setPreviewFile] = useState(null)
  const [viewMode, setViewMode] = useState('calendar')
  const [detail, setDetail] = useState(null)

  const monthUpload = uploads.find((u) => u.month_year === selectedMonth)
  const { from, to } = monthRange(selectedMonth)

  const loadUploads = useCallback(() => {
    schedulesService.listMonthUploads(courseId)
      .then(({ data }) => setUploads(data.data || []))
      .catch(() => setUploads([]))
  }, [courseId])

  const loadSchedules = useCallback(() => {
    setLoading(true)
    schedulesService.list({ course_id: courseId, date_from: from, date_to: to })
      .then(({ data }) => setSchedules(data.data || []))
      .catch(() => setSchedules([]))
      .finally(() => setLoading(false))
  }, [courseId, from, to])

  useEffect(() => { loadUploads() }, [loadUploads])
  useEffect(() => { loadSchedules() }, [loadSchedules])

  const handleFilePick = (e) => {
    const file = e.target.files?.[0]
    e.target.value = ''
    if (!file) return

    const reader = new FileReader()
    reader.onload = (evt) => {
      try {
        const rows = readScheduleWorkbook(evt.target.result)
        const parsed = parseMonthScheduleRows(rows, selectedMonth)
        if (!parsed.length) {
          toast.error('No valid rows for this month. Check Date, Start/End Time, and Lecture Title columns.')
          return
        }
        setPreview(parsed)
        setPreviewFile(file)
        toast.success(`Parsed ${parsed.length} class(es). Review and confirm to publish.`)
      } catch {
        toast.error('Could not read the Excel file')
      }
    }
    reader.readAsArrayBuffer(file)
  }

  const confirmImport = async () => {
    if (!preview?.length) return
    const ok = await confirm({
      title: 'Publish monthly schedule',
      message: `Replace all classes for ${months.find((m) => m.value === selectedMonth)?.label || selectedMonth} with ${preview.length} imported row(s)?`,
      confirmText: 'Publish',
      tone: 'primary',
    })
    if (!ok) return

    setImporting(true)
    try {
      const fd = new FormData()
      fd.append('month', selectedMonth)
      fd.append('replace', 'true')
      fd.append('rows', JSON.stringify(preview))
      if (previewFile) fd.append('file', previewFile)

      await schedulesService.importMonth(courseId, fd)
      toast.success('Monthly schedule published — everyone in the course can see it now.')
      setPreview(null)
      setPreviewFile(null)
      loadSchedules()
      loadUploads()
    } catch (err) {
      toast.error(err.response?.data?.message || 'Import failed')
    } finally {
      setImporting(false)
    }
  }

  const clearMonth = async () => {
    if (!(await confirm({
      title: 'Clear month schedule',
      message: `Delete all classes and the uploaded sheet for ${selectedMonth}?`,
      confirmText: 'Clear',
      tone: 'danger',
    }))) return

    try {
      await schedulesService.clearMonth(courseId, selectedMonth)
      toast.success('Month schedule cleared')
      loadSchedules()
      loadUploads()
    } catch {
      toast.error('Could not clear schedule')
    }
  }

  const downloadUploaded = () => {
    if (!monthUpload?.file_path) {
      downloadCurrentExcel()
      return
    }
    window.open(mediaUrl(monthUpload.file_path, { download: true }), '_blank')
  }

  const downloadCurrentExcel = () => {
    const rows = [['Date', 'Subject', 'Topic', 'Lecture Title', 'Start Time', 'End Time', 'Teacher', 'Meeting Link']]
    schedules.forEach((s) => rows.push([
      s.class_date,
      s.subject || '',
      s.topic_covered || '',
      s.lecture_title,
      String(s.start_time).slice(0, 5),
      String(s.end_time).slice(0, 5),
      s.teacher_name || '',
      s.meeting_link || '',
    ]))
    const ws = XLSX.utils.aoa_to_sheet(rows)
    const wb = XLSX.utils.book_new()
    XLSX.utils.book_append_sheet(wb, ws, 'Schedule')
    XLSX.writeFile(wb, `schedule-${selectedMonth}.xlsx`)
  }

  const sorted = [...schedules].sort((a, b) =>
    `${a.class_date}${a.start_time}`.localeCompare(`${b.class_date}${b.start_time}`)
  )

  return (
    <div className="space-y-6">
      {/* Header + month selector */}
      <div className="flex flex-wrap items-end justify-between gap-4 rounded-2xl border border-slate-100 bg-white p-5 shadow-soft">
        <div>
          <h3 className="flex items-center gap-2 font-bold text-navy">
            <FiCalendar className="text-primary" /> Monthly class schedule
          </h3>
          <p className="mt-1 text-sm text-slate-500">
            {canEdit
              ? 'Select a month, upload an Excel sheet, and publish — students see the calendar instantly.'
              : 'View your class timetable for the selected month.'}
          </p>
        </div>
        <div>
          <label className="text-xs font-medium uppercase tracking-wide text-slate-400">Month</label>
          <select
            value={selectedMonth}
            onChange={(e) => { setSelectedMonth(e.target.value); setPreview(null) }}
            className={`mt-1 ${inputCls}`}
          >
            {months.map((m) => (
              <option key={m.value} value={m.value}>{m.label}</option>
            ))}
          </select>
        </div>
      </div>

      {/* Teacher upload toolbar */}
      {canEdit && (
        <div className="rounded-2xl border border-slate-100 bg-white p-5 shadow-soft">
          <div className="flex flex-wrap items-center gap-3">
            <label className="btn-primary inline-flex cursor-pointer items-center gap-2 text-sm">
              <FiUpload size={16} /> Upload Excel for {months.find((m) => m.value === selectedMonth)?.label}
              <input type="file" accept=".xlsx,.xls,.csv" className="hidden" onChange={handleFilePick} />
            </label>
            <button type="button" onClick={downloadMonthTemplate} className="btn-secondary inline-flex items-center gap-2 text-sm">
              <FiDownload size={16} /> Download template
            </button>
            {(schedules.length > 0 || monthUpload) && (
              <button type="button" onClick={downloadUploaded} className="btn-secondary inline-flex items-center gap-2 text-sm">
                <FiDownload size={16} /> Download {monthUpload ? 'uploaded sheet' : 'current schedule'}
              </button>
            )}
            {(schedules.length > 0 || monthUpload) && (
              <button type="button" onClick={clearMonth} className="inline-flex items-center gap-2 rounded-xl border border-red-200 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50">
                <FiTrash2 size={16} /> Clear month
              </button>
            )}
          </div>

          {monthUpload && (
            <p className="mt-3 text-xs text-slate-500">
              Last upload: {monthUpload.row_count} classes
              {monthUpload.uploader_name ? ` by ${monthUpload.uploader_name}` : ''}
              {monthUpload.updated_at ? ` · ${new Date(monthUpload.updated_at).toLocaleString()}` : ''}
            </p>
          )}

          {preview && (
            <div className="mt-4 rounded-xl border border-primary/20 bg-primary/5 p-4">
              <p className="text-sm font-medium text-navy">
                Preview: {preview.length} class(es) ready for {months.find((m) => m.value === selectedMonth)?.label}
              </p>
              <div className="mt-3 max-h-48 overflow-auto rounded-lg border border-slate-100 bg-white">
                <table className="w-full text-left text-xs">
                  <thead className="sticky top-0 bg-slate-50 text-slate-500">
                    <tr>
                      <th className="px-3 py-2">Date</th>
                      <th className="px-3 py-2">Time</th>
                      <th className="px-3 py-2">Title</th>
                      <th className="px-3 py-2">Subject</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-slate-50">
                    {preview.slice(0, 20).map((r, i) => (
                      <tr key={i}>
                        <td className="px-3 py-2">{r.class_date}</td>
                        <td className="px-3 py-2">{r.start_time}–{r.end_time}</td>
                        <td className="px-3 py-2">{r.lecture_title}</td>
                        <td className="px-3 py-2">{r.subject || '—'}</td>
                      </tr>
                    ))}
                  </tbody>
                </table>
                {preview.length > 20 && <p className="px-3 py-2 text-xs text-slate-400">+ {preview.length - 20} more rows</p>}
              </div>
              <div className="mt-3 flex flex-wrap gap-2">
                <Button onClick={confirmImport} disabled={importing}>
                  <FiCheck size={16} /> {importing ? 'Publishing…' : 'Confirm & publish'}
                </Button>
                <Button variant="outline" onClick={() => { setPreview(null); setPreviewFile(null) }}>Cancel</Button>
              </div>
            </div>
          )}
        </div>
      )}

      {/* View toggle + legend */}
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div className="flex gap-1 rounded-xl bg-slate-100 p-1">
          <button
            type="button"
            onClick={() => setViewMode('calendar')}
            className={`inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-medium ${viewMode === 'calendar' ? 'bg-white text-navy shadow-sm' : 'text-slate-500'}`}
          >
            <FiCalendar size={14} /> Calendar
          </button>
          <button
            type="button"
            onClick={() => setViewMode('list')}
            className={`inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-medium ${viewMode === 'list' ? 'bg-white text-navy shadow-sm' : 'text-slate-500'}`}
          >
            <FiList size={14} /> List
          </button>
        </div>
        <div className="flex flex-wrap gap-3 text-xs text-slate-500">
          {Object.entries(STATUS_CONFIG).slice(0, 4).map(([k, c]) => (
            <span key={k} className="inline-flex items-center gap-1">
              <span className="h-2.5 w-2.5 rounded-full" style={{ background: c.color }} /> {c.label}
            </span>
          ))}
        </div>
      </div>

      {/* Content */}
      {loading ? (
        <div className="rounded-2xl border border-slate-100 bg-white p-12 text-center text-sm text-slate-400 shadow-soft">Loading schedule…</div>
      ) : schedules.length === 0 ? (
        <EmptyState
          icon={FiCalendar}
          title="No classes scheduled"
          description={canEdit
            ? `Upload an Excel sheet for ${months.find((m) => m.value === selectedMonth)?.label} to publish the timetable.`
            : 'Your teacher has not published a schedule for this month yet.'}
        />
      ) : viewMode === 'calendar' ? (
        <div className="rounded-2xl border border-slate-100 bg-white p-4 shadow-soft">
          <ScheduleCalendar
            key={selectedMonth}
            schedules={schedules}
            initialDate={`${selectedMonth}-01`}
            onEventClick={setDetail}
          />
        </div>
      ) : (
        <div className="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-soft">
          <table className="w-full text-left text-sm">
            <thead className="bg-slate-50 text-xs uppercase text-slate-500">
              <tr>
                <th className="px-4 py-3">Date</th>
                <th className="px-4 py-3">Time</th>
                <th className="px-4 py-3">Class</th>
                <th className="px-4 py-3">Subject</th>
                <th className="px-4 py-3">Teacher</th>
                <th className="px-4 py-3">Status</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {sorted.map((s) => (
                <tr key={s.id} className="cursor-pointer hover:bg-slate-50" onClick={() => setDetail(s)}>
                  <td className="px-4 py-3 whitespace-nowrap">{fmtDate(s.class_date)}</td>
                  <td className="px-4 py-3 whitespace-nowrap">{fmtTime(s.start_time)} – {fmtTime(s.end_time)}</td>
                  <td className="px-4 py-3 font-medium text-navy">{s.lecture_title}</td>
                  <td className="px-4 py-3 text-slate-600">{s.subject || '—'}</td>
                  <td className="px-4 py-3 text-slate-600">{s.teacher_name || '—'}</td>
                  <td className="px-4 py-3"><StatusBadge status={resolveScheduleStatus(s)} /></td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}

      <ScheduleDetailModal
        schedule={detail}
        open={!!detail}
        onClose={() => setDetail(null)}
        canEdit={false}
      />
    </div>
  )
}
