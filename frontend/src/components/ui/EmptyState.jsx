import { FiInbox } from 'react-icons/fi'

export default function EmptyState({ icon: Icon = FiInbox, title = 'Nothing here yet', description, action, className = '' }) {
  return (
    <div className={`flex flex-col items-center justify-center px-6 py-14 text-center ${className}`}>
      <div className="grid h-16 w-16 place-items-center rounded-2xl bg-slate-100 text-slate-400">
        <Icon size={28} />
      </div>
      <h3 className="mt-4 font-display text-base font-bold text-slate-700">{title}</h3>
      {description && <p className="mt-1 max-w-sm text-sm text-slate-400">{description}</p>}
      {action && <div className="mt-5">{action}</div>}
    </div>
  )
}
