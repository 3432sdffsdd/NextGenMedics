import { useEffect, useMemo, useState } from 'react'
import { FiAlertTriangle } from 'react-icons/fi'
import { Modal, Button } from '../ui'
import { schedulesService, batchesService } from '../../services/api'

const inputCls =
  'w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/15'
const labelCls = 'mb-1 block text-xs font-semibold text-slate-500'

const DOW = [
  { v: 1, l: 'Mon' }, { v: 2, l: 'Tue' }, { v: 3, l: 'Wed' }, { v: 4, l: 'Thu' },
  { v: 5, l: 'Fri' }, { v: 6, l: 'Sat' }, { v: 0, l: 'Sun' },
]

const today = () => new Date().toISOString().slice(0, 10)

function LabeledField({ label, children }) {
  return (
    <div>
      <label className={labelCls}>{label}</label>
      {children}
    </div>
  )
}

export default function ScheduleFormModal({
  open, onClose, onSaved, courses = [], teachers = [], userRole, defaultCourseId,
  initial = null,
}) {
  const isEdit = !!initial
  const [form, setForm] = useState({})
  const [batches, setBatches] = useState([])
  const [mode, setMode] = useState('single')
  const [saving, setSaving] = useState(false)
  const [error, setError] = useState('')
  const [conflicts, setConflicts] = useState(null)

  const set = (key) => (e) => setForm((f) => ({ ...f, [key]: e.target.value }))

  useEffect(() => {
    if (!open) return
    setError(''); setConflicts(null); setSaving(false)
    if (initial) {
      setMode('single')
      setForm({
        course_id: initial.course_id, batch_id: initial.batch_id || '',
        teacher_id: initial.teacher_id || '', subject: initial.subject || '',
        lecture_title: initial.lecture_title || '', lecture_number: initial.lecture_number || '',
        topic_covered: initial.topic_covered || '', description: initial.description || '',
        class_date: initial.class_date, start_time: (initial.start_time || '').slice(0, 5),
        end_time: (initial.end_time || '').slice(0, 5), meeting_link: initial.meeting_link || '',
        remarks: initial.remarks || '',
      })
    } else {
      setMode('single')
      setForm({
        course_id: defaultCourseId || '', batch_id: '', teacher_id: '', subject: '',
        lecture_title: '', lecture_number: '', topic_covered: '', description: '',
        class_date: today(), start_time: '09:00', end_time: '10:00',
        meeting_link: '', remarks: '', start_date: today(), until_date: today(),
        interval: 1, days_of_week: [], day_of_month: new Date().getDate(),
      })
    }
  }, [open, initial, defaultCourseId])

  // Load batches whenever the selected course changes.
  useEffect(() => {
    if (!form.course_id) { setBatches([]); return }
    batchesService.byCourse(form.course_id)
      .then(({ data }) => setBatches(data.data || []))
      .catch(() => setBatches([]))
  }, [form.course_id])

  const toggleDow = (v) =>
    setForm((f) => {
      const cur = f.days_of_week || []
      return { ...f, days_of_week: cur.includes(v) ? cur.filter((d) => d !== v) : [...cur, v] }
    })

  const canSave = form.course_id && form.lecture_title && form.start_time && form.end_time &&
    (isEdit || mode === 'single' ? form.class_date : form.start_date)

  const buildPayload = (force = false) => {
    const base = {
      course_id: Number(form.course_id),
      batch_id: form.batch_id ? Number(form.batch_id) : null,
      teacher_id: form.teacher_id ? Number(form.teacher_id) : undefined,
      subject: form.subject || null,
      lecture_title: form.lecture_title,
      lecture_number: form.lecture_number || null,
      topic_covered: form.topic_covered || null,
      description: form.description || null,
      start_time: `${form.start_time}:00`,
      end_time: `${form.end_time}:00`,
      meeting_link: form.meeting_link || null,
      remarks: form.remarks || null,
      force,
    }
    if (isEdit || mode === 'single') {
      return { ...base, class_date: form.class_date }
    }
    return {
      ...base,
      mode,
      start_date: form.start_date,
      until_date: form.until_date,
      interval: Number(form.interval) || 1,
      days_of_week: mode === 'weekly' ? (form.days_of_week || []) : undefined,
      day_of_month: mode === 'monthly' ? Number(form.day_of_month) : undefined,
    }
  }

  const submit = async (force = false) => {
    setSaving(true); setError(''); setConflicts(null)
    try {
      const payload = buildPayload(force)
      if (isEdit) {
        await schedulesService.update(initial.id, payload)
      } else if (mode === 'single') {
        await schedulesService.create(payload)
      } else {
        await schedulesService.bulk(payload)
      }
      onSaved?.()
      onClose?.()
    } catch (err) {
      if (err.response?.status === 409) {
        setConflicts(err.response.data?.errors?.conflicts || [])
      } else {
        setError(err.response?.data?.message || 'Could not save schedule.')
      }
    } finally {
      setSaving(false)
    }
  }

  const showTeacher = userRole === 'admin' && teachers.length > 0
  const recurring = !isEdit && mode !== 'single'

  const footer = (
    <>
      <Button variant="secondary" onClick={onClose} disabled={saving}>Cancel</Button>
      {conflicts ? (
        <Button variant="primary" className="!bg-amber-500 hover:!bg-amber-600" onClick={() => submit(true)} loading={saving}>Schedule anyway</Button>
      ) : (
        <Button variant="primary" onClick={() => submit(false)} loading={saving} disabled={!canSave}>
          {isEdit ? 'Save changes' : recurring ? 'Generate schedule' : 'Create class'}
        </Button>
      )}
    </>
  )

  const modeTabs = useMemo(() => ['single', 'daily', 'weekly', 'monthly'], [])

  return (
    <Modal
      open={open}
      onClose={onClose}
      size="xl"
      title={isEdit ? 'Edit class' : 'Schedule a class'}
      subtitle={isEdit ? initial?.course_title : 'Create one class or a recurring series'}
      footer={footer}
    >
      {!isEdit && (
        <div className="mb-4 inline-flex rounded-xl bg-slate-100 p-1">
          {modeTabs.map((m) => (
            <button
              key={m}
              type="button"
              onClick={() => setMode(m)}
              className={`rounded-lg px-3 py-1.5 text-sm font-semibold capitalize transition ${
                mode === m ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-700'
              }`}
            >
              {m}
            </button>
          ))}
        </div>
      )}

      <div className="grid gap-4 sm:grid-cols-2">
        <LabeledField label="Course *">
          <select className={inputCls} value={form.course_id || ''} onChange={set('course_id')} disabled={isEdit}>
            <option value="">Select course</option>
            {courses.map((c) => <option key={c.id} value={c.id}>{c.title}</option>)}
          </select>
        </LabeledField>

        <LabeledField label="Batch">
          <select className={inputCls} value={form.batch_id || ''} onChange={set('batch_id')}>
            <option value="">All enrolled students</option>
            {batches.map((b) => <option key={b.id} value={b.id}>{b.name}</option>)}
          </select>
        </LabeledField>

        {showTeacher && (
          <LabeledField label="Teacher (conducting)">
            <select className={inputCls} value={form.teacher_id || ''} onChange={set('teacher_id')}>
              <option value="">Select teacher</option>
              {teachers.map((t) => (
                <option key={t.id} value={t.id}>{t.first_name} {t.last_name}</option>
              ))}
            </select>
          </LabeledField>
        )}

        <LabeledField label="Subject">
          <input className={inputCls} value={form.subject || ''} onChange={set('subject')} placeholder="e.g. Anatomy" />
        </LabeledField>

        <LabeledField label="Lecture title *">
          <input className={inputCls} value={form.lecture_title || ''} onChange={set('lecture_title')} placeholder="e.g. Upper limb bones" />
        </LabeledField>

        <LabeledField label="Lecture number">
          <input type="number" min="1" className={inputCls} value={form.lecture_number || ''} onChange={set('lecture_number')} placeholder="e.g. 5" />
        </LabeledField>

        <LabeledField label="Topic covered">
          <input className={inputCls} value={form.topic_covered || ''} onChange={set('topic_covered')} />
        </LabeledField>

        <LabeledField label="Meeting link">
          <input className={inputCls} value={form.meeting_link || ''} onChange={set('meeting_link')} placeholder="https://…" />
        </LabeledField>
      </div>

      {/* Timing */}
      <div className="mt-4 grid gap-4 sm:grid-cols-3">
        {(isEdit || mode === 'single') ? (
          <LabeledField label="Date *">
            <input type="date" className={inputCls} value={form.class_date || ''} onChange={set('class_date')} />
          </LabeledField>
        ) : (
          <LabeledField label="Start date *">
            <input type="date" className={inputCls} value={form.start_date || ''} onChange={set('start_date')} />
          </LabeledField>
        )}
        <LabeledField label="Start time *">
          <input type="time" className={inputCls} value={form.start_time || ''} onChange={set('start_time')} />
        </LabeledField>
        <LabeledField label="End time *">
          <input type="time" className={inputCls} value={form.end_time || ''} onChange={set('end_time')} />
        </LabeledField>
      </div>

      {/* Recurrence controls */}
      {recurring && (
        <div className="mt-4 rounded-xl border border-slate-100 bg-slate-50 p-4">
          <p className="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-400">Recurrence</p>
          <div className="grid gap-4 sm:grid-cols-3">
            <LabeledField label="Repeat until *">
              <input type="date" className={inputCls} value={form.until_date || ''} onChange={set('until_date')} />
            </LabeledField>
            <LabeledField label={mode === 'weekly' ? 'Every N weeks' : mode === 'monthly' ? 'Every N months' : 'Every N days'}>
              <input type="number" min="1" className={inputCls} value={form.interval || 1} onChange={set('interval')} />
            </LabeledField>
            {mode === 'monthly' && (
              <LabeledField label="Day of month">
                <input type="number" min="1" max="31" className={inputCls} value={form.day_of_month || 1} onChange={set('day_of_month')} />
              </LabeledField>
            )}
          </div>
          {mode === 'weekly' && (
            <div className="mt-3">
              <label className={labelCls}>On days</label>
              <div className="flex flex-wrap gap-1.5">
                {DOW.map((d) => (
                  <button
                    key={d.v}
                    type="button"
                    onClick={() => toggleDow(d.v)}
                    className={`h-9 w-11 rounded-lg text-sm font-semibold transition ${
                      (form.days_of_week || []).includes(d.v)
                        ? 'bg-blue-600 text-white'
                        : 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-100'
                    }`}
                  >
                    {d.l}
                  </button>
                ))}
              </div>
            </div>
          )}
        </div>
      )}

      <div className="mt-4">
        <label className={labelCls}>Description</label>
        <textarea rows={2} className={inputCls} value={form.description || ''} onChange={set('description')} />
      </div>

      {conflicts && (
        <div className="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-3">
          <p className="flex items-center gap-2 text-sm font-semibold text-amber-700">
            <FiAlertTriangle size={16} /> Scheduling conflict detected
          </p>
          <ul className="mt-1 list-disc pl-6 text-sm text-amber-700">
            {conflicts.map((c) => (
              <li key={c.id}>{c.lecture_title} on {c.class_date} at {String(c.start_time).slice(0, 5)}{c.teacher_name ? ` — ${c.teacher_name}` : ''}</li>
            ))}
          </ul>
          <p className="mt-1 text-xs text-amber-600">You can adjust the time, or schedule anyway.</p>
        </div>
      )}

      {error && <p className="mt-3 text-sm font-medium text-red-500">{error}</p>}
    </Modal>
  )
}
