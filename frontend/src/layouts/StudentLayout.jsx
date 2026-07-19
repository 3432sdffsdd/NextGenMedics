import { FiHome, FiBookOpen, FiClipboard, FiCalendar, FiLayers, FiZap, FiTrendingUp, FiDatabase, FiAlertCircle, FiPackage, FiTarget, FiFilm, FiBarChart2 } from 'react-icons/fi'
import DashboardLayout from '../components/dashboard/DashboardLayout'

const navItems = [
  { to: '/student/dashboard', label: 'Dashboard', icon: FiHome },
  { to: '/student/courses', label: 'My Courses', icon: FiBookOpen },
  { to: '/student/study-material', label: 'Lecture Videos', icon: FiFilm },
  { to: '/student/video-progress', label: 'Video Progress', icon: FiBarChart2 },
  { to: '/student/study-pack', label: 'Study Pack', icon: FiPackage },
  { to: '/student/challenge', label: 'Daily Challenge', icon: FiZap },
  { to: '/student/weak-areas', label: 'Weak Areas', icon: FiTarget },
  { to: '/student/question-bank', label: 'Question Bank', icon: FiDatabase },
  { to: '/student/mistakes', label: 'My Mistakes', icon: FiAlertCircle },
  { to: '/student/fcps-planner', label: 'FCPS Study Planner', icon: FiCalendar },
  { to: '/student/flashcards', label: 'Flashcards', icon: FiLayers },
  { to: '/student/progress', label: 'My Progress', icon: FiTrendingUp },
  { to: '/student/timetable', label: 'Timetable', icon: FiCalendar },
  { to: '/student/assignments', label: 'Assignments & Grades', icon: FiClipboard },
]

export default function StudentLayout() {
  return <DashboardLayout title="Student Panel" navItems={navItems} />
}
