import { Link } from 'react-router-dom'

export function LogoMark({ className = '' }) {
  const NAVY = '#0F172A'
  const GREEN = '#16A34A'
  return (
    <svg viewBox="0 0 120 120" className={className} fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="NextGen Medics emblem">
      {/* outer ring (open arc) */}
      <path
        d="M60 14a46 46 0 1 1-32.5 13.5"
        stroke={NAVY}
        strokeWidth="6"
        strokeLinecap="round"
      />
      {/* inner accent arc */}
      <path
        d="M86 30a38 38 0 0 1 6 14"
        stroke={GREEN}
        strokeWidth="5"
        strokeLinecap="round"
      />
      {/* leaf */}
      <path
        d="M78 58c8-2 15 1 19-4-1 7-7 12-14 11-3 0-5-3-5-7z"
        fill={GREEN}
      />
      {/* green medical cross badge */}
      <g>
        <rect x="49" y="24" width="8" height="22" rx="2" fill={GREEN} />
        <rect x="42" y="31" width="22" height="8" rx="2" fill={GREEN} />
      </g>
      {/* NG monogram */}
      <text
        x="60"
        y="86"
        textAnchor="middle"
        fontFamily="Poppins, sans-serif"
        fontWeight="800"
        fontSize="42"
        fill={NAVY}
        letterSpacing="-2"
      >
        NG
      </text>
    </svg>
  )
}

export default function Logo({ light = false, withTagline = false, className = '' }) {
  return (
    <Link to="/" className={`flex items-center gap-2.5 ${className}`} aria-label="NextGen Medics home">
      <LogoMark className="h-11 w-11 shrink-0" />
      <span className="flex flex-col leading-none">
        <span className="flex items-baseline gap-1 font-display text-[19px] font-extrabold leading-none">
          <span className={light ? 'text-white' : 'text-navy'}>NextGen</span>
          <span className="text-brand-green">Medics</span>
        </span>
        {withTagline && (
          <span
            className={`mt-1 text-[9px] font-semibold uppercase tracking-[0.18em] ${
              light ? 'text-slate-400' : 'text-slate-500'
            }`}
          >
            High Yield · Structured · Success
          </span>
        )}
      </span>
    </Link>
  )
}
