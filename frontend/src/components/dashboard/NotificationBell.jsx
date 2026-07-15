import { useCallback, useEffect, useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { FiBell, FiExternalLink } from 'react-icons/fi'
import { notificationsService } from '../../services/api'
import { useAuth } from '../../context/AuthContext'
import { notificationActionLabel, notificationHref, dedupeNotifications } from '../../utils/notificationLinks'

export default function NotificationBell() {
  const navigate = useNavigate()
  const { user } = useAuth()
  const role = user?.role || 'student'
  const [open, setOpen] = useState(false)
  const [count, setCount] = useState(0)
  const [items, setItems] = useState([])

  const load = useCallback(() => {
    notificationsService.unreadCount().then(({ data }) => setCount(data.data?.count ?? 0)).catch(() => {})
    notificationsService.list({ per_page: 40 })
      .then(({ data }) => setItems(dedupeNotifications(data.data || [], 5)))
      .catch(() => {})
  }, [])

  useEffect(() => {
    load()
    const t = setInterval(load, 60000)
    return () => clearInterval(t)
  }, [load])

  const markRead = async (id) => {
    await notificationsService.markRead(id)
    load()
  }

  const markAll = async () => {
    await notificationsService.markAllRead()
    load()
  }

  const openNotification = async (n) => {
    if (!n.is_read) {
      try { await notificationsService.markRead(n.id) } catch { /* ignore */ }
    }
    const href = notificationHref(n, role === 'admin' ? 'teacher' : role)
    setOpen(false)
    load()
    if (href) navigate(href)
  }

  return (
    <div className="relative">
      <button
        type="button"
        onClick={() => setOpen(!open)}
        className="relative rounded-xl p-2 text-slate-600 hover:bg-slate-100"
        aria-label="Notifications"
      >
        <FiBell size={20} />
        {count > 0 && (
          <span className="absolute right-1 top-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white">
            {count > 9 ? '9+' : count}
          </span>
        )}
      </button>

      {open && (
        <>
          <button type="button" className="fixed inset-0 z-40" onClick={() => setOpen(false)} aria-label="Close" />
          <div className="absolute right-0 z-50 mt-2 w-80 rounded-2xl border border-slate-100 bg-white shadow-soft-lg">
            <div className="flex items-center justify-between border-b border-slate-100 px-3 py-2">
              <p className="text-sm font-semibold text-navy">Notifications</p>
              {count > 0 && (
                <button type="button" onClick={markAll} className="text-xs text-primary hover:underline">Mark all read</button>
              )}
            </div>
            <ul className="max-h-56 overflow-y-auto">
              {items.map((n) => {
                const href = notificationHref(n, role === 'admin' ? 'teacher' : role)
                return (
                  <li
                    key={n.id}
                    className={`border-b border-slate-50 px-3 py-2 text-sm ${n.is_read ? 'bg-white' : 'bg-primary/5'}`}
                  >
                    <button
                      type="button"
                      className={`w-full text-left ${href ? 'cursor-pointer' : 'cursor-default'}`}
                      onClick={() => href && openNotification(n)}
                      disabled={!href}
                    >
                      <p className="truncate text-sm font-medium text-navy">{n.title}</p>
                      <p className="mt-0.5 line-clamp-1 text-xs text-slate-500">{n.message}</p>
                    </button>
                    <div className="mt-1 flex flex-wrap items-center justify-between gap-1 text-[11px] text-slate-400">
                      <span>{new Date(n.created_at).toLocaleString()}</span>
                      <div className="flex items-center gap-2">
                        {href && (
                          <button
                            type="button"
                            onClick={() => openNotification(n)}
                            className="inline-flex items-center gap-1 font-semibold text-primary hover:underline"
                          >
                            <FiExternalLink size={11} /> {notificationActionLabel(n)}
                          </button>
                        )}
                        {!n.is_read && (
                          <button type="button" onClick={() => markRead(n.id)} className="text-slate-500 hover:underline">
                            Mark read
                          </button>
                        )}
                      </div>
                    </div>
                  </li>
                )
              })}
              {items.length === 0 && <li className="px-3 py-4 text-center text-xs text-slate-400">No notifications</li>}
            </ul>
          </div>
        </>
      )}
    </div>
  )
}
