import { FiHome, FiBookOpen, FiCalendar, FiBarChart2 } from 'react-icons/fi'
import DashboardLayout from '../components/dashboard/DashboardLayout'

const navItems = [
  { to: '/teacher/dashboard', label: 'Dashboard', icon: FiHome },
  { to: '/teacher/courses', label: 'My Courses', icon: FiBookOpen },
  { to: '/teacher/student-performance', label: 'Student Performance', icon: FiBarChart2 },
  { to: '/teacher/timetable', label: 'Timetable', icon: FiCalendar },
]

export default function TeacherLayout() {
  return <DashboardLayout title="Teacher Panel" navItems={navItems} />
}
