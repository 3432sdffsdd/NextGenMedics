import { FiCheckCircle, FiInfo, FiAlertTriangle, FiXCircle } from 'react-icons/fi'

const CONFIG = {
  success: { cls: 'border-emerald-200 bg-emerald-50 text-emerald-700', icon: FiCheckCircle },
  info: { cls: 'border-blue-200 bg-blue-50 text-blue-700', icon: FiInfo },
  warning: { cls: 'border-amber-200 bg-amber-50 text-amber-700', icon: FiAlertTriangle },
  error: { cls: 'border-red-200 bg-red-50 text-red-600', icon: FiXCircle },
}

export default function Alert({ type = 'error', title, children, className = '' }) {
  const { cls, icon: Icon } = CONFIG[type] || CONFIG.error
  return (
    <div className={`flex items-start gap-3 rounded-xl border px-4 py-3 text-sm ${cls} ${className}`} role="alert">
      <Icon className="mt-0.5 shrink-0" size={18} />
      <div className="min-w-0">
        {title && <p className="font-semibold">{title}</p>}
        <div className={title ? 'mt-0.5 opacity-90' : ''}>{children}</div>
      </div>
    </div>
  )
}
