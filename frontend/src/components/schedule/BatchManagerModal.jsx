import { useEffect, useState } from 'react'
import { FiPlus, FiTrash2, FiUsers, FiLayers } from 'react-icons/fi'
import { Modal, Button, EmptyState } from '../ui'
import { batchesService, adminCoursesService } from '../../services/api'

const inputCls =
  'w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/15'

export default function BatchManagerModal({ open, onClose, courses = [], defaultCourseId }) {
  const [courseId, setCourseId] = useState(defaultCourseId || '')
  const [batches, setBatches] = useState([])
  const [enrolled, setEnrolled] = useState([])
  const [selected, setSelected] = useState(null)
  const [memberIds, setMemberIds] = useState(new Set())
  const [newBatch, setNewBatch] = useState({ name: '', code: '' })
  const [busy, setBusy] = useState(false)

  useEffect(() => {
    if (open) setCourseId(defaultCourseId || '')
  }, [open, defaultCourseId])

  const loadBatches = () => {
    if (!courseId) { setBatches([]); return }
    batchesService.byCourse(courseId).then(({ data }) => setBatches(data.data || [])).catch(() => setBatches([]))
  }

  useEffect(() => {
    setSelected(null)
    loadBatches()
    if (courseId) {
      adminCoursesService.students(courseId)
        .then(({ data }) => setEnrolled(data.data || []))
        .catch(() => setEnrolled([]))
    } else {
      setEnrolled([])
    }
  }, [courseId])

  const openBatch = async (b) => {
    setSelected(b)
    const { data } = await batchesService.students(b.id)
    setMemberIds(new Set((data.data || []).map((s) => s.id)))
  }

  const createBatch = async () => {
    if (!newBatch.name || !courseId) return
    setBusy(true)
    try {
      await batchesService.create({ course_id: Number(courseId), name: newBatch.name, code: newBatch.code || null })
      setNewBatch({ name: '', code: '' })
      loadBatches()
    } finally { setBusy(false) }
  }

  const deleteBatch = async (b) => {
    await batchesService.remove(b.id)
    if (selected?.id === b.id) setSelected(null)
    loadBatches()
  }

  const toggleMember = async (student) => {
    if (!selected) return
    const next = new Set(memberIds)
    if (next.has(student.id)) {
      await batchesService.unassign(selected.id, student.id)
      next.delete(student.id)
    } else {
      await batchesService.assign(selected.id, [student.id])
      next.add(student.id)
    }
    setMemberIds(next)
    loadBatches()
  }

  return (
    <Modal open={open} onClose={onClose} size="xl" title="Manage batches" subtitle="Group students into cohorts per course">
      <div className="mb-4">
        <label className="mb-1 block text-xs font-semibold text-slate-500">Course</label>
        <select className={inputCls} value={courseId} onChange={(e) => setCourseId(e.target.value)}>
          <option value="">Select course</option>
          {courses.map((c) => <option key={c.id} value={c.id}>{c.title}</option>)}
        </select>
      </div>

      {!courseId ? (
        <EmptyState icon={FiLayers} title="Pick a course" description="Select a course to manage its batches." />
      ) : (
        <div className="grid gap-4 md:grid-cols-2">
          {/* Batches list */}
          <div>
            <div className="flex items-end gap-2">
              <div className="flex-1">
                <input className={inputCls} placeholder="New batch name" value={newBatch.name}
                  onChange={(e) => setNewBatch((b) => ({ ...b, name: e.target.value }))} />
              </div>
              <input className={`${inputCls} w-24`} placeholder="Code" value={newBatch.code}
                onChange={(e) => setNewBatch((b) => ({ ...b, code: e.target.value }))} />
              <Button size="sm" onClick={createBatch} loading={busy} disabled={!newBatch.name}><FiPlus size={14} /></Button>
            </div>

            <ul className="mt-3 space-y-2">
              {batches.map((b) => (
                <li key={b.id}
                  className={`flex items-center justify-between rounded-xl border px-3 py-2 text-sm transition ${
                    selected?.id === b.id ? 'border-blue-300 bg-blue-50' : 'border-slate-200 hover:bg-slate-50'
                  }`}>
                  <button type="button" className="flex-1 text-left" onClick={() => openBatch(b)}>
                    <span className="font-semibold text-slate-700">{b.name}</span>
                    {b.code && <span className="ml-2 text-xs text-slate-400">{b.code}</span>}
                    <span className="ml-2 inline-flex items-center gap-1 text-xs text-slate-400">
                      <FiUsers size={12} /> {b.student_count}
                    </span>
                  </button>
                  <button type="button" onClick={() => deleteBatch(b)} className="rounded-lg p-1.5 text-slate-400 hover:bg-red-50 hover:text-red-500">
                    <FiTrash2 size={14} />
                  </button>
                </li>
              ))}
              {batches.length === 0 && <li className="py-4 text-center text-sm text-slate-400">No batches yet.</li>}
            </ul>
          </div>

          {/* Members */}
          <div className="rounded-xl border border-slate-100 bg-slate-50 p-3">
            {selected ? (
              <>
                <p className="mb-2 text-sm font-semibold text-slate-700">Students in {selected.name}</p>
                <ul className="max-h-72 space-y-1 overflow-y-auto">
                  {enrolled.map((st) => (
                    <li key={st.id}>
                      <label className="flex cursor-pointer items-center gap-2 rounded-lg px-2 py-1.5 text-sm hover:bg-white">
                        <input type="checkbox" className="h-4 w-4 rounded border-slate-300 text-blue-600"
                          checked={memberIds.has(st.id)} onChange={() => toggleMember(st)} />
                        <span className="text-slate-700">{st.first_name} {st.last_name}</span>
                        <span className="text-xs text-slate-400">{st.email}</span>
                      </label>
                    </li>
                  ))}
                  {enrolled.length === 0 && <li className="py-3 text-center text-sm text-slate-400">No students enrolled in this course.</li>}
                </ul>
              </>
            ) : (
              <p className="py-8 text-center text-sm text-slate-400">Select a batch to assign students.</p>
            )}
          </div>
        </div>
      )}
    </Modal>
  )
}
