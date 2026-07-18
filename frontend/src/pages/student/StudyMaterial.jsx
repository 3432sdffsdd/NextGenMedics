import { useEffect, useState, lazy, Suspense } from 'react'
import { Link } from 'react-router-dom'
import { FiFilm, FiCheck, FiFilter } from 'react-icons/fi'
import { studyMaterialService } from '../../services/api'
import { useToast } from '../../context/ToastContext'
import { EmptyState, Modal } from '../../components/ui'
import MaterialCard from '../../components/dashboard/MaterialCard'

const MaterialViewer = lazy(() => import('../../components/dashboard/MaterialViewer'))

const WATCH_FILTERS = [
  { value: '', label: 'All videos' },
  { value: 'watched', label: 'Watched' },
  { value: 'unwatched', label: 'Unwatched' },
]

export default function StudyMaterial() {
  const toast = useToast()
  const [summary, setSummary] = useState({ total: 0, watched: 0, unwatched: 0 })
  const [topics, setTopics] = useState([])
  const [videos, setVideos] = useState([])
  const [filters, setFilters] = useState({ topic: '', watch_status: '' })
  const [loading, setLoading] = useState(true)
  const [viewing, setViewing] = useState(null)
  const [toggling, setToggling] = useState(null)

  const load = () => {
    setLoading(true)
    studyMaterialService.list(filters)
      .then(({ data }) => {
        const d = data.data || {}
        setSummary(d.summary || { total: 0, watched: 0, unwatched: 0 })
        setTopics(d.topics || [])
        setVideos(d.videos || [])
      })
      .catch((err) => toast.error(err.message || 'Failed to load videos'))
      .finally(() => setLoading(false))
  }

  useEffect(() => { load() }, [filters.topic, filters.watch_status])

  const toggleWatched = async (resource, next) => {
    setToggling(resource.id)
    try {
      const { data } = await studyMaterialService.setWatched(resource.id, next)
      setSummary(data.data?.summary || summary)
      setVideos((prev) => prev.map((v) => (
        v.id === resource.id ? { ...v, watched: next } : v
      )).filter((v) => {
        if (filters.watch_status === 'watched') return v.watched
        if (filters.watch_status === 'unwatched') return !v.watched
        return true
      }))
    } catch (err) {
      toast.error(err.message || 'Could not update watch status')
    } finally {
      setToggling(null)
    }
  }

  return (
    <div>
      <h2 className="font-display text-2xl font-bold text-navy">Lecture Videos</h2>
      <p className="text-sm text-slate-500">
        All lecture videos from your courses. Mark each as watched when you finish it.
      </p>

      <div className="mt-6 grid gap-4 sm:grid-cols-3">
        <div className="rounded-2xl border border-slate-100 bg-white p-5 shadow-soft">
          <p className="text-xs font-semibold uppercase text-slate-400">Total uploaded</p>
          <p className="mt-1 text-3xl font-bold text-navy">{summary.total}</p>
        </div>
        <div className="rounded-2xl border border-emerald-100 bg-emerald-50/50 p-5 shadow-soft">
          <p className="text-xs font-semibold uppercase text-emerald-600">Watched</p>
          <p className="mt-1 text-3xl font-bold text-emerald-700">{summary.watched}</p>
        </div>
        <div className="rounded-2xl border border-amber-100 bg-amber-50/40 p-5 shadow-soft">
          <p className="text-xs font-semibold uppercase text-amber-600">Unwatched</p>
          <p className="mt-1 text-3xl font-bold text-amber-700">{summary.unwatched}</p>
        </div>
      </div>

      <div className="mt-6 grid gap-4 rounded-2xl border border-slate-100 bg-white p-5 shadow-soft sm:grid-cols-2">
        <label className="block">
          <span className="mb-1 flex items-center gap-1 text-xs font-semibold text-slate-500">
            <FiFilter size={12} /> Topic
          </span>
          <select
            value={filters.topic}
            onChange={(e) => setFilters((f) => ({ ...f, topic: e.target.value }))}
            className="input-field text-sm"
          >
            <option value="">All topics</option>
            {topics.map((t) => (
              <option key={t} value={t}>{t}</option>
            ))}
          </select>
        </label>
        <label className="block">
          <span className="mb-1 block text-xs font-semibold text-slate-500">Watch status</span>
          <select
            value={filters.watch_status}
            onChange={(e) => setFilters((f) => ({ ...f, watch_status: e.target.value }))}
            className="input-field text-sm"
          >
            {WATCH_FILTERS.map((f) => (
              <option key={f.value || 'all'} value={f.value}>{f.label}</option>
            ))}
          </select>
        </label>
      </div>

      {loading ? (
        <p className="mt-8 text-center text-sm text-slate-400">Loading videos…</p>
      ) : videos.length === 0 ? (
        <div className="mt-8">
          <EmptyState
            icon={FiFilm}
            title="No lecture videos found"
            description="Videos uploaded by your teachers will appear here."
          />
        </div>
      ) : (
        <div className="mt-6 space-y-4">
          {videos.map((v) => (
            <div key={v.id} className="rounded-2xl border border-slate-100 bg-white p-4 shadow-soft">
              <div className="mb-3 flex flex-wrap items-center justify-between gap-2 text-xs text-slate-500">
                <div className="flex flex-wrap items-center gap-2">
                  <span className="font-medium text-navy">{v.course_title}</span>
                  <span>·</span>
                  <span>{v.topic || v.lecture_title}</span>
                </div>
                <span className={`inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 font-semibold ${
                  v.watched ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500'
                }`}>
                  {v.watched ? <><FiCheck size={12} /> Watched</> : 'Unwatched'}
                </span>
              </div>
              <MaterialCard
                resource={v}
                showWatchedCheckbox
                watched={!!v.watched}
                watchedDisabled={toggling === v.id}
                onToggleWatched={(next) => toggleWatched(v, next)}
                onView={(res) => {
                  if (res.external_url && !res.file_path) {
                    window.open(res.external_url, '_blank', 'noreferrer')
                  } else {
                    setViewing(res)
                  }
                }}
              />
              <div className="mt-2">
                <Link
                  to={`/student/courses/${v.course_id}`}
                  className="text-xs font-semibold text-primary hover:underline"
                >
                  Open course →
                </Link>
              </div>
            </div>
          ))}
        </div>
      )}

      <Modal open={!!viewing} onClose={() => setViewing(null)} title={viewing?.title} size="xl">
        {viewing && (
          <Suspense fallback={<p className="py-12 text-center text-sm text-slate-400">Loading preview…</p>}>
            <MaterialViewer resource={viewing} />
          </Suspense>
        )}
      </Modal>
    </div>
  )
}
