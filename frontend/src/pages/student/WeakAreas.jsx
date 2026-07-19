import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { FiTarget, FiPlay, FiArrowLeft } from 'react-icons/fi'
import { premiumStudyService } from '../../services/api'
import Alert from '../../components/dashboard/Alert'
import McqPlayer from '../../components/dashboard/McqPlayer'
import ProgressBar from '../../components/dashboard/ProgressBar'
import StatCard from '../../components/dashboard/StatCard'

export default function WeakAreas() {
  const [data, setData] = useState(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')
  const [page, setPage] = useState(1)
  const [practice, setPractice] = useState(null)

  const load = (p = 1) => {
    setLoading(true)
    setError('')
    premiumStudyService.weakAreasDetail({ page: p, per_page: 20 })
      .then(({ data: res }) => {
        setData(res.data)
        setPage(p)
      })
      .catch(() => setError('Could not load weak areas analytics.'))
      .finally(() => setLoading(false))
  }

  useEffect(() => { load(1) }, [])

  const startPractice = (limit) => {
    const params = limit === 'all' ? { limit: 'all' } : { limit }
    premiumStudyService.weakAreasPractice(params)
      .then(({ data: res }) => {
        const qs = res.data?.questions || []
        if (!qs.length) {
          setError('No incorrect questions to practice yet. Take quizzes or the Daily Challenge first.')
          return
        }
        setPractice(qs)
      })
      .catch(() => setError('Could not start weak areas practice.'))
  }

  if (practice?.length) {
    return (
      <div className="rounded-2xl border border-slate-100 bg-white p-6 shadow-soft">
        <McqPlayer
          questions={practice}
          source="weak"
          title={`Practice Weak Areas · ${practice.length} questions`}
          onClose={() => { setPractice(null); load(page) }}
        />
      </div>
    )
  }

  const perf = data?.performance || {}
  const incorrect = data?.most_incorrect || { items: [], total: 0, page: 1, per_page: 20 }
  const totalPages = Math.max(1, Math.ceil((incorrect.total || 0) / (incorrect.per_page || 20)))

  return (
    <div>
      <div className="flex flex-wrap items-start justify-between gap-4">
        <div>
          <Link to="/student/dashboard" className="mb-2 inline-flex items-center gap-1 text-xs font-semibold text-primary hover:underline">
            <FiArrowLeft /> Dashboard
          </Link>
          <h2 className="font-display text-2xl font-bold text-navy">Weak Areas</h2>
          <p className="text-sm text-slate-500">Analytics from your quiz attempts and Daily Challenges.</p>
        </div>
        <div className="flex flex-wrap gap-2">
          <button type="button" onClick={() => startPractice(10)} className="btn-primary text-sm">
            <FiPlay className="inline mr-1" /> Practice 10
          </button>
          <button type="button" onClick={() => startPractice(20)} className="btn-secondary text-sm">Practice 20</button>
          <button type="button" onClick={() => startPractice('all')} className="btn-secondary text-sm">Practice all incorrect</button>
        </div>
      </div>

      {loading && <p className="mt-8 text-slate-400">Loading analytics…</p>}
      {error && <div className="mt-4"><Alert>{error}</Alert></div>}

      {!loading && data && (
        <>
          <div className="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <StatCard label="Overall Accuracy %" value={data.overall_accuracy ?? 0} icon={FiTarget} tone="emerald" />
            <StatCard label="Questions Attempted" value={perf.questions_attempted ?? 0} icon={FiTarget} />
            <StatCard label="Correct" value={perf.correct ?? 0} icon={FiTarget} tone="emerald" />
            <StatCard label="Incorrect" value={perf.incorrect ?? 0} icon={FiTarget} tone="rose" />
          </div>

          <div className="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <StatCard label="Current Streak" value={perf.current_streak ?? 0} />
            <StatCard label="Daily Challenges Done" value={perf.daily_challenges_completed ?? 0} />
            <div className="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft">
              <p className="text-sm font-medium text-slate-500">Weakest Topic</p>
              <p className="mt-3 font-display text-xl font-bold text-navy">{perf.weakest_topic || perf.weakest_subject || '—'}</p>
              {perf.weakest_accuracy != null && <p className="mt-1 text-xs text-red-500">{perf.weakest_accuracy}% accuracy</p>}
            </div>
          </div>

          <section className="mt-8 rounded-2xl border border-slate-100 bg-white p-6 shadow-soft">
            <h3 className="font-display text-lg font-bold text-navy">Weak Topics</h3>
            <p className="mt-1 text-xs text-slate-500">From course quizzes and Daily Challenge — grouped by quiz/topic, not course name.</p>
            {(data.weak_topics || data.weak_subjects || []).length === 0 ? (
              <p className="mt-4 text-sm text-slate-500">Attempt course quizzes or Daily Challenge to unlock topic analytics.</p>
            ) : (
              <ul className="mt-4 grid gap-3 sm:grid-cols-2">
                {(data.weak_topics || data.weak_subjects || []).map((s) => {
                  const name = s.topic || s.subject || s.chapter
                  return (
                    <li key={name} className="rounded-xl bg-slate-50 px-3 py-3 text-sm">
                      <div className="flex justify-between text-sm">
                        <span className="font-medium text-navy">{name}</span>
                        <span className={(s.accuracy ?? 0) < 70 ? 'text-red-500' : 'text-amber-600'}>{s.accuracy}%</span>
                      </div>
                      <ProgressBar value={s.accuracy || 0} tone={(s.accuracy || 0) < 70 ? 'rose' : 'amber'} className="mt-1" />
                      <p className="mt-1 text-xs text-slate-400">{s.correct}/{s.total} correct</p>
                    </li>
                  )
                })}
              </ul>
            )}
          </section>

          <section className="mt-8 rounded-2xl border border-slate-100 bg-white p-6 shadow-soft">
            <div className="flex flex-wrap items-center justify-between gap-3">
              <h3 className="font-display text-lg font-bold text-navy">Most Incorrect MCQs</h3>
              <button type="button" onClick={() => startPractice(10)} className="btn-primary text-sm">
                <FiPlay className="inline mr-1" /> Practice Weak Areas
              </button>
            </div>
            {(incorrect.items || []).length === 0 ? (
              <p className="mt-4 text-sm text-slate-500">No incorrect answers recorded yet.</p>
            ) : (
              <ul className="mt-4 space-y-4">
                {incorrect.items.map((item) => (
                  <li key={`${item.question_id}-${item.date_attempted}`} className="rounded-xl border border-slate-100 p-4 text-sm">
                    <p className="font-medium text-navy">{item.question}</p>
                    <div className="mt-2 grid gap-1 text-xs text-slate-500 sm:grid-cols-2">
                      <p>
                        You chose:{' '}
                        <span className="font-semibold text-red-500">
                          {item.student_answer
                            ? `${item.student_answer}${item.options?.[item.student_answer] ? ` — ${item.options[item.student_answer]}` : ''}`
                            : 'No answer selected'}
                        </span>
                      </p>
                      <p>
                        Correct answer:{' '}
                        <span className="font-semibold text-green-600">
                          {item.correct_answer
                            ? `${item.correct_answer}${item.options?.[item.correct_answer] ? ` — ${item.options[item.correct_answer]}` : ''}`
                            : '—'}
                        </span>
                      </p>
                      <p>Topic: {item.topic || item.chapter || item.subject || '—'}</p>
                      <p>Source: {item.source === 'daily' ? 'Daily Challenge' : item.source === 'quiz' ? 'Course Quiz' : (item.source || '—')}</p>
                      <p>Date: {item.date_attempted ? new Date(item.date_attempted).toLocaleDateString() : '—'}</p>
                    </div>
                    {item.explanation && (
                      <p className="mt-2 rounded-lg bg-slate-50 px-3 py-2 text-slate-600">
                        <span className="font-semibold text-navy">Explanation: </span>{item.explanation}
                      </p>
                    )}
                    <button
                      type="button"
                      className="mt-3 text-xs font-semibold text-primary hover:underline"
                      onClick={() => {
                        premiumStudyService.weakAreasPractice({ limit: 1 })
                          .then(() => {
                            // Retry this specific question if present in bank via single-id practice of incorrect list
                            setPractice([{
                              id: item.question_id,
                              question: item.question,
                              option_a: item.options?.A || '',
                              option_b: item.options?.B || '',
                              option_c: item.options?.C || '',
                              option_d: item.options?.D || '',
                              option_e: item.options?.E || '',
                              topic: item.topic,
                              subject: item.subject,
                            }])
                          })
                      }}
                    >
                      Retry this question
                    </button>
                  </li>
                ))}
              </ul>
            )}
            {totalPages > 1 && (
              <div className="mt-4 flex items-center justify-between text-sm">
                <button type="button" disabled={page <= 1} onClick={() => load(page - 1)} className="btn-secondary text-xs disabled:opacity-40">Previous</button>
                <span className="text-slate-500">Page {page} of {totalPages}</span>
                <button type="button" disabled={page >= totalPages} onClick={() => load(page + 1)} className="btn-secondary text-xs disabled:opacity-40">Next</button>
              </div>
            )}
          </section>
        </>
      )}
    </div>
  )
}
