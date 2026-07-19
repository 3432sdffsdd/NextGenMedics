import { useEffect, useMemo, useRef, useState } from 'react'
import { premiumStudyService, studentAiService } from '../../services/api'
import Alert from './Alert'

const LETTERS = ['A', 'B', 'C', 'D', 'E']

function qKey(q) {
  return String(q?.bank_id || q?.id || '')
}

function formatTime(seconds) {
  const m = Math.floor(seconds / 60)
  const s = seconds % 60
  return `${m}:${String(s).padStart(2, '0')}`
}

/**
 * Reusable single-best-answer MCQ player. Used by Daily Challenge, Question Bank,
 * My Mistakes, and lecture practice. Grades server-side and renders a full review afterwards.
 */
export default function McqPlayer({
  questions = [],
  source = 'practice',
  challengeId = null,
  challengeDay = null,
  dailySetId = null,
  timeLimitSeconds = null,
  title,
  onClose,
  onFinished,
}) {
  const [answers, setAnswers] = useState({})
  const [result, setResult] = useState(null)
  const [submitting, setSubmitting] = useState(false)
  const [error, setError] = useState('')
  const [timeLeft, setTimeLeft] = useState(timeLimitSeconds)
  const startRef = useRef(Date.now())
  const submittedRef = useRef(false)

  useEffect(() => {
    startRef.current = Date.now()
    submittedRef.current = false
    setTimeLeft(timeLimitSeconds)
  }, [questions, timeLimitSeconds])

  useEffect(() => {
    if (!timeLimitSeconds || result || submitting) return undefined
    const timer = setInterval(() => {
      setTimeLeft((t) => {
        if (t == null || t <= 1) {
          clearInterval(timer)
          if (!submittedRef.current) submit(true)
          return 0
        }
        return t - 1
      })
    }, 1000)
    return () => clearInterval(timer)
  }, [timeLimitSeconds, result, submitting, questions])

  const optionsFor = (q) => LETTERS.filter((l) => (q[`option_${l.toLowerCase()}`] || '').trim() !== '')
  const answeredCount = Object.keys(answers).length

  const reviewById = useMemo(() => {
    const map = {}
    ;(result?.review || []).forEach((r) => {
      map[r.mcq_id] = r
      if (r.bank_id) map[r.bank_id] = r
    })
    return map
  }, [result])

  const submit = async (auto = false) => {
    if (submittedRef.current || submitting) return
    submittedRef.current = true
    setError('')
    setSubmitting(true)
    const timeSpent = Math.round((Date.now() - startRef.current) / 1000)
    try {
      const payload = {
        source,
        challenge_id: challengeId,
        challenge_day: challengeDay,
        daily_set_id: dailySetId,
        time_spent_seconds: timeSpent,
        answers: questions.map((q) => {
          const key = qKey(q)
          const rawId = q.raw_id != null ? q.raw_id : (typeof q.id === 'number' ? q.id : null)
          return {
            mcq_id: rawId ?? key,
            raw_id: rawId,
            bank_id: q.bank_id || key,
            source_type: q.source_type || (String(key).startsWith('study-') ? 'study' : 'quiz'),
            selected_option: answers[key] || null,
          }
        }),
      }
      let data
      if (source === 'daily') {
        ({ data } = await premiumStudyService.submitDailyChallenge(payload))
      } else if (source === 'weak') {
        ({ data } = await premiumStudyService.submitWeakPractice(payload))
      } else if (source === 'bank') {
        ({ data } = await premiumStudyService.submitBankPractice(payload))
      } else {
        ({ data } = await studentAiService.submitAttempt(payload))
      }
      setResult(data.data)
      onFinished?.(data.data)
    } catch (err) {
      submittedRef.current = false
      const msg = err?.response?.data?.message
      setError(msg || (auto ? 'Time is up — could not submit automatically. Please try again.' : 'Could not submit your answers. Please try again.'))
    } finally {
      setSubmitting(false)
    }
  }

  if (result) {
    return <McqResult result={result} questions={questions} reviewById={reviewById} title={title} onClose={onClose} />
  }

  const urgent = timeLimitSeconds && timeLeft != null && timeLeft <= 60

  return (
    <div className="space-y-5">
      <div className="flex items-center justify-between">
        <div>
          <h3 className="font-display text-lg font-bold text-navy">{title || 'MCQ Practice'}</h3>
          <p className="text-xs text-slate-400">{answeredCount} of {questions.length} answered</p>
        </div>
        <div className="flex items-center gap-3">
          {timeLimitSeconds != null && (
            <span className={`rounded-full px-3 py-1 text-sm font-semibold ${urgent ? 'bg-red-100 text-red-600' : 'bg-slate-100 text-slate-600'}`}>
              ⏱ {formatTime(timeLeft ?? 0)}
            </span>
          )}
          {onClose && <button type="button" onClick={onClose} className="text-sm text-slate-500 hover:text-slate-700">Close</button>}
        </div>
      </div>

      {error && <Alert>{error}</Alert>}

      <div className="h-1.5 w-full overflow-hidden rounded-full bg-slate-100">
        <div className="h-full rounded-full bg-primary transition-all" style={{ width: `${questions.length ? (answeredCount / questions.length) * 100 : 0}%` }} />
      </div>

      <div className="space-y-4">
        {questions.map((q, i) => {
          const key = qKey(q)
          return (
          <div key={key} className="rounded-2xl border border-slate-100 bg-white p-5 shadow-soft">
            <div className="flex items-start gap-2">
              <span className="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary/10 text-xs font-bold text-primary">{i + 1}</span>
              <p className="font-medium text-navy">{q.question}</p>
            </div>
            <div className="mt-3 space-y-2">
              {optionsFor(q).map((l) => {
                const active = answers[key] === l
                return (
                  <label key={l} className={`flex cursor-pointer items-center gap-3 rounded-xl border px-4 py-2.5 text-sm transition ${active ? 'border-primary bg-primary/5 text-navy' : 'border-slate-200 hover:bg-slate-50'}`}>
                    <input type="radio" name={`q-${key}`} className="accent-primary" checked={active}
                      onChange={() => setAnswers((prev) => ({ ...prev, [key]: l }))} />
                    <span className="font-semibold text-slate-500">{l}.</span>
                    <span>{q[`option_${l.toLowerCase()}`]}</span>
                  </label>
                )
              })}
            </div>
          </div>
          )
        })}
      </div>

      <div className="sticky bottom-4 flex items-center justify-between rounded-2xl border border-slate-100 bg-white/90 p-4 shadow-soft backdrop-blur">
        <span className="text-sm text-slate-500">{answeredCount}/{questions.length} answered</span>
        <button type="button" onClick={() => submit(false)} disabled={submitting || answeredCount === 0} className="btn-primary text-sm disabled:opacity-50">
          {submitting ? 'Submitting…' : 'Submit answers'}
        </button>
      </div>
    </div>
  )
}

function McqResult({ result, questions, reviewById, title, onClose }) {
  const pct = Math.round(result.score)
  const tone = pct >= 70 ? 'text-green-600' : pct >= 50 ? 'text-amber-500' : 'text-red-500'
  const ring = pct >= 70 ? '#16a34a' : pct >= 50 ? '#f59e0b' : '#ef4444'

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h3 className="font-display text-lg font-bold text-navy">{title || 'Results'}</h3>
        {onClose && <button type="button" onClick={onClose} className="text-sm text-slate-500 hover:text-slate-700">Close</button>}
      </div>

      {(result.new_badges?.length > 0) && (
        <Alert type="success">
          🏅 New achievement{result.new_badges.length > 1 ? 's' : ''} unlocked: {result.new_badges.map((b) => b.name).join(', ')}
        </Alert>
      )}

      <div className="grid gap-4 sm:grid-cols-4">
        <div className="flex flex-col items-center justify-center rounded-2xl border border-slate-100 bg-white p-5 shadow-soft">
          <div className="relative flex h-24 w-24 items-center justify-center rounded-full"
            style={{ background: `conic-gradient(${ring} ${pct * 3.6}deg, #e2e8f0 0deg)` }}>
            <div className="flex h-[76px] w-[76px] flex-col items-center justify-center rounded-full bg-white">
              <span className={`text-xl font-bold ${tone}`}>{pct}%</span>
            </div>
          </div>
          <p className="mt-2 text-xs text-slate-400">Score</p>
        </div>
        <Stat label="Correct" value={result.correct} tone="text-green-600" />
        <Stat label="Wrong" value={result.wrong} tone="text-red-500" />
        <Stat label="Time" value={`${Math.floor(result.time_spent / 60)}m ${result.time_spent % 60}s`} tone="text-navy" />
      </div>

      <div className="space-y-4">
        {questions.map((q, i) => {
          const key = qKey(q)
          const r = reviewById[key]
            || reviewById[q.bank_id]
            || reviewById[q.id]
            || reviewById[q.raw_id]
            || reviewById[`quiz-${q.raw_id || q.id}`]
          if (!r) return null
          return (
            <div key={key} className="rounded-2xl border border-slate-100 bg-white p-5 shadow-soft">
              <div className="flex items-start gap-2">
                <span className={`mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-xs font-bold text-white ${r.is_correct ? 'bg-green-500' : 'bg-red-500'}`}>{r.is_correct ? '✓' : '✕'}</span>
                <p className="font-medium text-navy">{i + 1}. {r.question}</p>
              </div>
              <div className="mt-3 space-y-1.5 pl-8">
                {LETTERS.filter((l) => (r.options?.[l] || '').trim() !== '').map((l) => {
                  const isCorrect = r.correct_option === l
                  const isSelected = r.selected_option === l
                  return (
                    <div key={l} className={`rounded-lg px-3 py-2 text-sm ${isCorrect ? 'bg-green-50 text-green-800' : isSelected ? 'bg-red-50 text-red-700' : 'text-slate-600'}`}>
                      <span className="font-semibold">{l}.</span> {r.options[l]}
                      {isCorrect && <span className="ml-2 text-xs font-semibold">✓ Correct</span>}
                      {isSelected && !isCorrect && <span className="ml-2 text-xs font-semibold">Your answer</span>}
                    </div>
                  )
                })}
              </div>
              {r.explanation && (
                <div className="mt-3 ml-8 rounded-xl bg-slate-50 p-3 text-sm text-slate-600">
                  <span className="font-semibold text-navy">Explanation: </span>{r.explanation}
                </div>
              )}
              {(r.topic || r.difficulty) && (
                <div className="mt-2 ml-8 flex gap-2 text-xs">
                  {r.topic && <span className="rounded-full bg-primary/10 px-2 py-0.5 text-primary">{r.topic}</span>}
                  {r.difficulty && <span className="rounded-full bg-slate-100 px-2 py-0.5 capitalize text-slate-500">{r.difficulty}</span>}
                </div>
              )}
            </div>
          )
        })}
      </div>
    </div>
  )
}

function Stat({ label, value, tone }) {
  return (
    <div className="flex flex-col items-center justify-center rounded-2xl border border-slate-100 bg-white p-5 shadow-soft">
      <span className={`text-2xl font-bold ${tone}`}>{value}</span>
      <p className="mt-1 text-xs text-slate-400">{label}</p>
    </div>
  )
}
