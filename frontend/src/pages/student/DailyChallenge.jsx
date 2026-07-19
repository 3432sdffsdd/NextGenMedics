import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { FiZap, FiCheckCircle, FiClock, FiTarget, FiBookOpen, FiAlertCircle, FiRefreshCw } from 'react-icons/fi'
import { premiumStudyService } from '../../services/api'
import Alert from '../../components/dashboard/Alert'
import McqPlayer from '../../components/dashboard/McqPlayer'

function Countdown({ seconds }) {
  const [left, setLeft] = useState(seconds)
  useEffect(() => {
    setLeft(seconds)
    const t = setInterval(() => setLeft((s) => Math.max(0, s - 1)), 1000)
    return () => clearInterval(t)
  }, [seconds])
  const h = Math.floor(left / 3600)
  const m = Math.floor((left % 3600) / 60)
  const s = left % 60
  return <span>{h > 0 ? `${h}h ` : ''}{m}m {s}s</span>
}

const REASON_HINTS = {
  not_enrolled: 'Browse courses and enroll to start your daily challenge.',
  no_questions: 'Daily Challenge uses MCQs from quizzes your teacher uploaded and published. Ask them to publish course quizzes.',
}

export default function DailyChallenge() {
  const [challenge, setChallenge] = useState(null)
  const [history, setHistory] = useState([])
  const [loading, setLoading] = useState(true)
  const [loadError, setLoadError] = useState('')
  const [active, setActive] = useState(false)

  const load = () => {
    setLoading(true)
    setLoadError('')
    Promise.all([
      premiumStudyService.dailyChallenge(),
      premiumStudyService.dailyHistory().catch(() => ({ data: { data: [] } })),
    ])
      .then(([ch, hist]) => {
        setChallenge(ch.data.data)
        setHistory(hist.data.data || [])
      })
      .catch((err) => {
        const msg = err.response?.data?.message || err.message
        const status = err.response?.status
        setLoadError(
          msg
            ? `${status ? `HTTP ${status}: ` : ''}${msg}`
            : 'Could not load today\'s challenge. Make sure you are logged in and the server is up to date.'
        )
      })
      .finally(() => setLoading(false))
  }

  useEffect(load, [])

  if (active && challenge?.questions?.length) {
    return (
      <div className="rounded-2xl border border-slate-100 bg-white p-6 shadow-soft">
        <McqPlayer
          questions={challenge.questions}
          source="daily"
          dailySetId={challenge.daily_set_id}
          timeLimitSeconds={(challenge.duration_minutes || 10) * 60}
          title={`Today's Challenge · ${challenge.total_questions} MCQs`}
          onClose={() => { setActive(false); load() }}
        />
      </div>
    )
  }

  const unavailable = challenge && challenge.available === false
  const canStart = challenge?.available !== false && !challenge?.completed && challenge?.questions?.length > 0

  return (
    <div>
      <h2 className="font-display text-2xl font-bold text-navy">Daily Challenge</h2>
      <p className="text-sm text-slate-500">
        10 random MCQs from your teachers&apos; published course quizzes. One attempt per day — 10 minute limit.
        Questions you have already seen are skipped until the full bank is used.
      </p>

      {loading ? (
        <p className="mt-8 text-center text-slate-400">Loading today&apos;s challenge…</p>
      ) : loadError ? (
        <div className="mt-6 space-y-3">
          <Alert>{loadError}</Alert>
          <button type="button" onClick={load} className="btn-secondary text-sm">
            <FiRefreshCw className="inline mr-1" /> Try again
          </button>
        </div>
      ) : unavailable ? (
        <div className="mt-6 space-y-3">
          <Alert>{challenge.message || REASON_HINTS[challenge.reason] || 'No challenge available right now.'}</Alert>
        </div>
      ) : !challenge ? (
        <div className="mt-6"><Alert>Could not load challenge data.</Alert></div>
      ) : (
        <>
          <div className="mt-6 rounded-2xl border border-primary/20 bg-gradient-to-br from-primary/5 to-white p-6 shadow-soft">
            <div className="flex items-start justify-between gap-4">
              <div className="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-primary/10 text-primary">
                <FiZap size={28} />
              </div>
              <div className="flex-1">
                <p className="text-xs font-semibold uppercase tracking-wide text-primary">Today&apos;s Challenge</p>
                <h3 className="mt-1 font-display text-xl font-bold text-navy">
                  {challenge.completed
                    ? "Today's Challenge Completed"
                    : `${challenge.total_questions || 10} Questions · ${challenge.duration_minutes || 10} min`}
                </h3>
                <div className="mt-2 flex flex-wrap gap-4 text-sm text-slate-500">
                  <span className="flex items-center gap-1"><FiClock /> {challenge.duration_minutes || 10} minutes</span>
                  <span className="flex items-center gap-1"><FiTarget /> From teacher quizzes</span>
                </div>
                {challenge.completed ? (
                  <div className="mt-4 space-y-2">
                    <div className="flex items-center gap-2 rounded-xl bg-green-50 px-4 py-2.5 text-sm font-semibold text-green-600">
                      <FiCheckCircle /> Completed Today
                      {challenge.last_score != null && <span className="ml-2 font-normal">Score: {Math.round(challenge.last_score)}%</span>}
                    </div>
                    <p className="text-sm text-slate-500">
                      Next challenge in <Countdown seconds={challenge.seconds_until_next} />
                    </p>
                  </div>
                ) : canStart ? (
                  <button type="button" onClick={() => setActive(true)} className="btn-primary mt-4 text-sm">
                    Start Challenge
                  </button>
                ) : (
                  <Alert className="mt-4">Not enough questions in today&apos;s set. Check back after your teacher publishes more quizzes.</Alert>
                )}
              </div>
            </div>
          </div>

          {history.length > 0 && (
            <div className="mt-10">
              <h3 className="font-display text-lg font-bold text-navy">Challenge History</h3>
              <div className="mt-4 overflow-x-auto rounded-2xl border border-slate-100 bg-white shadow-soft">
                <table className="w-full text-left text-sm">
                  <thead className="border-b border-slate-100 bg-slate-50 text-xs uppercase text-slate-500">
                    <tr>
                      <th className="px-4 py-3">Date</th>
                      <th className="px-4 py-3">Score</th>
                      <th className="px-4 py-3">Correct</th>
                      <th className="px-4 py-3">Wrong</th>
                      <th className="px-4 py-3">Time</th>
                    </tr>
                  </thead>
                  <tbody>
                    {history.slice(0, 15).map((h) => (
                      <tr key={h.id} className="border-b border-slate-50">
                        <td className="px-4 py-3">{new Date(h.submitted_at || h.challenge_date).toLocaleDateString()}</td>
                        <td className="px-4 py-3 font-semibold text-navy">{Math.round(h.score)}%</td>
                        <td className="px-4 py-3 text-green-600">{h.correct_count}</td>
                        <td className="px-4 py-3 text-red-500">{h.wrong_count}</td>
                        <td className="px-4 py-3 text-slate-500">{Math.floor((h.time_spent_seconds || 0) / 60)}m {(h.time_spent_seconds || 0) % 60}s</td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </div>
          )}

          <div className="mt-8 flex flex-wrap gap-3">
            <Link to="/student/weak-areas" className="btn-secondary text-sm"><FiTarget className="inline mr-1" /> Weak Areas</Link>
            <Link to="/student/question-bank" className="btn-secondary text-sm"><FiBookOpen className="inline mr-1" /> Question Bank</Link>
            <Link to="/student/mistakes" className="btn-secondary text-sm"><FiAlertCircle className="inline mr-1" /> My Mistakes</Link>
          </div>
        </>
      )}
    </div>
  )
}
