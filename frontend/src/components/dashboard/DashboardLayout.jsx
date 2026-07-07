import { useEffect, useState } from 'react'
import { Outlet, useLocation } from 'react-router-dom'
import { AnimatePresence, motion } from 'framer-motion'
import Sidebar from './Sidebar'
import Topbar from './Topbar'

const COLLAPSE_KEY = 'ngm_sidebar_collapsed'

// Turn a URL segment into a readable label, e.g. "course-form" -> "Course Form".
function humanize(segment) {
  return segment
    .replace(/-/g, ' ')
    .replace(/\b\w/g, (c) => c.toUpperCase())
}

export default function DashboardLayout({ title, navItems }) {
  const location = useLocation()
  const [collapsed, setCollapsed] = useState(() => localStorage.getItem(COLLAPSE_KEY) === '1')
  const [mobileOpen, setMobileOpen] = useState(false)

  useEffect(() => {
    localStorage.setItem(COLLAPSE_KEY, collapsed ? '1' : '0')
  }, [collapsed])

  // Close the mobile drawer whenever the route changes.
  useEffect(() => {
    setMobileOpen(false)
  }, [location.pathname])

  // Derive page title + breadcrumbs from the active nav item / URL.
  const active = navItems.find((n) => location.pathname.startsWith(n.to))
  const segments = location.pathname.split('/').filter(Boolean)
  const pageTitle = active?.label || (segments.length ? humanize(segments[segments.length - 1]) : title)

  const breadcrumbs = [{ label: title, to: navItems[0]?.to }]
  if (active) breadcrumbs.push({ label: active.label, to: active.to })
  const lastSeg = segments[segments.length - 1]
  if (lastSeg && (!active || !active.to.endsWith(lastSeg))) {
    breadcrumbs.push({ label: humanize(lastSeg) })
  }

  return (
    <div className="min-h-screen bg-[#F8FAFC]">
      {/* Desktop sidebar */}
      <aside
        className={`fixed inset-y-0 left-0 z-30 hidden transition-[width] duration-300 ease-in-out lg:block ${
          collapsed ? 'w-[76px]' : 'w-64'
        }`}
      >
        <Sidebar title={title} navItems={navItems} collapsed={collapsed} onToggleCollapse={() => setCollapsed((v) => !v)} />
      </aside>

      {/* Mobile drawer */}
      <AnimatePresence>
        {mobileOpen && (
          <div className="lg:hidden">
            <motion.div
              className="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-sm"
              initial={{ opacity: 0 }}
              animate={{ opacity: 1 }}
              exit={{ opacity: 0 }}
              onClick={() => setMobileOpen(false)}
            />
            <motion.aside
              className="fixed inset-y-0 left-0 z-50 w-64"
              initial={{ x: '-100%' }}
              animate={{ x: 0 }}
              exit={{ x: '-100%' }}
              transition={{ type: 'tween', duration: 0.25, ease: [0.16, 1, 0.3, 1] }}
            >
              <Sidebar title={title} navItems={navItems} collapsed={false} />
            </motion.aside>
          </div>
        )}
      </AnimatePresence>

      {/* Main column */}
      <div className={`flex min-h-screen flex-col transition-[padding] duration-300 ${collapsed ? 'lg:pl-[76px]' : 'lg:pl-64'}`}>
        <Topbar pageTitle={pageTitle} breadcrumbs={breadcrumbs} onOpenSidebar={() => setMobileOpen(true)} />
        <main className="flex-1 p-4 sm:p-6 lg:p-8">
          <motion.div
            key={location.pathname}
            initial={{ opacity: 0, y: 12 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.35, ease: [0.16, 1, 0.3, 1] }}
          >
            <Outlet />
          </motion.div>
        </main>
      </div>
    </div>
  )
}
