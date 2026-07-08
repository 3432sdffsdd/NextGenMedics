import { useCallback, useEffect, useState } from 'react'
import { FiPlus, FiEdit2, FiTrash2, FiLock, FiUnlock, FiUsers, FiFileText, FiDownload, FiEye, FiLink } from 'react-icons/fi'
import { assignmentService, downloadMedia } from '../../services/api'
import { useToast } from '../../context/ToastContext'
import { useConfirm } from '../../context/ConfirmContext'
import { Modal, Button, Badge, EmptyState } from '../../components/ui'
import MultiFileUploadField from '../../components/dashboard/MultiFileUploadField'
import ViewFileButton from '../../components/dashboard/ViewFileButton'
import AssignmentHtmlImport from '../../components/dashboard/AssignmentHtmlImport'
import { ACCEPT_ALL, formatDateTime, appendTitledFiles, normalizeExternalUrl } from '../../utils/files'

const STATUS_TONE = { draft: 'neutral', published: 'success', closed: 'warning' }
const empty = { title: '', description: '', instructions: '', due_date: '', max_marks: 100, assignment_type: 'file', external_url: '' }

export default function AssignmentManager({ courseId }) {
  const toast = useToast()
  const confirm = useConfirm()
  const [items, setItems] = useState([])
  const [loading, setLoading] = useState(true)
  const [editing, setEditing] = useState(null) // {} for new, object for edit
  const [form, setForm] = useState(empty)
  const [attachFiles, setAttachFiles] = useState([])
  const [htmlFile, setHtmlFile] = useState(null)
  const [htmlPreview, setHtmlPreview] = useState(null)
  const [saving, setSaving] = useState(false)
  const [progress, setProgress] = useState(0)
  const [preview, setPreview] = useState(null)
  const [submissionsFor, setSubmissionsFor] = useState(null)

  const load = useCallback(() => {
    if (!courseId || Number.isNaN(Number(courseId))) {
      setItems([])
      setLoading(false)
      return
    }
    setLoading(true)
    assignmentService.list(courseId)
      .then(({ data }) => setItems(data.data || []))
      .catch((err) => {
        toast.error(err.response?.data?.message || 'Could not load assignments for this course')
      })
      .finally(() => setLoading(false))
  }, [courseId, toast])

  useEffect(() => { load() }, [load])

  const openNew = () => {
    setForm(empty)
    setAttachFiles([])
    setHtmlFile(null)
    setHtmlPreview(null)
    setEditing({})
  }
  const openEdit = (a) => {
    setForm({
      title: a.title || '', description: a.description || '', instructions: a.instructions || '',
      due_date: a.due_date ? a.due_date.replace(' ', 'T').slice(0, 16) : '', max_marks: a.max_marks || 100,
      assignment_type: a.assignment_type || 'file',
      external_url: a.external_url || '',
    })
    setAttachFiles([])
    setHtmlFile(null)
    setHtmlPreview(null)
    setEditing(a)
  }

  const buildFormData = () => {
    const fd = new FormData()
    Object.entries(form).forEach(([k, v]) => {
      fd.append(k, k === 'external_url' ? normalizeExternalUrl(v) : v)
    })
    if (form.assignment_type === 'interactive_test') {
      if (htmlFile) fd.append('attachment', htmlFile)
    } else {
      appendTitledFiles(fd, attachFiles)
      const n = attachFiles.filter((x) => x?.file).length
      if (n > 0) fd.append('expected_files', String(n))
    }
    return fd
  }

  const save = async () => {
    if (!courseId || Number.isNaN(Number(courseId))) {
      return toast.error('Invalid course — open the course from My Courses and try again')
    }
    if (!form.title.trim()) return toast.error('Title is required')
    if (!form.due_date) return toast.error('Assignment deadline is required')
    if (form.assignment_type !== 'interactive_test' && attachFiles.length > 0 && attachFiles.some((x) => !(x.title || '').trim())) {
      return toast.error('Give each attachment a title before saving')
    }
    if (form.assignment_type === 'interactive_test' && !editing?.id && !htmlFile && !form.external_url.trim()) {
      return toast.error('Upload an HTML file or enter a link for the interactive test')
    }
    setSaving(true)
    setProgress(0)
    const onProg = (e) => { if (e.total) setProgress(Math.round((e.loaded / e.total) * 100)) }
    try {
      if (editing.id) {
        const fd = buildFormData()
        await assignmentService.update(editing.id, fd, onProg)
        toast.success('Assignment updated successfully')
      } else {
        const fd = buildFormData()
        fd.append('course_id', String(courseId))
        const { data } = await assignmentService.create(fd, onProg)
        if (!data?.success) {
          throw new Error(data?.message || 'Create failed')
        }
        const created = data.data
        if (!created?.id) {
          throw new Error('Assignment was not returned by the server. Refresh the page.')
        }
        setItems((prev) => [created, ...prev.filter((a) => a.id !== created.id)])
        toast.success(data.message || 'Assignment created successfully')
      }
      setEditing(null)
      setHtmlPreview(null)
      load()
    } catch (err) {
      const msg = err.response?.data?.message
      const fieldErrors = err.response?.data?.errors
      const detail = fieldErrors
        ? Object.values(fieldErrors).flat().join(' ')
        : null
      toast.error(detail || msg || err.message || 'Could not save assignment')
    } finally { setSaving(false) }
  }

  const remove = async (a) => {
    if (!(await confirm({ title: 'Delete assignment', message: `Are you sure you want to delete "${a.title}"?` }))) return
    try { await assignmentService.remove(a.id); toast.success('Deleted successfully'); load() }
    catch { toast.error('Could not delete assignment') }
  }

  const toggleStatus = async (a) => {
    const next = a.status === 'closed' ? 'published' : 'closed'
    try {
      await assignmentService.setStatus(a.id, next)
      toast.success(next === 'closed' ? 'Submissions closed' : 'Submissions reopened')
      load()
    } catch { toast.error('Could not update status') }
  }

  const removeAttachment = async (attachmentId) => {
    if (!editing?.id || !attachmentId) return
    try {
      await assignmentService.deleteAttachment(editing.id, attachmentId)
      toast.success('Attachment removed')
      setEditing((prev) => ({
        ...prev,
        attachments: (prev.attachments || []).filter((a) => a.id !== attachmentId),
        attachment_path: (prev.attachments || []).filter((a) => a.id !== attachmentId).length ? prev.attachment_path : null,
      }))
      load()
    } catch {
      toast.error('Could not remove attachment')
    }
  }

  const publishAssignment = async (a) => {
    try {
      await assignmentService.setStatus(a.id, 'published')
      toast.success('Assignment published — students can now see it')
      load()
    } catch { toast.error('Could not publish assignment') }
  }

  return (
    <div className="space-y-4">
      {(!courseId || Number.isNaN(Number(courseId))) && (
        <p className="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
          Invalid course link — go to <strong>My Courses</strong>, open a course, then open the <strong>Assignments</strong> tab.
        </p>
      )}
      <div className="flex items-center justify-between">
        <h3 className="font-display text-lg font-bold text-navy">Assignments</h3>
        <Button icon={FiPlus} onClick={openNew}>New assignment</Button>
      </div>

      {loading ? (
        <p className="text-slate-400">Loading assignments…</p>
      ) : items.length === 0 ? (
        <EmptyState icon={FiFileText} title="No assignments yet" description="Create your first assignment for this course."
          action={<Button icon={FiPlus} onClick={openNew}>New assignment</Button>} />
      ) : (
        <div className="space-y-3">
          {items.map((a) => (
            <div key={a.id} className="rounded-2xl border border-slate-100 bg-white p-5 shadow-soft">
              <div className="flex flex-wrap items-start justify-between gap-3">
                <div className="min-w-0">
                  <div className="flex items-center gap-2">
                    <p className="font-semibold text-navy">{a.title}</p>
                    <Badge tone={STATUS_TONE[a.status] || 'neutral'}>{a.status}</Badge>
                    {a.assignment_type === 'interactive_test' && (
                      <Badge tone="purple">MCQ test · {a.question_count || 0} Q</Badge>
                    )}
                  </div>
                  <p className="mt-1 text-xs text-slate-400">Due {formatDateTime(a.due_date)} · {a.max_marks} marks · {a.submission_count || 0} submissions</p>
                  {a.status === 'draft' && (
                    <p className="mt-1 text-xs font-medium text-amber-700">Hidden from students — click Publish below</p>
                  )}
                  {a.assignment_type !== 'interactive_test' && (
                    <>
                      <AttachmentLinks attachments={a.attachments} legacyPath={a.attachment_path} />
                      {a.external_url && <ExternalResourceLink url={a.external_url} />}
                    </>
                  )}
                </div>
                <div className="flex flex-wrap items-center gap-2">
                  <Button size="sm" variant="secondary" icon={FiUsers} onClick={() => setSubmissionsFor(a)}>Submissions</Button>
                  <Button size="sm" variant="outline" icon={FiEye} onClick={() => setPreview(a)}>Preview</Button>
                  {a.status === 'draft' && (
                    <Button size="sm" variant="success" icon={FiUnlock} onClick={() => publishAssignment(a)}>Publish</Button>
                  )}
                  {a.status !== 'draft' && (
                    <Button size="sm" variant="outline" icon={a.status === 'closed' ? FiUnlock : FiLock} onClick={() => toggleStatus(a)}>
                      {a.status === 'closed' ? 'Reopen' : 'Close'}
                    </Button>
                  )}
                  <button type="button" onClick={() => openEdit(a)} className="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-primary"><FiEdit2 size={16} /></button>
                  <button type="button" onClick={() => remove(a)} className="rounded-lg p-2 text-slate-400 hover:bg-red-50 hover:text-red-500"><FiTrash2 size={16} /></button>
                </div>
              </div>
            </div>
          ))}
        </div>
      )}

      {/* Create / edit modal */}
      <Modal open={!!editing} onClose={() => !saving && setEditing(null)} title={editing?.id ? 'Edit assignment' : 'New assignment'} size="lg"
        footer={<>
          <Button variant="outline" onClick={() => setEditing(null)} disabled={saving}>Cancel</Button>
          <Button onClick={save} loading={saving}>{editing?.id ? 'Save changes' : 'Create assignment'}</Button>
        </>}>
        <div className="space-y-4">
          <div>
            <label className="text-sm font-medium text-slate-600">Assignment type</label>
            <select
              value={form.assignment_type}
              onChange={(e) => {
                setForm((f) => ({ ...f, assignment_type: e.target.value }))
                setHtmlPreview(null)
              }}
              className="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"
            >
              <option value="file">File upload (homework)</option>
              <option value="interactive_test">Interactive MCQ test (HTML / link)</option>
            </select>
          </div>
          <div>
            <label className="text-sm font-medium text-slate-600">Title *</label>
            <input value={form.title} onChange={(e) => setForm((f) => ({ ...f, title: e.target.value }))}
              className="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" placeholder="Assignment title" />
          </div>
          <div>
            <label className="text-sm font-medium text-slate-600">Description</label>
            <textarea value={form.description} onChange={(e) => setForm((f) => ({ ...f, description: e.target.value }))} rows={3}
              className="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" placeholder="Instructions for students" />
          </div>
          <div className="grid gap-4 sm:grid-cols-2">
            <div>
              <label className="text-sm font-medium text-slate-600">Deadline *</label>
              <input type="datetime-local" value={form.due_date} onChange={(e) => setForm((f) => ({ ...f, due_date: e.target.value }))}
                className="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" />
            </div>
            <div>
              <label className="text-sm font-medium text-slate-600">Max marks</label>
              <input type="number" value={form.max_marks} onChange={(e) => setForm((f) => ({ ...f, max_marks: e.target.value }))}
                className="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" />
            </div>
          </div>
          {form.assignment_type === 'interactive_test' ? (
            <AssignmentHtmlImport
              externalUrl={form.external_url}
              onExternalUrl={(v) => setForm((f) => ({ ...f, external_url: v }))}
              htmlFile={htmlFile}
              onHtmlFile={setHtmlFile}
              preview={htmlPreview}
              onPreview={setHtmlPreview}
            />
          ) : (
            <div>
              <label className="text-sm font-medium text-slate-600">Attachments (optional)</label>
              {(editing?.attachments?.length > 0 || (editing?.attachment_path && !editing?.attachments?.length)) && (
                <ul className="mt-2 space-y-2">
                  {(editing.attachments?.length ? editing.attachments : [{
                    id: 0,
                    title: 'Attachment',
                    file_path: editing.attachment_path,
                  }]).map((att) => (
                    <li key={att.id || att.file_path} className="flex flex-wrap items-center justify-between gap-2 rounded-xl border border-slate-100 bg-slate-50 px-3 py-2">
                      <AttachmentLinks attachments={[att]} compact />
                      {att.id > 0 && (
                        <button type="button" onClick={() => removeAttachment(att.id)} className="rounded-lg p-1.5 text-slate-400 hover:bg-red-50 hover:text-red-500" aria-label="Remove attachment">
                          <FiTrash2 size={15} />
                        </button>
                      )}
                    </li>
                  ))}
                </ul>
              )}
              <div className="mt-2">
                <MultiFileUploadField
                  items={attachFiles}
                  onChange={setAttachFiles}
                  accept={ACCEPT_ALL}
                  uploading={saving}
                  hint="Add one or more files — each needs a title for students"
                />
              </div>
              <div className="mt-4">
                <label className="text-sm font-medium text-slate-600">External resource link (optional)</label>
                <div className="relative mt-1">
                  <FiLink className="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
                  <input
                    type="url"
                    value={form.external_url}
                    onChange={(e) => setForm((f) => ({ ...f, external_url: e.target.value }))}
                    placeholder="https://example.com/reference"
                    className="w-full rounded-xl border border-slate-200 py-2 pl-9 pr-3 text-sm"
                  />
                </div>
                <p className="mt-1 text-xs text-slate-400">Optional link to an article, video, or reference page for students.</p>
              </div>
            </div>
          )}
        </div>
      </Modal>

      {/* Preview modal */}
      <Modal open={!!preview} onClose={() => setPreview(null)} title={preview?.title} subtitle="Student preview">
        {preview && (
          <div className="space-y-3 text-sm">
            <p className="text-slate-600">{preview.description || 'No description provided.'}</p>
            <div className="flex flex-wrap gap-4 text-xs text-slate-400">
              <span>Due: {formatDateTime(preview.due_date)}</span>
              <span>Max marks: {preview.max_marks}</span>
              <span>Status: {preview.status}</span>
            </div>
            {preview.assignment_type !== 'interactive_test' && (
              <>
                <AttachmentLinks attachments={preview.attachments} legacyPath={preview.attachment_path} />
                {preview.external_url && <ExternalResourceLink url={preview.external_url} />}
              </>
            )}
          </div>
        )}
      </Modal>

      {submissionsFor && (
        <SubmissionsModal assignment={submissionsFor} onClose={() => { setSubmissionsFor(null); load() }} />
      )}
    </div>
  )
}

function SubmissionsModal({ assignment, onClose }) {
  const toast = useToast()
  const confirm = useConfirm()
  const [subs, setSubs] = useState([])
  const [loading, setLoading] = useState(true)

  const load = useCallback(() => {
    setLoading(true)
    assignmentService.submissions(assignment.id).then(({ data }) => setSubs(data.data || [])).finally(() => setLoading(false))
  }, [assignment.id])

  useEffect(() => { load() }, [load])

  const grade = async (id, marks, remarks) => {
    try { await assignmentService.grade({ submission_id: id, marks: Number(marks), remarks }); toast.success('Saved successfully'); load() }
    catch { toast.error('Could not save grade') }
  }

  const remove = async (s) => {
    if (!(await confirm({ title: 'Delete submission', message: `Delete ${s.student_name}'s submission?` }))) return
    try { await assignmentService.deleteSubmission(s.id); toast.success('Deleted successfully'); load() }
    catch { toast.error('Could not delete submission') }
  }

  return (
    <Modal open onClose={onClose} title="Submissions" subtitle={assignment.title} size="xl">
      {loading ? (
        <p className="py-8 text-center text-slate-400">Loading submissions…</p>
      ) : subs.length === 0 ? (
        <EmptyState icon={FiUsers} title="No submissions received yet" description="Submissions will appear here as students upload their work." />
      ) : (
        <div className="overflow-x-auto">
          <table className="w-full text-left text-sm">
            <thead className="border-b border-slate-100 text-xs uppercase text-slate-400">
              <tr>
                <th className="py-2 pr-3">Student</th>
                <th className="py-2 pr-3">Roll / Email</th>
                <th className="py-2 pr-3">Submitted</th>
                <th className="py-2 pr-3">File</th>
                <th className="py-2 pr-3">Status</th>
                <th className="py-2 pr-3">Marks</th>
                <th className="py-2">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {subs.map((s) => <SubmissionRow key={s.id} s={s} onGrade={grade} onDelete={remove} />)}
            </tbody>
          </table>
        </div>
      )}
    </Modal>
  )
}

function SubmissionRow({ s, onGrade, onDelete }) {
  const [marks, setMarks] = useState(s.marks ?? '')
  const [remarks, setRemarks] = useState(s.remarks ?? '')
  const submitted = s.submitted_at ? new Date(s.submitted_at.replace(' ', 'T')) : null
  const tone = { submitted: 'warning', late: 'danger', graded: 'success' }[s.status] || 'neutral'

  return (
    <tr>
      <td className="py-3 pr-3 font-medium text-navy">{s.student_name}</td>
      <td className="py-3 pr-3 text-slate-500">{s.roll_number || s.student_email}</td>
      <td className="py-3 pr-3 text-slate-500">
        {submitted ? <>{submitted.toLocaleDateString()}<span className="block text-xs text-slate-400">{submitted.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</span></> : '—'}
      </td>
      <td className="py-3 pr-3">
        {s.percentage != null && s.percentage !== '' ? (
          <span className="text-sm font-medium text-navy">{s.marks ?? '—'} ({s.percentage}%)</span>
        ) : (s.files?.length || s.file_path) ? (
          <SubmissionFileLinks submission={s} />
        ) : s.submission_text ? <span className="text-xs text-slate-400">Text answer</span> : '—'}
      </td>
      <td className="py-3 pr-3"><Badge tone={tone}>{s.status}</Badge></td>
      <td className="py-3 pr-3">
        <input type="number" value={marks} onChange={(e) => setMarks(e.target.value)} className="w-16 rounded-lg border border-slate-200 px-2 py-1 text-sm" placeholder="—" />
      </td>
      <td className="py-3">
        <div className="flex items-center gap-2">
          <input type="text" value={remarks} onChange={(e) => setRemarks(e.target.value)} placeholder="Remarks" className="w-28 rounded-lg border border-slate-200 px-2 py-1 text-sm" />
          <button type="button" onClick={() => onGrade(s.id, marks, remarks)} className="rounded-lg bg-blue-50 px-2.5 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-100">Save</button>
          <button type="button" onClick={() => onDelete(s)} className="rounded-lg p-1.5 text-slate-400 hover:bg-red-50 hover:text-red-500"><FiTrash2 size={15} /></button>
        </div>
      </td>
    </tr>
  )
}

function ExternalResourceLink({ url, compact = false }) {
  const href = normalizeExternalUrl(url)
  if (!href) return null
  return (
    <a
      href={href}
      target="_blank"
      rel="noreferrer"
      className={`inline-flex items-center gap-1.5 font-medium text-violet-600 hover:underline ${compact ? 'text-xs' : 'text-sm mt-2'}`}
    >
      <FiLink size={compact ? 13 : 14} /> Open external resource
    </a>
  )
}

function AttachmentLinks({ attachments, legacyPath, compact = false }) {
  const list = attachments?.length
    ? attachments
    : legacyPath
      ? [{ id: 0, title: 'Attachment', file_path: legacyPath }]
      : []
  if (!list.length) return null
  return (
    <div className={`flex flex-col gap-1 ${compact ? '' : 'mt-2'}`}>
      {list.map((att) => (
        <div key={att.id || att.file_path} className="flex flex-wrap items-center gap-3">
          <span className="text-xs font-medium text-slate-500">{att.title}</span>
          <ViewFileButton
            title={att.title}
            filePath={att.file_path}
            className="inline-flex items-center gap-1.5 text-xs font-medium text-primary hover:underline"
            size={13}
          />
          <button
            type="button"
            onClick={() => downloadMedia(att.file_path, att.title)}
            className="inline-flex items-center gap-1.5 text-xs text-slate-500 hover:underline"
          >
            <FiDownload size={13} /> Download
          </button>
        </div>
      ))}
    </div>
  )
}

function SubmissionFileLinks({ submission }) {
  const files = submission.files?.length
    ? submission.files
    : submission.file_path
      ? [{ id: 0, title: submission.original_filename || 'Uploaded file', file_path: submission.file_path }]
      : []
  if (!files.length) return null
  return (
    <div className="space-y-1">
      {files.map((f) => (
        <div key={f.id || f.file_path} className="flex flex-wrap items-center gap-2 text-xs">
          <span className="font-medium text-slate-500">{f.title}</span>
          <ViewFileButton title={f.title} filePath={f.file_path} className="inline-flex items-center gap-1 text-primary hover:underline" />
          <button type="button" onClick={() => downloadMedia(f.file_path, f.title)} className="inline-flex items-center gap-1 text-slate-500 hover:underline">
            <FiDownload size={14} />
          </button>
        </div>
      ))}
    </div>
  )
}
