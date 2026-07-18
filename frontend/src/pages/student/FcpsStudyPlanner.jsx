import { useEffect, useMemo, useState } from 'react'
import {
  FiCalendar, FiCheck, FiZap, FiTarget, FiBookOpen, FiRefreshCw, FiPrinter,
  FiDownload, FiTrash2, FiClock, FiTrendingUp, FiSearch, FiAward, FiSkipForward,
} from 'react-icons/fi'
import { fcpsStudyPlannerService } from '../../services/api'
import { useToast } from '../../context/ToastContext'
import { Modal } from '../../components/ui'
import Alert from '../../components/dashboard/Alert'

const DAYS = [
  { id: 'monday', label: 'Mon' }, { id: 'tuesday', label: 'Tue' },
  { id: 'wednesday', label: 'Wed' }, { id: 'thursday', label: 'Thu' },
  { id: 'friday', label: 'Fri' }, { id: 'saturday', label: 'Sat' },
  { id: 'sunday', label: 'Sun' },
]

const REVISION_OPTS = [
  { value: 'every_3_days', label: 'Every 3 days' },
  { value: 'every_5_days', label: 'Every 5 days' },
  { value: 'every_7_days', label: 'Every 7 days' },
  { value: 'every_sunday', label: 'Every Sunday' },
  { value: 'after_each_subject', label: 'After each completed subject' },
]

function Ring({ value = 0, label, tone = 'teal' }) {
  const v = Math.max(0, Math.min(100, Number(value) || 0))
  const c = { teal: '#0d9488', emerald: '#059669', amber: '#d97706' }[tone] || '#0d9488'
  return (
    <div className="flex flex-col items-center">
      <div className="grid h-24 w-24 place-items-center rounded-full" style={{ background: `conic-gradient(${c} ${v * 3.6}deg, #e2e8f0 0deg)` }}>
        <div className="grid h-[4.5rem] w-[4.5rem] place-items-center rounded-full bg-white text-lg font-bold text-navy dark:bg-slate-900 dark:text-slate-100">
          {Math.round(v)}%
        </div>
      </div>
      <p className="mt-2 text-xs font-medium text-slate-500">{label}</p>
    </div>
  )
}

const parseList = (text) => String(text || '').split(/[,;\n]+/).map((s) => s.trim()).filter(Boolean)

const emptyForm = () => ({
  exam_date: '',
  start_date: new Date().toISOString().slice(0, 10),
  hours_per_day: 3,
  preferred_days: ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'],
  sessions_per_day: 2,
  preferred_time: 'evening',
  subjects_completed: '',
  subjects_remaining: 'Anatomy, Physiology, Pathology, Pharmacology, Biochemistry, Microbiology',
  subjects_weak: 'Pathology, Pharmacology',
  subjects_strong: 'Anatomy',
  daily_mcq_target: 40,
  daily_flashcard_target: 30,
  revision_preference: 'every_7_days',
})

export default function FcpsStudyPlanner() {
  const toast = useToast()
  const [tab, setTab] = useState('dashboard')
  const [loading, setLoading] = useState(true)
  const [saving, setSaving] = useState(false)
  const [form, setForm] = useState(emptyForm)
  const [plan, setPlan] = useState(null)
  const [dash, setDash] = useState(null)
  const [month, setMonth] = useState(() => new Date().toISOString().slice(0, 7))
  const [calDays, setCalDays] = useState([])
  const [dayModal, setDayModal] = useState(null)
  const [rescheduleId, setRescheduleId] = useState(null)
  const [rescheduleDate, setRescheduleDate] = useState('')
  const [searchQ, setSearchQ] = useState('')
  const [searchItems, setSearchItems] = useState([])
  const [note, setNote] = useState('')

  const load = async () => {
    setLoading(true)
    try {
      const { data } = await fcpsStudyPlannerService.dashboard()
      const d = data.data
      if (!d?.has_plan) {
        setPlan(null)
        setDash(null)
        setTab('setup')
      } else {
        setPlan(d.plan)
        setDash(d.dashboard)
        const p = d.plan
        setForm((f) => ({
          ...f,
          exam_date: p.exam_date?.slice?.(0, 10) || f.exam_date,
          start_date: p.start_date?.slice?.(0, 10) || f.start_date,
          hours_per_day: p.hours_per_day ?? f.hours_per_day,
          preferred_days: p.preferred_days?.length ? p.preferred_days : f.preferred_days,
          sessions_per_day: p.sessions_per_day ?? f.sessions_per_day,
          preferred_time: p.preferred_time || f.preferred_time,
          subjects_completed: (p.subjects_completed || []).join(', '),
          subjects_remaining: (p.subjects_remaining || []).join(', '),
          subjects_weak: (p.subjects_weak || []).join(', '),
          subjects_strong: (p.subjects_strong || []).join(', '),
          daily_mcq_target: p.daily_mcq_target ?? f.daily_mcq_target,
          daily_flashcard_target: p.daily_flashcard_target ?? f.daily_flashcard_target,
          revision_preference: p.revision_preference || f.revision_preference,
        }))
        setTab((t) => (t === 'setup' ? 'dashboard' : t))
      }
    } catch {
      setPlan(null)
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => { load() }, [])

  useEffect(() => {
    if (!plan) return
    fcpsStudyPlannerService.calendar(month)
      .then(({ data }) => setCalDays(data.data?.days || []))
      .catch(() => setCalDays([]))
  }, [plan, month])

  const generate = async () => {
    setSaving(true)
    try {
      const { data } = await fcpsStudyPlannerService.generate({
        ...form,
        subjects_completed: parseList(form.subjects_completed),
        subjects_remaining: parseList(form.subjects_remaining),
        subjects_weak: parseList(form.subjects_weak),
        subjects_strong: parseList(form.subjects_strong),
      })
      setPlan(data.data?.plan)
      setDash(data.data?.dashboard)
      setTab('dashboard')
      toast.success('Study plan generated')
    } catch (err) {
      toast.error(err.response?.data?.message || err.message)
    } finally {
      setSaving(false)
    }
  }

  const regenerate = async () => {
    setSaving(true)
    try {
      const { data } = await fcpsStudyPlannerService.regenerate()
      setPlan(data.data?.plan)
      setDash(data.data?.dashboard)
      toast.success('Plan regenerated')
      load()
    } catch (err) {
      toast.error(err.response?.data?.message || err.message)
    } finally {
      setSaving(false)
    }
  }

  const resetPlan = async () => {
    if (!window.confirm('Reset your FCPS study plan?')) return
    await fcpsStudyPlannerService.reset()
    setPlan(null)
    setDash(null)
    setForm(emptyForm())
    setTab('setup')
    toast.success('Plan reset')
  }

  const openDay = async (date) => {
    const { data } = await fcpsStudyPlannerService.day(date)
    setDayModal(data.data)
    setRescheduleId(null)
  }

  const setTask = async (task, status) => {
    const { data } = await fcpsStudyPlannerService.setTask(task.id, status)
    setDayModal(data.data)
    load()
  }

  const toggleTask = (task) => setTask(task, task.status === 'completed' ? 'pending' : 'completed')

  const doReschedule = async () => {
    if (!rescheduleId || !rescheduleDate) return
    await fcpsStudyPlannerService.rescheduleTask(rescheduleId, rescheduleDate)
    toast.success('Task rescheduled')
    openDay(rescheduleDate)
    load()
  }

  const handleMissed = async () => {
    const { data } = await fcpsStudyPlannerService.handleMissed()
    setNote(data.data?.explanation || '')
    setDash(data.data?.dashboard)
    toast.success('Missed work redistributed')
    load()
  }

  const resetToday = async () => {
    await fcpsStudyPlannerService.resetToday()
    toast.success('Today reset')
    load()
    openDay(new Date().toISOString().slice(0, 10))
  }

  const runSearch = async () => {
    const { data } = await fcpsStudyPlannerService.search({ q: searchQ })
    setSearchItems(data.data?.items || [])
  }

  const printPlan = async () => {
    const { data } = await fcpsStudyPlannerService.export()
    const payload = data.data
    const w = window.open('', '_blank')
    if (!w) return
    const rows = (payload.calendar || []).map((d) => (
      `<tr><td>${d.plan_date}</td><td>${(d.topics || []).join(', ')}</td><td>${d.mcq_target}</td><td>${d.flashcard_target}</td><td>${d.revision_subject || '—'}</td></tr>`
    )).join('')
    w.document.write(`<!DOCTYPE html><html><head><title>FCPS Study Plan</title>
      <style>body{font-family:Segoe UI,sans-serif;padding:24px}table{width:100%;border-collapse:collapse}td,th{border:1px solid #ddd;padding:8px;font-size:12px}th{background:#f8fafc}</style></head>
      <body><h1>FCPS Part 1 Study Plan</h1>
      <p>Exam: ${payload.plan?.exam_date} · ${payload.plan?.hours_per_day} hrs/day</p>
      <p>${payload.plan?.strategy_notes || ''}</p>
      <table><thead><tr><th>Date</th><th>Topics</th><th>MCQs</th><th>Cards</th><th>Revision</th></tr></thead><tbody>${rows}</tbody></table>
      <script>window.onload=()=>window.print()</script></body></html>`)
    w.document.close()
  }

  const exportJson = async () => {
    const { data } = await fcpsStudyPlannerService.export()
    const blob = new Blob([JSON.stringify(data.data, null, 2)], { type: 'application/json' })
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `fcps-study-schedule-${plan?.exam_date || 'export'}.json`
    a.click()
    URL.revokeObjectURL(url)
  }

  const calGrid = useMemo(() => {
    const [y, m] = month.split('-').map(Number)
    const first = new Date(y, m - 1, 1)
    const startPad = first.getDay()
    const daysInMonth = new Date(y, m, 0).getDate()
    const map = Object.fromEntries(calDays.map((d) => [d.plan_date, d]))
    const cells = []
    for (let i = 0; i < startPad; i++) cells.push(null)
    for (let d = 1; d <= daysInMonth; d++) {
      const date = `${month}-${String(d).padStart(2, '0')}`
      cells.push({ date, day: map[date] || null, n: d })
    }
    return cells
  }, [month, calDays])

  if (loading) return <p className="py-16 text-center text-slate-400">Loading FCPS Study Planner…</p>

  return (
    <div className="pb-10">
      <div className="flex flex-wrap items-start justify-between gap-4">
        <div>
          <p className="text-xs font-semibold uppercase tracking-wide text-teal-600">Premium · PHP Scheduler</p>
          <h2 className="font-display text-2xl font-bold text-navy dark:text-slate-100">FCPS Study Planner</h2>
          <p className="text-sm text-slate-500">Personalized plan powered by scheduling algorithms — no AI required.</p>
        </div>
        {plan && (
          <div className="flex flex-wrap gap-2">
            <button type="button" onClick={printPlan} className="btn-secondary text-xs"><FiPrinter className="mr-1 inline" /> Print / PDF</button>
            <button type="button" onClick={exportJson} className="btn-secondary text-xs"><FiDownload className="mr-1 inline" /> Download</button>
            <button type="button" onClick={regenerate} disabled={saving} className="btn-secondary text-xs"><FiRefreshCw className="mr-1 inline" /> Regenerate</button>
            <button type="button" onClick={resetPlan} className="btn-secondary text-xs text-red-600"><FiTrash2 className="mr-1 inline" /> Reset</button>
          </div>
        )}
      </div>

      <div className="mt-6 flex flex-wrap gap-2 border-b border-slate-100 pb-3 dark:border-slate-800">
        {(plan
          ? [['dashboard', 'Dashboard'], ['calendar', 'Calendar'], ['stats', 'Statistics'], ['search', 'Search'], ['setup', 'Edit inputs']]
          : [['setup', 'Create plan']]
        ).map(([id, label]) => (
          <button key={id} type="button" onClick={() => setTab(id)}
            className={`rounded-full px-4 py-1.5 text-sm font-semibold ${tab === id ? 'bg-teal-600 text-white' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300'}`}>
            {label}
          </button>
        ))}
      </div>

      {tab === 'setup' && (
        <div className="mt-6 space-y-5 rounded-2xl border border-slate-100 bg-white p-6 shadow-soft dark:border-slate-800 dark:bg-slate-900">
          <h3 className="font-bold text-navy dark:text-slate-100">{plan ? 'Update inputs & regenerate' : 'Build your FCPS study plan'}</h3>
          <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            {[
              ['exam_date', 'Exam date', 'date'],
              ['start_date', 'Study start date', 'date'],
              ['hours_per_day', 'Hours per day', 'number'],
              ['daily_mcq_target', 'Daily MCQ target', 'number'],
              ['daily_flashcard_target', 'Daily flashcard target', 'number'],
            ].map(([key, label, type]) => (
              <label key={key} className="block text-sm">
                <span className="text-xs font-semibold text-slate-500">{label}</span>
                <input type={type} className="input-field mt-1 w-full" value={form[key]}
                  min={type === 'number' ? 0.5 : undefined} step={key === 'hours_per_day' ? 0.5 : 1}
                  onChange={(e) => setForm({ ...form, [key]: type === 'number' ? Number(e.target.value) : e.target.value })} />
              </label>
            ))}
            <label className="block text-sm">
              <span className="text-xs font-semibold text-slate-500">Sessions per day</span>
              <select className="input-field mt-1 w-full" value={form.sessions_per_day}
                onChange={(e) => setForm({ ...form, sessions_per_day: Number(e.target.value) })}>
                <option value={1}>1 session</option>
                <option value={2}>2 sessions</option>
                <option value={3}>3 sessions</option>
              </select>
            </label>
            <label className="block text-sm">
              <span className="text-xs font-semibold text-slate-500">Preferred study time</span>
              <select className="input-field mt-1 w-full" value={form.preferred_time}
                onChange={(e) => setForm({ ...form, preferred_time: e.target.value })}>
                <option value="morning">Morning</option>
                <option value="afternoon">Afternoon</option>
                <option value="evening">Evening</option>
                <option value="night">Night</option>
              </select>
            </label>
            <label className="block text-sm">
              <span className="text-xs font-semibold text-slate-500">Revision preference</span>
              <select className="input-field mt-1 w-full" value={form.revision_preference}
                onChange={(e) => setForm({ ...form, revision_preference: e.target.value })}>
                {REVISION_OPTS.map((o) => <option key={o.value} value={o.value}>{o.label}</option>)}
              </select>
            </label>
          </div>
          <div>
            <p className="text-xs font-semibold text-slate-500">Preferred study days</p>
            <div className="mt-2 flex flex-wrap gap-2">
              {DAYS.map((d) => (
                <button key={d.id} type="button"
                  onClick={() => setForm((f) => ({
                    ...f,
                    preferred_days: f.preferred_days.includes(d.id)
                      ? f.preferred_days.filter((x) => x !== d.id)
                      : [...f.preferred_days, d.id],
                  }))}
                  className={`rounded-full px-3 py-1.5 text-xs font-semibold ${form.preferred_days.includes(d.id) ? 'bg-teal-600 text-white' : 'bg-slate-100 text-slate-600'}`}>
                  {d.label}
                </button>
              ))}
            </div>
          </div>
          {[
            ['subjects_completed', 'Subjects already completed'],
            ['subjects_remaining', 'Remaining subjects *'],
            ['subjects_weak', 'Weak subjects'],
            ['subjects_strong', 'Strong subjects'],
          ].map(([key, label]) => (
            <label key={key} className="block text-sm">
              <span className="text-xs font-semibold text-slate-500">{label}</span>
              <textarea rows={2} className="input-field mt-1 w-full" value={form[key]}
                onChange={(e) => setForm({ ...form, [key]: e.target.value })} />
            </label>
          ))}
          <button type="button" disabled={saving || !form.exam_date} onClick={generate} className="btn-primary disabled:opacity-50">
            {saving ? 'Generating…' : plan ? 'Regenerate plan' : 'Generate study plan'}
          </button>
        </div>
      )}

      {tab === 'dashboard' && plan && dash && (
        <div className="mt-6 space-y-6">
          {note && <Alert>{note}</Alert>}
          {dash.strategy_notes && <p className="text-sm text-slate-600 dark:text-slate-300">{dash.strategy_notes}</p>}

          <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div className="rounded-2xl border border-teal-100 bg-gradient-to-br from-teal-50 to-white p-5 shadow-soft dark:border-teal-900 dark:from-teal-950 dark:to-slate-900">
              <p className="text-xs font-semibold uppercase text-teal-600">Exam countdown</p>
              <p className="mt-2 text-4xl font-bold text-navy dark:text-white">{dash.exam_countdown_days}</p>
              <p className="text-xs text-slate-500">days · {dash.exam_date}</p>
            </div>
            <div className="rounded-2xl border border-slate-100 bg-white p-5 shadow-soft dark:border-slate-800 dark:bg-slate-900">
              <p className="text-xs font-semibold uppercase text-slate-400">Study streak</p>
              <p className="mt-2 flex items-center gap-2 text-4xl font-bold text-navy dark:text-white"><FiZap className="text-amber-500" /> {dash.study_streak}</p>
            </div>
            <div className="rounded-2xl border border-slate-100 bg-white p-5 shadow-soft dark:border-slate-800 dark:bg-slate-900 flex justify-around">
              <Ring value={dash.weekly_progress} label="Weekly" />
              <Ring value={dash.monthly_progress} label="Monthly" tone="amber" />
            </div>
            <div className="rounded-2xl border border-slate-100 bg-white p-5 shadow-soft dark:border-slate-800 dark:bg-slate-900 flex justify-center">
              <Ring value={dash.completion_pct} label="Overall" tone="emerald" />
            </div>
          </div>

          <div className="grid gap-3 sm:grid-cols-3">
            <div className="rounded-xl bg-emerald-50 px-4 py-3 dark:bg-emerald-950/40"><p className="text-2xl font-bold text-emerald-700">{dash.completed_tasks}</p><p className="text-xs text-slate-500">Completed</p></div>
            <div className="rounded-xl bg-amber-50 px-4 py-3 dark:bg-amber-950/40"><p className="text-2xl font-bold text-amber-700">{dash.pending_tasks}</p><p className="text-xs text-slate-500">Pending</p></div>
            <div className="rounded-xl bg-rose-50 px-4 py-3 dark:bg-rose-950/40"><p className="text-2xl font-bold text-rose-700">{dash.missed_tasks}</p><p className="text-xs text-slate-500">Missed</p></div>
          </div>

          <div className="grid gap-4 lg:grid-cols-2">
            {[
              ["Today's study", dash.today?.study, FiBookOpen],
              ["Today's MCQs", dash.today?.mcqs, FiTarget],
              ["Today's flashcards", dash.today?.flashcards, FiTrendingUp],
              ["Today's revision", dash.today?.revision, FiRefreshCw],
            ].map(([title, items, Icon]) => (
              <div key={title} className="rounded-2xl border border-slate-100 bg-white p-5 shadow-soft dark:border-slate-800 dark:bg-slate-900">
                <div className="flex items-center gap-2"><Icon className="text-teal-600" /><h3 className="font-bold text-navy dark:text-slate-100">{title}</h3></div>
                {!items?.length ? (
                  <p className="mt-3 text-sm text-slate-400">Nothing scheduled today.</p>
                ) : (
                  <ul className="mt-3 space-y-2">
                    {items.map((t) => (
                      <li key={t.id} className="flex items-center gap-2 text-sm">
                        <button type="button" onClick={() => toggleTask(t)}
                          className={`flex h-5 w-5 items-center justify-center rounded-full border-2 ${t.status === 'completed' ? 'border-emerald-500 bg-emerald-500 text-white' : 'border-slate-300'}`}>
                          {t.status === 'completed' && <FiCheck size={12} />}
                        </button>
                        <span className={t.status === 'completed' ? 'text-slate-400 line-through' : 'text-navy dark:text-slate-200'}>{t.title}</span>
                      </li>
                    ))}
                  </ul>
                )}
              </div>
            ))}
          </div>

          <div className="flex flex-wrap gap-3">
            <button type="button" onClick={handleMissed} className="btn-secondary text-sm"><FiClock className="mr-1 inline" /> Reschedule missed</button>
            <button type="button" onClick={resetToday} className="btn-secondary text-sm">Reset today</button>
            <button type="button" onClick={() => setTab('calendar')} className="btn-secondary text-sm"><FiCalendar className="mr-1 inline" /> Calendar</button>
          </div>

          {(dash.weekly_goals || []).length > 0 && (
            <div className="rounded-2xl border border-slate-100 bg-white p-6 shadow-soft dark:border-slate-800 dark:bg-slate-900">
              <h3 className="font-bold text-navy dark:text-slate-100">Upcoming milestones</h3>
              <ul className="mt-3 space-y-2 text-sm text-slate-600 dark:text-slate-300">
                {dash.weekly_goals.slice(0, 4).map((g) => (
                  <li key={g.week} className="flex gap-2"><span className="font-semibold text-teal-600">W{g.week}</span> {g.milestone} — {g.focus}</li>
                ))}
              </ul>
            </div>
          )}

          {(dash.badges || []).length > 0 && (
            <div className="rounded-2xl border border-slate-100 bg-white p-6 shadow-soft dark:border-slate-800 dark:bg-slate-900">
              <h3 className="font-bold text-navy dark:text-slate-100"><FiAward className="mr-1 inline text-amber-500" /> Achievements</h3>
              <div className="mt-3 flex flex-wrap gap-2">
                {dash.badges.map((b) => (
                  <span key={b.id} className="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-800 dark:bg-amber-950 dark:text-amber-200">{b.title}</span>
                ))}
              </div>
            </div>
          )}
        </div>
      )}

      {tab === 'calendar' && plan && (
        <div className="mt-6 rounded-2xl border border-slate-100 bg-white p-6 shadow-soft dark:border-slate-800 dark:bg-slate-900">
          <div className="flex flex-wrap items-center justify-between gap-3">
            <h3 className="font-bold text-navy dark:text-slate-100">Interactive calendar</h3>
            <input type="month" value={month} onChange={(e) => setMonth(e.target.value)} className="input-field text-sm" />
          </div>
          <div className="mt-4 grid grid-cols-7 gap-2 text-center text-xs font-semibold text-slate-400">
            {['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'].map((d) => <div key={d}>{d}</div>)}
          </div>
          <div className="mt-2 grid grid-cols-7 gap-2">
            {calGrid.map((cell, i) => {
              if (!cell) return <div key={`e-${i}`} />
              const d = cell.day
              const tone = !d ? 'border-slate-50 bg-slate-50/50 text-slate-300'
                : d.day_status === 'completed' ? 'border-emerald-200 bg-emerald-50'
                  : d.day_status === 'missed' ? 'border-rose-200 bg-rose-50'
                    : d.day_status === 'partial' ? 'border-amber-200 bg-amber-50'
                      : 'border-teal-100 bg-white hover:border-teal-300 dark:bg-slate-950'
              return (
                <button key={cell.date} type="button" disabled={!d} onClick={() => d && openDay(cell.date)}
                  className={`min-h-[4.5rem] rounded-xl border p-1.5 text-left ${tone}`}>
                  <span className="text-xs font-bold text-navy dark:text-slate-100">{cell.n}</span>
                  {d && (
                    <div className="mt-1 space-y-0.5 text-[10px] text-slate-600">
                      <p className="truncate">{(d.topics || [])[0] || 'Study'}</p>
                      <p>MCQ {d.mcq_target} · FC {d.flashcard_target}</p>
                      <p className="capitalize text-slate-400">{d.day_status}</p>
                    </div>
                  )}
                </button>
              )
            })}
          </div>
        </div>
      )}

      {tab === 'stats' && dash && (
        <div className="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
          {[
            ['Days remaining', dash.days_remaining],
            ['Topics remaining', dash.topics_remaining],
            ['Lectures remaining', dash.lectures_remaining],
            ['Revision completed', dash.revision_completed],
            ['MCQs completed', dash.mcqs_completed],
            ['Flashcards completed', dash.flashcards_completed],
            ['Study hours done', dash.study_hours_completed],
            ['Study hours left', dash.study_hours_remaining],
            ['Overall %', `${dash.completion_pct}%`],
          ].map(([label, value]) => (
            <div key={label} className="rounded-2xl border border-slate-100 bg-white p-5 shadow-soft dark:border-slate-800 dark:bg-slate-900">
              <p className="text-xs font-semibold uppercase text-slate-400">{label}</p>
              <p className="mt-2 text-3xl font-bold text-navy dark:text-white">{value}</p>
            </div>
          ))}
          {(dash.subject_progress || []).length > 0 && (
            <div className="sm:col-span-2 lg:col-span-3 rounded-2xl border border-slate-100 bg-white p-6 shadow-soft dark:border-slate-800 dark:bg-slate-900">
              <h3 className="font-bold text-navy dark:text-slate-100">Subject completion</h3>
              <ul className="mt-4 space-y-3">
                {dash.subject_progress.map((s) => (
                  <li key={s.subject}>
                    <div className="flex justify-between text-sm"><span>{s.subject}</span><span>{s.completion_pct}%</span></div>
                    <div className="mt-1 h-2 rounded-full bg-slate-100"><div className="h-2 rounded-full bg-teal-500" style={{ width: `${s.completion_pct}%` }} /></div>
                  </li>
                ))}
              </ul>
            </div>
          )}
        </div>
      )}

      {tab === 'search' && (
        <div className="mt-6 rounded-2xl border border-slate-100 bg-white p-6 shadow-soft dark:border-slate-800 dark:bg-slate-900">
          <div className="flex gap-2">
            <input className="input-field flex-1" placeholder="Search tasks…" value={searchQ} onChange={(e) => setSearchQ(e.target.value)} />
            <button type="button" onClick={runSearch} className="btn-primary text-sm"><FiSearch className="mr-1 inline" /> Search</button>
          </div>
          <ul className="mt-4 space-y-2">
            {searchItems.map((t) => (
              <li key={t.id} className="flex flex-wrap items-center justify-between gap-2 rounded-xl border border-slate-100 px-3 py-2 text-sm dark:border-slate-800">
                <span>{t.plan_date} · {t.title}</span>
                <span className="capitalize text-slate-400">{t.status}</span>
              </li>
            ))}
          </ul>
        </div>
      )}

      <Modal open={!!dayModal} onClose={() => setDayModal(null)} title={dayModal?.date ? `Plan · ${dayModal.date}` : 'Day'} size="lg">
        {dayModal && (
          <div className="space-y-4">
            <ul className="space-y-2">
              {(dayModal.tasks || []).map((t) => (
                <li key={t.id} className="flex flex-wrap items-center gap-2 rounded-xl border border-slate-100 px-3 py-2 text-sm dark:border-slate-800">
                  <button type="button" onClick={() => toggleTask(t)}
                    className={`flex h-6 w-6 items-center justify-center rounded-full border-2 ${t.status === 'completed' ? 'border-emerald-500 bg-emerald-500 text-white' : 'border-slate-300'}`}>
                    {t.status === 'completed' && <FiCheck size={14} />}
                  </button>
                  <div className="min-w-0 flex-1">
                    <p className={t.status === 'completed' ? 'text-slate-400 line-through' : ''}>{t.title}</p>
                    <p className="text-xs capitalize text-slate-400">{t.task_type} · {t.status}</p>
                  </div>
                  <button type="button" className="text-xs font-semibold text-slate-500 hover:text-amber-600" onClick={() => setTask(t, 'skipped')}>
                    <FiSkipForward className="inline" /> Skip
                  </button>
                  <button type="button" className="text-xs font-semibold text-teal-600" onClick={() => { setRescheduleId(t.id); setRescheduleDate(dayModal.date) }}>
                    Reschedule
                  </button>
                </li>
              ))}
            </ul>
            {rescheduleId && (
              <div className="flex flex-wrap items-end gap-2 rounded-xl bg-slate-50 p-3 dark:bg-slate-800">
                <label className="text-sm">
                  <span className="text-xs font-semibold text-slate-500">New date</span>
                  <input type="date" className="input-field mt-1" value={rescheduleDate} onChange={(e) => setRescheduleDate(e.target.value)} />
                </label>
                <button type="button" onClick={doReschedule} className="btn-primary text-sm">Move task</button>
              </div>
            )}
          </div>
        )}
      </Modal>
    </div>
  )
}
