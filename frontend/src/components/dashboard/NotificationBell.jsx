import { useCallback, useEffect, useState } from 'react'
import { FiBell } from 'react-icons/fi'
import { notificationsService } from '../../services/api'

export default function NotificationBell() {
  const [open, setOpen] = useState(false)
  const [count, setCount] = useState(0)
  const [items, setItems] = useState([])

  const load = useCallback(() => {
    notificationsService.unreadCount().then(({ data }) => setCount(data.data?.count ?? 0)).catch(() => {})
    notificationsService.list({ per_page: 10 }).then(({ data }) => setItems(data.data || [])).catch(() => {})
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
            <div className="flex items-center justify-between border-b border-slate-100 px-4 py-3">
              <p className="font-semibold text-navy">Notifications</p>
              {count > 0 && (
                <button type="button" onClick={markAll} className="text-xs text-primary hover:underline">Mark all read</button>
              )}
            </div>
            <ul className="max-h-80 overflow-y-auto">
              {items.map((n) => (
                <li
                  key={n.id}
                  className={`border-b border-slate-50 px-4 py-3 text-sm ${n.is_read ? 'bg-white' : 'bg-primary/5'}`}
                >
                  <p className="font-medium text-navy">{n.title}</p>
                  <p className="mt-1 text-slate-500">{n.message}</p>
                  <div className="mt-2 flex items-center justify-between text-xs text-slate-400">
                    <span>{new Date(n.created_at).toLocaleString()}</span>
                    {n.email_sent ? <span className="text-green-600">Emailed</span> : null}
                    {!n.is_read && (
                      <button type="button" onClick={() => markRead(n.id)} className="text-primary hover:underline">Mark read</button>
                    )}
                  </div>
                </li>
              ))}
              {items.length === 0 && <li className="px-4 py-6 text-center text-slate-400">No notifications</li>}
            </ul>
          </div>
        </>
      )}
    </div>
  )
}
