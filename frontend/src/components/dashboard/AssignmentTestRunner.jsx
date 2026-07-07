import { useState } from 'react'
import { FiCheck, FiX } from 'react-icons/fi'
import { Button } from '../ui'

export default function AssignmentTestRunner({ testData, onSubmit, onCancel, submitting }) {
  const [answers, setAnswers] = useState({})
  const { assignment, questions } = testData

  const allAnswered = questions?.length > 0 && questions.every((q) => answers[q.id])

  return (
    <div className="mt-6 rounded-2xl border border-slate-100 bg-white p-6 shadow-soft">
      <div className="flex flex-wrap items-start justify-between gap-3">
        <div>
          <p className="text-xs font-semibold uppercase tracking-wide text-indigo-600">Interactive test</p>
          <h3 className="font-bold text-navy">{assignment.title}</h3>
          <p className="mt-1 text-xs text-slate-400">
            {questions.length} questions · Max {assignment.max_marks} marks · Due {new Date(assignment.due_date).toLocaleString()}
          </p>
        </div>
        <Button variant="outline" size="sm" onClick={onCancel}>Back to list</Button>
      </div>

      {assignment.description && <p className="mt-4 text-sm text-slate-600">{assignment.description}</p>}

      <div className="mt-6 space-y-6">
        {questions.map((q, idx) => (
          <div key={q.id} className="border-b border-slate-100 pb-5">
            <p className="font-medium text-navy">
              <span className="mr-2 text-slate-400">{idx + 1}.</span>
              {q.question_text}
            </p>
            <div className="mt-3 space-y-2">
              {(q.options || []).map((opt) => (
                <label
                  key={opt.id}
                  className={`flex cursor-pointer items-center gap-3 rounded-xl border px-3 py-2.5 text-sm transition ${
                    answers[q.id] === opt.id
                      ? 'border-primary bg-primary/5 text-navy'
                      : 'border-slate-100 hover:border-slate-200 hover:bg-slate-50'
                  }`}
                >
                  <input
                    type="radio"
                    name={`aq-${q.id}`}
                    checked={answers[q.id] === opt.id}
                    onChange={() => setAnswers((prev) => ({ ...prev, [q.id]: opt.id }))}
                  />
                  <span>{opt.option_text}</span>
                </label>
              ))}
            </div>
          </div>
        ))}
      </div>

      <div className="mt-6 flex flex-wrap items-center gap-3">
        <Button onClick={() => onSubmit(answers)} loading={submitting} disabled={!allAnswered}>
          Submit test
        </Button>
        {!allAnswered && <span className="text-xs text-slate-400">Answer all questions to submit</span>}
      </div>
    </div>
  )
}

export function AssignmentTestResult({ result, assignment, onBack, onReview }) {
  const passed = result.passed
  return (
    <div className="mt-6 rounded-2xl border border-slate-100 bg-white p-6 shadow-soft">
      <p className="text-xs font-semibold uppercase tracking-wide text-indigo-600">Test submitted</p>
      <h3 className="mt-1 font-bold text-navy">{assignment?.title}</h3>
      <div className={`mt-4 inline-flex items-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold ${passed ? 'bg-green-50 text-green-700' : 'bg-amber-50 text-amber-700'}`}>
        {passed ? <FiCheck /> : <FiX />}
        Score: {result.score} / {result.max_marks} ({result.percentage}%) · {result.correct}/{result.total} correct
      </div>
      <div className="mt-5 flex flex-wrap gap-3">
        {result.review?.length > 0 && (
          <Button variant="secondary" size="sm" onClick={onReview}>Review answers</Button>
        )}
        <Button variant="outline" size="sm" onClick={onBack}>Back to assignments</Button>
      </div>
    </div>
  )
}

export function AssignmentTestReview({ review, assignment, onBack }) {
  return (
    <div className="mt-6 rounded-2xl border border-slate-100 bg-white p-6 shadow-soft">
      <h3 className="font-bold text-navy">Review — {assignment?.title}</h3>
      <div className="mt-6 space-y-5">
        {review.map((item, idx) => (
          <div key={item.question_id} className="rounded-xl border border-slate-100 p-4">
            <p className="font-medium text-navy">{idx + 1}. {item.question_text}</p>
            <div className="mt-2 space-y-1">
              {item.options.map((opt) => {
                const selected = item.selected === opt.id
                const correct = opt.is_correct
                let cls = 'text-sm text-slate-600'
                if (correct) cls = 'text-sm font-medium text-green-700'
                else if (selected && !correct) cls = 'text-sm font-medium text-red-600'
                return (
                  <p key={opt.id} className={cls}>
                    {selected ? '→ ' : '  '}{opt.option_text}
                    {correct && ' ✓'}
                  </p>
                )
              })}
            </div>
            {item.explanation && <p className="mt-2 text-xs text-slate-500">{item.explanation}</p>}
          </div>
        ))}
      </div>
      <Button variant="outline" className="mt-6" size="sm" onClick={onBack}>Back</Button>
    </div>
  )
}
