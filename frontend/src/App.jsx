import { Routes, Route, useLocation } from 'react-router-dom'
import { useEffect } from 'react'
import { AuthProvider } from './context/AuthContext'
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
import FloatingWhatsApp from './components/common/FloatingWhatsApp'
import NotFound from './pages/NotFound'

export default function App() {
  const location = useLocation()
  const isAuthPage = location.pathname === '/login'

  useEffect(() => {
    window.scrollTo(0, 0)
  }, [location.pathname])

  return (
    <AuthProvider>
      <ScrollToTop />
      <div className="flex min-h-screen flex-col bg-white">
        {!isAuthPage && <Navbar />}
        <main className="flex-1">
          <Routes>
            <Route path="/" element={<Home />} />
            <Route path="/login" element={<Login />} />
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
        {!isAuthPage && <Footer />}
        {!isAuthPage && <FloatingWhatsApp />}
      </div>
    </AuthProvider>
  )
}
