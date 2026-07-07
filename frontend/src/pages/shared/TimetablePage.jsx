import { useCallback, useEffect, useState } from 'react'
import { FiPlus, FiLayers, FiCalendar } from 'react-icons/fi'
import { useAuth } from '../../context/AuthContext'
import { useConfirm } from '../../context/ConfirmContext'
import { useToast } from '../../context/ToastContext'
import {
  schedulesService, batchesService, adminCoursesService, myCoursesService, adminUsersService,
} from '../../services/api'
import { Button, Card } from '../../components/ui'
import Alert from '../../components/dashboard/Alert'
import DuplicateScheduleModal from '../../components/dashboard/DuplicateScheduleModal'
import ScheduleCalendar from '../../components/schedule/ScheduleCalendar'
import ScheduleFilters from '../../components/schedule/ScheduleFilters'
import ScheduleFormModal from '../../components/schedule/ScheduleFormModal'
import ScheduleDetailModal from '../../components/schedule/ScheduleDetailModal'
import BatchManagerModal from '../../components/schedule/BatchManagerModal'
import { STATUS_CONFIG } from '../../utils/scheduleStatus'

const extractList = (payload) => (Array.isArray(payload) ? payload : payload?.items || [])
const inputCls =
  'w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/15'

export default function TimetablePage({ title = 'Class Timetable' }) {
  const { user } = useAuth()
  const confirm = useConfirm()
  const toast = useToast()
  const role = user?.role
  const canEdit = role === 'admin' || role === 'teacher'

  const [schedules, setSchedules] = useState([])
  const [courses, setCourses] = useState([])
  const [teachers, setTeachers] = useState([])
  const [batches, setBatches] = useState([])
  const [filters, setFilters] = useState({})
  const [range, setRange] = useState({ from: null, to: null })
  const [error, setError] = useState('')

  const [detail, setDetail] = useState(null)
  const [formOpen, setFormOpen] = useState(false)
  const [editing, setEditing] = useState(null)
  const [batchOpen, setBatchOpen] = useState(false)
  const [reschedule, setReschedule] = useState(null)
  const [duplicateFor, setDuplicateFor] = useState(null)

  // Reference data
  useEffect(() => {
    const loader = role === 'admin' ? adminCoursesService.list() : myCoursesService.list()
    loader.then(({ data }) => setCourses(extractList(data.data))).catch(() => {})
    if (role === 'admin') {
      adminUsersService.list('teacher').then(({ data }) => setTeachers(extractList(data.data))).catch(() => {})
    }
  }, [role])

  // Batches for the batch filter follow the selected course.
  useEffect(() => {
    if (!filters.course_id) { setBatches([]); return }
    batchesService.byCourse(filters.course_id).then(({ data }) => setBatches(data.data || [])).catch(() => setBatches([]))
  }, [filters.course_id])

  const load = useCallback(() => {
    const params = { ...filters }
    if (range.from) params.date_from = range.from
    if (range.to) params.date_to = range.to
    schedulesService.list(params)
      .then(({ data }) => { setSchedules(data.data || []); setError('') })
      .catch(() => setError('Could not load the timetable.'))
  }, [filters, range])

  useEffect(() => { load() }, [load])

  // Actions ----------------------------------------------------
  const doAction = async (fn, successMsg) => {
    try {
      await fn()
      load()
      if (successMsg) toast.success(successMsg)
    } catch {
      setError('Action failed.')
      toast.error('Action failed.')
    }
  }

  const onEventDrop = (s, patch, revert) =>
    schedulesService.reschedule(s.id, patch).then(load).catch(() => { revert?.(); setError('Could not reschedule.') })

  const handleUpload = async (s, formData) => {
    await schedulesService.uploadAttachment(s.id, formData)
    const { data } = await schedulesService.get(s.id)
    setDetail(data.data)
    load()
  }

  const openEdit = (s) => { setDetail(null); setEditing(s); setFormOpen(true) }
  const openCreate = () => { setEditing(null); setFormOpen(true) }

  return (
    <div>
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h2 className="font-display text-xl font-bold text-slate-800">{title}</h2>
          <p className="text-sm text-slate-500">
            {canEdit ? 'Create, reschedule and track classes. Drag events to reschedule.' : 'Your class schedule. Click any class for details.'}
          </p>
        </div>
        {canEdit && (
          <div className="flex gap-2">
            <Button variant="outline" onClick={() => setBatchOpen(true)}><FiLayers size={15} /> Manage batches</Button>
            <Button variant="primary" onClick={openCreate}><FiPlus size={15} /> Schedule class</Button>
          </div>
        )}
      </div>

      {error && <div className="mt-4"><Alert type="error">{error}</Alert></div>}

      <Card className="mt-5" padding="p-4">
        <ScheduleFilters
          filters={filters}
          onChange={setFilters}
          courses={courses}
          batches={batches}
          teachers={teachers}
          showTeacher={role !== 'teacher'}
        />
        <div className="mt-3 flex flex-wrap gap-3 border-t border-slate-100 pt-3">
          {Object.entries(STATUS_CONFIG).map(([k, c]) => (
            <span key={k} className="flex items-center gap-1.5 text-xs font-medium text-slate-500">
              <span className="h-2.5 w-2.5 rounded-full" style={{ backgroundColor: c.color }} />
              {c.label}
            </span>
          ))}
        </div>
      </Card>

      <Card className="mt-5" padding="p-4">
        {schedules.length === 0 && !error && (
          <div className="mb-3 flex items-center gap-2 rounded-lg bg-slate-50 px-3 py-2 text-sm text-slate-400">
            <FiCalendar size={15} /> No classes in this range. {canEdit ? 'Use “Schedule class” to add one.' : ''}
          </div>
        )}
        <ScheduleCalendar
          schedules={schedules}
          editable={canEdit}
          onEventClick={setDetail}
          onDatesSet={(from, to) => setRange({ from, to })}
          onEventDrop={onEventDrop}
        />
      </Card>

      {/* Detail */}
      <ScheduleDetailModal
        schedule={detail}
        open={!!detail}
        onClose={() => setDetail(null)}
        canEdit={canEdit}
        onEdit={openEdit}
        onComplete={(s) => doAction(() => schedulesService.complete(s.id), 'Class marked complete').then(() => setDetail(null))}
        onCancel={async (s) => {
          if (!(await confirm({ title: 'Cancel class', message: 'Cancel this class?', confirmText: 'Yes, cancel', tone: 'danger' }))) return
          doAction(() => schedulesService.cancel(s.id), 'Class cancelled').then(() => setDetail(null))
        }}
        onDelete={async (s) => {
          if (!(await confirm({ title: 'Delete class', message: 'Delete this class permanently?', confirmText: 'Yes, Delete', tone: 'danger' }))) return
          doAction(() => schedulesService.remove(s.id), 'Deleted successfully').then(() => setDetail(null))
        }}
        onDuplicate={(s) => setDuplicateFor(s)}
        onReschedule={(s) => { setDetail(null); setReschedule({ id: s.id, class_date: s.class_date, start_time: (s.start_time || '').slice(0, 5), end_time: (s.end_time || '').slice(0, 5) }) }}
        onUpload={handleUpload}
      />

      {/* Create / edit */}
      <ScheduleFormModal
        open={formOpen}
        onClose={() => setFormOpen(false)}
        onSaved={load}
        courses={courses}
        teachers={teachers}
        userRole={role}
        defaultCourseId={filters.course_id}
        initial={editing}
      />

      {/* Batches */}
      <BatchManagerModal
        open={batchOpen}
        onClose={() => setBatchOpen(false)}
        courses={courses}
        defaultCourseId={filters.course_id}
      />

      {/* Reschedule */}
      {reschedule && (
        <RescheduleModal
          data={reschedule}
          onClose={() => setReschedule(null)}
          onSave={(patch) => doAction(() => schedulesService.reschedule(reschedule.id, patch), 'Class rescheduled').then(() => setReschedule(null))}
        />
      )}

      <DuplicateScheduleModal
        open={!!duplicateFor}
        defaultDate={duplicateFor?.class_date?.slice?.(0, 10) || duplicateFor?.class_date}
        onClose={() => setDuplicateFor(null)}
        onConfirm={(date) => doAction(() => schedulesService.duplicate(duplicateFor.id, date), 'Class duplicated').then(() => { setDetail(null); setDuplicateFor(null) })}
      />
    </div>
  )
}

function RescheduleModal({ data, onClose, onSave }) {
  const [d, setD] = useState(data)
  return (
    <div className="fixed inset-0 z-[110] flex items-center justify-center p-4">
      <div className="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onClick={onClose} />
      <div className="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
        <h3 className="font-display text-lg font-bold text-slate-800">Reschedule class</h3>
        <div className="mt-4 space-y-3">
          <div>
            <label className="mb-1 block text-xs font-semibold text-slate-500">New date</label>
            <input type="date" className={inputCls} value={d.class_date} onChange={(e) => setD({ ...d, class_date: e.target.value })} />
          </div>
          <div className="grid grid-cols-2 gap-3">
            <div>
              <label className="mb-1 block text-xs font-semibold text-slate-500">Start</label>
              <input type="time" className={inputCls} value={d.start_time} onChange={(e) => setD({ ...d, start_time: e.target.value })} />
            </div>
            <div>
              <label className="mb-1 block text-xs font-semibold text-slate-500">End</label>
              <input type="time" className={inputCls} value={d.end_time} onChange={(e) => setD({ ...d, end_time: e.target.value })} />
            </div>
          </div>
        </div>
        <div className="mt-5 flex justify-end gap-2">
          <Button variant="secondary" onClick={onClose}>Cancel</Button>
          <Button
            variant="primary"
            onClick={() => onSave({ class_date: d.class_date, start_time: `${d.start_time}:00`, end_time: `${d.end_time}:00` })}
          >Reschedule</Button>
        </div>
      </div>
    </div>
  )
}
