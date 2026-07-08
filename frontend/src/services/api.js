import axios from 'axios'

const API_BASE_URL = import.meta.env.VITE_API_URL || '/api'

const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
  },
})

api.interceptors.request.use((config) => {
  const token = localStorage.getItem('ngm_token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('ngm_token')
      localStorage.removeItem('ngm_user')
    }
    return Promise.reject(error)
  }
)

export const authService = {
  login: (email, password) => api.post('/auth/login', { email, password }),
  me: () => api.get('/auth/me'),
  logout: () => {
    localStorage.removeItem('ngm_token')
    localStorage.removeItem('ngm_user')
  },
}

export const dashboardService = {
  admin: () => api.get('/admin/dashboard'),
  teacher: () => api.get('/teacher/dashboard'),
  student: () => api.get('/student/dashboard'),
}

export const dashboardPathByRole = {
  admin: '/admin/dashboard',
  teacher: '/teacher/dashboard',
  student: '/student/dashboard',
}

export const adminUsersService = {
  list: (role, params = {}) => api.get(`/admin/users/${role}`, { params }),
  get: (id) => api.get(`/admin/users/id/${id}`),
  create: (role, data) => api.post(`/admin/users/${role}`, data),
  update: (id, data) => api.put(`/admin/users/${id}`, data),
  suspend: (id) => api.patch(`/admin/users/${id}/suspend`),
  activate: (id) => api.patch(`/admin/users/${id}/activate`),
  resetPassword: (id) => api.post(`/admin/users/${id}/reset-password`),
  delete: (id) => api.delete(`/admin/users/${id}`),
}

export const adminCoursesService = {
  list: (params = {}) => api.get('/admin/courses', { params }),
  get: (id) => api.get(`/courses/id/${id}`),
  categories: () => api.get('/courses/categories'),
  create: (data) => api.post('/courses', data),
  update: (id, data) => api.put(`/courses/${id}`, data),
  delete: (id) => api.delete(`/courses/${id}`),
  publish: (id) => api.patch(`/courses/${id}/publish`),
  archive: (id) => api.patch(`/courses/${id}/archive`),
  assignTeacher: (id, teacherIds) =>
    api.post(`/courses/${id}/assign-teacher`, { teacher_ids: [].concat(teacherIds ?? []).filter(Boolean) }),
  enrollStudents: (id, studentIds) => api.post(`/courses/${id}/enroll`, { student_ids: studentIds }),
  enrollments: (id) => api.get(`/courses/${id}/enrollments`),
  unenroll: (id, studentId) => api.delete(`/courses/${id}/enroll/${studentId}`),
  students: (id) => api.get(`/courses/${id}/students`),
  structure: (id) => api.get(`/courses/${id}/structure`),
}

export const myCoursesService = {
  list: () => api.get('/my/courses'),
}

/** Let axios/browser set multipart boundary automatically (do not set Content-Type manually). */
const uploadCfg = (onUploadProgress) => ({
  headers: { 'Content-Type': undefined },
  ...(onUploadProgress ? { onUploadProgress } : {}),
})

export const contentService = {
  createModule: (data) => api.post('/content/modules', data),
  createChapter: (data) => api.post('/content/chapters', data),
  createLecture: (data) => api.post('/content/lectures', data),
  uploadResource: (formData, onUploadProgress) =>
    api.post('/content/resources', formData, uploadCfg(onUploadProgress)),
  update: (entity, id, data) => api.put(`/content/${entity}/${id}`, data),
  updateResource: (id, data) => api.put(`/content/lecture_resources/${id}`, data),
  deleteResource: (id) => api.delete(`/content/lecture_resources/${id}`),
  delete: (entity, id) => api.delete(`/content/${entity}/${id}`),
}

export const quizService = {
  list: (courseId) => api.get('/quizzes', { params: { course_id: courseId } }),
  get: (id) => api.get(`/quizzes/${id}`),
  create: (data) => api.post('/quizzes', data),
  update: (id, data) => api.put(`/quizzes/${id}`, data),
  remove: (id) => api.delete(`/quizzes/${id}`),
  setStatus: (id, status) => api.patch(`/quizzes/${id}/status`, { status }),
  duplicate: (id) => api.post(`/quizzes/${id}/duplicate`),
  addQuestion: (data) => api.post('/quizzes/questions', data),
  deleteQuestion: (questionId) => api.delete(`/quizzes/questions/${questionId}`),
  parseWord: (file) => {
    const fd = new FormData()
    fd.append('file', file)
    return api.post('/quizzes/parse-word', fd, { headers: { 'Content-Type': undefined } })
  },
  downloadTemplate: (format = 'txt') =>
    api.get('/quizzes/template', { params: { format }, responseType: 'blob' }),
  importQuestions: (quizId, questions) => api.post(`/quizzes/${quizId}/import-questions`, { questions }),
  listAttempts: (quizId, search) => api.get(`/quizzes/${quizId}/attempts`, { params: search ? { search } : {} }),
  teacherReview: (attemptId) => api.get(`/quizzes/attempts/${attemptId}/teacher-review`),
  start: (id) => api.post(`/quizzes/${id}/start`),
  submit: (attemptId, answers, timeTakenSeconds) => api.post(`/quizzes/attempts/${attemptId}/submit`, {
    answers,
    time_taken_seconds: timeTakenSeconds,
  }),
  reviewAttempt: (attemptId) => api.get(`/quizzes/attempts/${attemptId}`),
  myAttempts: (quizId) => api.get(`/quizzes/${quizId}/my-attempts`),
  leaderboard: (id) => api.get(`/quizzes/${id}/leaderboard`),
}

export const assignmentService = {
  list: (courseId) => api.get('/assignments', { params: { course_id: courseId } }),
  my: () => api.get('/assignments/my'),
  get: (id) => api.get(`/assignments/${id}`),
  parseHtml: (formData) => api.post('/assignments/parse-html', formData, uploadCfg()),
  getTest: (id) => api.get(`/assignments/${id}/test`),
  submitTest: (id, answers) => api.post(`/assignments/${id}/submit-test`, { answers }),
  create: (formData, onUploadProgress) => api.post('/assignments', formData, uploadCfg(onUploadProgress)),
  update: (id, formData, onUploadProgress) =>
    formData instanceof FormData
      ? api.post(`/assignments/${id}/update`, formData, uploadCfg(onUploadProgress))
      : api.put(`/assignments/${id}`, formData),
  remove: (id) => api.delete(`/assignments/${id}`),
  deleteAttachment: (assignmentId, attachmentId) => api.delete(`/assignments/${assignmentId}/attachments/${attachmentId}`),
  setStatus: (id, status) => api.patch(`/assignments/${id}/status`, { status }),
  submit: (id, formData, onUploadProgress) => api.post(`/assignments/${id}/submit`, formData, uploadCfg(onUploadProgress)),
  submissions: (id) => api.get(`/assignments/${id}/submissions`),
  deleteSubmission: (id) => api.delete(`/assignments/submissions/${id}`),
  grade: (data) => api.post('/assignments/grade', data),
}

export const attendanceService = {
  createSession: (data) => api.post('/attendance/sessions', data),
  sessionByDate: (courseId, date) => api.get(`/attendance/course/${courseId}/by-date`, { params: { date } }),
  updateSession: (sessionId, data) => api.put(`/attendance/sessions/${sessionId}`, data),
  deleteSession: (sessionId) => api.delete(`/attendance/sessions/${sessionId}`),
  sessionRecords: (sessionId) => api.get(`/attendance/sessions/${sessionId}/records`),
  mark: (records) => api.post('/attendance/mark', { records }),
  byCourse: (courseId, params = {}) => api.get(`/attendance/course/${courseId}`, { params }),
  my: (courseId) => api.get('/attendance/my', { params: courseId ? { course_id: courseId } : {} }),
  reports: (courseId) => api.get('/attendance/reports', { params: { course_id: courseId } }),
  courseStudents: (courseId) => adminCoursesService.students(courseId),
}

export const coursesService = {
  list: () => api.get('/courses'),
  get: (slug) => api.get(`/courses/${slug}`),
}

export const contactService = {
  send: (payload) => api.post('/contact', payload),
}

export const resourcesService = {
  list: (type) => api.get(`/resources${type ? `?type=${type}` : ''}`),
}

export const mentorsService = {
  list: () => api.get('/mentors'),
}

export const testimonialsService = {
  list: () => api.get('/testimonials'),
}

export const announcementsService = {
  list: (params = {}) => api.get('/announcements', { params }),
  create: (data) => api.post('/announcements', data),
  update: (id, data) => api.put(`/announcements/${id}`, data),
  remove: (id) => api.delete(`/announcements/${id}`),
}

export const discussionService = {
  list: (courseId, params = {}) => api.get(`/discussions/course/${courseId}`, { params }),
  byLecture: (lectureId) => api.get(`/discussions/lecture/${lectureId}`),
  get: (id) => api.get(`/discussions/${id}`),
  create: (data) => api.post('/discussions', data),
  update: (id, data) => api.put(`/discussions/${id}`, data),
  remove: (id) => api.delete(`/discussions/${id}`),
  reply: (id, payload) =>
    api.post(`/discussions/${id}/reply`, typeof payload === 'string' ? { content: payload } : payload),
  likeReply: (id) => api.post(`/discussions/replies/${id}/like`),
  flagReply: (id, flags) => api.patch(`/discussions/replies/${id}/flag`, flags),
  editReply: (id, content) => api.put(`/discussions/replies/${id}`, { content }),
  deleteReply: (id) => api.delete(`/discussions/replies/${id}`),
  report: (data) => api.post('/discussions/report', data),
  moderate: (id, data) => api.patch(`/discussions/${id}/moderate`, data),
}

export const studentAiService = {
  revisionLectures: () => api.get('/student/revision/lectures'),
  revisionContent: (lectureId) => api.get(`/student/revision/lectures/${lectureId}`),
  flashcards: (params = {}) => api.get('/student/flashcards', { params }),
  flashcardProgress: (id, data) => api.post(`/student/flashcards/${id}/progress`, data),
  mcqPractice: (lectureId) => api.get(`/student/lectures/${lectureId}/mcqs`),
  challengeToday: () => api.get('/student/challenge/today'),
  submitAttempt: (data) => api.post('/student/mcqs/attempt', data),
  recentAttempts: () => api.get('/student/mcqs/attempts'),
  toggleBookmark: (data) => api.post('/student/bookmarks/toggle', data),
  bookmarks: (type) => api.get('/student/bookmarks', { params: { type } }),
  addHighlight: (lectureId, data) => api.post(`/student/lectures/${lectureId}/highlights`, data),
  deleteHighlight: (id) => api.delete(`/student/highlights/${id}`),
}

export const progressService = {
  ping: (type = 'login') => api.post('/student/activity/ping', { type }),
  streak: () => api.get('/student/streak'),
  analytics: () => api.get('/student/analytics'),
  badges: () => api.get('/student/badges'),
}

export const premiumStudyService = {
  dashboard: () => api.get('/student/premium/dashboard'),
  dailyChallenge: () => api.get('/student/premium/daily-challenge'),
  dailyHistory: () => api.get('/student/premium/daily-challenge/history'),
  weakAreas: () => api.get('/student/premium/weak-areas'),
  getStudyPlan: () => api.get('/student/premium/study-plan'),
  saveStudyPlan: (data) => api.put('/student/premium/study-plan', data),
  completeTask: (id, status = 'completed') => api.patch(`/student/premium/study-plan/tasks/${id}`, { status }),
  questionBankFilters: () => api.get('/student/premium/question-bank/filters'),
  questionBank: (params = {}) => api.get('/student/premium/question-bank', { params }),
  questionBankPractice: (data) => api.post('/student/premium/question-bank/practice', data),
  mistakeStats: () => api.get('/student/premium/mistakes/stats'),
  mistakes: (params = {}) => api.get('/student/premium/mistakes', { params }),
  mistakesPractice: (params = {}) => api.get('/student/premium/mistakes/practice', { params }),
  startRevision: () => api.post('/student/premium/revision/start'),
  completeRevision: (id, data) => api.post(`/student/premium/revision/${id}/complete`, data),
}

export const scheduleService = {
  my: () => api.get('/schedule'),
  byCourse: (courseId) => api.get(`/courses/${courseId}/schedule`),
  save: (courseId, slots) => api.post(`/courses/${courseId}/schedule`, { slots }),
  remove: (id) => api.delete(`/schedule/${id}`),
}

export const sessionsService = {
  list: (params = {}) => api.get('/sessions', { params }),
  byCourse: (courseId) => api.get('/sessions', { params: { course_id: courseId } }),
  create: (data) => api.post('/sessions', data),
  update: (id, data) => api.put(`/sessions/${id}`, data),
  cancel: (id) => api.delete(`/sessions/${id}`),
}

export const schedulesService = {
  list: (params = {}) => api.get('/schedules', { params }),
  get: (id) => api.get(`/schedules/${id}`),
  create: (data) => api.post('/schedules', data),
  bulk: (data) => api.post('/schedules/bulk', data),
  update: (id, data) => api.put(`/schedules/${id}`, data),
  remove: (id) => api.delete(`/schedules/${id}`),
  cancel: (id, remarks) => api.patch(`/schedules/${id}/cancel`, { remarks }),
  complete: (id) => api.patch(`/schedules/${id}/complete`),
  setStatus: (id, status) => api.patch(`/schedules/${id}/status`, { status }),
  reschedule: (id, data) => api.patch(`/schedules/${id}/reschedule`, data),
  duplicate: (id, class_date) => api.post(`/schedules/${id}/duplicate`, { class_date }),
  uploadAttachment: (id, formData) =>
    api.post(`/schedules/${id}/attachment`, formData, { headers: { 'Content-Type': undefined } }),
  listMonthUploads: (courseId) => api.get(`/courses/${courseId}/schedules/months`),
  importMonth: (courseId, data) =>
    data instanceof FormData
      ? api.post(`/courses/${courseId}/schedules/import`, data, { headers: { 'Content-Type': undefined } })
      : api.post(`/courses/${courseId}/schedules/import`, data),
  clearMonth: (courseId, month) => api.delete(`/courses/${courseId}/schedules/month/${month}`),
}

export const batchesService = {
  byCourse: (courseId) => api.get(`/courses/${courseId}/batches`),
  create: (data) => api.post('/batches', data),
  update: (id, data) => api.put(`/batches/${id}`, data),
  remove: (id) => api.delete(`/batches/${id}`),
  students: (id) => api.get(`/batches/${id}/students`),
  assign: (id, studentIds) => api.post(`/batches/${id}/students`, { student_ids: studentIds }),
  unassign: (id, studentId) => api.delete(`/batches/${id}/students/${studentId}`),
}

export const aiService = {
  status: () => api.get('/ai/status'),
  generate: (lectureId, options) => api.post(`/ai/lectures/${lectureId}/generate`, options),
  process: (jobId) => api.post(`/ai/jobs/${jobId}/process`),
  jobStatus: (lectureId) => api.get(`/ai/lectures/${lectureId}/job`),
  review: (lectureId) => api.get(`/ai/lectures/${lectureId}/review`),
  updateContent: (lectureId, data) => api.put(`/ai/lectures/${lectureId}/content`, data),
  addFlashcard: (lectureId, data) => api.post(`/ai/lectures/${lectureId}/flashcards`, data),
  updateFlashcard: (id, data) => api.put(`/ai/flashcards/${id}`, data),
  deleteFlashcard: (id) => api.delete(`/ai/flashcards/${id}`),
  addMcq: (lectureId, data) => api.post(`/ai/lectures/${lectureId}/mcqs`, data),
  updateMcq: (id, data) => api.put(`/ai/mcqs/${id}`, data),
  deleteMcq: (id) => api.delete(`/ai/mcqs/${id}`),
  approve: (lectureId) => api.post(`/ai/lectures/${lectureId}/approve`),
  publish: (lectureId) => api.post(`/ai/lectures/${lectureId}/publish`),
  saveChallenge: (lectureId, data) => api.put(`/ai/lectures/${lectureId}/challenge`, data),
  importWord: (lectureId, formData) => api.post(`/ai/lectures/${lectureId}/import/word`, formData, uploadCfg()),
  importFlashcards: (lectureId, formData) => api.post(`/ai/lectures/${lectureId}/import/flashcards`, formData, uploadCfg()),
  importMcqs: (lectureId, formData) => api.post(`/ai/lectures/${lectureId}/import/mcqs`, formData, uploadCfg()),
}

export const notificationsService = {
  list: (params = {}) => api.get('/notifications', { params }),
  unreadCount: () => api.get('/notifications/unread-count'),
  markRead: (id) => api.patch(`/notifications/${id}/read`),
  markAllRead: () => api.patch('/notifications/read-all'),
  courseTabBadges: (courseId) => api.get(`/courses/${courseId}/tab-notifications`),
  markCourseTabRead: (courseId, tab) => api.patch(`/courses/${courseId}/tab-notifications/read`, { tab }),
}

/** Normalize stored file path for the media API. */
export function normalizeMediaPath(filePath) {
  if (!filePath) return ''
  return String(filePath).replace(/\\/g, '/').replace(/^\/+/, '').replace(/^storage\//, '')
}

/** Media API URL (no auth token — use fetchMediaBlob or mediaUrl). */
export function mediaEndpoint(filePath, { download = false } = {}) {
  const path = normalizeMediaPath(filePath)
  if (!path) return null
  if (path.startsWith('http')) return path
  const base = API_BASE_URL.replace(/\/$/, '')
  const params = new URLSearchParams({ path })
  if (download) params.set('download', '1')
  return `${base}/media?${params.toString()}`
}

/** Direct URL for streaming (video) — includes token query param. */
export function mediaUrl(filePath, { download = false } = {}) {
  const endpoint = mediaEndpoint(filePath, { download })
  if (!endpoint || endpoint.startsWith('http')) return endpoint
  const token = localStorage.getItem('ngm_token')
  return `${endpoint}&token=${encodeURIComponent(token || '')}`
}

/** Fetch Word / PowerPoint HTML preview (.doc, .docx, .ppt, .pptx). */
export async function fetchDocumentPreview(filePath) {
  const path = normalizeMediaPath(filePath)
  if (!path) throw new Error('Invalid file path')
  const base = API_BASE_URL.replace(/\/$/, '')
  const params = new URLSearchParams({ path })
  const token = localStorage.getItem('ngm_token')
  const res = await fetch(`${base}/media/document-preview?${params.toString()}`, {
    credentials: 'same-origin',
    headers: token ? { Authorization: `Bearer ${token}` } : {},
  })
  const data = await res.json().catch(() => ({}))
  if (!res.ok || !data?.success) {
    throw new Error(data?.message || `Could not preview document (${res.status})`)
  }
  return data.data?.html || '<p>Empty document</p>'
}

/** Fetch file bytes with Authorization header (reliable for PDF/docs). */
export async function fetchMediaBlob(filePath, { download = false } = {}) {
  const url = mediaEndpoint(filePath, { download })
  if (!url || url.startsWith('http')) {
    throw new Error('Invalid file path')
  }
  const token = localStorage.getItem('ngm_token')
  const res = await fetch(url, {
    credentials: 'same-origin',
    headers: token ? { Authorization: `Bearer ${token}` } : {},
  })
  if (!res.ok) {
    let message = `Could not load file (${res.status})`
    try {
      const data = await res.json()
      if (data?.message) message = data.message
    } catch {
      /* binary or empty body */
    }
    throw new Error(message)
  }
  const contentType = (res.headers.get('Content-Type') || 'application/octet-stream').split(';')[0].trim()
  const buffer = await res.arrayBuffer()
  if (contentType.includes('application/json')) {
    try {
      const data = JSON.parse(new TextDecoder().decode(buffer))
      if (data?.message) throw new Error(data.message)
    } catch (e) {
      if (e instanceof Error && e.message && !e.message.includes('JSON')) throw e
    }
    throw new Error('File not available — log in again or ask your teacher to re-upload.')
  }
  return new Blob([buffer], { type: contentType })
}

/** Fetch plain-text file content. */
export async function fetchMediaText(filePath) {
  const blob = await fetchMediaBlob(filePath)
  return blob.text()
}

/** Trigger a browser download for an uploaded file. */
export async function downloadMedia(filePath, filename) {
  const blob = await fetchMediaBlob(filePath, { download: true })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = filename || 'download'
  document.body.appendChild(a)
  a.click()
  a.remove()
  URL.revokeObjectURL(url)
}

export default api
