import { NavLink } from 'react-router-dom'
import { FiLogOut, FiChevronLeft } from 'react-icons/fi'
import { useAuth } from '../../context/AuthContext'
import { LogoMark } from '../common/Logo'

function initials(name = '') {
  return name.split(' ').filter(Boolean).slice(0, 2).map((n) => n[0]?.toUpperCase()).join('') || 'U'
}

export default function Sidebar({ title, navItems, collapsed, onToggleCollapse }) {
  const { user, logout } = useAuth()

  return (
    <div className="flex h-full flex-col bg-[#0F172A] text-slate-300">
      {/* Brand */}
      <div className={`flex items-center gap-2.5 border-b border-white/5 px-4 ${collapsed ? 'justify-center py-5' : 'py-[18px]'}`}>
        <span className="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-white/5">
          <LogoMark className="h-7 w-7" />
        </span>
        {!collapsed && (
          <div className="min-w-0 leading-tight">
            <p className="truncate font-display text-sm font-extrabold text-white">
              NextGen<span className="text-emerald-400">Medics</span>
            </p>
            <p className="truncate text-[10px] font-semibold uppercase tracking-[0.15em] text-slate-500">{title}</p>
          </div>
        )}
      </div>

      {/* Nav */}
      <nav className="flex-1 space-y-1 overflow-y-auto px-3 py-4">
        {navItems.map(({ to, label, icon: Icon }) => (
          <NavLink
            key={to}
            to={to}
            end={to.endsWith('/dashboard')}
            title={collapsed ? label : undefined}
            className={({ isActive }) =>
              `group relative flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-200 ${
                collapsed ? 'justify-center' : ''
              } ${
                isActive
                  ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/25'
                  : 'text-slate-400 hover:bg-white/5 hover:text-white'
              }`
            }
          >
            {({ isActive }) => (
              <>
                {isActive && !collapsed && (
                  <span className="absolute left-0 top-1/2 h-6 -translate-y-1/2 rounded-r-full bg-white/80" style={{ width: 3 }} />
                )}
                <Icon size={19} className="shrink-0" />
                {!collapsed && <span className="truncate">{label}</span>}
              </>
            )}
          </NavLink>
        ))}
      </nav>

      {/* Collapse toggle (desktop) */}
      {onToggleCollapse && (
        <button
          type="button"
          onClick={onToggleCollapse}
          className="mx-3 mb-2 hidden items-center justify-center gap-2 rounded-xl border border-white/5 py-2 text-xs font-medium text-slate-400 transition-colors hover:bg-white/5 hover:text-white lg:flex"
        >
          <FiChevronLeft className={`transition-transform duration-300 ${collapsed ? 'rotate-180' : ''}`} size={16} />
          {!collapsed && 'Collapse'}
        </button>
      )}

      {/* User + logout */}
      <div className="border-t border-white/5 p-3">
        <div className={`flex items-center gap-3 rounded-xl px-2 py-2 ${collapsed ? 'justify-center' : ''}`}>
          <span className="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-gradient-to-br from-blue-500 to-blue-700 text-xs font-bold text-white">
            {initials(user?.full_name)}
          </span>
          {!collapsed && (
            <div className="min-w-0 flex-1">
              <p className="truncate text-sm font-semibold text-white">{user?.full_name}</p>
              <p className="truncate text-xs capitalize text-slate-500">{user?.role}</p>
            </div>
          )}
        </div>
        <button
          type="button"
          onClick={logout}
          title={collapsed ? 'Logout' : undefined}
          className={`mt-1 flex w-full items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-400 transition-colors hover:bg-red-500/10 hover:text-red-400 ${
            collapsed ? 'justify-center' : ''
          }`}
        >
          <FiLogOut size={18} />
          {!collapsed && 'Logout'}
        </button>
      </div>
    </div>
  )
}
