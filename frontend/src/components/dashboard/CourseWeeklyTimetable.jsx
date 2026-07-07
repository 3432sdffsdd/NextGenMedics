import { useEffect, useState } from 'react'
import { FiPlus, FiTrash2, FiUpload, FiDownload } from 'react-icons/fi'
import * as XLSX from 'xlsx'
import { scheduleService } from '../../services/api'
import { useConfirm } from '../../context/ConfirmContext'
import { useToast } from '../../context/ToastContext'

const DAYS = [
  { value: 1, label: 'Monday' },
  { value: 2, label: 'Tuesday' },
  { value: 3, label: 'Wednesday' },
  { value: 4, label: 'Thursday' },
  { value: 5, label: 'Friday' },
  { value: 6, label: 'Saturday' },
  { value: 0, label: 'Sunday' },
]

const emptySlot = () => ({ day_of_week: 1, start_time: '10:00', duration_minutes: 60, title: 'Live Class', meeting_url: '' })

const DAY_NAME_TO_NUM = {
  sunday: 0, sun: 0,
  monday: 1, mon: 1,
  tuesday: 2, tue: 2, tues: 2,
  wednesday: 3, wed: 3, weds: 3,
  thursday: 4, thu: 4, thur: 4, thurs: 4,
  friday: 5, fri: 5,
  saturday: 6, sat: 6,
}

const parseDay = (v) => {
  if (v === '' || v == null) return 1
  const n = Number(v)
  if (!Number.isNaN(n) && n >= 0 && n <= 6) return n
  return DAY_NAME_TO_NUM[String(v).trim().toLowerCase()] ?? 1
}

// Excel stores times as a fraction of a day; also accept "19:30" or "7:30 PM".
const parseTime = (v) => {
  if (v === '' || v == null) return ''
  if (typeof v === 'number' && v >= 0 && v < 1.0000001) {
    const totalMin = Math.round(v * 24 * 60)
    const h = Math.floor(totalMin / 60) % 24
    const m = totalMin % 60
    return `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}`
  }
  const match = String(v).trim().match(/^(\d{1,2}):(\d{2})\s*([APap][Mm])?/)
  if (match) {
    let h = Number(match[1])
    const m = Number(match[2])
    const suffix = match[3]?.toLowerCase()
    if (suffix === 'pm' && h < 12) h += 12
    if (suffix === 'am' && h === 12) h = 0
    return `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}`
  }
  return String(v).trim()
}

const parseDuration = (v) => {
  const n = parseInt(v, 10)
  return Number.isNaN(n) ? 60 : n
}

// Find a column value by fuzzy header match (case/spacing insensitive).
const pick = (row, ...needles) => {
  const keys = Object.keys(row)
  for (const needle of needles) {
    const k = keys.find((key) => key.toLowerCase().replace(/[^a-z]/g, '').includes(needle))
    if (k != null && String(row[k]).trim() !== '') return row[k]
  }
  return ''
}

export default function CourseWeeklyTimetable({ courseId, canEdit = false }) {
  const confirm = useConfirm()
  const toast = useToast()
  const [slots, setSlots] = useState([emptySlot()])
  const [saving, setSaving] = useState(false)
  const [importing, setImporting] = useState(false)

  useEffect(() => {
    if (!courseId) return
    scheduleService.byCourse(courseId).then(({ data }) => {
      const list = data.data || []
      if (list.length) {
        setSlots(list.map((s) => ({
          day_of_week: Number(s.day_of_week),
          start_time: String(s.start_time).slice(0, 5),
          duration_minutes: s.duration_minutes || 60,
          title: s.title || 'Live Class',
          meeting_url: s.meeting_url || '',
        })))
      }
    })
  }, [courseId])

  const updateSlot = (i, field, value) => {
    setSlots((prev) => prev.map((s, idx) => (idx === i ? { ...s, [field]: value } : s)))
  }

  const handleSave = async (e) => {
    e.preventDefault()
    setSaving(true)
    try {
      await scheduleService.save(courseId, slots)
      toast.success('Weekly timetable saved successfully')
    } catch {
      toast.error('Could not save timetable')
    } finally {
      setSaving(false)
    }
  }

  const removeSlot = async (i) => {
    if (slots.length <= 1) { setSlots([emptySlot()]); return }
    if (!(await confirm({ title: 'Remove slot', message: 'Remove this timetable slot?' }))) return
    setSlots(slots.filter((_, idx) => idx !== i))
  }

  const downloadTemplate = () => {
    const rows = [
      ['Day', 'Start Time', 'Duration (min)', 'Title', 'Meeting URL'],
      ['Monday', '19:30', 90, 'Live Class', 'https://meet.google.com/your-link'],
      ['Wednesday', '19:30', 90, 'Live Class', ''],
      ['Friday', '21:00', 60, 'Doubt Session', ''],
    ]
    const ws = XLSX.utils.aoa_to_sheet(rows)
    const wb = XLSX.utils.book_new()
    XLSX.utils.book_append_sheet(wb, ws, 'Timetable')
    XLSX.writeFile(wb, 'timetable-template.xlsx')
  }

  const downloadCurrent = () => {
    const rows = [['Day', 'Start Time', 'Duration (min)', 'Title', 'Meeting URL']]
    slots.forEach((s) => rows.push([
      DAYS.find((d) => d.value === Number(s.day_of_week))?.label || '',
      s.start_time, s.duration_minutes, s.title, s.meeting_url || '',
    ]))
    const ws = XLSX.utils.aoa_to_sheet(rows)
    const wb = XLSX.utils.book_new()
    XLSX.utils.book_append_sheet(wb, ws, 'Timetable')
    XLSX.writeFile(wb, 'weekly-timetable.xlsx')
  }

  const handleImport = (e) => {
    const file = e.target.files?.[0]
    e.target.value = ''
    if (!file) return
    setImporting(true)
    const reader = new FileReader()
    reader.onload = (evt) => {
      try {
        const wb = XLSX.read(evt.target.result, { type: 'array' })
        const ws = wb.Sheets[wb.SheetNames[0]]
        const rows = XLSX.utils.sheet_to_json(ws, { defval: '' })
        const parsed = rows
          .map((row) => ({
            day_of_week: parseDay(pick(row, 'day')),
            start_time: parseTime(pick(row, 'starttime', 'start', 'time')),
            duration_minutes: parseDuration(pick(row, 'duration', 'minutes', 'mins')),
            title: String(pick(row, 'title', 'class', 'subject') || 'Live Class'),
            meeting_url: String(pick(row, 'meeting', 'url', 'link', 'zoom') || ''),
          }))
          .filter((s) => s.start_time)
        if (!parsed.length) {
          toast.error('No valid rows found. Use columns: Day, Start Time, Duration, Title, Meeting URL.')
          return
        }
        setSlots(parsed)
        toast.success(`Imported ${parsed.length} slot(s). Review and save to apply.`)
      } catch {
        toast.error('Could not read the Excel file')
      } finally {
        setImporting(false)
      }
    }
    reader.readAsArrayBuffer(file)
  }

  const slotStatus = (s) => {
    const now = new Date()
    const today = now.getDay()
    const slotDay = Number(s.day_of_week)
    const [h, m] = String(s.start_time).split(':').map(Number)
    const startMin = (h || 0) * 60 + (m || 0)
    const endMin = startMin + Number(s.duration_minutes || 0)
    const nowMin = now.getHours() * 60 + now.getMinutes()
    if (slotDay < today) return 'completed'
    if (slotDay > today) return 'upcoming'
    if (nowMin >= endMin) return 'completed'
    if (nowMin >= startMin && nowMin < endMin) return 'current'
    return 'upcoming'
  }

  // Order slots Mon→Sun for display
  const orderVal = (d) => (Number(d) === 0 ? 7 : Number(d))
  const sortedSlots = [...slots].sort((a, b) => orderVal(a.day_of_week) - orderVal(b.day_of_week) || String(a.start_time).localeCompare(String(b.start_time)))

  if (!canEdit) {
    return (
      <div className="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-soft">
        <div className="flex items-center justify-between gap-3 border-b border-slate-100 px-4 py-3">
          <h3 className="font-semibold text-navy">Weekly Timetable</h3>
          {slots.length > 0 && (
            <button type="button" onClick={downloadCurrent} className="inline-flex items-center gap-1.5 rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-medium text-navy hover:bg-slate-200">
              <FiDownload size={14} /> Download
            </button>
          )}
        </div>
        <table className="w-full text-left text-sm">
          <thead className="bg-slate-50 text-xs uppercase text-slate-500">
            <tr>
              <th className="px-4 py-3">Day</th>
              <th className="px-4 py-3">Time</th>
              <th className="px-4 py-3">Class</th>
              <th className="px-4 py-3">Duration</th>
              <th className="px-4 py-3">Status</th>
              <th className="px-4 py-3">Link</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-slate-100">
            {sortedSlots.map((s, i) => {
              const status = slotStatus(s)
              return (
                <tr key={i} className={status === 'current' ? 'bg-primary/5' : status === 'completed' ? 'opacity-60' : ''}>
                  <td className="px-4 py-3">{DAYS.find((d) => d.value === Number(s.day_of_week))?.label}</td>
                  <td className="px-4 py-3 font-medium text-navy">{s.start_time}</td>
                  <td className="px-4 py-3">{s.title}</td>
                  <td className="px-4 py-3">{s.duration_minutes} min</td>
                  <td className="px-4 py-3">
                    {status === 'completed' && <span className="inline-flex items-center gap-1 text-xs font-medium text-emerald-600">✓ Completed</span>}
                    {status === 'current' && <span className="inline-flex items-center gap-1 rounded-full bg-primary/10 px-2 py-0.5 text-xs font-semibold text-primary">● Live now</span>}
                    {status === 'upcoming' && <span className="text-xs text-slate-400">Upcoming</span>}
                  </td>
                  <td className="px-4 py-3">
                    {s.meeting_url ? <a href={s.meeting_url} target="_blank" rel="noreferrer" className="text-primary hover:underline">Join</a> : '—'}
                  </td>
                </tr>
              )
            })}
            {slots.length === 0 && (
              <tr><td colSpan={6} className="px-4 py-8 text-center text-slate-400">No schedule uploaded yet</td></tr>
            )}
          </tbody>
        </table>
        <p className="border-t border-slate-100 px-4 py-3 text-xs text-slate-400">
          You will receive a WhatsApp and in-app reminder 10 minutes before each class.
        </p>
      </div>
    )
  }

  return (
    <div>
      <form onSubmit={handleSave} className="rounded-2xl border border-slate-100 bg-white p-6 shadow-soft">
        <h3 className="font-semibold text-navy">Weekly Class Timetable</h3>
        <p className="mt-1 text-sm text-slate-500">
          Set fixed days and times for this course. Teacher and students get WhatsApp + notification 10 minutes before each class.
        </p>

        <div className="mt-4 flex flex-wrap items-center gap-3 rounded-xl border border-dashed border-slate-300 bg-slate-50 p-4">
          <div className="flex-1 min-w-[200px]">
            <p className="text-sm font-medium text-navy">Bulk import from Excel</p>
            <p className="text-xs text-slate-500">
              Upload an .xlsx/.csv file with columns: Day, Start Time, Duration, Title, Meeting URL. This replaces the slots below (save to apply).
            </p>
          </div>
          <button type="button" onClick={downloadTemplate} className="btn-secondary text-sm">
            <FiDownload /> Template
          </button>
          <label className={`btn-primary cursor-pointer text-sm ${importing ? 'pointer-events-none opacity-60' : ''}`}>
            <FiUpload /> {importing ? 'Importing…' : 'Import Excel'}
            <input type="file" accept=".xlsx,.xls,.csv" onChange={handleImport} className="hidden" disabled={importing} />
          </label>
        </div>

        <div className="mt-4 space-y-4">
          {slots.map((slot, i) => (
            <div key={i} className="grid gap-3 rounded-xl bg-slate-50 p-4 sm:grid-cols-2 lg:grid-cols-6">
              <select value={slot.day_of_week} onChange={(e) => updateSlot(i, 'day_of_week', Number(e.target.value))}
                className="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                {DAYS.map((d) => <option key={d.value} value={d.value}>{d.label}</option>)}
              </select>
              <input type="time" required value={slot.start_time} onChange={(e) => updateSlot(i, 'start_time', e.target.value)}
                className="rounded-xl border border-slate-200 px-3 py-2 text-sm" />
              <input type="number" min={15} value={slot.duration_minutes} onChange={(e) => updateSlot(i, 'duration_minutes', e.target.value)}
                placeholder="Minutes" className="rounded-xl border border-slate-200 px-3 py-2 text-sm" />
              <input value={slot.title} onChange={(e) => updateSlot(i, 'title', e.target.value)} placeholder="Class title"
                className="rounded-xl border border-slate-200 px-3 py-2 text-sm lg:col-span-2" />
              <input value={slot.meeting_url} onChange={(e) => updateSlot(i, 'meeting_url', e.target.value)} placeholder="Zoom / Meet URL"
                className="rounded-xl border border-slate-200 px-3 py-2 text-sm lg:col-span-2" />
              {slots.length > 1 && (
                <button type="button" onClick={() => removeSlot(i)} className="text-red-500 hover:text-red-700">
                  <FiTrash2 />
                </button>
              )}
            </div>
          ))}
        </div>

        <div className="mt-4 flex flex-wrap gap-3">
          <button type="button" onClick={() => setSlots([...slots, emptySlot()])} className="btn-secondary text-sm">
            <FiPlus /> Add day
          </button>
          <button type="button" onClick={downloadCurrent} className="btn-secondary text-sm">
            <FiDownload /> Download
          </button>
          <button type="submit" className="btn-primary text-sm" disabled={saving}>{saving ? 'Saving…' : 'Save weekly timetable'}</button>
        </div>
      </form>
    </div>
  )
}
