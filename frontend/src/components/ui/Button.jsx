import { FiLoader } from 'react-icons/fi'

const VARIANTS = {
  primary:
    'bg-blue-600 text-white shadow-sm hover:bg-blue-700 focus-visible:ring-blue-500/40 disabled:hover:bg-blue-600',
  secondary:
    'bg-blue-50 text-blue-700 hover:bg-blue-100 focus-visible:ring-blue-500/30',
  outline:
    'border border-slate-200 bg-white text-slate-700 hover:border-slate-300 hover:bg-slate-50 focus-visible:ring-slate-400/30',
  ghost:
    'text-slate-600 hover:bg-slate-100 focus-visible:ring-slate-400/30',
  danger:
    'bg-red-500 text-white shadow-sm hover:bg-red-600 focus-visible:ring-red-500/40',
  success:
    'bg-emerald-500 text-white shadow-sm hover:bg-emerald-600 focus-visible:ring-emerald-500/40',
}

const SIZES = {
  sm: 'px-3 py-1.5 text-xs gap-1.5',
  md: 'px-4 py-2.5 text-sm gap-2',
  lg: 'px-5 py-3 text-sm gap-2',
  icon: 'p-2.5',
}

export default function Button({
  as: Comp = 'button',
  variant = 'primary',
  size = 'md',
  loading = false,
  icon: Icon,
  iconRight: IconRight,
  className = '',
  children,
  disabled,
  ...props
}) {
  const isIconOnly = size === 'icon'
  return (
    <Comp
      className={`inline-flex items-center justify-center rounded-xl font-semibold transition-all duration-200 focus:outline-none focus-visible:ring-4 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60 ${VARIANTS[variant]} ${SIZES[size]} ${className}`}
      disabled={disabled || loading}
      {...props}
    >
      {loading && <FiLoader className="animate-spin" size={isIconOnly ? 18 : 16} />}
      {!loading && Icon && <Icon size={isIconOnly ? 18 : 16} />}
      {!isIconOnly && children}
      {!loading && !isIconOnly && IconRight && <IconRight size={16} />}
    </Comp>
  )
}
