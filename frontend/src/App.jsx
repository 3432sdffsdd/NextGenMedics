import { Routes, Route, useLocation, Navigate } from 'react-router-dom'
import { useEffect } from 'react'
import { AuthProvider } from './context/AuthContext'
import { ToastProvider } from './context/ToastContext'
import { ConfirmProvider } from './context/ConfirmContext'
import Navbar from './components/layout/Navbar'
import Footer from './components/layout/Footer'
import ScrollToTop from './components/common/ScrollToTop'
import Home from './pages/Home'
import Login from './pages/Login'
import GenericPage from './pages/GenericPage'
import Contact from './pages/Contact'
import CourseDetail from './pages/CourseDetail'
import Syllabus from './pages/Syllabus'
import HelpCenter from './pages/HelpCenter'
import FreeVideos from './pages/FreeVideos'
import ProtectedRoute from './components/common/ProtectedRoute'
import AdminLayout from './layouts/AdminLayout'
import TeacherLayout from './layouts/TeacherLayout'
import AdminDashboard from './pages/admin/AdminDashboard'
import AdminStudents from './pages/admin/AdminStudents'
import AdminTeachers from './pages/admin/AdminTeachers'
import AdminCourses from './pages/admin/AdminCourses'
import AdminCourseForm from './pages/admin/AdminCourseForm'
import AdminAiJobs from './pages/admin/AdminAiJobs'
import TeacherDashboard from './pages/teacher/TeacherDashboard'
import TeacherCourses from './pages/teacher/TeacherCourses'
import TeacherCourseHub from './pages/teacher/TeacherCourseHub'
import StudentLayout from './layouts/StudentLayout'
import StudentDashboard from './pages/student/StudentDashboard'
import StudentCourses from './pages/student/StudentCourses'
import StudentCourseHub from './pages/student/StudentCourseHub'
import StudentAssignments from './pages/student/StudentAssignments'
import FlashcardCenter from './pages/student/FlashcardCenter'
import StudyPack from './pages/student/StudyPack'
import DailyChallenge from './pages/student/DailyChallenge'
import WeakAreas from './pages/student/WeakAreas'
import StudentProgress from './pages/student/StudentProgress'
import QuestionBank from './pages/student/QuestionBank'
import MyMistakes from './pages/student/MyMistakes'
import VideoProgress from './pages/student/VideoProgress'
import StudyMaterial from './pages/student/StudyMaterial'
import PersonalStudyPlanner from './pages/student/PersonalStudyPlanner'
import StudentPerformance from './pages/teacher/StudentPerformance'
import TimetablePage from './pages/shared/TimetablePage'
import FloatingWhatsApp from './components/common/FloatingWhatsApp'
import NotFound from './pages/NotFound'

export default function App() {
  const location = useLocation()
  const isMinimalLayout =
    location.pathname === '/login' ||
    location.pathname.startsWith('/admin/') ||
    location.pathname.startsWith('/teacher/') ||
    location.pathname.startsWith('/student/')

  useEffect(() => {
    window.scrollTo(0, 0)
  }, [location.pathname])

  return (
    <AuthProvider>
      <ToastProvider>
        <ConfirmProvider>
      <ScrollToTop />
      <div className="flex min-h-screen flex-col bg-white">
        {!isMinimalLayout && <Navbar />}
        <main className="flex-1">
          <Routes>
            <Route path="/" element={<Home />} />
            <Route path="/login" element={<Login />} />

            {/* Admin panel */}
            <Route
              path="/admin"
              element={
                <ProtectedRoute roles={['admin']}>
                  <AdminLayout />
                </ProtectedRoute>
              }
            >
              <Route index element={<Navigate to="dashboard" replace />} />
              <Route path="dashboard" element={<AdminDashboard />} />
              <Route path="students" element={<AdminStudents />} />
              <Route path="teachers" element={<AdminTeachers />} />
              <Route path="courses" element={<AdminCourses />} />
              <Route path="courses/new" element={<AdminCourseForm />} />
              <Route path="courses/:id" element={<AdminCourseForm />} />
              <Route path="ai-jobs" element={<AdminAiJobs />} />
              <Route path="timetable" element={<TimetablePage title="All Scheduled Classes" />} />
            </Route>

            {/* Teacher panel */}
            <Route
              path="/teacher"
              element={
                <ProtectedRoute roles={['teacher', 'admin']}>
                  <TeacherLayout />
                </ProtectedRoute>
              }
            >
              <Route index element={<Navigate to="dashboard" replace />} />
              <Route path="dashboard" element={<TeacherDashboard />} />
              <Route path="courses" element={<TeacherCourses />} />
              <Route path="courses/:id" element={<TeacherCourseHub />} />
              <Route path="student-performance" element={<StudentPerformance />} />
              <Route path="video-analytics" element={<Navigate to="/teacher/student-performance" replace />} />
              <Route path="timetable" element={<TimetablePage title="My Class Timetable" />} />
            </Route>

            {/* Student panel */}
            <Route
              path="/student"
              element={
                <ProtectedRoute roles={['student']}>
                  <StudentLayout />
                </ProtectedRoute>
              }
            >
              <Route index element={<Navigate to="dashboard" replace />} />
              <Route path="dashboard" element={<StudentDashboard />} />
              <Route path="courses" element={<StudentCourses />} />
              <Route path="courses/:id" element={<StudentCourseHub />} />
              <Route path="challenge" element={<DailyChallenge />} />
              <Route path="weak-areas" element={<WeakAreas />} />
              <Route path="question-bank" element={<QuestionBank />} />
              <Route path="study-material" element={<StudyMaterial />} />
              <Route path="video-progress" element={<VideoProgress />} />
              <Route path="mistakes" element={<MyMistakes />} />
              <Route path="personal-planner" element={<Navigate to="/student/dashboard" replace />} />
              <Route path="planner" element={<Navigate to="/student/dashboard" replace />} />
              <Route path="study-planner" element={<Navigate to="/student/dashboard" replace />} />
              {/* FCPS Study Planner = Personal planner with LMS / Manual / Mixed modes */}
              <Route path="fcps-planner" element={<PersonalStudyPlanner />} />
              <Route path="flashcards" element={<FlashcardCenter />} />
              <Route path="revision" element={<Navigate to="/student/study-pack" replace />} />
              <Route path="study-pack" element={<StudyPack />} />
              <Route path="progress" element={<StudentProgress />} />
              <Route path="assignments" element={<StudentAssignments />} />
              <Route path="timetable" element={<TimetablePage title="My Class Timetable" />} />
            </Route>

            <Route path="/contact" element={<Contact />} />
            <Route path="/help-center" element={<HelpCenter />} />
            <Route path="/courses/fcps-part-1" element={<CourseDetail />} />
            <Route path="/courses/:slug" element={<GenericPage section="Courses" />} />
            <Route path="/resources/syllabus" element={<Syllabus />} />
            <Route path="/resources/free-videos" element={<FreeVideos />} />
            <Route path="/resources/:slug" element={<GenericPage section="Resources" />} />
            <Route path="/community/:slug" element={<GenericPage section="Community" />} />
            <Route path="/announcements/:slug" element={<GenericPage section="Announcements" />} />
            <Route path="*" element={<NotFound />} />
          </Routes>
        </main>
        {!isMinimalLayout && <Footer />}
        {!isMinimalLayout && <FloatingWhatsApp />}
      </div>
        </ConfirmProvider>
      </ToastProvider>
    </AuthProvider>
  )
}
