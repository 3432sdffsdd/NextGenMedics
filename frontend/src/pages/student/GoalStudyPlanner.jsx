import { useEffect, useMemo, useState } from 'react'
import {
  FiCheck, FiCalendar, FiTarget, FiBookOpen, FiRefreshCw, FiZap, FiDownload,
  FiTrash2, FiClock, FiAward, FiChevronDown, FiChevronRight,
} from 'react-icons/fi'
import { goalStudyPlannerService } from '../../services/api'
import { useToast } from '../../context/ToastContext'
import { Modal } from '../../components/ui'

const DAYS = [
  { id: 'monday', label: 'Mon' }, { id: 'tuesday', label: 'Tue' },
  { id: 'wednesday', label: 'Wed' }, { id: 'thursday', label: 'Thu' },
  { id: 'friday', label: 'Fri' }, { id: 'saturday', label: 'Sat' },
  { id: 'sunday', label: 'Sun' },
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

const emptyTargets = () => ({
  start_date: new Date().toISOString().slice(0, 10),
  target_date: new Date(Date.now() + 30 * 86400000).toISOString().slice(0, 10),
  exam_date: '',
  hours_per_day: 3,
  preferred_days: ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'],
  sessions_per_day: 2,
  preferred_time: 'evening',
  daily_mcq_target: 40,
  daily_flashcard_target: 30,
  revision_preference: 'every_7_days',
})

export default function GoalStudyPlanner() {
  const toast = useToast()
  const [loading, setLoading] = useState(true)
  const [saving, setSaving] = useState(false)
  const [step, setStep] = useState(1)
  const [tab, setTab] = useState('dashboard')
  const [catalog, setCatalog] = useState({ subjects: [], goal_types: [] })
  const [goalType, setGoalType] = useState('full_syllabus')
  const [goalTitle, setGoalTitle] = useState('My FCPS Study Plan')
  const [selection, setSelection] = useState([])
  const [expanded, setExpanded] = useState({})
  const [targets, setTargets] = useState(emptyTargets)
  const [plan, setPlan] = useState(null)
  const [dash, setDash] = useState(null)
  const [month, setMonth] = useState(() => new Date().toISOString().slice(0, 7))
  const [calDays, setCalDays] = useState([])
  const [dayModal, setDayModal] = useState(null)
  const [challengeForm, setChallengeForm] = useState({ title: '', end_date: '', target_value: 50 })

  const loadDash = async () => {
    setLoading(true)
    try {
      const { data } = await goalStudyPlannerService.dashboard()
      const d = data.data
      if (!d?.has_plan) {
        setPlan(null)
        setDash(null)
        setCatalog(d.catalog || { subjects: [], goal_types: [] })
        setStep(1)
      } else {
        setPlan(d.plan)
        setDash(d.dashboard)
        setTab('dashboard')
      }
    } catch {
      setPlan(null)
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => { loadDash() }, [])

  useEffect(() => {
    if (!plan) return
    goalStudyPlannerService.catalog().then(({ data }) => setCatalog(data.data || { subjects: [], goal_types: [] })).catch(() => {})
  }, [plan])

  useEffect(() => {
    if (!plan) return
    goalStudyPlannerService.calendar(month)
      .then(({ data }) => setCalDays(data.data?.days || []))
      .catch(() => setCalDays([]))
  }, [plan, month])

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

  const generate = async () => {
    setSaving(true)
    try {
      const { data } = await goalStudyPlannerService.generate({
        goal_type: goalType,
        goal_title: goalTitle,
        selection,
        ...targets,
        exam_date: targets.exam_date || undefined,
      })
      setPlan(data.data?.plan)
      setDash(data.data?.dashboard)
      setTab('dashboard')
      toast.success('Study plan generated from your LMS content')
    } catch (err) {
      toast.error(err.response?.data?.message || err.message)
    } finally {
      setSaving(false)
    }
  }

  const setTask = async (task, status) => {
    const { data } = await goalStudyPlannerService.setTask(task.id, status)
    setDayModal(data.data)
    loadDash()
  }

  const openDay = async (date) => {
    const { data } = await goalStudyPlannerService.day(date)
    setDayModal(data.data)
  }

  const handleMissed = async () => {
    await goalStudyPlannerService.handleMissed()
    toast.success('Missed tasks redistributed')
    loadDash()
  }

  const createChallenge = async () => {
    if (!challengeForm.title || !challengeForm.end_date) return
    await goalStudyPlannerService.createChallenge({
      title: challengeForm.title,
      end_date: challengeForm.end_date,
      target_value: Number(challengeForm.target_value) || 50,
      challenge_type: 'deadline',
      start_date: new Date().toISOString().slice(0, 10),
    })
    toast.success('Challenge created')
    loadDash()
  }

  const exportPlan = async () => {
    const { data } = await goalStudyPlannerService.export()
    const blob = new Blob([JSON.stringify(data.data, null, 2)], { type: 'application/json' })
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = 'study-plan.json'
    a.click()
    URL.revokeObjectURL(url)
  }

  const printPlan = async () => {
    const { data } = await goalStudyPlannerService.export()
    const cal = data.data?.calendar || []
    const w = window.open('', '_blank')
    if (!w) return
    const rows = cal.map((d) => `<tr><td>${d.plan_date}</td><td>${(d.tasks || []).map((t) => t.title).join('; ')}</td><td>${d.day_status}</td></tr>`).join('')
    w.document.write(`<!DOCTYPE html><html><head><title>Study Plan</title>
      <style>body{font-family:Segoe UI,sans-serif;padding:24px}table{width:100%;border-collapse:collapse}td,th{border:1px solid #ddd;padding:8px;font-size:12px}</style></head>
      <body><h1>${data.data?.plan?.goal_title || 'Study Plan'}</h1>
      <table><thead><tr><th>Date</th><th>Tasks</th><th>Status</th></tr></thead><tbody>${rows}</tbody></table>
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

  if (loading) return <p className="py-16 text-center text-slate-400">Loading Study Planner…</p>

  if (!plan) {
    return (
      <div className="pb-10">
        <p className="text-xs font-semibold uppercase text-teal-600">Premium · Goal-Based</p>
        <h2 className="font-display text-2xl font-bold text-navy">Study Planner</h2>
        <p className="text-sm text-slate-500">Build a plan from your enrolled lectures, quizzes, notes & flashcards.</p>

        <div className="mt-4 flex gap-2">
          {[1, 2, 3, 4].map((s) => (
            <button key={s} type="button" onClick={() => setStep(s)}
              className={`rounded-full px-3 py-1 text-xs font-semibold ${step === s ? 'bg-teal-600 text-white' : 'bg-slate-100 text-slate-600'}`}>
              Step {s}
            </button>
          ))}
        </div>

        {step === 1 && (
          <div className="mt-6 space-y-3 rounded-2xl border border-slate-100 bg-white p-6 shadow-soft">
            <h3 className="font-bold text-navy">Select your goal</h3>
            <input className="input-field w-full text-sm" value={goalTitle} onChange={(e) => setGoalTitle(e.target.value)} placeholder="Plan title" />
            <div className="grid gap-2 sm:grid-cols-2">
              {(catalog.goal_types || []).map((g) => (
                <button key={g.value} type="button" onClick={() => setGoalType(g.value)}
                  className={`rounded-xl border px-4 py-3 text-left text-sm ${goalType === g.value ? 'border-teal-500 bg-teal-50 font-semibold text-teal-800' : 'border-slate-100'}`}>
                  {g.label}
                </button>
              ))}
            </div>
            <button type="button" className="btn-primary text-sm" onClick={() => setStep(goalType === 'full_syllabus' || goalType === 'revision_only' || goalType === 'mock_exam' ? 3 : 2)}>Next</button>
          </div>
        )}

        {step === 2 && (
          <div className="mt-6 space-y-4 rounded-2xl border border-slate-100 bg-white p-6 shadow-soft">
            <h3 className="font-bold text-navy">Select content from your courses</h3>
            {!catalog.subjects?.length && <p className="text-sm text-slate-500">No enrolled courses found.</p>}
            {catalog.subjects?.map((sub) => (
              <div key={sub.course_id} className="rounded-xl border border-slate-100 p-4">
                <div className="flex flex-wrap items-center justify-between gap-2">
                  <button type="button" className="flex items-center gap-2 font-bold text-navy" onClick={() => setExpanded((e) => ({ ...e, [sub.course_id]: !e[sub.course_id] }))}>
                    {expanded[sub.course_id] ? <FiChevronDown /> : <FiChevronRight />} {sub.title}
                  </button>
                  <button type="button" className="text-xs font-semibold text-teal-600" onClick={() => toggleSelect({ type: 'subject', ref_id: sub.course_id, course_id: sub.course_id, title: sub.title })}>
                    {isSelected('subject', sub.course_id, sub.course_id) ? '✓ Entire subject' : 'Select entire subject'}
                  </button>
                </div>
                <p className="mt-1 text-xs text-slate-400">
                  {sub.totals.lectures} lectures · {sub.totals.quizzes} quizzes · {sub.totals.notes} notes · {sub.totals.flashcards} flashcards
                </p>
                {expanded[sub.course_id] && (
                  <div className="mt-3 space-y-2 border-t border-slate-50 pt-3">
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
                                onChange={() => toggleSelect({ type: 'video', ref_id: v.id, course_id: sub.course_id, lecture_id: lec.id, title: v.title })} /> Video: {v.title}
                            </label>
                          ))}
                          {lec.notes.map((n) => (
                            <label key={n.id} className="inline-flex items-center gap-1 rounded bg-white px-2 py-0.5">
                              <input type="checkbox" checked={isSelected('note', n.id, sub.course_id)}
                                onChange={() => toggleSelect({ type: 'note', ref_id: n.id, course_id: sub.course_id, lecture_id: lec.id, title: n.title })} /> Notes: {n.title}
                            </label>
                          ))}
                          {lec.flashcard_count > 0 && (
                            <label className="inline-flex items-center gap-1 rounded bg-white px-2 py-0.5">
                              <input type="checkbox" checked={isSelected('flashcard_set', lec.id, sub.course_id)}
                                onChange={() => toggleSelect({ type: 'flashcard_set', ref_id: lec.id, course_id: sub.course_id, title: lec.title })} /> Flashcards ({lec.flashcard_count})
                            </label>
                          )}
                          {lec.has_revision && (
                            <label className="inline-flex items-center gap-1 rounded bg-white px-2 py-0.5">
                              <input type="checkbox" checked={isSelected('revision', lec.id, sub.course_id)}
                                onChange={() => toggleSelect({ type: 'revision', ref_id: lec.id, course_id: sub.course_id, title: lec.title })} /> Revision pack
                            </label>
                          )}
                        </div>
                      </div>
                    ))}
                    {sub.quizzes.map((q) => (
                      <label key={q.id} className="flex items-center gap-2 rounded-lg bg-amber-50 px-3 py-2 text-sm">
                        <input type="checkbox" checked={isSelected('quiz', q.id, sub.course_id)}
                          onChange={() => toggleSelect({ type: 'quiz', ref_id: q.id, course_id: sub.course_id, title: q.title })} />
                        Quiz: {q.title} ({q.question_count} Qs)
                      </label>
                    ))}
                  </div>
                )}
              </div>
            ))}
            <div className="flex gap-2">
              <button type="button" className="btn-secondary text-sm" onClick={() => setStep(1)}>Back</button>
              <button type="button" className="btn-primary text-sm" onClick={() => setStep(3)}>Next ({selection.length} selected)</button>
            </div>
          </div>
        )}

        {step === 3 && (
          <div className="mt-6 space-y-4 rounded-2xl border border-slate-100 bg-white p-6 shadow-soft">
            <h3 className="font-bold text-navy">Set target & schedule</h3>
            <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
              {[
                ['start_date', 'Start date', 'date'],
                ['target_date', 'Target completion', 'date'],
                ['exam_date', 'Exam date (optional)', 'date'],
                ['hours_per_day', 'Hours / day', 'number'],
                ['daily_mcq_target', 'Daily MCQ target', 'number'],
                ['daily_flashcard_target', 'Daily flashcard target', 'number'],
              ].map(([k, label, type]) => (
                <label key={k} className="text-sm">
                  <span className="text-xs font-semibold text-slate-500">{label}</span>
                  <input type={type} className="input-field mt-1 w-full" value={targets[k]}
                    onChange={(e) => setTargets({ ...targets, [k]: type === 'number' ? Number(e.target.value) : e.target.value })} />
                </label>
              ))}
              <label className="text-sm">
                <span className="text-xs font-semibold text-slate-500">Sessions / day</span>
                <select className="input-field mt-1 w-full" value={targets.sessions_per_day}
                  onChange={(e) => setTargets({ ...targets, sessions_per_day: Number(e.target.value) })}>
                  <option value={1}>1</option><option value={2}>2</option><option value={3}>3</option>
                </select>
              </label>
              <label className="text-sm">
                <span className="text-xs font-semibold text-slate-500">Preferred time</span>
                <select className="input-field mt-1 w-full" value={targets.preferred_time}
                  onChange={(e) => setTargets({ ...targets, preferred_time: e.target.value })}>
                  <option value="morning">Morning</option><option value="afternoon">Afternoon</option>
                  <option value="evening">Evening</option><option value="night">Night</option>
                </select>
              </label>
              <label className="text-sm">
                <span className="text-xs font-semibold text-slate-500">Revision interval</span>
                <select className="input-field mt-1 w-full" value={targets.revision_preference}
                  onChange={(e) => setTargets({ ...targets, revision_preference: e.target.value })}>
                  <option value="every_3_days">Every 3 days</option>
                  <option value="every_5_days">Every 5 days</option>
                  <option value="every_7_days">Every 7 days</option>
                  <option value="every_sunday">Every Sunday</option>
                  <option value="after_each_subject">After every subject</option>
                </select>
              </label>
            </div>
            <div className="flex flex-wrap gap-2">
              {DAYS.map((d) => (
                <button key={d.id} type="button"
                  onClick={() => setTargets((t) => ({
                    ...t,
                    preferred_days: t.preferred_days.includes(d.id)
                      ? t.preferred_days.filter((x) => x !== d.id) : [...t.preferred_days, d.id],
                  }))}
                  className={`rounded-full px-3 py-1 text-xs font-semibold ${targets.preferred_days.includes(d.id) ? 'bg-teal-600 text-white' : 'bg-slate-100'}`}>
                  {d.label}
                </button>
              ))}
            </div>
            <div className="flex gap-2">
              <button type="button" className="btn-secondary text-sm" onClick={() => setStep(2)}>Back</button>
              <button type="button" className="btn-primary text-sm" onClick={() => setStep(4)}>Review & generate</button>
            </div>
          </div>
        )}

        {step === 4 && (
          <div className="mt-6 space-y-4 rounded-2xl border border-slate-100 bg-white p-6 shadow-soft">
            <h3 className="font-bold text-navy">Generate plan</h3>
            <p className="text-sm text-slate-600">Goal: <strong>{goalTitle}</strong> ({goalType.replace(/_/g, ' ')})</p>
            <p className="text-sm text-slate-600">{targets.start_date} → {targets.target_date} · {targets.hours_per_day}h/day · {targets.sessions_per_day} sessions</p>
            <p className="text-sm text-slate-500">{selection.length || (goalType === 'full_syllabus' ? 'All enrolled content' : 'Auto selection')} item(s)</p>
            <div className="flex gap-2">
              <button type="button" className="btn-secondary text-sm" onClick={() => setStep(3)}>Back</button>
              <button type="button" disabled={saving} className="btn-primary text-sm disabled:opacity-50" onClick={generate}>
                {saving ? 'Generating…' : 'Generate study plan'}
              </button>
            </div>
          </div>
        )}
      </div>
    )
  }

  return (
    <div className="pb-10">
      <div className="flex flex-wrap items-start justify-between gap-4">
        <div>
          <p className="text-xs font-semibold uppercase text-teal-600">Premium · Goal-Based</p>
          <h2 className="font-display text-2xl font-bold text-navy">{plan.goal_title}</h2>
          <p className="text-sm text-slate-500">{plan.start_date} → {plan.target_date} · {plan.goal_type.replace(/_/g, ' ')}</p>
        </div>
        <div className="flex flex-wrap gap-2">
          <button type="button" onClick={printPlan} className="btn-secondary text-xs">Print / PDF</button>
          <button type="button" onClick={exportPlan} className="btn-secondary text-xs"><FiDownload className="mr-1 inline" /> Download</button>
          <button type="button" onClick={async () => { await goalStudyPlannerService.reset(); setPlan(null); loadDash() }} className="btn-secondary text-xs text-red-600"><FiTrash2 className="mr-1 inline" /> Reset</button>
        </div>
      </div>

      <div className="mt-4 flex flex-wrap gap-2">
        {['dashboard', 'calendar', 'subjects', 'challenges', 'reports'].map((t) => (
          <button key={t} type="button" onClick={() => setTab(t)}
            className={`rounded-full px-3 py-1 text-xs font-semibold capitalize ${tab === t ? 'bg-teal-600 text-white' : 'bg-slate-100 text-slate-600'}`}>
            {t}
          </button>
        ))}
      </div>

      {tab === 'dashboard' && dash && (
        <div className="mt-6 space-y-6">
          <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div className="rounded-2xl border border-teal-100 bg-teal-50/50 p-5">
              <p className="text-xs font-semibold uppercase text-teal-600">Days to target</p>
              <p className="mt-1 text-4xl font-bold text-navy">{dash.target_countdown_days}</p>
            </div>
            <div className="rounded-2xl border border-slate-100 bg-white p-5"><p className="text-xs text-slate-400">Streak</p><p className="mt-1 flex items-center gap-2 text-3xl font-bold"><FiZap className="text-amber-500" />{dash.study_streak}</p></div>
            <div className="rounded-2xl border border-slate-100 bg-white p-5 flex justify-around">
              <Ring value={dash.weekly_progress} label="Weekly" />
              <Ring value={dash.completion_pct} label="Overall" />
            </div>
            <div className="rounded-2xl border border-slate-100 bg-white p-5 grid grid-cols-3 gap-2 text-center">
              <div><p className="text-xl font-bold text-emerald-600">{dash.completed_tasks}</p><p className="text-[10px] text-slate-400">Done</p></div>
              <div><p className="text-xl font-bold text-amber-600">{dash.pending_tasks}</p><p className="text-[10px] text-slate-400">Pending</p></div>
              <div><p className="text-xl font-bold text-rose-600">{dash.missed_tasks}</p><p className="text-[10px] text-slate-400">Missed</p></div>
            </div>
          </div>

          <div className="grid gap-4 lg:grid-cols-2">
            {[
              ["Today's lectures", dash.today?.lectures, FiBookOpen],
              ["Today's notes", dash.today?.notes, FiBookOpen],
              ["Today's quizzes", dash.today?.quizzes, FiTarget],
              ["Today's flashcards", dash.today?.flashcards, FiRefreshCw],
              ["Today's revision", dash.today?.revision, FiClock],
              ["MCQ practice", dash.today?.mcq_practice, FiAward],
            ].map(([title, items, Icon]) => (
              <div key={title} className="rounded-2xl border border-slate-100 bg-white p-5 shadow-soft">
                <div className="flex items-center gap-2"><Icon className="text-teal-600" /><h3 className="font-bold text-navy">{title}</h3></div>
                {!items?.length ? <p className="mt-3 text-sm text-slate-400">Nothing scheduled</p> : (
                  <ul className="mt-3 space-y-2">
                    {items.map((t) => (
                      <li key={t.id} className="flex items-center gap-2 text-sm">
                        <button type="button" onClick={() => setTask(t, t.status === 'completed' ? 'pending' : 'completed')}
                          className={`flex h-5 w-5 items-center justify-center rounded-full border-2 ${t.status === 'completed' ? 'border-emerald-500 bg-emerald-500 text-white' : 'border-slate-300'}`}>
                          {t.status === 'completed' && <FiCheck size={12} />}
                        </button>
                        <span className={t.status === 'completed' ? 'text-slate-400 line-through' : 'text-navy'}>{t.title}</span>
                      </li>
                    ))}
                  </ul>
                )}
              </div>
            ))}
          </div>

          <div className="flex flex-wrap gap-2">
            <button type="button" onClick={handleMissed} className="btn-secondary text-sm"><FiClock className="mr-1 inline" /> Reschedule missed</button>
            <button type="button" onClick={async () => { await goalStudyPlannerService.resetToday(); loadDash() }} className="btn-secondary text-sm">Reset today</button>
            <button type="button" onClick={() => openDay(new Date().toISOString().slice(0, 10))} className="btn-secondary text-sm"><FiCalendar className="mr-1 inline" /> Today details</button>
          </div>
        </div>
      )}

      {tab === 'calendar' && (
        <div className="mt-6 rounded-2xl border border-slate-100 bg-white p-6 shadow-soft">
          <div className="flex justify-between"><h3 className="font-bold text-navy">Calendar</h3>
            <input type="month" className="input-field text-sm" value={month} onChange={(e) => setMonth(e.target.value)} /></div>
          <div className="mt-4 grid grid-cols-7 gap-2 text-center text-xs font-semibold text-slate-400">
            {['Sun','Mon','Tue','Wed','Thu','Fri','Sat'].map((d) => <div key={d}>{d}</div>)}
          </div>
          <div className="mt-2 grid grid-cols-7 gap-2">
            {calGrid.map((cell, i) => !cell ? <div key={`e${i}`} /> : (
              <button key={cell.date} type="button" disabled={!cell.day} onClick={() => cell.day && openDay(cell.date)}
                className={`min-h-[4rem] rounded-xl border p-1.5 text-left text-[10px] ${!cell.day ? 'border-slate-50 text-slate-300' : 'border-teal-100 hover:border-teal-300'}`}>
                <span className="font-bold text-navy">{cell.n}</span>
                {cell.day && <p className="mt-1 capitalize text-slate-500">{cell.day.day_status} · {cell.day.task_count} tasks</p>}
              </button>
            ))}
          </div>
        </div>
      )}

      {tab === 'subjects' && (
        <div className="mt-6 space-y-3">
          {(dash?.subject_progress || []).map((s) => (
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
          {!dash?.subject_progress?.length && <p className="text-sm text-slate-400">No subject progress yet.</p>}
        </div>
      )}

      {tab === 'challenges' && (
        <div className="mt-6 space-y-4">
          <div className="rounded-2xl border border-slate-100 bg-white p-5 shadow-soft">
            <h3 className="font-bold text-navy">Create challenge</h3>
            <div className="mt-3 grid gap-3 sm:grid-cols-3">
              <input className="input-field text-sm" placeholder="e.g. Complete Physiology in 15 days" value={challengeForm.title}
                onChange={(e) => setChallengeForm({ ...challengeForm, title: e.target.value })} />
              <input type="date" className="input-field text-sm" value={challengeForm.end_date}
                onChange={(e) => setChallengeForm({ ...challengeForm, end_date: e.target.value })} />
              <input type="number" className="input-field text-sm" placeholder="Target tasks" value={challengeForm.target_value}
                onChange={(e) => setChallengeForm({ ...challengeForm, target_value: e.target.value })} />
            </div>
            <button type="button" className="btn-primary mt-3 text-sm" onClick={createChallenge}>Start challenge</button>
          </div>
          {(dash?.challenges || []).map((c) => (
            <div key={c.id} className="rounded-2xl border border-amber-100 bg-amber-50/40 p-5">
              <div className="flex justify-between"><h3 className="font-bold text-navy">{c.title}</h3><span className="text-xs">{c.remaining_days} days left</span></div>
              <p className="mt-1 text-sm text-slate-600">{c.current_value}/{c.target_value} tasks · {c.progress_pct}%</p>
              <div className="mt-2 h-2 rounded-full bg-white"><div className="h-2 rounded-full bg-amber-500" style={{ width: `${c.progress_pct}%` }} /></div>
            </div>
          ))}
        </div>
      )}

      {tab === 'reports' && dash && (
        <div className="mt-6 grid gap-4 lg:grid-cols-2">
          <div className="rounded-2xl border border-slate-100 bg-white p-5 shadow-soft">
            <h3 className="font-bold text-navy">Weekly review</h3>
            <ul className="mt-3 space-y-1 text-sm text-slate-600">
              <li>Hours studied: {dash.weekly_review?.hours_studied}</li>
              <li>Lectures: {dash.weekly_review?.lectures_completed}</li>
              <li>Quizzes: {dash.weekly_review?.quizzes_completed}</li>
              <li>Flashcards: {dash.weekly_review?.flashcards_reviewed}</li>
              <li>Revision: {dash.weekly_review?.revision_completed}</li>
              <li>Remaining: {dash.weekly_review?.topics_remaining}</li>
              <li>Completion: {dash.weekly_review?.completion_pct}%</li>
            </ul>
          </div>
          <div className="rounded-2xl border border-slate-100 bg-white p-5 shadow-soft">
            <h3 className="font-bold text-navy">Monthly report</h3>
            <ul className="mt-3 space-y-1 text-sm text-slate-600">
              <li>Hours studied: {dash.monthly_report?.hours_studied}</li>
              <li>Lectures: {dash.monthly_report?.lectures_completed}</li>
              <li>Quizzes: {dash.monthly_report?.quizzes_completed}</li>
              <li>Flashcards: {dash.monthly_report?.flashcards_reviewed}</li>
              <li>Remaining: {dash.monthly_report?.topics_remaining}</li>
              <li>Completion: {dash.monthly_report?.completion_pct}%</li>
            </ul>
          </div>
        </div>
      )}

      <Modal open={!!dayModal} onClose={() => setDayModal(null)} title={dayModal?.date || 'Day'} size="lg">
        {dayModal && (
          <ul className="space-y-2">
            {(dayModal.tasks || []).map((t) => (
              <li key={t.id} className="flex flex-wrap items-center gap-2 rounded-xl border border-slate-100 px-3 py-2 text-sm">
                <button type="button" onClick={() => setTask(t, t.status === 'completed' ? 'pending' : 'completed')}
                  className={`flex h-6 w-6 items-center justify-center rounded-full border-2 ${t.status === 'completed' ? 'border-emerald-500 bg-emerald-500 text-white' : 'border-slate-300'}`}>
                  {t.status === 'completed' && <FiCheck size={14} />}
                </button>
                <span className="flex-1">{t.title}</span>
                <button type="button" className="text-xs text-amber-600" onClick={() => setTask(t, 'skipped')}>Skip</button>
              </li>
            ))}
          </ul>
        )}
      </Modal>
    </div>
  )
}
