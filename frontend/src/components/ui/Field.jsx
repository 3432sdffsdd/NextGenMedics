import { useId } from 'react'

/**
 * Floating-label input / textarea / select wrapper.
 * Pass `as="textarea"` or `as="select"` (with children options) as needed.
 */
export default function Field({
  as = 'input',
  label,
  error,
  hint,
  className = '',
  containerClassName = '',
  children,
  ...props
}) {
  const id = useId()
  const Comp = as
  const base =
    'peer w-full rounded-xl border bg-white px-3.5 pt-5 pb-2 text-sm text-slate-800 outline-none transition-all placeholder-transparent focus:ring-4'
  const state = error
    ? 'border-red-300 focus:border-red-500 focus:ring-red-500/15'
    : 'border-slate-200 focus:border-blue-500 focus:ring-blue-500/15'

  return (
    <div className={containerClassName}>
      <div className="relative">
        <Comp
          id={id}
          placeholder={label || ' '}
          className={`${base} ${state} ${as === 'textarea' ? 'min-h-[96px] resize-y' : ''} ${className}`}
          {...props}
        >
          {children}
        </Comp>
        {label && (
          <label
            htmlFor={id}
            className="pointer-events-none absolute left-3.5 top-2 text-xs font-medium text-slate-400 transition-all peer-placeholder-shown:top-3.5 peer-placeholder-shown:text-sm peer-focus:top-2 peer-focus:text-xs peer-focus:text-blue-600"
          >
            {label}
          </label>
        )}
      </div>
      {error ? (
        <p className="mt-1 text-xs font-medium text-red-500">{error}</p>
      ) : hint ? (
        <p className="mt-1 text-xs text-slate-400">{hint}</p>
      ) : null}
    </div>
  )
}
