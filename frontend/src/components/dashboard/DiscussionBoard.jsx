import { useCallback, useEffect, useState } from 'react'
import { FiThumbsUp, FiCornerUpLeft, FiTrash2, FiFlag, FiCheckCircle, FiStar, FiEdit2, FiMessageSquare } from 'react-icons/fi'
import { useAuth } from '../../context/AuthContext'
import { useToast } from '../../context/ToastContext'
import { useConfirm } from '../../context/ConfirmContext'
import { discussionService } from '../../services/api'
import { EmptyState } from '../ui'

const AVATAR_COLORS = ['bg-blue-500', 'bg-emerald-500', 'bg-violet-500', 'bg-amber-500', 'bg-rose-500', 'bg-cyan-500', 'bg-indigo-500']

function initials(name = '?') {
  const parts = name.trim().split(/\s+/)
  return ((parts[0]?.[0] || '') + (parts[1]?.[0] || '')).toUpperCase() || '?'
}
function colorFor(name = '') {
  let h = 0
  for (let i = 0; i < name.length; i++) h = (h * 31 + name.charCodeAt(i)) % AVATAR_COLORS.length
  return AVATAR_COLORS[h]
}
function timeAgo(value) {
  if (!value) return ''
  const d = new Date(value.replace(' ', 'T'))
  const s = Math.floor((Date.now() - d.getTime()) / 1000)
  if (s < 60) return 'just now'
  if (s < 3600) return `${Math.floor(s / 60)}m ago`
  if (s < 86400) return `${Math.floor(s / 3600)}h ago`
  if (s < 604800) return `${Math.floor(s / 86400)}d ago`
  return d.toLocaleDateString()
}

function Avatar({ name, role }) {
  const staff = role === 'teacher' || role === 'admin'
  return (
    <div className={`grid h-9 w-9 shrink-0 place-items-center rounded-full text-xs font-bold text-white ${staff ? 'bg-primary' : colorFor(name)}`}>
      {initials(name)}
    </div>
  )
}

function RoleBadge({ role }) {
  if (role === 'teacher') return <span className="rounded-full bg-primary/10 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-primary">Teacher</span>
  if (role === 'admin') return <span className="rounded-full bg-violet-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-violet-700">Admin</span>
  return <span className="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-slate-500">Student</span>
}

/**
 * Unified modern discussion board. Works at course level (lectureId omitted)
 * or lecture level. Supports nested replies, likes, edit/delete own, staff
 * pin/approve, and reporting.
 */
export default function DiscussionBoard({ courseId, lectureId = null, canModerate = false }) {
  const { user } = useAuth()
  const toast = useToast()
  const confirm = useConfirm()
  const isStaff = canModerate || user?.role === 'teacher' || user?.role === 'admin'
  const [threads, setThreads] = useState([])
  const [active, setActive] = useState(null)
  const [loading, setLoading] = useState(true)
  const [title, setTitle] = useState('')
  const [content, setContent] = useState('')

  const loadThreads = useCallback(() => {
    if (!courseId || Number.isNaN(Number(courseId))) {
      setThreads([])
      setLoading(false)
      return
    }
    setLoading(true)
    const req = lectureId ? discussionService.byLecture(lectureId) : discussionService.list(courseId, { per_page: 50 })
    req.then(({ data }) => setThreads(data.data || [])).catch(() => {}).finally(() => setLoading(false))
  }, [courseId, lectureId])

  useEffect(() => { loadThreads() }, [loadThreads])

  const openThread = async (id, startEdit = false) => {
    const { data } = await discussionService.get(id)
    setActive({ ...data.data, _startEdit: startEdit })
  }
  const refresh = () => active && openThread(active.id)

  const createThread = async (e) => {
    e.preventDefault()
    if (!courseId || Number.isNaN(Number(courseId))) {
      return toast.error('Invalid course — refresh the page and try again')
    }
    if (!title.trim()) return toast.error('Title is required')
    if (!content.trim()) return toast.error('Please write your question')
    try {
      const payload = {
        course_id: Number(courseId),
        title: title.trim(),
        content: content.trim(),
      }
      if (lectureId) payload.lecture_id = Number(lectureId)
      await discussionService.create(payload)
      setTitle(''); setContent('')
      toast.success('Question posted')
      loadThreads()
    } catch (err) {
      const fieldErrors = err.response?.data?.errors
      const detail = fieldErrors ? Object.values(fieldErrors).flat().join(' ') : null
      toast.error(detail || err.response?.data?.message || 'Could not post your question')
    }
  }

  if (active) {
    return <ThreadView thread={active} isStaff={isStaff} userId={user?.id} toast={toast} confirm={confirm}
      onBack={() => { setActive(null); loadThreads() }} onChanged={refresh} onDeleted={() => { setActive(null); loadThreads() }} />
  }

  const canManageThread = (t) => isStaff || Number(t.author_id) === Number(user?.id)

  return (
    <div className="space-y-4">
      <form onSubmit={createThread} className="rounded-2xl border border-slate-100 bg-white p-5 shadow-soft">
        <h4 className="font-semibold text-navy">{lectureId ? 'Ask about this lecture' : 'Start a discussion'}</h4>
        <input value={title} onChange={(e) => setTitle(e.target.value)} placeholder="Title"
          className="mt-3 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" />
        <textarea value={content} onChange={(e) => setContent(e.target.value)} rows={2} placeholder="What would you like to discuss?"
          className="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" />
        <button type="submit" className="btn-primary mt-3 text-sm">Post</button>
      </form>

      {loading ? (
        <p className="text-sm text-slate-400">Loading discussions…</p>
      ) : threads.length === 0 ? (
        <EmptyState icon={FiMessageSquare} title="No discussions yet" description="Be the first to start the conversation." className="py-8" />
      ) : (
        <div className="space-y-2">
          {threads.map((t) => (
            <div key={t.id} className="rounded-2xl border border-slate-100 bg-white p-4 shadow-soft transition-colors hover:border-slate-200">
              <div className="flex flex-wrap items-start gap-2">
              <button type="button" onClick={() => openThread(t.id)} className="flex min-w-0 flex-1 items-center gap-3 text-left">
                <Avatar name={t.author_name} role={t.author_role} />
                <div className="min-w-0 flex-1">
                  <p className="truncate font-medium text-navy">{t.is_pinned ? '📌 ' : ''}{t.title}</p>
                  <p className="mt-0.5 text-xs text-slate-400">{t.author_name} · {t.reply_count} repl{Number(t.reply_count) === 1 ? 'y' : 'ies'} · {timeAgo(t.created_at)}</p>
                </div>
              </button>
              {canManageThread(t) && (
                <ThreadListActions thread={t} toast={toast} confirm={confirm} onChanged={loadThreads} />
              )}
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  )
}

function ThreadListActions({ thread, toast, confirm, onChanged }) {
  const [editing, setEditing] = useState(false)
  const [title, setTitle] = useState(thread.title || '')
  const [content, setContent] = useState(thread.content || '')

  useEffect(() => {
    setTitle(thread.title || '')
    setContent(thread.content || '')
  }, [thread.id, thread.title, thread.content])

  const save = async (e) => {
    e?.stopPropagation()
    if (!title.trim()) return toast.error('Title is required')
    if (!content.trim()) return toast.error('Content is required')
    try {
      await discussionService.update(thread.id, { title: title.trim(), content: content.trim() })
      toast.success('Discussion updated')
      setEditing(false)
      onChanged()
    } catch {
      toast.error('Could not update discussion')
    }
  }

  const remove = async (e) => {
    e.stopPropagation()
    if (!(await confirm({ title: 'Delete discussion', message: `Delete "${thread.title}" and all replies?` }))) return
    try {
      await discussionService.remove(thread.id)
      toast.success('Deleted successfully')
      onChanged()
    } catch {
      toast.error('Could not delete discussion')
    }
  }

  if (editing) {
    return (
      <div className="w-full min-w-[240px] space-y-2 rounded-xl border border-slate-200 bg-slate-50 p-3" onClick={(e) => e.stopPropagation()}>
        <input value={title} onChange={(e) => setTitle(e.target.value)} className="w-full rounded-lg border border-slate-200 px-3 py-1.5 text-sm" placeholder="Title" />
        <textarea value={content} onChange={(e) => setContent(e.target.value)} rows={2} className="w-full rounded-lg border border-slate-200 px-3 py-1.5 text-sm" placeholder="Content" />
        <div className="flex gap-2">
          <button type="button" onClick={save} className="btn-primary text-xs">Save</button>
          <button type="button" onClick={() => setEditing(false)} className="text-xs text-slate-500">Cancel</button>
        </div>
      </div>
    )
  }

  return (
    <div className="flex shrink-0 gap-1">
      <button type="button" onClick={(e) => { e.stopPropagation(); setEditing(true) }} className="inline-flex items-center gap-1 rounded-lg px-2 py-1.5 text-xs font-medium text-slate-500 hover:bg-slate-100 hover:text-primary">
        <FiEdit2 size={14} /> Edit
      </button>
      <button type="button" onClick={remove} className="inline-flex items-center gap-1 rounded-lg px-2 py-1.5 text-xs font-medium text-slate-500 hover:bg-red-50 hover:text-red-500">
        <FiTrash2 size={14} /> Delete
      </button>
    </div>
  )
}

function ThreadView({ thread, isStaff, userId, toast, confirm, onBack, onChanged, onDeleted }) {
  const [reply, setReply] = useState('')
  const [replyTo, setReplyTo] = useState(null)
  const [editingId, setEditingId] = useState(null)
  const [editValue, setEditValue] = useState('')
  const [editingThread, setEditingThread] = useState(false)
  const [editTitle, setEditTitle] = useState(thread.title || '')
  const [editContent, setEditContent] = useState(thread.content || '')

  const mine = Number(thread.author_id) === Number(userId)
  const canManage = isStaff || mine

  useEffect(() => {
    setEditTitle(thread.title || '')
    setEditContent(thread.content || '')
    if (thread._startEdit) setEditingThread(true)
  }, [thread.id, thread.title, thread.content, thread._startEdit])

  const replies = thread.replies || []
  const roots = replies.filter((r) => !r.parent_id)
  const childrenOf = (id) => replies.filter((r) => Number(r.parent_id) === Number(id))

  const postReply = async (e) => {
    e.preventDefault()
    if (!reply.trim()) return
    try {
      await discussionService.reply(thread.id, { content: reply.trim(), parent_id: replyTo, is_answer: isStaff && !replyTo })
      setReply(''); setReplyTo(null); onChanged()
    } catch { toast.error('Could not post reply') }
  }
  const saveEdit = async (id) => {
    if (!editValue.trim()) return
    try { await discussionService.editReply(id, editValue.trim()); setEditingId(null); toast.success('Comment updated'); onChanged() }
    catch { toast.error('Could not update comment') }
  }
  const saveThread = async () => {
    if (!editTitle.trim()) return toast.error('Title is required')
    if (!editContent.trim()) return toast.error('Content is required')
    try {
      await discussionService.update(thread.id, { title: editTitle.trim(), content: editContent.trim() })
      setEditingThread(false)
      toast.success('Discussion updated')
      onChanged()
    } catch {
      toast.error('Could not update discussion')
    }
  }
  const deleteThread = async () => {
    if (!(await confirm({ title: 'Delete discussion', message: 'Delete this discussion and all replies?' }))) return
    try {
      await discussionService.remove(thread.id)
      toast.success('Deleted successfully')
      onDeleted()
    } catch {
      toast.error('Could not delete discussion')
    }
  }
  const like = async (id) => { await discussionService.likeReply(id); onChanged() }
  const flag = async (id, flags) => { await discussionService.flagReply(id, flags); onChanged() }
  const remove = async (id) => {
    if (!(await confirm({ title: 'Delete comment', message: 'Are you sure you want to delete this comment?' }))) return
    await discussionService.deleteReply(id); toast.success('Deleted successfully'); onChanged()
  }
  const report = async (id) => { await discussionService.report({ reply_id: id, reason: 'Reported by user' }); toast.info('Reported to moderators') }

  const ReplyCard = ({ r, nested }) => {
    const mine = Number(r.author_id) === Number(userId)
    return (
      <div className={nested ? 'ml-6 border-l-2 border-slate-100 pl-4' : ''}>
        <div className={`rounded-2xl border p-4 text-sm ${r.is_teacher_approved ? 'border-emerald-200 bg-emerald-50' : r.is_pinned ? 'border-amber-200 bg-amber-50' : 'border-slate-100 bg-white'}`}>
          <div className="flex items-start gap-3">
            <Avatar name={r.author_name} role={r.author_role} />
            <div className="min-w-0 flex-1">
              <div className="flex flex-wrap items-center gap-2">
                <span className="font-semibold text-navy">{r.author_name}</span>
                <RoleBadge role={r.author_role} />
                <span className="text-xs text-slate-400">{timeAgo(r.created_at)}</span>
                {r.is_pinned ? <span className="text-xs font-medium text-amber-600">📌 Pinned</span> : null}
                {r.is_teacher_approved ? <span className="flex items-center gap-1 text-xs font-semibold text-emerald-600"><FiCheckCircle size={12} /> Teacher Approved</span> : null}
              </div>
              {editingId === r.id ? (
                <div className="mt-2 flex gap-2">
                  <input autoFocus value={editValue} onChange={(e) => setEditValue(e.target.value)} className="flex-1 rounded-lg border border-slate-200 px-3 py-1.5 text-sm" />
                  <button type="button" onClick={() => saveEdit(r.id)} className="btn-primary text-xs">Save</button>
                  <button type="button" onClick={() => setEditingId(null)} className="text-xs text-slate-400">Cancel</button>
                </div>
              ) : (
                <p className="mt-1.5 whitespace-pre-wrap text-slate-600">{r.content}</p>
              )}
              <div className="mt-3 flex flex-wrap items-center gap-3 text-xs">
                <button type="button" onClick={() => like(r.id)} className={`flex items-center gap-1 ${Number(r.liked_by_me) ? 'font-semibold text-primary' : 'text-slate-400 hover:text-primary'}`}>
                  <FiThumbsUp size={13} /> {r.likes_count || 0}
                </button>
                {!nested && <button type="button" onClick={() => { setReplyTo(r.id); setReply('') }} className="flex items-center gap-1 text-slate-400 hover:text-slate-600"><FiCornerUpLeft size={13} /> Reply</button>}
                {(isStaff || mine) && editingId !== r.id && (
                  <button type="button" onClick={() => { setEditingId(r.id); setEditValue(r.content) }} className="flex items-center gap-1 text-slate-400 hover:text-primary">
                    <FiEdit2 size={13} /> Edit
                  </button>
                )}
                {isStaff && (
                  <>
                    <button type="button" onClick={() => flag(r.id, { is_pinned: r.is_pinned ? 0 : 1 })} className="flex items-center gap-1 text-slate-400 hover:text-amber-600"><FiStar size={13} /> {r.is_pinned ? 'Unpin' : 'Pin'}</button>
                    <button type="button" onClick={() => flag(r.id, { is_teacher_approved: r.is_teacher_approved ? 0 : 1 })} className="flex items-center gap-1 text-slate-400 hover:text-emerald-600"><FiCheckCircle size={13} /> {r.is_teacher_approved ? 'Unapprove' : 'Approve'}</button>
                  </>
                )}
                {(isStaff || mine) && <button type="button" onClick={() => remove(r.id)} className="flex items-center gap-1 text-slate-400 hover:text-red-500"><FiTrash2 size={13} /> Delete</button>}
                {!isStaff && !mine && <button type="button" onClick={() => report(r.id)} className="flex items-center gap-1 text-slate-400 hover:text-red-500"><FiFlag size={13} /> Report</button>}
              </div>
              {replyTo === r.id && (
                <form onSubmit={postReply} className="mt-3 flex gap-2">
                  <input autoFocus value={reply} onChange={(e) => setReply(e.target.value)} placeholder="Write a reply…" className="flex-1 rounded-lg border border-slate-200 px-3 py-1.5 text-sm" />
                  <button type="submit" className="btn-primary text-xs">Send</button>
                </form>
              )}
            </div>
          </div>
        </div>
        {childrenOf(r.id).map((c) => <div key={c.id} className="mt-2"><ReplyCard r={c} nested /></div>)}
      </div>
    )
  }

  return (
    <div>
      <button type="button" onClick={onBack} className="text-sm text-primary hover:underline">← Back to discussions</button>
      <div className="mt-3 flex items-start justify-between gap-3">
        <div className="flex min-w-0 flex-1 items-start gap-3">
          <Avatar name={thread.author_name} role={thread.author_role} />
          <div className="min-w-0 flex-1">
            {editingThread ? (
              <div className="space-y-2">
                <input value={editTitle} onChange={(e) => setEditTitle(e.target.value)} className="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold" />
                <textarea value={editContent} onChange={(e) => setEditContent(e.target.value)} rows={4} className="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" />
                <div className="flex gap-2">
                  <button type="button" onClick={saveThread} className="btn-primary text-xs">Save</button>
                  <button type="button" onClick={() => { setEditingThread(false); setEditTitle(thread.title); setEditContent(thread.content) }} className="text-xs text-slate-400">Cancel</button>
                </div>
              </div>
            ) : (
              <>
                <h3 className="font-bold text-navy">{thread.title}</h3>
                <p className="text-xs text-slate-400">{thread.author_name} · {timeAgo(thread.created_at)}</p>
              </>
            )}
          </div>
        </div>
        {canManage && !editingThread && (
          <div className="flex shrink-0 gap-1">
            <button type="button" onClick={() => setEditingThread(true)} className="inline-flex items-center gap-1 rounded-lg px-2 py-1.5 text-xs font-medium text-slate-500 hover:bg-slate-100 hover:text-primary">
              <FiEdit2 size={14} /> Edit
            </button>
            <button type="button" onClick={deleteThread} className="inline-flex items-center gap-1 rounded-lg px-2 py-1.5 text-xs font-medium text-slate-500 hover:bg-red-50 hover:text-red-500">
              <FiTrash2 size={14} /> Delete
            </button>
          </div>
        )}
      </div>
      {!editingThread && (
        <p className="mt-3 whitespace-pre-wrap rounded-2xl bg-slate-50 p-4 text-sm text-slate-700">{thread.content}</p>
      )}

      <div className="mt-5 space-y-3">
        {roots.map((r) => <ReplyCard key={r.id} r={r} />)}
        {roots.length === 0 && <p className="text-sm text-slate-400">No replies yet. Be the first to respond.</p>}
      </div>

      {replyTo === null && (
        <form onSubmit={postReply} className="mt-5">
          <textarea value={reply} onChange={(e) => setReply(e.target.value)} rows={3} placeholder="Add to the discussion…" className="w-full rounded-xl border border-slate-200 p-3 text-sm" />
          <button type="submit" className="btn-primary mt-2 text-sm">Post reply</button>
        </form>
      )}
    </div>
  )
}
