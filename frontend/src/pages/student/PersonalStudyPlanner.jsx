import { useEffect, useMemo, useState } from 'react'
import {
  FiCheck, FiCalendar, FiTarget, FiBookOpen, FiRefreshCw, FiDownload,
  FiTrash2, FiClock, FiPlus, FiChevronDown, FiChevronRight, FiCopy, FiArchive, FiPlay,
} from 'react-icons/fi'
import { personalStudyPlannerService } from '../../services/api'
import { useToast } from '../../context/ToastContext'
import { Modal } from '../../components/ui'

const WEEKDAYS = [
  { id: 'monday', label: 'Mon' }, { id: 'tuesday', label: 'Tue' },
  { id: 'wednesday', label: 'Wed' }, { id: 'thursday', label: 'Thu' },
  { id: 'friday', label: 'Fri' }, { id: 'saturday', label: 'Sat' },
  { id: 'sunday', label: 'Sun' },
]

const DURATIONS = [
  { value: 'today', label: 'Today', days: 1 },
  { value: '2', label: '2 Days', days: 2 },
  { value: '3', label: '3 Days', days: 3 },
  { value: '5', label: '5 Days', days: 5 },
  { value: '7', label: '7 Days', days: 7 },
  { value: '10', label: '10 Days', days: 10 },
  { value: '15', label: '15 Days', days: 15 },
  { value: '30', label: '30 Days', days: 30 },
  { value: 'custom', label: 'Custom', days: null },
]

function Ring({ value = 0, label }) {
  const v = Math.max(0, Math.min(100, Number(value) || 0))
  return (
    <div className="flex flex-col items-center">
      <div className="grid h-20 w-20 place-items-center rounded-full" style={{ background: `conic-gradient(#0d9488 ${v * 3.6}deg, #e2e8f0 0deg)` }}>
        <div className="grid h-14 w-14 place-items-center rounded-full bg-white text-sm font-bold text-navy">{Math.round(v)}%</div>
      </div>
      <p className="mt-1 text-xs text-slate-500">{label}</p>
    </div>
  )
}

function TaskRow({ task, onStatus, onMove }) {
  return (
    <li className="flex flex-wrap items-center gap-2 rounded-xl border border-slate-100 px-3 py-2 text-sm">
      <button type="button" onClick={() => onStatus(task, task.status === 'completed' ? 'pending' : 'completed')}
        className={`flex h-6 w-6 items-center justify-center rounded-full border-2 ${task.status === 'completed' ? 'border-emerald-500 bg-emerald-500 text-white' : 'border-slate-300'}`}>
        {task.status === 'completed' && <FiCheck size={14} />}
      </button>
      <span className={`flex-1 ${task.status === 'completed' ? 'text-slate-400 line-through' : 'text-navy'}`}>{task.title}</span>
      <span className="rounded bg-slate-100 px-1.5 py-0.5 text-[10px] uppercase text-slate-500">{task.source}</span>
      <button type="button" className="text-xs text-amber-600" onClick={() => onStatus(task, 'skipped')}>Skip</button>
      <button type="button" className="text-xs text-teal-600" onClick={() => onMove(task, 'tomorrow')}>Tomorrow</button>
      <button type="button" className="text-xs text-slate-500" onClick={() => onMove(task, 'end')}>End</button>
    </li>
  )
}

function StatusBadge({ status }) {
  const styles = {
    active: 'bg-teal-100 text-teal-800',
    completed: 'bg-emerald-100 text-emerald-800',
    archived: 'bg-slate-100 text-slate-600',
  }
  return (
    <span className={`rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase ${styles[status] || 'bg-slate-100 text-slate-600'}`}>
      {status}
    </span>
  )
}

function PlanActions({ plan, onView, onResume, onDuplicate, onArchive, onDelete, compact = false }) {
  if (!plan) return null
  return (
    <div className={`flex flex-wrap gap-2 ${compact ? '' : 'mt-3'}`}>
      <button type="button" className="btn-secondary text-xs" onClick={() => onView(plan.id)}>View</button>
      {plan.status !== 'active' && (
        <button type="button" className="btn-secondary text-xs" onClick={() => onResume(plan.id)}>
          <FiPlay className="mr-1 inline" /> Make current
        </button>
      )}
      <button type="button" className="btn-secondary text-xs" onClick={() => onDuplicate(plan.id)}>
        <FiCopy className="mr-1 inline" /> Duplicate
      </button>
      {plan.status !== 'archived' && (
        <button type="button" className="btn-secondary text-xs" onClick={() => onArchive(plan.id)}>
          <FiArchive className="mr-1 inline" /> Archive
        </button>
      )}
      <button type="button" className="btn-secondary text-xs text-rose-600" onClick={() => onDelete(plan.id, plan.plan_name)}>
        <FiTrash2 className="mr-1 inline" /> Delete
      </button>
    </div>
  )
}

export default function PersonalStudyPlanner() {
  const toast = useToast()
  const [loading, setLoading] = useState(true)
  const [saving, setSaving] = useState(false)
  const [data, setData] = useState(null)
  const [mainTab, setMainTab] = useState('dashboard')
  const [createMode, setCreateMode] = useState(null) // lms | manual | mixed
  const [duration, setDuration] = useState('5')
  const [customDays, setCustomDays] = useState(4)
  const [planName, setPlanName] = useState('')
  const [selection, setSelection] = useState([])
  const [expanded, setExpanded] = useState({})
  const [manualDays, setManualDays] = useState([{ day_number: 1, title: '' }])
  const [setupForm, setSetupForm] = useState({
    exam_date: '',
    hours_per_day: 3,
    preferred_days: ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'],
  })
  const [month, setMonth] = useState(() => new Date().toISOString().slice(0, 7))
  const [calDays, setCalDays] = useState([])
  const [dayModal, setDayModal] = useState(null)
  const [viewModal, setViewModal] = useState(null)
  const [planFilter, setPlanFilter] = useState('all') // all | active | completed | archived

  const applyDash = (d) => {
    setData(d)
    if (d?.settings) {
      setSetupForm({
        exam_date: d.settings.exam_date?.slice?.(0, 10) || d.settings.exam_date || '',
        hours_per_day: parseFloat(d.settings.hours_per_day) || 3,
        preferred_days: d.settings.preferred_days || setupForm.preferred_days,
      })
    }
  }

  const plans = data?.history || []
  const filteredPlans = useMemo(() => {
    if (planFilter === 'all') return plans
    return plans.filter((p) => p.status === planFilter)
  }, [plans, planFilter])

  const viewPlan = async (id) => {
    try {
      const { data: res } = await personalStudyPlannerService.viewPlan(id)
      setViewModal(res.data)
    } catch (err) {
      toast.error(err.response?.data?.message || 'Could not open plan')
    }
  }

  const resumePlan = async (id) => {
    try {
      const { data: res } = await personalStudyPlannerService.resume(id)
      applyDash(res.data)
      setMainTab('dashboard')
      toast.success('Plan resumed as current plan')
    } catch (err) {
      toast.error(err.response?.data?.message || 'Could not resume plan')
    }
  }

  const duplicatePlan = async (id) => {
    try {
      const { data: res } = await personalStudyPlannerService.duplicate(id)
      applyDash(res.data)
      setMainTab('dashboard')
      toast.success('Plan duplicated and set as current')
    } catch (err) {
      toast.error(err.response?.data?.message || 'Could not duplicate plan')
    }
  }

  const archivePlan = async (id) => {
    try {
      const { data: res } = await personalStudyPlannerService.archive(id)
      applyDash(res.data)
      toast.success('Plan archived')
    } catch (err) {
      toast.error(err.response?.data?.message || 'Could not archive plan')
    }
  }

  const deletePlan = async (id, name) => {
    if (!window.confirm(`Delete "${name || 'this plan'}" permanently? This cannot be undone.`)) return
    try {
      const { data: res } = await personalStudyPlannerService.delete(id)
      applyDash(res.data)
      setViewModal(null)
      toast.success('Plan deleted')
    } catch (err) {
      toast.error(err.response?.data?.message || 'Could not delete plan')
    }
  }

  const planActionProps = {
    onView: viewPlan,
    onResume: resumePlan,
    onDuplicate: duplicatePlan,
    onArchive: archivePlan,
    onDelete: deletePlan,
  }

  const load = async () => {
    setLoading(true)
    try {
      const { data: res } = await personalStudyPlannerService.bootstrap()
      applyDash(res.data)
    } catch {
      setData(null)
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => { load() }, [])

  useEffect(() => {
    if (!data?.active_plan || data.setup_required) return
    personalStudyPlannerService.calendar(month)
      .then(({ data: res }) => setCalDays(res.data?.days || []))
      .catch(() => setCalDays([]))
  }, [data?.active_plan?.id, month, data?.setup_required])

  const catalog = data?.catalog || { subjects: [] }
  const durationDays = duration === 'custom' ? customDays : (DURATIONS.find((d) => d.value === duration)?.days || 7)

  const toggleSelect = (item) => {
    const key = `${item.type}:${item.ref_id}:${item.course_id || 0}`
    setSelection((prev) => {
      const exists = prev.some((p) => `${p.type}:${p.ref_id}:${p.course_id || 0}` === key)
      if (exists) return prev.filter((p) => `${p.type}:${p.ref_id}:${p.course_id || 0}` !== key)
      return [...prev, item]
    })
  }

  const isSelected = (type, refId, courseId) =>
    selection.some((p) => p.type === type && p.ref_id === refId && (p.course_id || 0) === (courseId || 0))

  const saveSetup = async () => {
    if (!setupForm.exam_date) {
      toast.error('Exam date is required')
      return
    }
    setSaving(true)
    try {
      const { data: res } = await personalStudyPlannerService.setup(setupForm)
      applyDash(res.data)
      toast.success('Settings saved')
    } catch (err) {
      toast.error(err.response?.data?.message || err.message)
    } finally {
      setSaving(false)
    }
  }

  const createPlan = async () => {
    setSaving(true)
    try {
      const payload = {
        plan_mode: createMode,
        plan_name: planName || undefined,
        duration_days: durationDays,
        selection: createMode === 'manual' ? [] : selection,
        manual_tasks: (createMode === 'lms' ? [] : manualDays.filter((m) => m.title.trim())).map((m) => ({
          day_number: Number(m.day_number) || 1,
          title: m.title.trim(),
          description: m.description || '',
        })),
      }
      const { data: res } = await personalStudyPlannerService.createPlan(payload)
      applyDash(res.data)
      setCreateMode(null)
      setSelection([])
      setManualDays([{ day_number: 1, title: '' }])
      setPlanName('')
      setMainTab('dashboard')
      toast.success('Study plan created')
    } catch (err) {
      toast.error(err.response?.data?.message || err.message)
    } finally {
      setSaving(false)
    }
  }

  const setTask = async (task, status) => {
    const { data: res } = await personalStudyPlannerService.setTask(task.id, status)
    setDayModal(res.data)
    if (viewModal?.plan?.id) {
      try {
        const { data: refreshed } = await personalStudyPlannerService.viewPlan(viewModal.plan.id)
        setViewModal(refreshed.data)
      } catch { /* keep current view */ }
    }
    load()
  }

  const moveTask = async (task, action) => {
    await personalStudyPlannerService.moveTask(task.id, action)
    toast.success(action === 'tomorrow' ? 'Moved to next study day' : action === 'end' ? 'Moved to end of plan' : 'Kept pending')
    load()
    if (dayModal) {
      const { data: res } = await personalStudyPlannerService.day(dayModal.date)
      setDayModal(res.data)
    }
    if (viewModal?.plan?.id) {
      try {
        const { data: refreshed } = await personalStudyPlannerService.viewPlan(viewModal.plan.id)
        setViewModal(refreshed.data)
      } catch { /* ignore */ }
    }
  }

  const openDay = async (date) => {
    const { data: res } = await personalStudyPlannerService.day(date)
    setDayModal(res.data)
  }

  const exportPlan = async () => {
    const { data: res } = await personalStudyPlannerService.export()
    const blob = new Blob([JSON.stringify(res.data, null, 2)], { type: 'application/json' })
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = 'personal-study-plan.json'
    a.click()
    URL.revokeObjectURL(url)
  }

  const printPlan = async () => {
    const { data: res } = await personalStudyPlannerService.export()
    const cal = res.data?.calendar || []
    const w = window.open('', '_blank')
    if (!w) return
    const rows = cal.map((d) => `<tr><td>Day ${d.day_number}</td><td>${d.plan_date}</td><td>${(d.tasks || []).map((t) => t.title).join('; ')}</td></tr>`).join('')
    w.document.write(`<!DOCTYPE html><html><head><title>${res.data?.plan?.plan_name || 'Plan'}</title>
      <style>body{font-family:Segoe UI,sans-serif;padding:24px}table{width:100%;border-collapse:collapse}td,th{border:1px solid #ddd;padding:8px;font-size:12px}</style></head>
      <body><h1>${res.data?.plan?.plan_name || 'Study Plan'}</h1>
      <table><thead><tr><th>Day</th><th>Date</th><th>Tasks</th></tr></thead><tbody>${rows}</tbody></table>
      <script>window.onload=()=>window.print()</script></body></html>`)
    w.document.close()
  }

  const calGrid = useMemo(() => {
    const [y, m] = month.split('-').map(Number)
    const startPad = new Date(y, m - 1, 1).getDay()
    const dim = new Date(y, m, 0).getDate()
    const map = Object.fromEntries(calDays.map((d) => [d.plan_date, d]))
    const cells = []
    for (let i = 0; i < startPad; i++) cells.push(null)
    for (let d = 1; d <= dim; d++) {
      const date = `${month}-${String(d).padStart(2, '0')}`
      cells.push({ date, day: map[date], n: d })
    }
    return cells
  }, [month, calDays])

  const renderContentPicker = () => (
    <div className="space-y-3">
      {!catalog.subjects?.length && <p className="text-sm text-slate-500">No enrolled courses found.</p>}
      {catalog.subjects?.map((sub) => (
        <div key={sub.course_id} className="rounded-xl border border-slate-100 p-4">
          <div className="flex flex-wrap items-center justify-between gap-2">
            <button type="button" className="flex items-center gap-2 font-bold text-navy"
              onClick={() => setExpanded((e) => ({ ...e, [sub.course_id]: !e[sub.course_id] }))}>
              {expanded[sub.course_id] ? <FiChevronDown /> : <FiChevronRight />} {sub.title}
            </button>
            <button type="button" className="text-xs font-semibold text-teal-600"
              onClick={() => toggleSelect({ type: 'subject', ref_id: sub.course_id, course_id: sub.course_id, title: sub.title })}>
              {isSelected('subject', sub.course_id, sub.course_id) ? '✓ Entire subject' : 'Select entire subject'}
            </button>
          </div>
          <p className="mt-1 text-xs text-slate-400">
            {sub.totals.lectures} lectures · {sub.totals.quizzes} quizzes · {sub.totals.notes} notes · {sub.totals.flashcards} flashcards
          </p>
          {expanded[sub.course_id] && (
            <div className="mt-3 space-y-2 border-t border-slate-50 pt-3">
              <p className="text-xs font-semibold uppercase text-slate-400">Lectures</p>
              {sub.lectures.map((lec) => (
                <div key={lec.id} className="rounded-lg bg-slate-50 px-3 py-2 text-sm">
                  <label className="flex items-center gap-2 font-medium text-navy">
                    <input type="checkbox" checked={isSelected('lecture', lec.id, sub.course_id)}
                      onChange={() => toggleSelect({ type: 'lecture', ref_id: lec.id, course_id: sub.course_id, title: lec.title })} />
                    {lec.title}
                  </label>
                  <div className="ml-6 mt-1 flex flex-wrap gap-2 text-xs">
                    {lec.videos.map((v) => (
                      <label key={v.id} className="inline-flex items-center gap-1 rounded bg-white px-2 py-0.5">
                        <input type="checkbox" checked={isSelected('video', v.id, sub.course_id)}
                          onChange={() => toggleSelect({ type: 'video', ref_id: v.id, course_id: sub.course_id, lecture_id: lec.id, title: v.title })} />
                        Video
                      </label>
                    ))}
                    {lec.notes.map((n) => (
                      <label key={n.id} className="inline-flex items-center gap-1 rounded bg-white px-2 py-0.5">
                        <input type="checkbox" checked={isSelected('note', n.id, sub.course_id)}
                          onChange={() => toggleSelect({ type: 'note', ref_id: n.id, course_id: sub.course_id, lecture_id: lec.id, title: n.title })} />
                        Notes
                      </label>
                    ))}
                    {lec.flashcard_count > 0 && (
                      <label className="inline-flex items-center gap-1 rounded bg-white px-2 py-0.5">
                        <input type="checkbox" checked={isSelected('flashcard_set', lec.id, sub.course_id)}
                          onChange={() => toggleSelect({ type: 'flashcard_set', ref_id: lec.id, course_id: sub.course_id, title: lec.title })} />
                        Flashcards ({lec.flashcard_count})
                      </label>
                    )}
                    {lec.has_revision && (
                      <label className="inline-flex items-center gap-1 rounded bg-white px-2 py-0.5">
                        <input type="checkbox" checked={isSelected('revision', lec.id, sub.course_id)}
                          onChange={() => toggleSelect({ type: 'revision', ref_id: lec.id, course_id: sub.course_id, title: lec.title })} />
                        Revision
                      </label>
                    )}
                  </div>
                </div>
              ))}
              {!!sub.quizzes.length && <p className="pt-2 text-xs font-semibold uppercase text-slate-400">Quizzes</p>}
              {sub.quizzes.map((q) => (
                <label key={q.id} className="flex items-center gap-2 rounded-lg bg-amber-50 px-3 py-2 text-sm">
                  <input type="checkbox" checked={isSelected('quiz', q.id, sub.course_id)}
                    onChange={() => toggleSelect({ type: 'quiz', ref_id: q.id, course_id: sub.course_id, title: q.title })} />
                  {q.title}
                </label>
              ))}
            </div>
          )}
        </div>
      ))}
    </div>
  )

  const renderManualEditor = () => (
    <div className="space-y-3">
      {manualDays.map((m, idx) => (
        <div key={`manual-task-${idx}`} className="grid gap-2 rounded-xl border border-slate-100 p-3 sm:grid-cols-[100px_1fr_auto]">
          <label className="text-xs">
            <span className="text-slate-500">Day</span>
            <input type="number" min={1} max={durationDays} className="input-field mt-1 w-full text-sm"
              value={m.day_number}
              onChange={(e) => {
                const value = Number(e.target.value) || 1
                setManualDays((prev) => prev.map((row, i) => (i === idx ? { ...row, day_number: value } : row)))
              }} />
          </label>
          <label className="text-xs">
            <span className="text-slate-500">Task</span>
            <input className="input-field mt-1 w-full text-sm" placeholder="e.g. Study Cell Injury"
              value={m.title}
              onChange={(e) => {
                const value = e.target.value
                setManualDays((prev) => prev.map((row, i) => (i === idx ? { ...row, title: value } : row)))
              }} />
          </label>
          <button type="button" className="self-end text-xs text-rose-500"
            onClick={() => setManualDays((prev) => prev.filter((_, i) => i !== idx))}>
            Remove
          </button>
        </div>
      ))}
      <button type="button" className="btn-secondary text-xs"
        onClick={() => setManualDays((prev) => [...prev, { day_number: Math.min(prev.length + 1, durationDays), title: '' }])}>
        <FiPlus className="mr-1 inline" /> Add task
      </button>
    </div>
  )

  if (loading) return <p className="py-16 text-center text-slate-400">Loading FCPS Study Planner…</p>

  if (data?.setup_required) {
    return (
      <div className="mx-auto max-w-xl pb-10">
        <p className="text-xs font-semibold uppercase text-teal-600">First-time setup</p>
        <h2 className="font-display text-2xl font-bold text-navy">FCPS Study Planner</h2>
        <p className="text-sm text-slate-500">Set your exam date to unlock your study dashboard.</p>
        <div className="mt-6 space-y-4 rounded-2xl border border-slate-100 bg-white p-6 shadow-soft">
          <label className="block text-sm">
            <span className="text-xs font-semibold text-slate-500">Exam date *</span>
            <input type="date" className="input-field mt-1 w-full" value={setupForm.exam_date}
              min={new Date().toISOString().slice(0, 10)}
              onChange={(e) => setSetupForm({ ...setupForm, exam_date: e.target.value })} />
          </label>
          <label className="block text-sm">
            <span className="text-xs font-semibold text-slate-500">Daily study hours (optional)</span>
            <input type="number" min={0.5} max={16} step={0.5} className="input-field mt-1 w-full"
              value={setupForm.hours_per_day}
              onChange={(e) => setSetupForm({ ...setupForm, hours_per_day: Number(e.target.value) || 3 })} />
          </label>
          <div>
            <p className="text-xs font-semibold text-slate-500">Preferred study days</p>
            <div className="mt-2 flex flex-wrap gap-2">
              {WEEKDAYS.map((d) => (
                <button key={d.id} type="button"
                  onClick={() => setSetupForm((s) => ({
                    ...s,
                    preferred_days: s.preferred_days.includes(d.id)
                      ? s.preferred_days.filter((x) => x !== d.id) : [...s.preferred_days, d.id],
                  }))}
                  className={`rounded-full px-3 py-1 text-xs font-semibold ${setupForm.preferred_days.includes(d.id) ? 'bg-teal-600 text-white' : 'bg-slate-100'}`}>
                  {d.label}
                </button>
              ))}
            </div>
          </div>
          <button type="button" disabled={saving} className="btn-primary w-full text-sm" onClick={saveSetup}>
            {saving ? 'Saving…' : 'Save & continue'}
          </button>
        </div>
      </div>
    )
  }

  return (
    <div className="pb-10">
      <div className="flex flex-wrap items-start justify-between gap-4">
        <div>
          <p className="text-xs font-semibold uppercase text-teal-600">Premium · Personal</p>
          <h2 className="font-display text-2xl font-bold text-navy">FCPS Study Planner</h2>
          <p className="text-sm text-slate-500">Choose LMS, Manual, or Mixed mode for your FCPS study plan.</p>
        </div>
        <div className="flex flex-wrap gap-2">
          <button type="button" onClick={() => setMainTab('plans')} className="btn-secondary text-xs">My Plans ({plans.length})</button>
          <button type="button" onClick={printPlan} disabled={!data?.active_plan} className="btn-secondary text-xs disabled:opacity-40">Print / PDF</button>
          <button type="button" onClick={exportPlan} disabled={!data?.active_plan} className="btn-secondary text-xs disabled:opacity-40"><FiDownload className="mr-1 inline" /> Export</button>
        </div>
      </div>

      <div className="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
        <div className="rounded-2xl border border-teal-100 bg-teal-50/50 p-4">
          <p className="text-[10px] font-semibold uppercase text-teal-600">Exam countdown</p>
          <p className="text-3xl font-bold text-navy">{data?.exam_countdown_days ?? '—'}</p>
          <p className="text-xs text-slate-500">days remaining</p>
        </div>
        <div className="rounded-2xl border border-slate-100 bg-white p-4"><p className="text-[10px] uppercase text-slate-400">Total plans</p><p className="text-2xl font-bold text-navy">{data?.total_plans ?? 0}</p></div>
        <div className="rounded-2xl border border-slate-100 bg-white p-4"><p className="text-[10px] uppercase text-slate-400">Completed</p><p className="text-2xl font-bold text-emerald-600">{data?.completed_plans ?? 0}</p></div>
        <div className="rounded-2xl border border-slate-100 bg-white p-4 col-span-1 sm:col-span-2">
          <div className="flex items-start justify-between gap-2">
            <div className="min-w-0">
              <p className="text-[10px] uppercase text-slate-400">Current plan</p>
              <p className="truncate text-lg font-bold text-navy">{data?.active_plan?.plan_name || 'None'}</p>
              {data?.active_plan && (
                <p className="text-xs text-slate-500 capitalize">{data.active_plan.plan_mode} · {data.active_plan.completion_pct}% · {data.active_plan.start_date} → {data.active_plan.end_date}</p>
              )}
            </div>
            {data?.active_plan && <StatusBadge status="active" />}
          </div>
          {data?.active_plan && <PlanActions plan={data.active_plan} {...planActionProps} />}
          {!data?.active_plan && (
            <button type="button" className="btn-primary mt-3 text-xs" onClick={() => setMainTab('create')}>Create a plan</button>
          )}
        </div>
      </div>

      <div className="mt-4 flex flex-wrap gap-2">
        {[
          ['dashboard', 'Dashboard'],
          ['create', 'New plan'],
          ['plans', 'My Plans'],
          ['calendar', 'Calendar'],
          ['subjects', 'Subjects'],
          ['stats', 'Stats'],
        ].map(([t, label]) => (
          <button key={t} type="button" onClick={() => { setMainTab(t); if (t === 'create') setCreateMode(null) }}
            className={`rounded-full px-3 py-1 text-xs font-semibold ${mainTab === t ? 'bg-teal-600 text-white' : 'bg-slate-100 text-slate-600'}`}>
            {label}{t === 'plans' ? ` (${plans.length})` : ''}
          </button>
        ))}
      </div>

      {mainTab === 'dashboard' && (
        <div className="mt-6 space-y-6">
          <div className="flex flex-wrap justify-around gap-4 rounded-2xl border border-slate-100 bg-white p-5">
            <Ring value={data?.today?.completion_pct} label="Today" />
            <Ring value={data?.active_plan?.completion_pct || data?.progress?.overall_pct} label="Current plan" />
            <Ring value={data?.weekly_pct} label="Weekly" />
            <Ring value={data?.monthly_pct} label="Monthly" />
            <div className="text-center"><p className="text-3xl font-bold text-amber-500">{data?.streak_days ?? 0}</p><p className="text-xs text-slate-500">Streak</p></div>
          </div>

          {!!data?.notifications?.length && (
            <div className="space-y-2">
              {data.notifications.map((n, i) => (
                <div key={i} className="rounded-xl border border-amber-100 bg-amber-50/50 px-4 py-3 text-sm">
                  <strong className="text-navy">{n.title}</strong> — <span className="text-slate-600">{n.message}</span>
                </div>
              ))}
            </div>
          )}

          <div className="grid gap-4 lg:grid-cols-2">
            {[
              ["Today's videos", data?.today?.videos, FiBookOpen],
              ["Today's quizzes", data?.today?.quizzes, FiTarget],
              ["Today's flashcards", data?.today?.flashcards, FiRefreshCw],
              ["Today's manual tasks", data?.today?.manual, FiClock],
              ["Today's revision", data?.today?.revision, FiCalendar],
              ["Today's notes", data?.today?.notes, FiBookOpen],
            ].map(([title, items, Icon]) => (
              <div key={title} className="rounded-2xl border border-slate-100 bg-white p-5 shadow-soft">
                <div className="flex items-center gap-2"><Icon className="text-teal-600" /><h3 className="font-bold text-navy">{title}</h3></div>
                {!items?.length ? <p className="mt-3 text-sm text-slate-400">Nothing scheduled</p> : (
                  <ul className="mt-3 space-y-2">{items.map((t) => <TaskRow key={t.id} task={t} onStatus={setTask} onMove={moveTask} />)}</ul>
                )}
              </div>
            ))}
          </div>
          <p className="text-sm text-slate-500">Completed today: <strong>{data?.today?.completed_today ?? 0}</strong> · Remaining: <strong>{data?.today?.remaining_today ?? 0}</strong></p>

          {!!plans.length && (
            <div className="rounded-2xl border border-slate-100 bg-white p-5 shadow-soft">
              <div className="flex items-center justify-between gap-2">
                <h3 className="font-bold text-navy">Your plans</h3>
                <button type="button" className="text-xs font-semibold text-teal-600" onClick={() => setMainTab('plans')}>Manage all →</button>
              </div>
              <ul className="mt-3 space-y-2">
                {plans.slice(0, 4).map((p) => (
                  <li key={p.id} className="flex flex-wrap items-center justify-between gap-2 rounded-xl border border-slate-50 px-3 py-2 text-sm">
                    <div className="min-w-0">
                      <p className="truncate font-medium text-navy">{p.plan_name}</p>
                      <p className="text-xs capitalize text-slate-400">{p.plan_mode} · {p.duration_days} days · {p.completion_pct}%</p>
                    </div>
                    <div className="flex items-center gap-2">
                      <StatusBadge status={p.status} />
                      <button type="button" className="text-xs font-semibold text-teal-600" onClick={() => viewPlan(p.id)}>View</button>
                      <button type="button" className="text-xs font-semibold text-rose-600" onClick={() => deletePlan(p.id, p.plan_name)}>Delete</button>
                    </div>
                  </li>
                ))}
              </ul>
            </div>
          )}
        </div>
      )}

      {mainTab === 'create' && !createMode && (
        <div className="mt-6 grid gap-4 sm:grid-cols-3">
          {[
            ['lms', 'LMS Content Plan', 'Build from enrolled lectures, quizzes, notes & flashcards'],
            ['manual', 'Manual Study Plan', 'Enter your own day-by-day study tasks'],
            ['mixed', 'Mixed Study Plan', 'Combine LMS content with personal tasks'],
          ].map(([mode, title, desc]) => (
            <button key={mode} type="button" onClick={() => setCreateMode(mode)}
              className="rounded-2xl border border-slate-100 bg-white p-6 text-left shadow-soft transition hover:border-teal-300">
              <h3 className="font-bold text-navy">{title}</h3>
              <p className="mt-2 text-sm text-slate-500">{desc}</p>
            </button>
          ))}
        </div>
      )}

      {mainTab === 'create' && createMode && (
        <div className="mt-6 space-y-4 rounded-2xl border border-slate-100 bg-white p-6 shadow-soft">
          <div className="flex flex-wrap items-center justify-between gap-2">
            <h3 className="font-bold capitalize text-navy">{createMode} plan</h3>
            <button type="button" className="text-xs text-slate-500" onClick={() => setCreateMode(null)}>Change mode</button>
          </div>
          <input className="input-field w-full text-sm" placeholder="Plan name (optional)" value={planName} onChange={(e) => setPlanName(e.target.value)} />
          <div>
            <p className="text-xs font-semibold text-slate-500">Duration</p>
            <div className="mt-2 flex flex-wrap gap-2">
              {DURATIONS.map((d) => (
                <button key={d.value} type="button" onClick={() => setDuration(d.value)}
                  className={`rounded-full px-3 py-1 text-xs font-semibold ${duration === d.value ? 'bg-teal-600 text-white' : 'bg-slate-100'}`}>
                  {d.label}
                </button>
              ))}
            </div>
            {duration === 'custom' && (
              <input type="number" min={1} max={90} className="input-field mt-2 w-32 text-sm" value={customDays}
                onChange={(e) => setCustomDays(Number(e.target.value) || 1)} />
            )}
          </div>
          {(createMode === 'lms' || createMode === 'mixed') && (
            <div>
              <h4 className="mb-2 font-semibold text-navy">Select LMS content</h4>
              {renderContentPicker()}
            </div>
          )}
          {(createMode === 'manual' || createMode === 'mixed') && (
            <div>
              <h4 className="mb-2 font-semibold text-navy">Manual tasks</h4>
              {renderManualEditor()}
            </div>
          )}
          <button type="button" disabled={saving} className="btn-primary text-sm disabled:opacity-50" onClick={createPlan}>
            {saving ? 'Generating…' : 'Generate Study Plan'}
          </button>
        </div>
      )}

      {mainTab === 'calendar' && (
        <div className="mt-6 rounded-2xl border border-slate-100 bg-white p-6 shadow-soft">
          {!data?.active_plan ? <p className="text-sm text-slate-400">Create a plan to see the calendar.</p> : (
            <>
              <div className="flex justify-between"><h3 className="font-bold text-navy">Calendar</h3>
                <input type="month" className="input-field text-sm" value={month} onChange={(e) => setMonth(e.target.value)} /></div>
              <div className="mt-4 grid grid-cols-7 gap-2 text-center text-xs font-semibold text-slate-400">
                {['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'].map((d) => <div key={d}>{d}</div>)}
              </div>
              <div className="mt-2 grid grid-cols-7 gap-2">
                {calGrid.map((cell, i) => !cell ? <div key={`e${i}`} /> : (
                  <button key={cell.date} type="button" disabled={!cell.day} onClick={() => cell.day && openDay(cell.date)}
                    className={`min-h-[4rem] rounded-xl border p-1.5 text-left text-[10px] ${!cell.day ? 'border-slate-50 text-slate-300' : 'border-teal-100 hover:border-teal-300'}`}>
                    <span className="font-bold text-navy">{cell.n}</span>
                    {cell.day && <p className="mt-1 text-slate-500">{cell.day.task_count} tasks · {Math.round(cell.day.completed_pct)}%</p>}
                  </button>
                ))}
              </div>
            </>
          )}
        </div>
      )}

      {mainTab === 'subjects' && (
        <div className="mt-6 space-y-3">
          {(data?.subject_progress || []).map((s) => (
            <div key={s.subject_title} className="rounded-2xl border border-slate-100 bg-white p-5 shadow-soft">
              <div className="flex justify-between"><h3 className="font-bold text-navy">{s.subject_title}</h3><span className="text-sm font-semibold text-teal-600">{s.completion_pct}%</span></div>
              <div className="mt-2 h-2 rounded-full bg-slate-100"><div className="h-2 rounded-full bg-teal-500" style={{ width: `${s.completion_pct}%` }} /></div>
              <div className="mt-3 grid grid-cols-2 gap-2 text-xs text-slate-500 sm:grid-cols-4">
                <p>Lectures {s.completed_lectures}/{s.total_lectures}</p>
                <p>Quizzes {s.completed_quizzes}/{s.total_quizzes}</p>
                <p>Notes {s.completed_notes}/{s.total_notes}</p>
                <p>Flashcards {s.completed_flashcards}/{s.total_flashcards}</p>
              </div>
            </div>
          ))}
          {!data?.subject_progress?.length && <p className="text-sm text-slate-400">No subject progress yet — create an LMS or mixed plan.</p>}
        </div>
      )}

      {mainTab === 'plans' && (
        <div className="mt-6 space-y-4">
          <div className="flex flex-wrap items-center justify-between gap-3">
            <div>
              <h3 className="font-bold text-navy">My Plans</h3>
              <p className="text-sm text-slate-500">All plans you have created. View, switch, duplicate, archive, or delete any of them.</p>
            </div>
            <button type="button" className="btn-primary text-xs" onClick={() => { setMainTab('create'); setCreateMode(null) }}>
              <FiPlus className="mr-1 inline" /> New plan
            </button>
          </div>
          <div className="flex flex-wrap gap-2">
            {[
              ['all', 'All'],
              ['active', 'Current'],
              ['completed', 'Completed'],
              ['archived', 'Archived'],
            ].map(([key, label]) => (
              <button key={key} type="button" onClick={() => setPlanFilter(key)}
                className={`rounded-full px-3 py-1 text-xs font-semibold ${planFilter === key ? 'bg-navy text-white' : 'bg-slate-100 text-slate-600'}`}>
                {label}
              </button>
            ))}
          </div>
          {filteredPlans.map((p) => (
            <div key={p.id} className={`rounded-2xl border bg-white p-5 shadow-soft ${p.status === 'active' ? 'border-teal-200' : 'border-slate-100'}`}>
              <div className="flex flex-wrap items-start justify-between gap-3">
                <div>
                  <div className="flex flex-wrap items-center gap-2">
                    <h3 className="font-bold text-navy">{p.plan_name}</h3>
                    <StatusBadge status={p.status} />
                  </div>
                  <p className="mt-1 text-xs capitalize text-slate-500">
                    {p.plan_mode} · {p.duration_days} days · {p.start_date} → {p.end_date} · {p.completion_pct}% complete
                  </p>
                </div>
                <div className="w-28">
                  <div className="h-2 rounded-full bg-slate-100">
                    <div className="h-2 rounded-full bg-teal-500" style={{ width: `${Math.min(100, p.completion_pct || 0)}%` }} />
                  </div>
                </div>
              </div>
              <PlanActions plan={p} {...planActionProps} />
            </div>
          ))}
          {!filteredPlans.length && (
            <div className="rounded-2xl border border-dashed border-slate-200 p-8 text-center">
              <p className="text-sm text-slate-400">{plans.length ? 'No plans in this filter.' : 'No plans yet.'}</p>
              <button type="button" className="btn-primary mt-3 text-xs" onClick={() => setMainTab('create')}>Create your first plan</button>
            </div>
          )}
        </div>
      )}

      {mainTab === 'stats' && data?.statistics && (
        <div className="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
          {[
            ['Study hours', data.statistics.study_hours],
            ['Active plans', data.statistics.active_plans],
            ['Completed plans', data.statistics.completed_plans],
            ['Avg daily tasks', data.statistics.average_daily_progress],
            ['Videos watched', data.statistics.videos_watched],
            ['Quizzes attempted', data.statistics.quizzes_attempted],
            ['Flashcards reviewed', data.statistics.flashcards_reviewed],
            ['Manual completed', data.statistics.manual_tasks_completed],
            ['Weekly progress %', data.statistics.weekly_progress],
            ['Monthly progress %', data.statistics.monthly_progress],
          ].map(([label, val]) => (
            <div key={label} className="rounded-2xl border border-slate-100 bg-white p-5 shadow-soft">
              <p className="text-xs uppercase text-slate-400">{label}</p>
              <p className="mt-1 text-2xl font-bold text-navy">{val}</p>
            </div>
          ))}
        </div>
      )}

      <Modal open={!!dayModal} onClose={() => setDayModal(null)} title={dayModal?.date || 'Day'} size="lg">
        {dayModal && (
          <ul className="space-y-2">
            {(dayModal.tasks || []).map((t) => <TaskRow key={t.id} task={t} onStatus={setTask} onMove={moveTask} />)}
            {!dayModal.tasks?.length && <p className="text-sm text-slate-400">No tasks</p>}
          </ul>
        )}
      </Modal>

      <Modal
        open={!!viewModal}
        onClose={() => setViewModal(null)}
        title={viewModal?.plan?.plan_name || 'Study Plan'}
        subtitle={viewModal ? `${viewModal.plan?.start_date} → ${viewModal.plan?.end_date}` : ''}
        size="xl"
      >
        {viewModal && (() => {
          const allTasks = (viewModal.days || []).flatMap((d) => d.tasks || [])
          const done = allTasks.filter((t) => t.status === 'completed').length
          const pending = allTasks.filter((t) => t.status === 'pending').length
          const skipped = allTasks.filter((t) => t.status === 'skipped').length
          const pct = Math.round(Number(viewModal.plan?.completion_pct) || (allTasks.length ? (done / allTasks.length) * 100 : 0))
          const modeLabel = { lms: 'LMS Content', manual: 'Manual', mixed: 'Mixed' }[viewModal.plan?.plan_mode] || viewModal.plan?.plan_mode
          const typeLabel = (type) => ({
            video: 'Video', lecture: 'Lecture', quiz: 'Quiz', flashcard: 'Flashcards',
            note: 'Notes', revision: 'Revision', manual: 'Personal', mcq: 'MCQ',
          }[type] || type)
          const typeTone = (type) => ({
            video: 'bg-rose-50 text-rose-700', lecture: 'bg-indigo-50 text-indigo-700',
            quiz: 'bg-amber-50 text-amber-800', flashcard: 'bg-violet-50 text-violet-700',
            note: 'bg-sky-50 text-sky-700', revision: 'bg-teal-50 text-teal-800',
            manual: 'bg-slate-100 text-slate-700', mcq: 'bg-orange-50 text-orange-800',
          }[type] || 'bg-slate-100 text-slate-600')
          const statusTone = (s) => ({
            completed: 'bg-emerald-100 text-emerald-800',
            pending: 'bg-amber-100 text-amber-800',
            skipped: 'bg-slate-200 text-slate-600',
          }[s] || 'bg-slate-100 text-slate-600')
          const canEdit = viewModal.plan?.status === 'active'

          return (
            <div className="flex min-h-0 flex-1 flex-col gap-4">
              {/* Summary */}
              <div className="rounded-2xl bg-gradient-to-br from-teal-50 via-white to-slate-50 p-4 ring-1 ring-teal-100">
                <div className="flex flex-wrap items-center gap-4">
                  <div className="grid h-16 w-16 place-items-center rounded-full" style={{ background: `conic-gradient(#0d9488 ${pct * 3.6}deg, #e2e8f0 0deg)` }}>
                    <div className="grid h-12 w-12 place-items-center rounded-full bg-white text-sm font-bold text-navy">{pct}%</div>
                  </div>
                  <div className="min-w-0 flex-1">
                    <div className="flex flex-wrap items-center gap-2">
                      <StatusBadge status={viewModal.plan?.status} />
                      <span className="rounded-full bg-white px-2.5 py-0.5 text-[11px] font-semibold text-slate-600 ring-1 ring-slate-200">{modeLabel}</span>
                      <span className="text-xs text-slate-500">{viewModal.plan?.duration_days} study days</span>
                    </div>
                    <div className="mt-3 grid grid-cols-3 gap-2 sm:max-w-md">
                      <div className="rounded-xl bg-white/80 px-3 py-2 text-center ring-1 ring-emerald-100">
                        <p className="text-lg font-bold text-emerald-600">{done}</p>
                        <p className="text-[10px] font-semibold uppercase text-slate-400">Done</p>
                      </div>
                      <div className="rounded-xl bg-white/80 px-3 py-2 text-center ring-1 ring-amber-100">
                        <p className="text-lg font-bold text-amber-600">{pending}</p>
                        <p className="text-[10px] font-semibold uppercase text-slate-400">Pending</p>
                      </div>
                      <div className="rounded-xl bg-white/80 px-3 py-2 text-center ring-1 ring-slate-200">
                        <p className="text-lg font-bold text-slate-500">{skipped}</p>
                        <p className="text-[10px] font-semibold uppercase text-slate-400">Skipped</p>
                      </div>
                    </div>
                  </div>
                </div>
                <div className="mt-3 flex flex-wrap gap-2">
                  {viewModal.plan?.status !== 'active' && (
                    <button type="button" className="btn-primary text-xs" onClick={() => resumePlan(viewModal.plan.id)}>
                      <FiPlay className="mr-1 inline" /> Make current plan
                    </button>
                  )}
                  <button type="button" className="btn-secondary text-xs" onClick={() => duplicatePlan(viewModal.plan.id)}>
                    <FiCopy className="mr-1 inline" /> Duplicate
                  </button>
                  {viewModal.plan?.status !== 'archived' && (
                    <button type="button" className="btn-secondary text-xs" onClick={() => archivePlan(viewModal.plan.id)}>
                      <FiArchive className="mr-1 inline" /> Archive
                    </button>
                  )}
                  <button type="button" className="btn-secondary text-xs text-rose-600" onClick={() => deletePlan(viewModal.plan.id, viewModal.plan.plan_name)}>
                    <FiTrash2 className="mr-1 inline" /> Delete
                  </button>
                </div>
              </div>

              {/* Day timeline */}
              <div className="max-h-[52vh] space-y-3 overflow-y-auto pr-1">
                {(viewModal.days || []).map((d) => {
                  const tasks = d.tasks || []
                  const dayDone = tasks.filter((t) => t.status === 'completed').length
                  const dayPct = tasks.length ? Math.round((dayDone / tasks.length) * 100) : 0
                  const dateLabel = new Date(`${d.day.plan_date}T12:00:00`).toLocaleDateString(undefined, {
                    weekday: 'short', month: 'short', day: 'numeric',
                  })
                  return (
                    <section key={d.day.id} className="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
                      <header className="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 bg-slate-50/80 px-4 py-3">
                        <div>
                          <p className="text-sm font-bold text-navy">Day {d.day.day_number}</p>
                          <p className="text-xs text-slate-500">{dateLabel}</p>
                        </div>
                        <div className="flex items-center gap-3">
                          <span className="text-xs font-semibold text-slate-500">{dayDone}/{tasks.length} tasks</span>
                          <div className="w-24">
                            <div className="h-1.5 rounded-full bg-slate-200">
                              <div className="h-1.5 rounded-full bg-teal-500" style={{ width: `${dayPct}%` }} />
                            </div>
                          </div>
                          <span className="text-xs font-bold text-teal-700">{dayPct}%</span>
                        </div>
                      </header>
                      <ul className="divide-y divide-slate-50">
                        {tasks.map((t) => (
                          <li key={t.id} className="flex items-start gap-3 px-4 py-3">
                            <button
                              type="button"
                              disabled={!canEdit}
                              onClick={() => canEdit && setTask(t, t.status === 'completed' ? 'pending' : 'completed')}
                              className={`mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full border-2 transition ${
                                t.status === 'completed'
                                  ? 'border-emerald-500 bg-emerald-500 text-white'
                                  : 'border-slate-300 bg-white'
                              } ${canEdit ? 'hover:border-teal-400' : 'cursor-default opacity-80'}`}
                              title={canEdit ? 'Toggle complete' : t.status}
                            >
                              {t.status === 'completed' && <FiCheck size={14} />}
                            </button>
                            <div className="min-w-0 flex-1">
                              <p className={`text-sm font-medium leading-snug ${t.status === 'completed' ? 'text-slate-400 line-through' : 'text-navy'}`}>
                                {t.title}
                              </p>
                              <div className="mt-1.5 flex flex-wrap gap-1.5">
                                <span className={`rounded-md px-1.5 py-0.5 text-[10px] font-semibold uppercase ${typeTone(t.task_type)}`}>
                                  {typeLabel(t.task_type)}
                                </span>
                                <span className={`rounded-md px-1.5 py-0.5 text-[10px] font-semibold uppercase ${statusTone(t.status)}`}>
                                  {t.status}
                                </span>
                                {t.source === 'manual' && (
                                  <span className="rounded-md bg-slate-100 px-1.5 py-0.5 text-[10px] font-semibold uppercase text-slate-500">Manual</span>
                                )}
                                {t.subject_title && (
                                  <span className="truncate rounded-md bg-white px-1.5 py-0.5 text-[10px] text-slate-500 ring-1 ring-slate-200">
                                    {t.subject_title}
                                  </span>
                                )}
                              </div>
                            </div>
                            {canEdit && t.status !== 'completed' && (
                              <div className="flex shrink-0 flex-col gap-1">
                                <button type="button" className="text-[10px] font-semibold text-amber-600" onClick={() => setTask(t, 'skipped')}>Skip</button>
                                <button type="button" className="text-[10px] font-semibold text-teal-600" onClick={() => moveTask(t, 'tomorrow')}>Tomorrow</button>
                              </div>
                            )}
                          </li>
                        ))}
                        {!tasks.length && (
                          <li className="px-4 py-6 text-center text-sm text-slate-400">No tasks on this day</li>
                        )}
                      </ul>
                    </section>
                  )
                })}
                {!viewModal.days?.length && (
                  <p className="py-8 text-center text-sm text-slate-400">This plan has no scheduled days yet.</p>
                )}
              </div>
            </div>
          )
        })()}
      </Modal>
    </div>
  )
}
