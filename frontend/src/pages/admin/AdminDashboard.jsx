import { useEffect, useMemo, useState } from 'react'
import { Link } from 'react-router-dom'
import {
  FiUsers,
  FiUserCheck,
  FiBookOpen,
  FiClipboard,
  FiActivity,
  FiCheckCircle,
  FiPlus,
  FiArrowRight,
} from 'react-icons/fi'
import { useAuth } from '../../context/AuthContext'
import { dashboardService, adminUsersService, adminCoursesService } from '../../services/api'
import StatCard from '../../components/dashboard/StatCard'
import Alert from '../../components/dashboard/Alert'
import { AreaTrend, DonutChart, BarMini, ChartLegend } from '../../components/dashboard/Charts'
import { Card, Button, Badge, EmptyState, SkeletonStatCards, SkeletonRows } from '../../components/ui'

function initials(name = '') {
  return name.split(' ').filter(Boolean).slice(0, 2).map((n) => n[0]?.toUpperCase()).join('') || '?'
}

function activityByDay(activities = []) {
  const days = []
  for (let i = 6; i >= 0; i--) {
    const d = new Date()
    d.setDate(d.getDate() - i)
    days.push({ key: d.toISOString().slice(0, 10), label: d.toLocaleDateString(undefined, { weekday: 'short' }), value: 0 })
  }
  const index = Object.fromEntries(days.map((d) => [d.key, d]))
  activities.forEach((a) => {
    const key = (a.created_at || '').slice(0, 10)
    if (index[key]) index[key].value += 1
  })
  return days
}

export default function AdminDashboard() {
  const { user } = useAuth()
  const [stats, setStats] = useState(null)
  const [students, setStudents] = useState([])
  const [courses, setCourses] = useState([])
  const [error, setError] = useState('')
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    Promise.all([
      dashboardService.admin().then(({ data }) => setStats(data.data)),
      adminUsersService.list('student', { per_page: 5 }).then(({ data }) => setStudents(data.data || [])).catch(() => {}),
      adminCoursesService.list({ per_page: 5 }).then(({ data }) => setCourses(data.data || [])).catch(() => {}),
    ])
      .catch(() => setError('Could not load dashboard. Is the API running?'))
      .finally(() => setLoading(false))
  }, [])

  const trend = useMemo(() => activityByDay(stats?.recent_activities), [stats])

  const community = useMemo(
    () => [
      { name: 'Students', value: stats?.total_students || 0, color: '#2563EB' },
      { name: 'Teachers', value: stats?.total_teachers || 0, color: '#22C55E' },
    ],
    [stats]
  )

  const courseBreakdown = useMemo(
    () => [
      { label: 'Active', value: stats?.active_courses || 0 },
      { label: 'Draft/Other', value: Math.max((stats?.total_courses || 0) - (stats?.active_courses || 0), 0) },
      { label: 'Enrollments', value: stats?.total_enrollments || 0 },
    ],
    [stats]
  )

  return (
    <div className="space-y-6">
      {/* Hero header */}
      <div className="relative overflow-hidden rounded-2xl bg-gradient-to-br from-[#0F172A] via-[#1E293B] to-[#0F172A] px-6 py-7 text-white sm:px-8">
        <div className="pointer-events-none absolute -right-10 -top-10 h-48 w-48 rounded-full bg-blue-500/20 blur-3xl" />
        <div className="pointer-events-none absolute -bottom-16 right-24 h-56 w-56 rounded-full bg-emerald-500/10 blur-3xl" />
        <div className="relative flex flex-wrap items-center justify-between gap-4">
          <div>
            <p className="text-sm text-slate-300">Welcome back,</p>
            <h2 className="font-display text-2xl font-extrabold sm:text-3xl">{user?.first_name} {user?.last_name} 👋</h2>
            <p className="mt-1 max-w-lg text-sm text-slate-400">
              Here's what's happening across your academy today.
            </p>
          </div>
          <div className="flex gap-3">
            <Button as={Link} to="/admin/courses/new" icon={FiPlus}>New Course</Button>
            <Button as={Link} to="/admin/students" variant="outline" className="!border-white/15 !bg-white/5 !text-white hover:!bg-white/10">
              Manage Students
            </Button>
          </div>
        </div>
      </div>

      {error && <Alert>{error}</Alert>}

      {/* Stat cards */}
      {loading ? (
        <SkeletonStatCards count={4} />
      ) : (
        <div className="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
          <StatCard index={0} label="Total Students" value={stats?.total_students} icon={FiUsers} tone="blue" hint="enrolled learners" />
          <StatCard index={1} label="Teachers" value={stats?.total_teachers} icon={FiUserCheck} tone="emerald" hint="active instructors" />
          <StatCard index={2} label="Total Courses" value={stats?.total_courses} icon={FiBookOpen} tone="violet" hint={`${stats?.active_courses || 0} published`} />
          <StatCard index={3} label="Enrollments" value={stats?.total_enrollments} icon={FiActivity} tone="amber" hint="active enrollments" />
          <StatCard index={4} label="Pending Assignments" value={stats?.pending_assignments} icon={FiClipboard} tone="rose" hint="awaiting grading" />
          <StatCard index={5} label="Quiz Reviews" value={stats?.pending_quiz_reviews} icon={FiClipboard} tone="amber" hint="need review" />
          <StatCard index={6} label="Active Courses" value={stats?.active_courses} icon={FiCheckCircle} tone="emerald" hint="live now" />
          <StatCard index={7} label="Published Rate" value={stats?.total_courses ? Math.round((stats.active_courses / stats.total_courses) * 100) : 0} icon={FiBookOpen} tone="blue" hint="% of catalog live" />
        </div>
      )}

      {/* Charts row */}
      <div className="grid gap-5 lg:grid-cols-3">
        <Card title="Activity (Last 7 Days)" subtitle="Platform events per day" className="lg:col-span-2">
          {loading ? <SkeletonRows rows={4} /> : <AreaTrend data={trend} />}
        </Card>
        <Card title="Community">
          {loading ? (
            <SkeletonRows rows={4} />
          ) : community[0].value + community[1].value === 0 ? (
            <EmptyState title="No users yet" description="Students and teachers will appear here." />
          ) : (
            <>
              <DonutChart data={community} />
              <ChartLegend items={community} />
            </>
          )}
        </Card>
      </div>

      <Card title="Catalog Overview" subtitle="Courses & enrollment distribution">
        {loading ? <SkeletonRows rows={4} /> : <BarMini data={courseBreakdown} color="#2563EB" />}
      </Card>

      {/* Widgets row */}
      <div className="grid gap-5 lg:grid-cols-3">
        {/* Recent students */}
        <Card
          title="Recent Students"
          actions={<Button as={Link} to="/admin/students" variant="ghost" size="sm" iconRight={FiArrowRight}>View all</Button>}
          bodyClassName="!p-0"
          padding="p-0"
        >
          {loading ? (
            <div className="p-6"><SkeletonRows rows={4} /></div>
          ) : students.length === 0 ? (
            <EmptyState icon={FiUsers} title="No students" description="Add your first student to get started." />
          ) : (
            <ul className="divide-y divide-slate-100">
              {students.map((s) => (
                <li key={s.id} className="flex items-center gap-3 px-6 py-3.5 transition-colors hover:bg-slate-50">
                  <span className="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-blue-50 text-xs font-bold text-blue-600">
                    {initials(`${s.first_name} ${s.last_name}`)}
                  </span>
                  <div className="min-w-0 flex-1">
                    <p className="truncate text-sm font-medium text-slate-700">{s.first_name} {s.last_name}</p>
                    <p className="truncate text-xs text-slate-400">{s.email}</p>
                  </div>
                  <Badge status={s.status} dot />
                </li>
              ))}
            </ul>
          )}
        </Card>

        {/* Recent courses */}
        <Card
          title="Recent Courses"
          actions={<Button as={Link} to="/admin/courses" variant="ghost" size="sm" iconRight={FiArrowRight}>View all</Button>}
          padding="p-0"
        >
          {loading ? (
            <div className="p-6"><SkeletonRows rows={4} /></div>
          ) : courses.length === 0 ? (
            <EmptyState icon={FiBookOpen} title="No courses" description="Create your first course." />
          ) : (
            <ul className="divide-y divide-slate-100">
              {courses.map((c) => (
                <li key={c.id} className="flex items-center gap-3 px-6 py-3.5 transition-colors hover:bg-slate-50">
                  <span className="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-violet-50 text-violet-600">
                    <FiBookOpen size={16} />
                  </span>
                  <div className="min-w-0 flex-1">
                    <p className="truncate text-sm font-medium text-slate-700">{c.title}</p>
                    <p className="truncate text-xs text-slate-400">{c.teacher_names || c.teacher_name || 'Unassigned'}</p>
                  </div>
                  <Badge status={c.status} />
                </li>
              ))}
            </ul>
          )}
        </Card>

        {/* Recent activity */}
        <Card title="Recent Activity" padding="p-0">
          {loading ? (
            <div className="p-6"><SkeletonRows rows={4} /></div>
          ) : !stats?.recent_activities?.length ? (
            <EmptyState icon={FiActivity} title="No activity yet" />
          ) : (
            <ul className="divide-y divide-slate-100">
              {stats.recent_activities.slice(0, 6).map((a) => (
                <li key={a.id} className="px-6 py-3.5">
                  <p className="text-sm text-slate-600">
                    <span className="font-semibold text-slate-800">{a.user_name || 'System'}</span> — {a.action}
                  </p>
                  <p className="mt-0.5 text-xs text-slate-400">{new Date(a.created_at).toLocaleString()}</p>
                </li>
              ))}
            </ul>
          )}
        </Card>
      </div>
    </div>
  )
}
