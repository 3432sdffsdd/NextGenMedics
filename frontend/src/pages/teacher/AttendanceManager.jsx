import { useCallback, useEffect, useState } from 'react'
import { FiCalendar, FiTrash2, FiEdit2, FiCheck, FiClipboard } from 'react-icons/fi'
import { attendanceService } from '../../services/api'
import { useToast } from '../../context/ToastContext'
import { useConfirm } from '../../context/ConfirmContext'
import { Button, EmptyState } from '../../components/ui'
import { formatDate } from '../../utils/files'

const STATUS = [
  { key: 'present', label: 'Present', cls: 'bg-emerald-500' },
  { key: 'absent', label: 'Absent', cls: 'bg-red-500' },
  { key: 'late', label: 'Late', cls: 'bg-amber-500' },
  { key: 'leave', label: 'Leave', cls: 'bg-slate-400' },
]

export default function AttendanceManager({ courseId, students }) {
  const toast = useToast()
  const confirm = useConfirm()
  const today = new Date().toISOString().slice(0, 10)

  const [date, setDate] = useState(today)
  const [marks, setMarks] = useState({})
  const [sessionId, setSessionId] = useState(null)
  const [loaded, setLoaded] = useState(false)
  const [loading, setLoading] = useState(false)
  const [saving, setSaving] = useState(false)
  const [history, setHistory] = useState([])

  const loadHistory = useCallback(() => {
    attendanceService.byCourse(courseId).then(({ data }) => setHistory(data.data || [])).catch(() => {})
  }, [courseId])

  useEffect(() => { loadHistory() }, [loadHistory])

  const load = async (targetDate = date) => {
    setLoading(true)
    setDate(targetDate)
    try {
      const { data } = await attendanceService.sessionByDate(courseId, targetDate)
      const existing = data.data.session
      const records = data.data.records || []
      const map = {}
      if (records.length) {
        records.forEach((r) => { map[r.student_id] = r.status })
        students.forEach((s) => { if (!map[s.id]) map[s.id] = 'present' })
      } else {
        students.forEach((s) => { map[s.id] = 'present' })
      }
      setMarks(map)
      setSessionId(existing ? existing.id : null)
      setLoaded(true)
      if (existing) toast.info('Loaded existing attendance — you can edit and re-save')
    } catch { toast.error('Could not load attendance') }
    finally { setLoading(false) }
  }

  const setAll = (status) => {
    const map = {}
    students.forEach((s) => { map[s.id] = status })
    setMarks(map)
  }

  const save = async () => {
    if (saving) return
    setSaving(true)
    try {
      let sid = sessionId
      if (!sid) {
        const { data } = await attendanceService.createSession({ course_id: courseId, session_date: date })
        sid = data.data.id
        setSessionId(sid)
      }
      const records = students.map((s) => ({ session_id: sid, student_id: s.id, status: marks[s.id] || 'present' }))
      await attendanceService.mark(records)
      toast.success('Attendance saved')
      loadHistory()
    } catch { toast.error('Could not save attendance') }
    finally { setSaving(false) }
  }

  const removeSession = async (s) => {
    if (!(await confirm({ title: 'Delete attendance', message: `Delete attendance for ${formatDate(s.session_date)}? This cannot be undone.` }))) return
    try {
      await attendanceService.deleteSession(s.id)
      toast.success('Deleted successfully')
      if (sessionId === s.id) { setLoaded(false); setSessionId(null) }
      loadHistory()
    } catch { toast.error('Could not delete attendance') }
  }

  const summary = STATUS.map((st) => ({ ...st, count: Object.values(marks).filter((v) => v === st.key).length }))

  return (
    <div className="grid gap-6 lg:grid-cols-[1fr,320px]">
      <div>
        <div className="rounded-2xl border border-slate-100 bg-white p-5 shadow-soft">
          <div className="flex flex-wrap items-end gap-3">
            <div>
              <label className="text-xs font-medium text-slate-500">Class date</label>
              <input type="date" value={date} onChange={(e) => setDate(e.target.value)} max={today}
                className="mt-1 block rounded-xl border border-slate-200 px-3 py-2 text-sm" />
            </div>
            <Button icon={FiCalendar} onClick={() => load()} loading={loading}>Load attendance</Button>
          </div>

          {students.length === 0 && <p className="mt-4 text-sm text-slate-400">No students enrolled in this course yet.</p>}

          {loaded && students.length > 0 && (
            <>
              <div className="mt-5 flex flex-wrap items-center gap-2">
                <span className="text-xs text-slate-400">Quick set:</span>
                {STATUS.map((st) => (
                  <button key={st.key} type="button" onClick={() => setAll(st.key)}
                    className="rounded-full border border-slate-200 px-3 py-1 text-xs font-medium text-slate-600 hover:bg-slate-50">
                    All {st.label}
                  </button>
                ))}
              </div>

              <div className="mt-4 divide-y divide-slate-100">
                {students.map((s) => (
                  <div key={s.id} className="flex flex-wrap items-center justify-between gap-3 py-2.5">
                    <span className="text-sm font-medium text-navy">{s.first_name} {s.last_name}</span>
                    <div className="flex gap-1.5">
                      {STATUS.map((st) => {
                        const active = marks[s.id] === st.key
                        return (
                          <button key={st.key} type="button" onClick={() => setMarks((m) => ({ ...m, [s.id]: st.key }))}
                            className={`rounded-lg px-3 py-1.5 text-xs font-semibold transition ${active ? `${st.cls} text-white` : 'bg-slate-100 text-slate-500 hover:bg-slate-200'}`}>
                            {st.label}
                          </button>
                        )
                      })}
                    </div>
                  </div>
                ))}
              </div>

              <div className="mt-5 flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-4">
                <div className="flex flex-wrap gap-3 text-xs text-slate-500">
                  {summary.map((st) => (
                    <span key={st.key} className="inline-flex items-center gap-1.5">
                      <span className={`h-2.5 w-2.5 rounded-full ${st.cls}`} /> {st.label}: <strong>{st.count}</strong>
                    </span>
                  ))}
                </div>
                <Button icon={FiCheck} onClick={save} loading={saving} variant="success">Save attendance</Button>
              </div>
            </>
          )}

          {!loaded && students.length > 0 && (
            <p className="mt-4 text-sm text-slate-400">Pick a date and click <strong>Load attendance</strong> to begin.</p>
          )}
        </div>
      </div>

      <div>
        <h4 className="mb-3 text-sm font-semibold text-navy">Attendance History</h4>
        {history.length === 0 ? (
          <EmptyState icon={FiClipboard} title="No attendance records" description="Saved sessions will appear here." className="py-8" />
        ) : (
          <div className="space-y-2">
            {history.map((h) => (
              <div key={h.id} className="flex items-center justify-between rounded-xl border border-slate-100 bg-white p-3 shadow-soft">
                <div>
                  <p className="text-sm font-medium text-navy">{formatDate(h.session_date)}</p>
                  <p className="text-xs text-slate-400">{h.present_count}/{h.total_marked} present</p>
                </div>
                <div className="flex items-center gap-1">
                  <button type="button" onClick={() => load(h.session_date.slice(0, 10))} className="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-primary" aria-label="Edit"><FiEdit2 size={15} /></button>
                  <button type="button" onClick={() => removeSession(h)} className="rounded-lg p-1.5 text-slate-400 hover:bg-red-50 hover:text-red-500" aria-label="Delete"><FiTrash2 size={15} /></button>
                </div>
              </div>
            ))}
          </div>
        )}
      </div>
    </div>
  )
}
