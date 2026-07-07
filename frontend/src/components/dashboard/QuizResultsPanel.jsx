import { useCallback, useEffect, useMemo, useState } from 'react'
import { FiDownload, FiSearch, FiEye } from 'react-icons/fi'
import * as XLSX from 'xlsx'
import { quizService } from '../../services/api'
import { Modal, Button, Badge, EmptyState } from '../ui'

function formatTime(seconds) {
  if (seconds == null) return '—'
  const m = Math.floor(seconds / 60)
  const s = seconds % 60
  return `${m}m ${s}s`
}

function formatDate(d) {
  if (!d) return '—'
  return new Date(d).toLocaleString()
}

export default function QuizResultsPanel({ quiz, onClose }) {
  const [attempts, setAttempts] = useState([])
  const [loading, setLoading] = useState(true)
  const [search, setSearch] = useState('')
  const [sortDesc, setSortDesc] = useState(true)
  const [detail, setDetail] = useState(null)
  const [detailLoading, setDetailLoading] = useState(false)

  const load = useCallback(() => {
    setLoading(true)
    quizService.listAttempts(quiz.id, search.trim() || undefined)
      .then(({ data }) => setAttempts(data.data?.attempts || []))
      .catch(() => setAttempts([]))
      .finally(() => setLoading(false))
  }, [quiz.id, search])

  useEffect(() => { load() }, [load])

  const sorted = useMemo(() => {
    const list = [...attempts]
    list.sort((a, b) => {
      const diff = (Number(b.percentage) || 0) - (Number(a.percentage) || 0)
      return sortDesc ? diff : -diff
    })
    return list
  }, [attempts, sortDesc])

  const openDetail = async (attemptId) => {
    setDetailLoading(true)
    try {
      const { data } = await quizService.teacherReview(attemptId)
      setDetail(data.data)
    } catch {
      setDetail(null)
    } finally {
      setDetailLoading(false)
    }
  }

  const exportExcel = () => {
    const rows = sorted.map((a) => ({
      'Student Name': a.student_name,
      'Roll / Username': a.username,
      'Quiz': quiz.title,
      'Score': a.score,
      'Percentage': a.percentage,
      'Pass/Fail': a.passed ? 'Pass' : 'Fail',
      'Time Taken': formatTime(a.time_taken_seconds),
      'Submitted': formatDate(a.submitted_at),
    }))
    const ws = XLSX.utils.json_to_sheet(rows)
    const wb = XLSX.utils.book_new()
    XLSX.utils.book_append_sheet(wb, ws, 'Results')
    XLSX.writeFile(wb, `${quiz.title.replace(/[^\w\-]+/g, '_')}_results.xlsx`)
  }

  const exportPdf = () => {
    const win = window.open('', '_blank')
    if (!win) return
    const rows = sorted.map((a) => `
      <tr>
        <td>${a.student_name}</td>
        <td>${a.username}</td>
        <td>${a.score ?? '—'}</td>
        <td>${a.percentage ?? '—'}%</td>
        <td>${a.passed ? 'Pass' : 'Fail'}</td>
        <td>${formatTime(a.time_taken_seconds)}</td>
        <td>${formatDate(a.submitted_at)}</td>
      </tr>`).join('')
    win.document.write(`<!DOCTYPE html><html><head><title>${quiz.title} Results</title>
      <style>body{font-family:sans-serif;padding:24px}table{border-collapse:collapse;width:100%}th,td{border:1px solid #ccc;padding:8px;text-align:left}th{background:#f1f5f9}</style>
      </head><body><h1>${quiz.title} — Quiz Results</h1>
      <table><thead><tr><th>Student</th><th>Roll</th><th>Score</th><th>%</th><th>Pass/Fail</th><th>Time</th><th>Submitted</th></tr></thead><tbody>${rows}</tbody></table>
      </body></html>`)
    win.document.close()
    win.print()
  }

  return (
    <>
      <Modal open onClose={onClose} title="Quiz results" subtitle={quiz.title} size="xl"
        footer={<Button variant="outline" onClick={onClose}>Close</Button>}>
        <div className="space-y-4">
          <div className="flex flex-wrap items-center gap-3">
            <div className="relative min-w-[200px] flex-1">
              <FiSearch className="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" size={16} />
              <input value={search} onChange={(e) => setSearch(e.target.value)} placeholder="Search by student name or roll…"
                className="w-full rounded-xl border border-slate-200 py-2 pl-9 pr-3 text-sm" />
            </div>
            <Button size="sm" variant="outline" onClick={() => setSortDesc((v) => !v)}>
              Sort: {sortDesc ? 'Highest score' : 'Lowest score'}
            </Button>
            <Button size="sm" variant="outline" icon={FiDownload} onClick={exportExcel}>Excel</Button>
            <Button size="sm" variant="outline" onClick={exportPdf}>PDF</Button>
          </div>

          {loading ? (
            <p className="text-sm text-slate-400">Loading results…</p>
          ) : sorted.length === 0 ? (
            <EmptyState title="No submissions yet" description="Student quiz attempts will appear here after they submit." />
          ) : (
            <div className="overflow-x-auto rounded-xl border border-slate-100">
              <table className="min-w-full text-sm">
                <thead className="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-500">
                  <tr>
                    <th className="px-4 py-3">Student</th>
                    <th className="px-4 py-3">Roll</th>
                    <th className="px-4 py-3">Score</th>
                    <th className="px-4 py-3">%</th>
                    <th className="px-4 py-3">Pass/Fail</th>
                    <th className="px-4 py-3">Time</th>
                    <th className="px-4 py-3">Submitted</th>
                    <th className="px-4 py-3"></th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-100">
                  {sorted.map((a) => (
                    <tr key={a.id} className="hover:bg-slate-50/80">
                      <td className="px-4 py-3 font-medium text-navy">{a.student_name}</td>
                      <td className="px-4 py-3 text-slate-500">{a.username}</td>
                      <td className="px-4 py-3">{a.score ?? '—'}</td>
                      <td className="px-4 py-3">{a.percentage ?? '—'}%</td>
                      <td className="px-4 py-3">
                        <Badge tone={a.passed ? 'success' : 'warning'}>{a.passed ? 'Pass' : 'Fail'}</Badge>
                      </td>
                      <td className="px-4 py-3 text-slate-500">{formatTime(a.time_taken_seconds)}</td>
                      <td className="px-4 py-3 text-slate-500">{formatDate(a.submitted_at)}</td>
                      <td className="px-4 py-3">
                        <button type="button" onClick={() => openDetail(a.id)} className="text-primary hover:underline">
                          <FiEye className="inline" size={14} /> Detail
                        </button>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
        </div>
      </Modal>

      <Modal open={!!detail || detailLoading} onClose={() => setDetail(null)} title="Attempt detail" size="lg"
        footer={<Button variant="outline" onClick={() => setDetail(null)}>Close</Button>}>
        {detailLoading && !detail ? (
          <p className="text-sm text-slate-400">Loading…</p>
        ) : detail ? (
          <div className="space-y-4">
            <div className="rounded-xl bg-slate-50 p-4 text-sm">
              <p><strong>{detail.student?.student_name}</strong> · Roll: {detail.student?.username}</p>
              <p className="mt-1 text-slate-600">
                Score {detail.attempt?.score} ({detail.attempt?.percentage}%) · {detail.attempt?.passed ? 'Passed' : 'Not passed'}
                · {formatTime(detail.attempt?.time_taken_seconds)} · {formatDate(detail.attempt?.submitted_at)}
              </p>
            </div>
            <div className="space-y-3">
              {(detail.review || []).map((q, i) => (
                <div key={q.question_id} className={`rounded-xl border p-3 ${q.is_correct ? 'border-emerald-200 bg-emerald-50/30' : q.is_correct === false ? 'border-red-200 bg-red-50/30' : 'border-slate-100'}`}>
                  <p className="text-sm font-medium text-navy">{i + 1}. {q.question_text}</p>
                  <div className="mt-2 space-y-1">
                    {(q.options || []).map((o) => {
                      const selected = (q.selected || []).includes(o.id)
                      let cls = 'text-xs px-2 py-1 rounded bg-white text-slate-600'
                      if (o.is_correct) cls = 'text-xs px-2 py-1 rounded bg-emerald-100 text-emerald-800'
                      else if (selected) cls = 'text-xs px-2 py-1 rounded bg-red-100 text-red-800'
                      return <div key={o.id} className={cls}>{o.option_text}{selected && !o.is_correct ? ' (student)' : ''}{o.is_correct ? ' ✓' : ''}</div>
                    })}
                  </div>
                </div>
              ))}
            </div>
          </div>
        ) : null}
      </Modal>
    </>
  )
}
