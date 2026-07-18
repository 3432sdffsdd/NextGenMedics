import { useMemo, useState, lazy, Suspense } from 'react'
import { FiPlus, FiFolder, FiChevronDown, FiChevronRight, FiTrash2, FiUpload, FiLink, FiEdit2 } from 'react-icons/fi'
import { contentService } from '../../services/api'
import { useToast } from '../../context/ToastContext'
import { useConfirm } from '../../context/ConfirmContext'
import { Modal, Button, EmptyState } from '../../components/ui'
import MultiFileUploadField from '../../components/dashboard/MultiFileUploadField'
import MaterialCard from '../../components/dashboard/MaterialCard'
const MaterialViewer = lazy(() => import('../../components/dashboard/MaterialViewer'))
import { ACCEPT_ALL, fileKind } from '../../utils/files'

function apiError(err, fallback) {
  const errors = err.response?.data?.errors
  if (errors && typeof errors === 'object') {
    const first = Object.values(errors).flat().find(Boolean)
    if (first) return first
  }
  return err.response?.data?.message || fallback
}

export default function ContentManager({ courseId, structure, reload }) {
  const toast = useToast()
  const confirm = useConfirm()

  const [moduleTitle, setModuleTitle] = useState('')
  const [chapterForm, setChapterForm] = useState({ module_id: '', title: '' })
  const [lectureForm, setLectureForm] = useState({ chapter_id: '', title: '' })
  const [collapsed, setCollapsed] = useState({})

  const [uploadFor, setUploadFor] = useState(null) // lecture object
  const [materialMode, setMaterialMode] = useState('file') // 'file' | 'link'
  const [uploadItems, setUploadItems] = useState([])
  const [materialTitle, setMaterialTitle] = useState('')
  const [externalUrl, setExternalUrl] = useState('')
  const [linkType, setLinkType] = useState('video')
  const [uploading, setUploading] = useState(false)
  const [progress, setProgress] = useState(0)

  const [viewing, setViewing] = useState(null)
  const [renaming, setRenaming] = useState(null)
  const [renameValue, setRenameValue] = useState('')
  const [editEntity, setEditEntity] = useState(null) // { entity, id, title, label }
  const [editEntityValue, setEditEntityValue] = useState('')

  const allChapters = useMemo(
    () => structure.flatMap((m) => (m.chapters || []).map((ch) => ({ ...ch, moduleTitle: m.title }))),
    [structure]
  )

  const toggle = (id) => setCollapsed((c) => ({ ...c, [id]: !c[id] }))

  const addModule = async (e) => {
    e.preventDefault()
    if (!courseId || Number.isNaN(courseId)) return toast.error('Invalid course. Go back to My Courses and open the course again.')
    if (!moduleTitle.trim()) return toast.error('Module title is required')
    if (moduleTitle.trim().length < 2) return toast.error('Module title must be at least 2 characters')
    try {
      await contentService.createModule({ course_id: courseId, title: moduleTitle.trim() })
      setModuleTitle('')
      toast.success('Module added')
      reload()
    } catch (err) {
      toast.error(apiError(err, 'Could not add module'))
    }
  }

  const addChapter = async (e) => {
    e.preventDefault()
    if (!chapterForm.module_id) return toast.error('Please select a module')
    if (!chapterForm.title.trim()) return toast.error('Chapter title is required')
    try {
      await contentService.createChapter({ module_id: Number(chapterForm.module_id), title: chapterForm.title.trim() })
      setChapterForm({ module_id: '', title: '' })
      toast.success('Chapter added')
      reload()
    } catch (err) {
      toast.error(apiError(err, 'Could not add chapter'))
    }
  }

  const addLecture = async (e) => {
    e.preventDefault()
    if (!lectureForm.chapter_id) return toast.error('Please select a chapter')
    if (!lectureForm.title.trim()) return toast.error('Lecture title is required')
    try {
      await contentService.createLecture({ chapter_id: Number(lectureForm.chapter_id), title: lectureForm.title.trim(), content_type: 'mixed' })
      setLectureForm({ chapter_id: '', title: '' })
      toast.success('Lecture added')
      reload()
    } catch (err) {
      toast.error(apiError(err, 'Could not add lecture'))
    }
  }

  const openUpload = (lecture) => {
    setUploadFor(lecture)
    setMaterialMode('file')
    setUploadItems([])
    setMaterialTitle('')
    setExternalUrl('')
    setLinkType('video')
    setProgress(0)
  }

  const doUpload = async () => {
    if (uploading) return

    if (materialMode === 'link') {
      const url = externalUrl.trim()
      if (!url) return toast.error('Please enter a link URL')
      if (!/^https?:\/\//i.test(url)) return toast.error('URL must start with http:// or https://')
      if (!materialTitle.trim()) return toast.error('Please enter a title for this link')

      const fd = new FormData()
      fd.append('lecture_id', uploadFor.id)
      fd.append('type', linkType)
      fd.append('title', materialTitle.trim())
      fd.append('external_url', url)

      setUploading(true)
      setProgress(0)
      try {
        await contentService.uploadResource(fd)
        toast.success('Link added successfully')
        setUploadFor(null)
        setUploadItems([])
        reload()
      } catch (err) {
        toast.error(err.response?.data?.message || 'Could not save material. Please try again.')
      } finally {
        setUploading(false)
      }
      return
    }

    if (!uploadItems.length) return toast.error('Please add at least one file')
    const missingTitle = uploadItems.find((x) => !x.title?.trim())
    if (missingTitle) return toast.error('Please enter a title for each file')

    setUploading(true)
    setProgress(0)
    try {
      const total = uploadItems.length
      for (let i = 0; i < total; i++) {
        const item = uploadItems[i]
        const { label } = fileKind({ file_path: item.file.name })
        const typeMap = { Video: 'video', PDF: 'pdf', Slides: 'slides' }
        const fd = new FormData()
        fd.append('lecture_id', uploadFor.id)
        fd.append('type', typeMap[label] || 'download')
        fd.append('title', item.title.trim())
        fd.append('file', item.file)
        await contentService.uploadResource(fd, total === 1 ? (e) => {
          if (e.total) setProgress(Math.round((e.loaded / e.total) * 100))
        } : undefined)
        if (total > 1) setProgress(Math.round(((i + 1) / total) * 100))
      }
      toast.success(total === 1 ? 'Material uploaded successfully' : `${total} materials uploaded successfully`)
      setUploadFor(null)
      setUploadItems([])
      reload()
    } catch (err) {
      toast.error(err.response?.data?.message || 'Could not save material. Please try again.')
    } finally {
      setUploading(false)
    }
  }

  const canSubmit = materialMode === 'link'
    ? externalUrl.trim() && materialTitle.trim()
    : uploadItems.length > 0 && uploadItems.every((x) => x.title?.trim())

  const saveRename = async () => {
    if (!renameValue.trim()) return toast.error('Title is required')
    try {
      await contentService.updateResource(renaming.id, { title: renameValue.trim() })
      toast.success('Material updated')
      setRenaming(null)
      reload()
    } catch { toast.error('Could not update material') }
  }

  const deleteResource = async (r) => {
    if (!(await confirm({ title: 'Delete material', message: `Are you sure you want to delete "${r.title}"?` }))) return
    try {
      await contentService.deleteResource(r.id)
      toast.success('Deleted successfully')
      reload()
    } catch { toast.error('Could not delete material') }
  }

  const deleteEntity = async (entity, id, label) => {
    if (!(await confirm({ title: `Delete ${label}`, message: `Are you sure you want to delete this ${label}? All nested content will be removed.` }))) return
    try {
      await contentService.delete(entity, id)
      toast.success('Deleted successfully')
      reload()
    } catch { toast.error(`Could not delete ${label}`) }
  }

  const openEditEntity = (entity, id, title, label) => {
    setEditEntity({ entity, id, label })
    setEditEntityValue(title)
  }

  const saveEntityEdit = async () => {
    if (!editEntityValue.trim()) return toast.error('Title is required')
    try {
      await contentService.update(editEntity.entity, editEntity.id, { title: editEntityValue.trim() })
      toast.success(`${editEntity.label} updated`)
      setEditEntity(null)
      reload()
    } catch { toast.error(`Could not update ${editEntity?.label || 'item'}`) }
  }

  return (
    <div className="space-y-6">
      <div className="rounded-xl border border-emerald-100 bg-emerald-50/50 px-4 py-3 text-sm text-slate-600">
        <strong className="text-navy">Student visibility:</strong> Videos, PDFs, and files uploaded here appear immediately for <strong>enrolled students</strong> in the Learn tab — no extra publish step.
        {' '}To generate the full AI study pack, open the <strong>Study tools</strong> tab, select the lecture, and click Generate.
      </div>
      <div className="grid gap-4 lg:grid-cols-3">
        <form onSubmit={addModule} className="rounded-2xl border border-slate-100 bg-white p-4 shadow-soft">
          <h4 className="text-sm font-semibold text-navy">Add Module</h4>
          <input value={moduleTitle} onChange={(e) => setModuleTitle(e.target.value)} placeholder="Module title"
            className="mt-3 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" />
          <Button type="submit" size="sm" icon={FiPlus} className="mt-3">Add module</Button>
        </form>

        <form onSubmit={addChapter} className="rounded-2xl border border-slate-100 bg-white p-4 shadow-soft">
          <h4 className="text-sm font-semibold text-navy">Add Chapter</h4>
          <select value={chapterForm.module_id} onChange={(e) => setChapterForm((f) => ({ ...f, module_id: e.target.value }))}
            className="mt-3 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
            <option value="">Select module</option>
            {structure.map((m) => <option key={m.id} value={m.id}>{m.title}</option>)}
          </select>
          <input value={chapterForm.title} onChange={(e) => setChapterForm((f) => ({ ...f, title: e.target.value }))} placeholder="Chapter title"
            className="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" />
          <Button type="submit" size="sm" icon={FiPlus} className="mt-3">Add chapter</Button>
        </form>

        <form onSubmit={addLecture} className="rounded-2xl border border-slate-100 bg-white p-4 shadow-soft">
          <h4 className="text-sm font-semibold text-navy">Add Lecture</h4>
          <select value={lectureForm.chapter_id} onChange={(e) => setLectureForm((f) => ({ ...f, chapter_id: e.target.value }))}
            className="mt-3 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
            <option value="">Select chapter</option>
            {allChapters.map((ch) => <option key={ch.id} value={ch.id}>{ch.moduleTitle} → {ch.title}</option>)}
          </select>
          <input value={lectureForm.title} onChange={(e) => setLectureForm((f) => ({ ...f, title: e.target.value }))} placeholder="Lecture title"
            className="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" />
          <Button type="submit" size="sm" icon={FiPlus} className="mt-3">Add lecture</Button>
        </form>
      </div>

      {structure.length === 0 ? (
        <EmptyState icon={FiFolder} title="No lecture materials uploaded" description="Start by adding a module, then chapters and lectures to upload materials." />
      ) : (
        <div className="space-y-4">
          {structure.map((mod) => (
            <div key={mod.id} className="rounded-2xl border border-slate-100 bg-white shadow-soft">
              <div className="flex items-center justify-between px-5 py-3">
                <button type="button" onClick={() => toggle(`m${mod.id}`)} className="flex items-center gap-2 text-left font-semibold text-navy">
                  {collapsed[`m${mod.id}`] ? <FiChevronRight /> : <FiChevronDown />} {mod.title}
                </button>
                <div className="flex items-center gap-1">
                  <button type="button" onClick={() => openEditEntity('modules', mod.id, mod.title, 'Module')} className="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-primary" aria-label="Edit module"><FiEdit2 size={15} /></button>
                  <button type="button" onClick={() => deleteEntity('modules', mod.id, 'module')} className="rounded-lg p-1.5 text-slate-400 hover:bg-red-50 hover:text-red-500"><FiTrash2 size={15} /></button>
                </div>
              </div>

              {!collapsed[`m${mod.id}`] && (
                <div className="space-y-3 px-5 pb-5">
                  {(mod.chapters || []).length === 0 && <p className="text-sm text-slate-400">No chapters yet.</p>}
                  {(mod.chapters || []).map((ch) => (
                    <div key={ch.id} className="rounded-xl border border-slate-100 bg-slate-50/60 p-4">
                      <div className="flex items-center justify-between">
                        <p className="text-sm font-semibold text-slate-700">{ch.title}</p>
                        <div className="flex items-center gap-1">
                          <button type="button" onClick={() => openEditEntity('chapters', ch.id, ch.title, 'Topic')} className="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-primary" aria-label="Edit topic"><FiEdit2 size={14} /></button>
                          <button type="button" onClick={() => deleteEntity('chapters', ch.id, 'chapter')} className="rounded-lg p-1.5 text-slate-400 hover:bg-red-50 hover:text-red-500"><FiTrash2 size={14} /></button>
                        </div>
                      </div>

                      {(ch.lectures || []).map((lec) => (
                        <div key={lec.id} className="mt-3 rounded-xl border border-slate-100 bg-white p-4">
                          <div className="flex items-center justify-between">
                            <p className="font-medium text-navy">{lec.title}</p>
                            <div className="flex items-center gap-2">
                              <Button size="sm" variant="secondary" icon={FiPlus} onClick={() => openUpload(lec)}>Material</Button>
                              <button type="button" onClick={() => openEditEntity('lectures', lec.id, lec.title, 'Lecture')} className="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-primary" aria-label="Edit lecture"><FiEdit2 size={14} /></button>
                              <button type="button" onClick={() => deleteEntity('lectures', lec.id, 'lecture')} className="rounded-lg p-1.5 text-slate-400 hover:bg-red-50 hover:text-red-500"><FiTrash2 size={14} /></button>
                            </div>
                          </div>
                          {(lec.resources || []).length > 0 ? (
                            <div className="mt-3 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                              {lec.resources.map((r) => (
                                <MaterialCard key={r.id} resource={r} canManage
                                  onView={setViewing}
                                  onEdit={(res) => { setRenaming(res); setRenameValue(res.title) }}
                                  onDelete={deleteResource} />
                              ))}
                            </div>
                          ) : (
                            <p className="mt-3 text-xs text-slate-400">No materials uploaded for this lecture yet.</p>
                          )}
                        </div>
                      ))}
                      {(ch.lectures || []).length === 0 && <p className="mt-2 text-xs text-slate-400">No lectures in this chapter.</p>}
                    </div>
                  ))}
                </div>
              )}
            </div>
          ))}
        </div>
      )}

      {/* Upload modal */}
      <Modal open={!!uploadFor} onClose={() => !uploading && setUploadFor(null)} title="Add material" subtitle={uploadFor?.title}
        footer={<>
          <Button variant="outline" onClick={() => setUploadFor(null)} disabled={uploading}>Cancel</Button>
          <Button onClick={doUpload} loading={uploading} disabled={!canSubmit}>
            {materialMode === 'link' ? 'Add link' : uploadItems.length > 1 ? `Upload ${uploadItems.length} files` : 'Upload'}
          </Button>
        </>}>
        <div className="space-y-4">
          <div className="flex rounded-xl border border-slate-200 p-1">
            <button
              type="button"
              onClick={() => setMaterialMode('file')}
              className={`flex flex-1 items-center justify-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition ${materialMode === 'file' ? 'bg-primary text-white' : 'text-slate-600 hover:bg-slate-50'}`}
            >
              <FiUpload size={16} /> Upload files
            </button>
            <button
              type="button"
              onClick={() => setMaterialMode('link')}
              className={`flex flex-1 items-center justify-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition ${materialMode === 'link' ? 'bg-primary text-white' : 'text-slate-600 hover:bg-slate-50'}`}
            >
              <FiLink size={16} /> External link
            </button>
          </div>

          {materialMode === 'link' ? (
            <>
              <div>
                <label className="text-sm font-medium text-slate-600">Title (required)</label>
                <input
                  value={materialTitle}
                  onChange={(e) => setMaterialTitle(e.target.value)}
                  placeholder="e.g. Renal Physiology — Lecture 1"
                  className="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"
                />
              </div>
              <div>
                <label className="text-sm font-medium text-slate-600">Link type</label>
                <select
                  value={linkType}
                  onChange={(e) => setLinkType(e.target.value)}
                  className="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"
                >
                  <option value="video">Video link (YouTube, Vimeo, Drive, etc.)</option>
                  <option value="link">General web link</option>
                  <option value="reference">Reference / reading link</option>
                </select>
              </div>
              <div>
                <label className="text-sm font-medium text-slate-600">URL (required)</label>
                <input
                  type="url"
                  value={externalUrl}
                  onChange={(e) => setExternalUrl(e.target.value)}
                  placeholder="https://youtube.com/watch?v=... or https://..."
                  className="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"
                />
                <p className="mt-2 text-xs text-slate-400">
                  Students will see an &quot;Open link&quot; button — the video opens on the external website in a new tab. No file upload needed.
                </p>
              </div>
            </>
          ) : (
            <>
              <MultiFileUploadField
                items={uploadItems}
                onChange={setUploadItems}
                accept={ACCEPT_ALL}
                uploading={uploading}
                hint="Each file needs its own title — students see these names"
              />
              {uploading && progress > 0 && (
                <p className="text-xs text-slate-500">Uploading… {progress}%</p>
              )}
            </>
          )}
        </div>
      </Modal>

      {/* Rename modal */}
      <Modal open={!!renaming} onClose={() => setRenaming(null)} title="Edit material"
        footer={<>
          <Button variant="outline" onClick={() => setRenaming(null)}>Cancel</Button>
          <Button onClick={saveRename}>Save</Button>
        </>}>
        <label className="text-sm font-medium text-slate-600">Title</label>
        <input value={renameValue} onChange={(e) => setRenameValue(e.target.value)}
          className="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" />
      </Modal>

      <Modal open={!!editEntity} onClose={() => setEditEntity(null)} title={`Edit ${editEntity?.label || ''}`}
        footer={<>
          <Button variant="outline" onClick={() => setEditEntity(null)}>Cancel</Button>
          <Button onClick={saveEntityEdit}>Save</Button>
        </>}>
        <label className="text-sm font-medium text-slate-600">{editEntity?.label} name</label>
        <input value={editEntityValue} onChange={(e) => setEditEntityValue(e.target.value)}
          className="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" />
      </Modal>

      {/* Viewer modal */}
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
