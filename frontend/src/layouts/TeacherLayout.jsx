import { FiHome, FiBookOpen, FiCalendar } from 'react-icons/fi'
import DashboardLayout from '../components/dashboard/DashboardLayout'

const navItems = [
  { to: '/teacher/dashboard', label: 'Dashboard', icon: FiHome },
  { to: '/teacher/courses', label: 'My Courses', icon: FiBookOpen },
  { to: '/teacher/timetable', label: 'Timetable', icon: FiCalendar },
]

export default function TeacherLayout() {
  return <DashboardLayout title="Teacher Panel" navItems={navItems} />
}
