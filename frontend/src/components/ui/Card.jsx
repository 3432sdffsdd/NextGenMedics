export default function Card({
  title,
  subtitle,
  actions,
  icon: Icon,
  padding = 'p-6',
  className = '',
  bodyClassName = '',
  children,
}) {
  const hasHeader = title || actions
  return (
    <div
      className={`rounded-2xl border border-slate-200 bg-white shadow-[0_2px_16px_rgba(15,23,42,0.06)] transition-shadow duration-300 hover:shadow-[0_8px_30px_rgba(15,23,42,0.10)] ${className}`}
    >
      {hasHeader && (
        <div className="flex items-start justify-between gap-4 border-b border-slate-100 px-6 py-4">
          <div className="flex items-center gap-3">
            {Icon && (
              <span className="grid h-9 w-9 place-items-center rounded-xl bg-blue-50 text-blue-600">
                <Icon size={18} />
              </span>
            )}
            <div>
              {title && <h3 className="font-display text-base font-bold text-slate-800">{title}</h3>}
              {subtitle && <p className="text-xs text-slate-400">{subtitle}</p>}
            </div>
          </div>
          {actions && <div className="flex items-center gap-2">{actions}</div>}
        </div>
      )}
      <div className={`${padding} ${bodyClassName}`}>{children}</div>
    </div>
  )
}
