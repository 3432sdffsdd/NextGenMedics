import { useEffect, useState } from 'react'
import { FiCalendar, FiCheck, FiClock } from 'react-icons/fi'
import { premiumStudyService } from '../../services/api'
import Alert from '../../components/dashboard/Alert'

export default function StudyPlanner() {
  const [plan, setPlan] = useState(null)
  const [tasks, setTasks] = useState([])
  const [examDate, setExamDate] = useState('')
  const [hours, setHours] = useState(2)
  const [loading, setLoading] = useState(true)
  const [saving, setSaving] = useState(false)

  const load = () => {
    setLoading(true)
    premiumStudyService.getStudyPlan()
      .then(({ data }) => {
        const p = data.data?.plan
        setPlan(p)
        setTasks(data.data?.tasks || [])
        if (p) {
          setExamDate(p.exam_date?.slice(0, 10) || '')
          setHours(parseFloat(p.hours_per_day) || 2)
        }
      })
      .finally(() => setLoading(false))
  }

  useEffect(load, [])

  const save = async () => {
    setSaving(true)
    try {
      const { data } = await premiumStudyService.saveStudyPlan({ exam_date: examDate, hours_per_day: hours })
      setPlan(data.data?.plan)
      setTasks(data.data?.tasks || [])
    } finally {
      setSaving(false)
    }
  }

  const toggleTask = async (task) => {
    const status = task.status === 'completed' ? 'pending' : 'completed'
    await premiumStudyService.completeTask(task.id, status)
    load()
  }

  const byDate = tasks.reduce((acc, t) => {
    const d = t.task_date
    if (!acc[d]) acc[d] = []
    acc[d].push(t)
    return acc
  }, {})

  return (
    <div>
      <h2 className="font-display text-2xl font-bold text-navy">Smart Study Planner</h2>
      <p className="text-sm text-slate-500">Set your FCPS exam date and daily study hours. We prioritize weak topics and build a personalized plan.</p>

      <div className="mt-6 grid gap-4 rounded-2xl border border-slate-100 bg-white p-6 shadow-soft sm:grid-cols-3">
        <div>
          <label className="text-xs font-semibold uppercase text-slate-500">FCPS Exam Date</label>
          <input type="date" value={examDate} min={new Date().toISOString().slice(0, 10)} onChange={(e) => setExamDate(e.target.value)} className="input-field mt-1 w-full text-sm" />
        </div>
        <div>
          <label className="text-xs font-semibold uppercase text-slate-500">Hours per day</label>
          <input type="number" min={0.5} max={16} step={0.5} value={hours} onChange={(e) => setHours(parseFloat(e.target.value) || 2)} className="input-field mt-1 w-full text-sm" />
        </div>
        <div className="flex items-end">
          <button type="button" onClick={save} disabled={saving || !examDate} className="btn-primary w-full text-sm disabled:opacity-50">
            {saving ? 'Generating…' : 'Generate plan'}
          </button>
        </div>
      </div>

      {loading ? (
        <p className="mt-8 text-center text-slate-400">Loading plan…</p>
      ) : !plan ? (
        <div className="mt-6"><Alert>Enter your exam date and study hours above to generate your personalized plan.</Alert></div>
      ) : (
        <div className="mt-8 space-y-6">
          <div className="flex flex-wrap gap-4 text-sm text-slate-600">
            <span className="flex items-center gap-1"><FiCalendar /> Exam: {new Date(plan.exam_date).toLocaleDateString()}</span>
            <span className="flex items-center gap-1"><FiClock /> {plan.hours_per_day} hrs/day</span>
          </div>
          {Object.keys(byDate).length === 0 ? (
            <Alert>No tasks scheduled yet. Regenerate your plan.</Alert>
          ) : (
            Object.entries(byDate).map(([date, dayTasks]) => {
              const isToday = date === new Date().toISOString().slice(0, 10)
              const done = dayTasks.filter((t) => t.status === 'completed').length
              return (
                <div key={date} className={`rounded-2xl border p-5 shadow-soft ${isToday ? 'border-primary/30 bg-primary/5' : 'border-slate-100 bg-white'}`}>
                  <div className="flex items-center justify-between">
                    <h3 className="font-bold text-navy">{isToday ? 'Today' : new Date(date).toLocaleDateString(undefined, { weekday: 'long', month: 'short', day: 'numeric' })}</h3>
                    <span className="text-xs text-slate-500">{done}/{dayTasks.length} done</span>
                  </div>
                  <ul className="mt-4 space-y-2">
                    {dayTasks.map((t) => (
                      <li key={t.id} className="flex items-center gap-3 rounded-xl bg-white/80 px-4 py-3 text-sm">
                        <button
                          type="button"
                          onClick={() => toggleTask(t)}
                          className={`flex h-6 w-6 shrink-0 items-center justify-center rounded-full border-2 ${t.status === 'completed' ? 'border-green-500 bg-green-500 text-white' : 'border-slate-300'}`}
                        >
                          {t.status === 'completed' && <FiCheck size={14} />}
                        </button>
                        <span className={t.status === 'completed' ? 'text-slate-400 line-through' : 'text-navy'}>{t.title}</span>
                        <span className="ml-auto text-xs capitalize text-slate-400">{t.task_type}</span>
                      </li>
                    ))}
                  </ul>
                </div>
              )
            })
          )}
        </div>
      )}
    </div>
  )
}
