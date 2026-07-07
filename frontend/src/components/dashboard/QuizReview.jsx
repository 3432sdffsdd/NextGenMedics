import { FiCheckCircle, FiXCircle } from 'react-icons/fi'

function formatTime(seconds) {
  if (seconds == null) return null
  const m = Math.floor(seconds / 60)
  const s = seconds % 60
  return `${m}m ${s}s`
}

/** Post-quiz review showing score and per-question breakdown. */
export default function QuizReview({ result, onClose, title }) {
  if (!result) return null
  const {
    percentage, score, total, passed, review,
    total_questions, attempted, correct, wrong, unanswered,
    time_taken_seconds, submitted_at,
  } = result

  return (
    <div className="rounded-2xl border border-slate-100 bg-white p-6 shadow-soft">
      <div className="flex flex-wrap items-start justify-between gap-3">
        <div>
          <h3 className="font-display text-lg font-bold text-navy">{title || 'Quiz result'}</h3>
          <p className={`mt-1 text-sm font-semibold ${passed ? 'text-emerald-600' : 'text-amber-600'}`}>
            {percentage ?? score}% — {passed ? 'Passed' : 'Not passed'}
            {total != null && <span className="font-normal text-slate-400"> · {score}/{total} marks</span>}
          </p>
          {(total_questions != null || time_taken_seconds != null) && (
            <p className="mt-2 text-xs text-slate-500">
              {total_questions != null && (
                <>
                  {total_questions} questions · {attempted ?? 0} attempted · {correct ?? 0} correct · {wrong ?? 0} wrong · {unanswered ?? 0} unanswered
                </>
              )}
              {time_taken_seconds != null && <> · Time: {formatTime(time_taken_seconds)}</>}
              {submitted_at && <> · Submitted {new Date(submitted_at).toLocaleString()}</>}
            </p>
          )}
        </div>
        {onClose && (
          <button type="button" onClick={onClose} className="text-sm font-medium text-primary hover:underline">Back to quizzes</button>
        )}
      </div>

      {review?.length > 0 ? (
        <div className="mt-6 space-y-4">
          {review.map((q, i) => (
            <div key={q.question_id} className={`rounded-xl border p-4 ${q.is_correct ? 'border-emerald-200 bg-emerald-50/40' : q.is_correct === false ? 'border-red-200 bg-red-50/40' : 'border-slate-100'}`}>
              <div className="flex items-start gap-2">
                {q.is_correct === true && <FiCheckCircle className="mt-0.5 shrink-0 text-emerald-500" />}
                {q.is_correct === false && <FiXCircle className="mt-0.5 shrink-0 text-red-500" />}
                <div className="min-w-0 flex-1">
                  <p className="text-sm font-medium text-navy">{i + 1}. {q.question_text}</p>
                  <div className="mt-2 space-y-1.5">
                    {(q.options || []).map((o) => {
                      const selected = (q.selected || []).includes(o.id)
                      const correctOpt = o.is_correct
                      let cls = 'bg-white border-slate-100 text-slate-600'
                      if (correctOpt) cls = 'bg-emerald-100 border-emerald-200 text-emerald-800'
                      else if (selected && !correctOpt) cls = 'bg-red-100 border-red-200 text-red-800'
                      return (
                        <div key={o.id} className={`rounded-lg border px-3 py-1.5 text-sm ${cls}`}>
                          {o.option_text}
                          {selected && !correctOpt && <span className="ml-2 text-xs font-medium">Your answer</span>}
                          {correctOpt && <span className="ml-2 text-xs font-medium">Correct</span>}
                        </div>
                      )
                    })}
                  </div>
                  {q.explanation && <p className="mt-2 text-xs text-slate-500"><strong>Explanation:</strong> {q.explanation}</p>}
                </div>
              </div>
            </div>
          ))}
        </div>
      ) : (
        <p className="mt-4 text-sm text-slate-400">
          {passed != null ? 'Your score has been recorded. Detailed review is not available for this quiz.' : 'Detailed review is not available for this quiz.'}
        </p>
      )}
    </div>
  )
}
