import { useEffect, useState, lazy, Suspense } from 'react'
import { FiFilm, FiPlay, FiCheck } from 'react-icons/fi'
import { videoTrackingService, studyMaterialService } from '../../services/api'
import { formatWatchTime } from '../../utils/videoTrackingClient'
import { useToast } from '../../context/ToastContext'
import { EmptyState, Modal } from '../../components/ui'

const MaterialViewer = lazy(() => import('../../components/dashboard/MaterialViewer'))

function StatusPill({ status }) {
  const map = {
    completed: 'bg-emerald-100 text-emerald-800',
    watching: 'bg-amber-100 text-amber-800',
    not_started: 'bg-slate-100 text-slate-600',
  }
  const label = { completed: 'Completed', watching: 'Watching', not_started: 'Not Started' }[status] || status
  return <span className={`rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase ${map[status] || map.not_started}`}>{label}</span>
}

function ProgressBar({ value }) {
  const v = Math.max(0, Math.min(100, Number(value) || 0))
  return (
    <div className="h-2 w-full overflow-hidden rounded-full bg-slate-100">
      <div className="h-2 rounded-full bg-teal-500" style={{ width: `${v}%` }} />
    </div>
  )
}

export default function VideoProgress() {
  const toast = useToast()
  const [loading, setLoading] = useState(true)
  const [summary, setSummary] = useState(null)
  const [videos, setVideos] = useState([])
  const [subjects, setSubjects] = useState([])
  const [timeline, setTimeline] = useState([])
  const [statusFilter, setStatusFilter] = useState('')
  const [viewing, setViewing] = useState(null)
  const [tab, setTab] = useState('lectures')

  const load = () => {
    setLoading(true)
    videoTrackingService.dashboard()
      .then(({ data }) => {
        const d = data.data || {}
        setSummary(d.summary)
        setSubjects(d.subjects || [])
        setTimeline(d.timeline || [])
        let list = d.videos || []
        if (statusFilter) list = list.filter((v) => v.status === statusFilter)
        setVideos(list)
      })
      .catch((err) => toast.error(err.message || 'Failed to load video progress'))
      .finally(() => setLoading(false))
  }

  useEffect(() => { load() }, [statusFilter])

  const openVideo = async (row) => {
    try {
      // Load resource details from study material list for file_path
      const { data } = await studyMaterialService.list({})
      const match = (data.data?.videos || []).find((v) => v.id === row.resource_id || v.resource_id === row.resource_id)
      if (match) {
        setViewing({ ...match, id: match.id || row.resource_id, type: 'video' })
      } else {
        setViewing({
          id: row.resource_id,
          type: 'video',
          title: row.video_title,
          file_path: row.file_path,
          file_size: row.file_size,
        })
      }
    } catch {
      setViewing({
        id: row.resource_id,
        type: 'video',
        title: row.video_title,
        file_path: row.file_path,
        file_size: row.file_size,
      })
    }
  }

  if (loading) return <p className="py-16 text-center text-slate-400">Loading video progress…</p>

  return (
    <div className="pb-10">
      <h2 className="font-display text-2xl font-bold text-navy">Video Progress</h2>
      <p className="text-sm text-slate-500">Automatic watch tracking across all your lecture videos.</p>

      <div className="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
        {[
          ['Total videos', summary?.total_videos ?? 0],
          ['Completed', summary?.completed ?? 0],
          ['Watching', summary?.watching ?? 0],
          ['Not started', summary?.not_started ?? 0],
          ['Avg watch %', `${summary?.average_watch_pct ?? 0}%`],
        ].map(([label, val]) => (
          <div key={label} className="rounded-2xl border border-slate-100 bg-white p-4 shadow-soft">
            <p className="text-[10px] uppercase text-slate-400">{label}</p>
            <p className="mt-1 text-2xl font-bold text-navy">{val}</p>
          </div>
        ))}
      </div>

      <div className="mt-4 flex flex-wrap gap-2">
        {['lectures', 'subjects', 'timeline'].map((t) => (
          <button key={t} type="button" onClick={() => setTab(t)}
            className={`rounded-full px-3 py-1 text-xs font-semibold capitalize ${tab === t ? 'bg-teal-600 text-white' : 'bg-slate-100'}`}>
            {t}
          </button>
        ))}
      </div>

      {tab === 'lectures' && (
        <div className="mt-6 space-y-3">
          <div className="flex flex-wrap gap-2">
            {[['','All'],['not_started','Not Started'],['watching','Watching'],['completed','Completed']].map(([v, l]) => (
              <button key={v || 'all'} type="button" onClick={() => setStatusFilter(v)}
                className={`rounded-full px-3 py-1 text-xs font-semibold ${statusFilter === v ? 'bg-navy text-white' : 'bg-slate-100'}`}>
                {l}
              </button>
            ))}
          </div>
          {!videos.length && <EmptyState title="No videos" description="No lecture videos match this filter." />}
          {videos.map((v) => {
            const remaining = Math.max(0, (Number(v.duration_seconds) || 0) - (Number(v.watched_seconds) || 0))
            return (
              <div key={v.resource_id} className="rounded-2xl border border-slate-100 bg-white p-4 shadow-soft">
                <div className="flex flex-wrap items-start justify-between gap-3">
                  <div className="min-w-0">
                    <div className="flex flex-wrap items-center gap-2">
                      <FiFilm className="text-teal-600" />
                      <h3 className="font-bold text-navy">{v.lecture_title || v.video_title}</h3>
                      <StatusPill status={v.status} />
                    </div>
                    <p className="mt-1 text-xs text-slate-500">{v.course_title} · {v.video_title}</p>
                    <p className="mt-1 text-xs text-slate-400">
                      Duration {formatWatchTime(v.duration_seconds)} · Remaining {formatWatchTime(remaining)}
                      {v.last_watched_at ? ` · Last viewed ${new Date(v.last_watched_at).toLocaleString()}` : ''}
                      {v.completed_at ? ` · Completed ${new Date(v.completed_at).toLocaleDateString()}` : ''}
                    </p>
                  </div>
                  <div className="w-40 text-right">
                    <p className="text-sm font-bold text-teal-700">{Math.round(v.completion_pct)}%</p>
                    <ProgressBar value={v.completion_pct} />
                    <button type="button" className="btn-primary mt-2 text-xs" onClick={() => openVideo(v)}>
                      {v.status === 'completed' ? <><FiCheck className="mr-1 inline" /> Rewatch</> : <><FiPlay className="mr-1 inline" /> {v.status === 'watching' ? 'Resume' : 'Watch'}</>}
                    </button>
                  </div>
                </div>
              </div>
            )
          })}
        </div>
      )}

      {tab === 'subjects' && (
        <div className="mt-6 space-y-3">
          {subjects.map((s) => (
            <div key={s.course_id} className="rounded-2xl border border-slate-100 bg-white p-5 shadow-soft">
              <div className="flex justify-between"><h3 className="font-bold text-navy">{s.subject_name}</h3><span className="text-sm font-semibold text-teal-600">{s.completion_pct}%</span></div>
              <ProgressBar value={s.completion_pct} />
              <div className="mt-3 grid grid-cols-2 gap-2 text-xs text-slate-500 sm:grid-cols-4">
                <p>Total {s.total_videos}</p>
                <p>Completed {s.completed_videos}</p>
                <p>Remaining {s.remaining_videos}</p>
                <p>Avg watch {s.average_watch_pct}%</p>
              </div>
            </div>
          ))}
          {!subjects.length && <p className="text-sm text-slate-400">No subject progress yet.</p>}
        </div>
      )}

      {tab === 'timeline' && (
        <div className="mt-6 space-y-2">
          {timeline.map((e, i) => (
            <div key={`${e.created_at}-${i}`} className="flex gap-3 rounded-xl border border-slate-100 bg-white px-4 py-3 text-sm">
              <span className="w-36 shrink-0 text-xs font-semibold text-teal-700">{new Date(e.created_at).toLocaleString()}</span>
              <span className="capitalize text-navy">{e.event_type.replace(/_/g, ' ')}</span>
              <span className="text-slate-500">{e.lecture_title || e.video_title}</span>
            </div>
          ))}
          {!timeline.length && <p className="text-sm text-slate-400">No activity yet — start watching a lecture.</p>}
        </div>
      )}

      <Modal open={!!viewing} onClose={() => { setViewing(null); load() }} title={viewing?.title || 'Video'} size="xl">
        {viewing && (
          <Suspense fallback={<p className="py-8 text-center text-slate-400">Loading player…</p>}>
            <MaterialViewer resource={viewing} />
          </Suspense>
        )}
      </Modal>
    </div>
  )
}
