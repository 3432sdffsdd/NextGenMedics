import { Link } from 'react-router-dom'
import { FiMenu, FiSearch, FiChevronRight } from 'react-icons/fi'
import NotificationBell from './NotificationBell'

export default function Topbar({ pageTitle, breadcrumbs = [], onOpenSidebar }) {
  return (
    <header className="sticky top-0 z-20 border-b border-slate-200 bg-white/80 backdrop-blur-md">
      <div className="flex items-center gap-4 px-4 py-3 sm:px-6">
        <button
          type="button"
          onClick={onOpenSidebar}
          className="rounded-xl p-2 text-slate-600 transition-colors hover:bg-slate-100 lg:hidden"
          aria-label="Open menu"
        >
          <FiMenu size={20} />
        </button>

        <div className="min-w-0 flex-1">
          <nav className="hidden items-center gap-1.5 text-xs text-slate-400 sm:flex">
            {breadcrumbs.map((crumb, i) => (
              <span key={i} className="flex items-center gap-1.5">
                {i > 0 && <FiChevronRight size={12} className="text-slate-300" />}
                {crumb.to && i < breadcrumbs.length - 1 ? (
                  <Link to={crumb.to} className="transition-colors hover:text-blue-600">{crumb.label}</Link>
                ) : (
                  <span className={i === breadcrumbs.length - 1 ? 'font-medium text-slate-600' : ''}>{crumb.label}</span>
                )}
              </span>
            ))}
          </nav>
          <h1 className="truncate font-display text-lg font-bold text-slate-800">{pageTitle}</h1>
        </div>

        <div className="relative hidden md:block">
          <FiSearch className="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" size={16} />
          <input
            type="text"
            placeholder="Search…"
            className="w-56 rounded-xl border border-slate-200 bg-slate-50 py-2 pl-10 pr-4 text-sm text-slate-700 outline-none transition-all placeholder:text-slate-400 focus:w-72 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/15"
          />
        </div>

        <div className="flex items-center gap-1">
          <NotificationBell />
        </div>
      </div>
    </header>
  )
}
