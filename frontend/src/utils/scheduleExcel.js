import * as XLSX from 'xlsx'

const DAY_NAME_TO_NUM = {
  sunday: 0, sun: 0,
  monday: 1, mon: 1,
  tuesday: 2, tue: 2, tues: 2,
  wednesday: 3, wed: 3, weds: 3,
  thursday: 4, thu: 4, thur: 4, thurs: 4,
  friday: 5, fri: 5,
  saturday: 6, sat: 6,
}

export const parseTime = (v) => {
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

export const parseDate = (v) => {
  if (v === '' || v == null) return ''
  if (typeof v === 'number') {
    const parsed = XLSX.SSF.parse_date_code(v)
    if (parsed) {
      return `${parsed.y}-${String(parsed.m).padStart(2, '0')}-${String(parsed.d).padStart(2, '0')}`
    }
  }
  const s = String(v).trim()
  const iso = s.match(/^(\d{4})-(\d{2})-(\d{2})/)
  if (iso) return `${iso[1]}-${iso[2]}-${iso[3]}`
  const dmy = s.match(/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{2,4})/)
  if (dmy) {
    const day = Number(dmy[1])
    const month = Number(dmy[2])
    let year = Number(dmy[3])
    if (year < 100) year += 2000
    return `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`
  }
  const d = new Date(s)
  if (!Number.isNaN(d.getTime())) {
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`
  }
  return ''
}

const pick = (row, ...needles) => {
  const keys = Object.keys(row)
  for (const needle of needles) {
    const k = keys.find((key) => key.toLowerCase().replace(/[^a-z]/g, '').includes(needle))
    if (k != null && String(row[k]).trim() !== '') return row[k]
  }
  return ''
}

export const parseMonthScheduleRows = (rows, monthYear) => {
  return rows
    .map((row) => {
      const class_date = parseDate(pick(row, 'date', 'classdate', 'day'))
      const start_time = parseTime(pick(row, 'starttime', 'start', 'from'))
      let end_time = parseTime(pick(row, 'endtime', 'end', 'to'))
      const duration = pick(row, 'duration', 'minutes', 'mins')
      if (!end_time && start_time && duration) {
        const [h, m] = start_time.split(':').map(Number)
        const endMin = (h * 60 + m) + (parseInt(duration, 10) || 60)
        end_time = `${String(Math.floor(endMin / 60) % 24).padStart(2, '0')}:${String(endMin % 60).padStart(2, '0')}`
      }
      return {
        class_date,
        start_time,
        end_time,
        subject: String(pick(row, 'subject') || '').trim(),
        topic_covered: String(pick(row, 'topic', 'topiccovered') || '').trim(),
        lecture_title: String(pick(row, 'title', 'lecture', 'lecturetitle', 'class') || '').trim(),
        teacher: String(pick(row, 'teacher', 'instructor', 'faculty') || '').trim(),
        meeting_link: String(pick(row, 'meeting', 'url', 'link', 'zoom') || '').trim().replace(/\s+/g, ''),
      }
    })
    .filter((r) => r.class_date && r.start_time && r.end_time && r.lecture_title)
    .filter((r) => !monthYear || r.class_date.startsWith(monthYear))
}

export const readScheduleWorkbook = (buffer) => {
  const wb = XLSX.read(buffer, { type: 'array', cellDates: true })
  const ws = wb.Sheets[wb.SheetNames[0]]
  return XLSX.utils.sheet_to_json(ws, { defval: '' })
}

export const downloadMonthTemplate = () => {
  const rows = [
    ['Date', 'Subject', 'Topic', 'Lecture Title', 'Start Time', 'End Time', 'Teacher', 'Meeting Link'],
    ['2026-07-01', 'Anatomy', 'Upper limb', 'Lecture 1', '19:30', '21:00', 'Dr. Ahmed', 'https://meet.google.com/abc-defg-hij'],
    ['2026-07-03', 'Physiology', 'Cardiac cycle', 'Lecture 2', '19:30', '21:00', 'Dr. Sara', ''],
    ['2026-07-05', 'Biochemistry', 'Enzymes', 'Lecture 3', '20:00', '21:30', '', ''],
  ]
  const ws = XLSX.utils.aoa_to_sheet(rows)
  const wb = XLSX.utils.book_new()
  XLSX.utils.book_append_sheet(wb, ws, 'Schedule')
  XLSX.writeFile(wb, 'monthly-schedule-template.xlsx')
}

export const monthOptions = (past = 6, future = 12) => {
  const opts = []
  const now = new Date()
  for (let i = -past; i <= future; i++) {
    const d = new Date(now.getFullYear(), now.getMonth() + i, 1)
    const value = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`
    const label = d.toLocaleDateString(undefined, { month: 'long', year: 'numeric' })
    opts.push({ value, label })
  }
  return opts
}

export const monthRange = (monthYear) => {
  const [y, m] = monthYear.split('-').map(Number)
  const last = new Date(y, m, 0).getDate()
  return {
    from: `${monthYear}-01`,
    to: `${monthYear}-${String(last).padStart(2, '0')}`,
  }
}

export { pick, DAY_NAME_TO_NUM }
