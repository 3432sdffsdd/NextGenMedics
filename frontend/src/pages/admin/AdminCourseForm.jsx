import { useEffect, useState } from 'react'
import { Link, useNavigate, useParams } from 'react-router-dom'
import { adminCoursesService, adminUsersService } from '../../services/api'
import Alert from '../../components/dashboard/Alert'
import CourseWeeklyTimetable from '../../components/dashboard/CourseWeeklyTimetable'
import ContentManager from '../teacher/ContentManager'

const defaultCourse = {
  title: '',
  subtitle: '',
  short_description: '',
  detailed_description: '',
  category_id: '',
  teacher_ids: [],
  duration: '',
  start_date: '',
  end_date: '',
  level: 'beginner',
  fee: 0,
  max_students: 100,
  certificate_available: 1,
  enrollment_status: 'open',
  status: 'draft',
}

export default function AdminCourseForm() {
  const { id } = useParams()
  const navigate = useNavigate()
  const isNew = id === 'new'

  const [form, setForm] = useState(defaultCourse)
  const [categories, setCategories] = useState([])
  const [teachers, setTeachers] = useState([])
  const [students, setStudents] = useState([])
  const [selectedStudents, setSelectedStudents] = useState([])
  const [enrolled, setEnrolled] = useState([])
  const [structure, setStructure] = useState([])
  const [error, setError] = useState('')
  const [message, setMessage] = useState('')
  const [saving, setSaving] = useState(false)

  useEffect(() => {
    adminCoursesService.categories().then(({ data }) => setCategories(data.data || []))
    adminUsersService.list('teacher', { per_page: 100 }).then(({ data }) => setTeachers(data.data || []))
    adminUsersService.list('student', { per_page: 100 }).then(({ data }) => setStudents(data.data || []))
  }, [])

  useEffect(() => {
    if (!isNew && id) {
      adminCoursesService.get(id).then(({ data }) => {
        const course = data.data
        if (course) {
          setForm({
            title: course.title || '',
            subtitle: course.subtitle || '',
            short_description: course.short_description || '',
            detailed_description: course.description || course.detailed_description || '',
            category_id: course.category_id || '',
            teacher_ids: (course.teacher_ids && course.teacher_ids.length
              ? course.teacher_ids
              : course.teacher_id ? [course.teacher_id] : []).map(Number),
            duration: course.duration || '',
            start_date: (course.start_date || '').slice(0, 10),
            end_date: (course.end_date || '').slice(0, 10),
            level: course.level || 'beginner',
            fee: course.fee || 0,
            max_students: course.max_students || 100,
            certificate_available: course.certificate_available ? 1 : 0,
            enrollment_status: course.enrollment_status || 'open',
            status: course.status || 'draft',
          })
        }
      })
      adminCoursesService.enrollments(id).then(({ data }) => setEnrolled(data.data || []))
      adminCoursesService.structure(Number(id)).then(({ data }) => setStructure(data.data || [])).catch(() => setStructure([]))
    }
  }, [id, isNew])

  const reloadStructure = () => {
    if (!isNew && id) {
      adminCoursesService.structure(Number(id)).then(({ data }) => setStructure(data.data || [])).catch(() => setStructure([]))
    }
  }

  const handleChange = (e) => {
    const { name, value, type, checked } = e.target
    setForm({ ...form, [name]: type === 'checkbox' ? (checked ? 1 : 0) : value })
  }

  const MAX_TEACHERS = 2

  const toggleTeacher = (teacherId) => {
    const tid = Number(teacherId)
    setForm((prev) => {
      const current = prev.teacher_ids.map(Number)
      const exists = current.includes(tid)
      if (exists) {
        return { ...prev, teacher_ids: current.filter((t) => t !== tid) }
      }
      if (current.length >= MAX_TEACHERS) return prev
      return { ...prev, teacher_ids: [...current, tid] }
    })
  }

  const handleSave = async (e) => {
    e.preventDefault()
    setSaving(true)
    setError('')
    setMessage('')
    try {
      const { detailed_description, teacher_ids, ...rest } = form
      const payload = {
        ...rest,
        description: detailed_description,
        category_id: form.category_id ? Number(form.category_id) : null,
        teacher_ids: teacher_ids.map(Number),
        fee: Number(form.fee),
        max_students: Number(form.max_students),
        start_date: form.start_date || null,
        end_date: form.end_date || null,
      }
      if (isNew) {
        await adminCoursesService.create(payload)
        navigate('/admin/courses')
      } else {
        await adminCoursesService.update(Number(id), payload)
        setMessage('Course saved')
      }
    } catch (err) {
      setError(err.response?.data?.message || 'Save failed')
    } finally {
      setSaving(false)
    }
  }

  const handleEnroll = async () => {
    if (!selectedStudents.length) return
    try {
      await adminCoursesService.enrollStudents(Number(id), selectedStudents.map(Number))
      setMessage('Students enrolled successfully')
      setSelectedStudents([])
      adminCoursesService.enrollments(id).then(({ data }) => setEnrolled(data.data || []))
    } catch {
      setError('Enrollment failed')
    }
  }

  const toggleStudent = (studentId) => {
    setSelectedStudents((prev) =>
      prev.includes(studentId) ? prev.filter((s) => s !== studentId) : [...prev, studentId]
    )
  }

  return (
    <div>
      <Link to="/admin/courses" className="text-sm font-semibold text-primary hover:underline">← Back to courses</Link>
      <h2 className="mt-4 font-display text-xl font-bold text-navy">{isNew ? 'Create Course' : 'Edit Course'}</h2>

      {error && <div className="mt-4"><Alert>{error}</Alert></div>}
      {message && <div className="mt-4"><Alert type="success">{message}</Alert></div>}

      <form onSubmit={handleSave} className="mt-6 space-y-6">
        <section className="rounded-2xl border border-slate-100 bg-white p-6 shadow-soft">
          <h3 className="font-semibold text-navy">Basic Information</h3>
          <div className="mt-4 grid gap-4 sm:grid-cols-2">
            <div className="sm:col-span-2">
              <label className="mb-1 block text-xs font-medium text-slate-500">Course Title *</label>
              <input required name="title" value={form.title} onChange={handleChange} className="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" />
            </div>
            <div className="sm:col-span-2">
              <label className="mb-1 block text-xs font-medium text-slate-500">Subtitle</label>
              <input name="subtitle" value={form.subtitle} onChange={handleChange} className="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" />
            </div>
            <div className="sm:col-span-2">
              <label className="mb-1 block text-xs font-medium text-slate-500">Short Description</label>
              <textarea name="short_description" rows={2} value={form.short_description} onChange={handleChange} className="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" />
            </div>
            <div className="sm:col-span-2">
              <label className="mb-1 block text-xs font-medium text-slate-500">Detailed Description</label>
              <textarea name="detailed_description" rows={4} value={form.detailed_description} onChange={handleChange} className="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" />
            </div>
            <div>
              <label className="mb-1 block text-xs font-medium text-slate-500">Category</label>
              <select name="category_id" value={form.category_id} onChange={handleChange} className="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                <option value="">Select category</option>
                {categories.map((c) => <option key={c.id} value={c.id}>{c.name}</option>)}
              </select>
            </div>
            <div className="sm:col-span-2">
              <label className="mb-1 block text-xs font-medium text-slate-500">
                Assign Teachers <span className="text-slate-400">(select up to {MAX_TEACHERS})</span>
              </label>
              <div className="max-h-44 space-y-1 overflow-y-auto rounded-xl border border-slate-200 p-2">
                {teachers.length === 0 && (
                  <p className="px-1 py-2 text-sm text-slate-400">No teachers available</p>
                )}
                {teachers.map((t) => {
                  const checked = form.teacher_ids.map(Number).includes(Number(t.id))
                  const atLimit = form.teacher_ids.length >= MAX_TEACHERS && !checked
                  return (
                    <label
                      key={t.id}
                      className={`flex items-center gap-2 rounded-lg px-2 py-1 ${atLimit ? 'cursor-not-allowed opacity-40' : 'cursor-pointer hover:bg-slate-50'}`}
                    >
                      <input
                        type="checkbox"
                        checked={checked}
                        disabled={atLimit}
                        onChange={() => toggleTeacher(t.id)}
                      />
                      <span className="text-sm">{t.first_name} {t.last_name}</span>
                    </label>
                  )
                })}
              </div>
              <p className="mt-1 text-xs text-slate-400">{form.teacher_ids.length} of {MAX_TEACHERS} selected</p>
            </div>
            <div>
              <label className="mb-1 block text-xs font-medium text-slate-500">Duration</label>
              <input name="duration" value={form.duration} onChange={handleChange} placeholder="e.g. 12 weeks" className="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" />
            </div>
            <div>
              <label className="mb-1 block text-xs font-medium text-slate-500">Start Date</label>
              <input type="date" name="start_date" value={form.start_date} onChange={handleChange} className="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" />
              <p className="mt-1 text-xs text-slate-400">Used to compute course progress</p>
            </div>
            <div>
              <label className="mb-1 block text-xs font-medium text-slate-500">End Date <span className="text-slate-400">(optional)</span></label>
              <input type="date" name="end_date" value={form.end_date} onChange={handleChange} className="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" />
              <p className="mt-1 text-xs text-slate-400">If empty, duration is used</p>
            </div>
            <div>
              <label className="mb-1 block text-xs font-medium text-slate-500">Level</label>
              <select name="level" value={form.level} onChange={handleChange} className="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                <option value="beginner">Beginner</option>
                <option value="intermediate">Intermediate</option>
                <option value="advanced">Advanced</option>
              </select>
            </div>
            <div>
              <label className="mb-1 block text-xs font-medium text-slate-500">Course Fee</label>
              <input type="number" name="fee" value={form.fee} onChange={handleChange} className="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" />
            </div>
            <div>
              <label className="mb-1 block text-xs font-medium text-slate-500">Max Students</label>
              <input type="number" name="max_students" value={form.max_students} onChange={handleChange} className="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" />
            </div>
            <div className="flex items-center gap-2">
              <input type="checkbox" id="cert" checked={!!form.certificate_available} onChange={handleChange} name="certificate_available" />
              <label htmlFor="cert" className="text-sm text-slate-600">Certificate available</label>
            </div>
          </div>
        </section>

        <button type="submit" disabled={saving} className="btn-primary text-sm">
          {saving ? 'Saving...' : isNew ? 'Create Course' : 'Save Changes'}
        </button>
      </form>

      {!isNew && (
        <section className="mt-8">
          <h3 className="font-display text-lg font-bold text-navy">Course Content</h3>
          <p className="mt-1 text-sm text-slate-500">Modules, chapters, lectures, and materials</p>
          <div className="mt-4">
            <ContentManager courseId={Number(id)} structure={structure} reload={reloadStructure} />
          </div>
        </section>
      )}

      {!isNew && (
        <section className="mt-8">
          <CourseWeeklyTimetable courseId={Number(id)} canEdit />
        </section>
      )}

      {!isNew && (
        <section className="mt-8 rounded-2xl border border-slate-100 bg-white p-6 shadow-soft">
          <h3 className="font-semibold text-navy">Enroll Students</h3>
          <p className="mt-1 text-sm text-slate-500">Select students to add to this course</p>
          <div className="mt-4 max-h-48 space-y-2 overflow-y-auto">
            {students.map((s) => (
              <label key={s.id} className="flex cursor-pointer items-center gap-2 rounded-lg px-2 py-1 hover:bg-slate-50">
                <input
                  type="checkbox"
                  checked={selectedStudents.includes(s.id)}
                  onChange={() => toggleStudent(s.id)}
                />
                <span className="text-sm">{s.first_name} {s.last_name} ({s.email})</span>
              </label>
            ))}
          </div>
          <button type="button" onClick={handleEnroll} className="btn-secondary mt-4 text-sm">
            Enroll Selected Students
          </button>

          {enrolled.length > 0 && (
            <div className="mt-6">
              <h4 className="text-sm font-semibold text-navy">Enrolled students ({enrolled.length})</h4>
              <ul className="mt-2 divide-y divide-slate-100 rounded-xl border border-slate-100">
                {enrolled.map((s) => (
                  <li key={s.id} className="flex items-center justify-between px-4 py-2 text-sm">
                    <span>{s.first_name} {s.last_name} — {s.email}</span>
                    <button type="button" onClick={async () => {
                      await adminCoursesService.unenroll(Number(id), s.id)
                      setEnrolled((prev) => prev.filter((x) => x.id !== s.id))
                    }} className="text-red-500 hover:underline">Remove</button>
                  </li>
                ))}
              </ul>
            </div>
          )}
        </section>
      )}
    </div>
  )
}
