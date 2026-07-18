import { useEffect, useState, lazy, Suspense } from 'react'
import { Link } from 'react-router-dom'
import { FiBookOpen, FiPackage } from 'react-icons/fi'
import MaterialCard from './MaterialCard'
const MaterialViewer = lazy(() => import('./MaterialViewer'))
import LectureDiscussion from './LectureDiscussion'
import { Modal, EmptyState } from '../ui'
import { studyMaterialService } from '../../services/api'
import { useToast } from '../../context/ToastContext'

export default function StudentLearnMaterials({ structure, courseId, canDownloadVideos = false }) {
  const toast = useToast()
  const [viewing, setViewing] = useState(null)
  const [discussLecture, setDiscussLecture] = useState(null)
  const [watchedIds, setWatchedIds] = useState(() => new Set())
  const [toggling, setToggling] = useState(null)

  useEffect(() => {
    if (!courseId) return
    studyMaterialService.list({ course_id: courseId })
      .then(({ data }) => {
        const ids = new Set(
          (data.data?.videos || []).filter((v) => v.watched).map((v) => v.id)
        )
        setWatchedIds(ids)
      })
      .catch(() => {})
  }, [courseId])

  const toggleWatched = async (resource, next) => {
    setToggling(resource.id)
    try {
      await studyMaterialService.setWatched(resource.id, next)
      setWatchedIds((prev) => {
        const nextSet = new Set(prev)
        if (next) nextSet.add(resource.id)
        else nextSet.delete(resource.id)
        return nextSet
      })
    } catch (err) {
      toast.error(err.message || 'Could not update watch status')
    } finally {
      setToggling(null)
    }
  }

  if (!structure?.length) {
    return (
      <EmptyState
        icon={FiBookOpen}
        title="No lecture materials uploaded"
        description="Your teacher has not uploaded content yet."
      />
    )
  }

  return (
    <div className="space-y-6">
      {structure.map((mod) => (
        <div key={mod.id} className="rounded-2xl border border-slate-100 bg-white p-6 shadow-soft">
          <h3 className="font-bold text-navy">{mod.title}</h3>
          {(mod.chapters || []).map((ch) => (
            <div key={ch.id} className="ml-4 mt-4 border-l-2 border-primary/20 pl-4">
              <h4 className="font-medium text-slate-700">{ch.title}</h4>
              {(ch.lectures || []).map((lec) => (
                <div key={lec.id} className="mt-4">
                  <p className="font-medium text-navy">{lec.title}</p>
                  {(lec.resources || []).length > 0 ? (
                    <div className="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                      {(lec.resources || []).map((r) => (
                        <MaterialCard
                          key={r.id}
                          resource={r}
                          allowVideoDownload={canDownloadVideos}
                          showWatchedCheckbox={r.type === 'video'}
                          watched={watchedIds.has(r.id)}
                          watchedDisabled={toggling === r.id}
                          onToggleWatched={(next) => toggleWatched(r, next)}
                          onView={(res) => {
                            if (res.external_url && !res.file_path) {
                              window.open(res.external_url, '_blank', 'noreferrer')
                            } else {
                              setViewing(res)
                            }
                          }}
                        />
                      ))}
                    </div>
                  ) : (
                    <p className="mt-2 text-xs text-slate-400">No materials for this lecture yet.</p>
                  )}
                  <div className="mt-3 flex flex-wrap items-center gap-3">
                    <Link
                      to={`/student/study-pack?lecture=${lec.id}`}
                      className="inline-flex items-center gap-1 text-xs font-semibold text-primary hover:underline"
                    >
                      <FiPackage size={12} /> Study Pack
                    </Link>
                    <button
                      type="button"
                      onClick={() => setDiscussLecture(discussLecture === lec.id ? null : lec.id)}
                      className="text-xs font-semibold text-primary hover:underline"
                    >
                      {discussLecture === lec.id ? 'Hide discussion' : 'Discuss this lecture'}
                    </button>
                  </div>
                  {discussLecture === lec.id && (
                    <div className="mt-3 border-t border-slate-200 pt-4">
                      <LectureDiscussion lectureId={lec.id} courseId={courseId} />
                    </div>
                  )}
                </div>
              ))}
            </div>
          ))}
        </div>
      ))}

      <Modal open={!!viewing} onClose={() => setViewing(null)} title={viewing?.title} size="xl">
        {viewing && (
          <Suspense fallback={<p className="py-12 text-center text-sm text-slate-400">Loading preview…</p>}>
            <MaterialViewer resource={viewing} allowVideoDownload={canDownloadVideos} />
          </Suspense>
        )}
      </Modal>
    </div>
  )
}
