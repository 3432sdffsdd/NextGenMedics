import { FiHome, FiUsers, FiUserCheck, FiBookOpen, FiCalendar } from 'react-icons/fi'
import DashboardLayout from '../components/dashboard/DashboardLayout'

const navItems = [
  { to: '/admin/dashboard', label: 'Dashboard', icon: FiHome },
  { to: '/admin/students', label: 'Students', icon: FiUsers },
  { to: '/admin/teachers', label: 'Teachers', icon: FiUserCheck },
  { to: '/admin/courses', label: 'Courses', icon: FiBookOpen },
  { to: '/admin/timetable', label: 'Timetable', icon: FiCalendar },
]

export default function AdminLayout() {
  return <DashboardLayout title="Admin Panel" navItems={navItems} />
}
