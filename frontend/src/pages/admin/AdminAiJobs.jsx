import { useEffect, useState } from 'react'
import { FiBookOpen, FiChevronRight, FiRefreshCw } from 'react-icons/fi'
import { adminAiService } from '../../services/api'
import { useToast } from '../../context/ToastContext'
import Alert from '../../components/dashboard/Alert'

const FILTERS = [
  { id: '', label: 'All' },
  { id: 'processing', label: 'Running' },
  { id: 'completed', label: 'Completed' },
  { id: 'failed', label: 'Failed' },
  { id: 'cancelled', label: 'Cancelled' },
]

export default function AdminAiJobs() {
  const toast = useToast()
  const [status, setStatus] = useState('')
  const [data, setData] = useState(null)
  const [loading, setLoading] = useState(true)
  const [selected, setSelected] = useState(null)

  const load = async (s = status) => {
    setLoading(true)
    try {
      const params = {}
      if (s) params.status = s === 'processing' ? undefined : s
      // "Running" = pending + processing — fetch all then filter client-side if needed
      if (s === 'processing') {
        const [a, b] = await Promise.all([
          adminAiService.overview({ status: 'pending' }),
          adminAiService.overview({ status: 'processing' }),
        ])
        const counts = a.data.data?.counts || b.data.data?.counts
        const jobs = [...(a.data.data?.jobs || []), ...(b.data.data?.jobs || [])]
        setData({ counts, jobs, total: jobs.length, engine: a.data.data?.engine })
      } else {
        const { data: res } = await adminAiService.overview(params)
        setData(res.data)
      }
    } catch (e) {
      toast.error(e.response?.data?.message || 'Failed to load AI jobs')
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => { load() }, [status])

  const act = async (fn, id, label) => {
    try {
      const { data: res } = await fn(id)
      toast.success(label)
      setSelected(res.data?.job || null)
      load()
    } catch (e) {
      toast.error(e.response?.data?.message || `${label} failed`)
    }
  }

  const counts = data?.counts || {}

  return (
    <div>
      <div className="flex flex-wrap items-end justify-between gap-3">
        <div>
          <h2 className="font-display text-2xl font-bold text-navy">AI Generation Engine</h2>
          <p className="text-sm text-slate-500">
            Monitor Gemini jobs · Model: {data?.engine?.model || '—'} · Ready: {data?.engine?.ready ? 'yes' : 'no'}
          </p>
        </div>
        <button type="button" onClick={() => load()} className="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold">
          <FiRefreshCw /> Refresh
        </button>
      </div>

      <div className="mt-6 grid gap-3 sm:grid-cols-4">
        {[
          ['Running', counts.running || 0],
          ['Completed', counts.completed || 0],
          ['Failed', counts.failed || 0],
          ['Cancelled', counts.cancelled || 0],
        ].map(([label, n]) => (
          <div key={label} className="rounded-2xl border border-slate-100 bg-white p-4 shadow-soft">
            <p className="text-xs font-semibold uppercase tracking-wide text-slate-400">{label}</p>
            <p className="mt-1 font-display text-2xl font-bold text-navy">{n}</p>
          </div>
        ))}
      </div>

      <div className="mt-6 flex flex-wrap gap-2">
        {FILTERS.map((f) => (
          <button
            key={f.id || 'all'}
            type="button"
            onClick={() => setStatus(f.id)}
            className={`rounded-full px-3 py-1 text-xs font-semibold ${status === f.id ? 'bg-primary text-white' : 'bg-slate-100 text-slate-600'}`}
          >
            {f.label}
          </button>
        ))}
      </div>

      {loading ? (
        <p className="mt-6 text-sm text-slate-500">Loading…</p>
      ) : !(data?.jobs || []).length ? (
        <div className="mt-6"><Alert>No engine jobs found.</Alert></div>
      ) : (
        <div className="mt-6 overflow-x-auto rounded-2xl border border-slate-100 bg-white shadow-soft">
          <table className="min-w-full text-left text-sm">
            <thead className="border-b bg-slate-50 text-xs uppercase text-slate-500">
              <tr>
                <th className="px-4 py-3">Job</th>
                <th className="px-4 py-3">Lecture</th>
                <th className="px-4 py-3">Status</th>
                <th className="px-4 py-3">Progress</th>
                <th className="px-4 py-3">Tokens</th>
                <th className="px-4 py-3">Cost</th>
                <th className="px-4 py-3">Time</th>
                <th className="px-4 py-3">Actions</th>
              </tr>
            </thead>
            <tbody>
              {(data.jobs || []).map((j) => (
                <tr key={j.id} className="border-b border-slate-50">
                  <td className="px-4 py-3 font-medium">#{j.id}</td>
                  <td className="px-4 py-3">
                    <span className="block">{j.lecture_title || j.lecture_id}</span>
                    <span className="block text-xs text-slate-400">{j.course_title}</span>
                  </td>
                  <td className="px-4 py-3 capitalize">{j.status}</td>
                  <td className="px-4 py-3">{j.progress || 0}%</td>
                  <td className="px-4 py-3">{j.total_tokens || 0}</td>
                  <td className="px-4 py-3">${Number(j.estimated_cost || 0).toFixed(4)}</td>
                  <td className="px-4 py-3">{j.generation_seconds ? `${j.generation_seconds}s` : '—'}</td>
                  <td className="px-4 py-3">
                    <div className="flex flex-wrap gap-1">
                      <button type="button" className="rounded-lg bg-slate-100 px-2 py-1 text-xs" onClick={() => adminAiService.job(j.id).then(({ data: r }) => setSelected(r.data?.job))}>
                        View
                      </button>
                      {['failed', 'pending', 'processing'].includes(j.status) && (
                        <button type="button" className="rounded-lg bg-primary/10 px-2 py-1 text-xs text-primary" onClick={() => act(adminAiService.resume, j.id, 'Resumed')}>
                          Resume
                        </button>
                      )}
                      {j.status === 'failed' && (
                        <button type="button" className="rounded-lg bg-amber-50 px-2 py-1 text-xs text-amber-700" onClick={() => act(adminAiService.retry, j.id, 'Retry queued')}>
                          Retry
                        </button>
                      )}
                      {['pending', 'processing', 'failed'].includes(j.status) && (
                        <button type="button" className="rounded-lg bg-red-50 px-2 py-1 text-xs text-red-600" onClick={() => act(adminAiService.cancel, j.id, 'Cancelled')}>
                          Cancel
                        </button>
                      )}
                      {['pending', 'processing'].includes(j.status) && (
                        <button type="button" className="rounded-lg bg-slate-100 px-2 py-1 text-xs" onClick={() => act(adminAiService.process, j.id, 'Step advanced')}>
                          Step
                        </button>
                      )}
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}

      {selected && (
        <div className="mt-6 rounded-2xl border border-slate-100 bg-white p-5 shadow-soft">
          <div className="flex items-center justify-between">
            <h3 className="font-display text-lg font-bold text-navy">Job #{selected.id} stages</h3>
            <button type="button" className="text-sm text-slate-500" onClick={() => setSelected(null)}>Close</button>
          </div>
          <p className="mt-1 text-sm text-slate-500">{selected.stage_label} · {selected.progress}%</p>
          {selected.error && <p className="mt-2 text-sm text-red-600">{selected.error}</p>}
          <ul className="mt-4 space-y-2">
            {(selected.stages || []).map((s) => (
              <li key={s.stage_key} className="flex items-center gap-2 rounded-xl border border-slate-100 px-3 py-2 text-sm">
                <FiBookOpen className="text-slate-400" />
                <span className="flex-1">{s.title}</span>
                <span className="capitalize text-slate-500">{s.status}</span>
                <FiChevronRight className="text-slate-300" />
              </li>
            ))}
          </ul>
        </div>
      )}
    </div>
  )
}
