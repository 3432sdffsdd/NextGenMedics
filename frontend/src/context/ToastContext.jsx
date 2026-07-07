import { createContext, useCallback, useContext, useMemo, useRef, useState } from 'react'
import { createPortal } from 'react-dom'
import { AnimatePresence, motion } from 'framer-motion'
import { FiCheckCircle, FiAlertCircle, FiInfo, FiAlertTriangle, FiX } from 'react-icons/fi'

const ToastContext = createContext(null)

const STYLES = {
  success: { icon: FiCheckCircle, ring: 'border-emerald-200', bar: 'bg-emerald-500', text: 'text-emerald-600' },
  error: { icon: FiAlertCircle, ring: 'border-red-200', bar: 'bg-red-500', text: 'text-red-600' },
  info: { icon: FiInfo, ring: 'border-blue-200', bar: 'bg-blue-500', text: 'text-blue-600' },
  warning: { icon: FiAlertTriangle, ring: 'border-amber-200', bar: 'bg-amber-500', text: 'text-amber-600' },
}

export function ToastProvider({ children }) {
  const [toasts, setToasts] = useState([])
  const idRef = useRef(0)

  const remove = useCallback((id) => setToasts((t) => t.filter((x) => x.id !== id)), [])

  const push = useCallback((type, message, opts = {}) => {
    const id = ++idRef.current
    const duration = opts.duration ?? 3800
    setToasts((t) => [...t, { id, type, message, title: opts.title }])
    if (duration > 0) setTimeout(() => remove(id), duration)
    return id
  }, [remove])

  const toast = useMemo(() => ({
    success: (msg, opts) => push('success', msg, opts),
    error: (msg, opts) => push('error', msg, opts),
    info: (msg, opts) => push('info', msg, opts),
    warning: (msg, opts) => push('warning', msg, opts),
    show: push,
    dismiss: remove,
  }), [push, remove])

  return (
    <ToastContext.Provider value={toast}>
      {children}
      {createPortal(
        <div className="pointer-events-none fixed right-4 top-4 z-[200] flex w-full max-w-sm flex-col gap-3">
          <AnimatePresence>
            {toasts.map((t) => {
              const s = STYLES[t.type] || STYLES.info
              const Icon = s.icon
              return (
                <motion.div
                  key={t.id}
                  layout
                  initial={{ opacity: 0, x: 40, scale: 0.95 }}
                  animate={{ opacity: 1, x: 0, scale: 1 }}
                  exit={{ opacity: 0, x: 40, scale: 0.9 }}
                  transition={{ duration: 0.25, ease: [0.16, 1, 0.3, 1] }}
                  className={`pointer-events-auto flex items-start gap-3 overflow-hidden rounded-xl border ${s.ring} bg-white p-4 shadow-lg`}
                >
                  <div className={`mt-0.5 ${s.text}`}><Icon size={20} /></div>
                  <div className="flex-1">
                    {t.title && <p className="text-sm font-bold text-slate-800">{t.title}</p>}
                    <p className="text-sm text-slate-600">{t.message}</p>
                  </div>
                  <button type="button" onClick={() => remove(t.id)} className="text-slate-300 transition-colors hover:text-slate-500" aria-label="Dismiss">
                    <FiX size={16} />
                  </button>
                </motion.div>
              )
            })}
          </AnimatePresence>
        </div>,
        document.body
      )}
    </ToastContext.Provider>
  )
}

export function useToast() {
  const ctx = useContext(ToastContext)
  if (!ctx) throw new Error('useToast must be used within ToastProvider')
  return ctx
}
