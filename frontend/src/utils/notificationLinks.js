/** Parse notification.data (string or object) and build a deep link. */

export function parseNotificationData(n) {
  if (!n) return {}
  let data = n.data
  if (typeof data === 'string') {
    try {
      data = JSON.parse(data || '{}')
    } catch {
      data = {}
    }
  }
  return data && typeof data === 'object' ? data : {}
}

/** Stable key so duplicate / repeat notifications collapse to the newest. */
export function notificationDedupeKey(n) {
  const d = parseNotificationData(n)
  const type = n?.type || 'unknown'
  if (d.assignment_id) return `${type}:a:${d.assignment_id}`
  if (d.quiz_id) return `${type}:q:${d.quiz_id}`
  if (d.thread_id) return `${type}:t:${d.thread_id}`
  // Material uploads: one entry per lecture (not per file)
  if (d.lecture_id && (type === 'new_content' || type === 'ai_content_published' || type === 'material_uploaded')) {
    return `${type}:l:${d.lecture_id}`
  }
  if (d.resource_id) return `${type}:r:${d.resource_id}`
  if (d.schedule_id) return `${type}:s:${d.schedule_id}`
  return `${type}:c:${d.course_id || ''}:${n?.title || ''}`
}

/**
 * Keep newest-first list unique by entity (assignment / quiz / lecture / title).
 * Assumes `items` are already sorted newest → oldest.
 */
export function dedupeNotifications(items = [], limit = 5) {
  const seen = new Set()
  const out = []
  for (const n of items) {
    const key = notificationDedupeKey(n)
    if (seen.has(key)) continue
    seen.add(key)
    out.push(n)
    if (out.length >= limit) break
  }
  return out
}

const TYPE_TAB = {
  new_assignment: 'assignments',
  assignment_graded: 'assignments',
  assignment_submitted: 'assignments',
  new_quiz: 'quizzes',
  quiz_submitted: 'quizzes',
  new_content: 'learn',
  ai_content_published: 'learn',
  material_uploaded: 'learn',
  new_discussion: 'discussions',
  discussion_reply: 'discussions',
  discussion_question: 'discussions',
  class_scheduled: 'schedule',
  class_reminder: 'schedule',
}

export function notificationTab(n) {
  const data = parseNotificationData(n)
  if (data.tab) return String(data.tab)
  return TYPE_TAB[n?.type] || null
}

/**
 * Path for student/teacher to open the related course section.
 * @param {'student'|'teacher'|'admin'} role
 */
export function notificationHref(n, role = 'student') {
  const data = parseNotificationData(n)
  if (data.url) return String(data.url)

  const courseId = data.course_id
  if (!courseId) return null

  const prefix = role === 'student' ? '/student/courses' : '/teacher/courses'
  const tab = notificationTab(n)
  const base = `${prefix}/${courseId}`
  return tab ? `${base}?tab=${encodeURIComponent(tab)}` : base
}

export function notificationActionLabel(n) {
  const tab = notificationTab(n)
  if (tab === 'learn') return 'Open materials'
  if (tab === 'quizzes') return 'Open quizzes'
  if (tab === 'assignments') return 'Open assignments'
  if (tab === 'discussions') return 'Open discussion'
  if (tab === 'schedule') return 'Open schedule'
  return 'Open course'
}
