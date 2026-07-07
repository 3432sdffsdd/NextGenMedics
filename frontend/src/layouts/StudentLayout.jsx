import { FiHome, FiBookOpen, FiClipboard, FiCalendar, FiLayers, FiFileText, FiZap, FiTrendingUp, FiDatabase, FiAlertCircle } from 'react-icons/fi'
import DashboardLayout from '../components/dashboard/DashboardLayout'

const navItems = [
  { to: '/student/dashboard', label: 'Dashboard', icon: FiHome },
  { to: '/student/courses', label: 'My Courses', icon: FiBookOpen },
  { to: '/student/challenge', label: 'Daily Challenge', icon: FiZap },
  { to: '/student/question-bank', label: 'Question Bank', icon: FiDatabase },
  { to: '/student/mistakes', label: 'My Mistakes', icon: FiAlertCircle },
  { to: '/student/planner', label: 'Study Planner', icon: FiCalendar },
  { to: '/student/flashcards', label: 'Flashcards', icon: FiLayers },
  { to: '/student/revision', label: 'Revision Center', icon: FiFileText },
  { to: '/student/progress', label: 'My Progress', icon: FiTrendingUp },
  { to: '/student/timetable', label: 'Timetable', icon: FiCalendar },
  { to: '/student/assignments', label: 'Assignments & Grades', icon: FiClipboard },
]

export default function StudentLayout() {
  return <DashboardLayout title="Student Panel" navItems={navItems} />
}
