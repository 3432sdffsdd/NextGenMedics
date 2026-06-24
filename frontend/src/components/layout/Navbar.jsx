import { useState, useEffect } from 'react'
import { Link, NavLink, useLocation } from 'react-router-dom'
import { motion, AnimatePresence } from 'framer-motion'
import { FiChevronDown, FiMenu, FiX, FiArrowRight } from 'react-icons/fi'
import { navMenu } from '../../data/siteData'
import Logo from '../common/Logo'

export default function Navbar() {
  const [scrolled, setScrolled] = useState(false)
  const [openMenu, setOpenMenu] = useState(null)
  const [mobileOpen, setMobileOpen] = useState(false)
  const [mobileSub, setMobileSub] = useState(null)
  const location = useLocation()

  useEffect(() => {
    const onScroll = () => setScrolled(window.scrollY > 20)
    window.addEventListener('scroll', onScroll)
    return () => window.removeEventListener('scroll', onScroll)
  }, [])

  useEffect(() => {
    setMobileOpen(false)
    setOpenMenu(null)
  }, [location])

  return (
    <header
      className={`sticky top-0 z-50 transition-all duration-300 ${
        scrolled ? 'bg-white/90 backdrop-blur-xl shadow-soft' : 'bg-white/60 backdrop-blur-md'
      }`}
    >
      <nav className="container-px mx-auto flex h-[72px] max-w-[1400px] items-center justify-between">
        <Logo />

        {/* Desktop menu */}
        <ul className="hidden items-center gap-1 lg:flex">
          {navMenu.map((item) => (
            <li
              key={item.label}
              className="relative"
              onMouseEnter={() => item.mega && setOpenMenu(item.label)}
              onMouseLeave={() => setOpenMenu(null)}
            >
              {item.mega ? (
                <button className="flex items-center gap-1 rounded-xl px-3.5 py-2 text-[15px] font-medium text-navy/80 transition-colors hover:text-primary">
                  {item.label}
                  <FiChevronDown
                    className={`transition-transform duration-200 ${openMenu === item.label ? 'rotate-180' : ''}`}
                    size={15}
                  />
                </button>
              ) : (
                <NavLink
                  to={item.to}
                  className={({ isActive }) =>
                    `rounded-xl px-3.5 py-2 text-[15px] font-medium transition-colors hover:text-primary ${
                      isActive ? 'text-primary' : 'text-navy/80'
                    }`
                  }
                >
                  {item.label}
                </NavLink>
              )}

              <AnimatePresence>
                {item.mega && openMenu === item.label && (
                  <motion.div
                    initial={{ opacity: 0, y: 12 }}
                    animate={{ opacity: 1, y: 0 }}
                    exit={{ opacity: 0, y: 12 }}
                    transition={{ duration: 0.18 }}
                    className="absolute left-1/2 top-full -translate-x-1/2 pt-3"
                  >
                    <div
                      className={`grid gap-2 rounded-2xl border border-slate-100 bg-white p-4 shadow-soft-lg ${
                        item.columns.length > 1 ? 'w-[640px] grid-cols-2' : 'w-[340px] grid-cols-1'
                      }`}
                    >
                      {item.columns.map((col) => (
                        <div key={col.heading}>
                          <p className="mb-2 px-3 text-xs font-bold uppercase tracking-wider text-slate-400">
                            {col.heading}
                          </p>
                          {col.items.map((sub) => (
                            <Link
                              key={sub.label}
                              to={sub.to}
                              className="group flex flex-col rounded-xl px-3 py-2.5 transition-colors hover:bg-lightbg"
                            >
                              <span className="flex items-center gap-1.5 text-sm font-semibold text-navy group-hover:text-primary">
                                {sub.label}
                                <FiArrowRight className="opacity-0 transition-all group-hover:translate-x-0.5 group-hover:opacity-100" size={13} />
                              </span>
                              <span className="text-xs text-slate-500">{sub.desc}</span>
                            </Link>
                          ))}
                        </div>
                      ))}
                    </div>
                  </motion.div>
                )}
              </AnimatePresence>
            </li>
          ))}
        </ul>

        <div className="flex items-center gap-3">
          <Link to="/login" className="hidden btn-primary !py-2.5 !px-6 text-sm sm:inline-flex">
            Student Login
          </Link>
          <button
            className="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 text-navy lg:hidden"
            onClick={() => setMobileOpen((v) => !v)}
            aria-label="Toggle menu"
          >
            {mobileOpen ? <FiX size={22} /> : <FiMenu size={22} />}
          </button>
        </div>
      </nav>

      {/* Mobile menu */}
      <AnimatePresence>
        {mobileOpen && (
          <motion.div
            initial={{ opacity: 0, height: 0 }}
            animate={{ opacity: 1, height: 'auto' }}
            exit={{ opacity: 0, height: 0 }}
            className="overflow-hidden border-t border-slate-100 bg-white lg:hidden"
          >
            <div className="container-px mx-auto max-h-[70vh] overflow-y-auto py-4">
              {navMenu.map((item) => (
                <div key={item.label} className="border-b border-slate-50 py-1">
                  {item.mega ? (
                    <>
                      <button
                        className="flex w-full items-center justify-between py-2.5 font-semibold text-navy"
                        onClick={() => setMobileSub(mobileSub === item.label ? null : item.label)}
                      >
                        {item.label}
                        <FiChevronDown className={`transition-transform ${mobileSub === item.label ? 'rotate-180' : ''}`} />
                      </button>
                      <AnimatePresence>
                        {mobileSub === item.label && (
                          <motion.div
                            initial={{ height: 0 }}
                            animate={{ height: 'auto' }}
                            exit={{ height: 0 }}
                            className="overflow-hidden"
                          >
                            {item.columns.flatMap((c) => c.items).map((sub) => (
                              <Link
                                key={sub.label}
                                to={sub.to}
                                className="block rounded-lg py-2 pl-4 text-sm text-slate-600 hover:text-primary"
                              >
                                {sub.label}
                              </Link>
                            ))}
                          </motion.div>
                        )}
                      </AnimatePresence>
                    </>
                  ) : (
                    <Link to={item.to} className="block py-2.5 font-semibold text-navy">
                      {item.label}
                    </Link>
                  )}
                </div>
              ))}
              <Link to="/login" className="btn-primary mt-4 w-full">
                Student Login
              </Link>
            </div>
          </motion.div>
        )}
      </AnimatePresence>
    </header>
  )
}
