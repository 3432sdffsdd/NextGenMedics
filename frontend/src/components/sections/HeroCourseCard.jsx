import { motion } from 'framer-motion'
import {
  FiClock, FiBarChart2, FiStar, FiArrowRight, FiAward, FiUsers, FiCheck,
} from 'react-icons/fi'
import { whatsappLink } from '../../data/siteData'

const highlights = [
  'Strong conceptual foundation',
  'Exam-focused high-yield content',
]

const WHATSAPP_URL = whatsappLink(
  "Hi NextGen Medics! I'm interested in enrolling in the FCPS 1 Preparation Course (October 2026 attempt). Please share the details."
)

export default function HeroCourseCard() {
  return (
    <div className="relative mx-auto max-w-md">
      <motion.div
        whileHover={{ y: -6 }}
        transition={{ type: 'spring', stiffness: 200, damping: 18 }}
        className="relative overflow-hidden rounded-[28px] border border-slate-100 bg-white shadow-soft-lg"
      >
        {/* banner */}
        <div className="relative h-44 overflow-hidden">
          <img
            src="https://images.unsplash.com/photo-1581595219315-a187dd40c322?w=800&h=500&fit=crop"
            alt="FCPS 1 Preparation Course"
            className="h-full w-full object-cover"
          />
          <div className="absolute inset-0 bg-gradient-to-t from-navy/90 via-navy/40 to-transparent" />

          <span className="absolute left-4 top-4 inline-flex items-center gap-1.5 rounded-full bg-brand-green px-3 py-1.5 text-[11px] font-bold uppercase tracking-wider text-white shadow-glow">
            <FiAward size={12} /> Featured Course
          </span>

          <div className="absolute bottom-4 left-4 right-4">
            <h3 className="font-display text-xl font-extrabold leading-tight text-white">
              FCPS 1 Preparation Course
            </h3>
            <p className="mt-0.5 text-sm font-medium text-primary-light">For October 2026 Attempt</p>
          </div>
        </div>

        {/* body */}
        <div className="p-6">
          {/* rating + students */}
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-1.5">
              <div className="flex text-amber-400">
                {Array.from({ length: 5 }).map((_, i) => (
                  <FiStar key={i} size={14} className="fill-amber-400" />
                ))}
              </div>
              <span className="text-xs font-semibold text-navy">4.9</span>
            </div>
            <span className="flex items-center gap-1.5 text-xs text-slate-500">
            </span>
          </div>

          {/* price */}
          <div className="mt-4 flex items-end gap-3">
            <span className="font-display text-3xl font-extrabold text-navy">₨8,000</span>
            <span className="mb-1 text-base text-slate-400 line-through">₨10,000</span>
            <span className="mb-1 rounded-full bg-brand-green/10 px-2.5 py-1 text-xs font-bold text-brand-greenDark">
              Save 20%
            </span>
          </div>

          {/* chips */}
          <div className="mt-4 grid grid-cols-2 gap-3">
            <div className="flex items-center gap-2.5 rounded-2xl bg-lightbg px-3.5 py-3">
              <span className="flex h-9 w-9 items-center justify-center rounded-xl bg-primary/10 text-primary">
                <FiClock size={16} />
              </span>
              <div className="leading-tight">
                <p className="text-[11px] text-slate-400">Duration</p>
                <p className="text-sm font-bold text-navy">12 Weeks</p>
              </div>
            </div>
          </div>

          {/* description */}
          <p className="mt-4 text-sm leading-relaxed text-slate-500">
            Our FCPS 1 preparation course is designed to build strong conceptual understanding and
            exam-focused knowledge.
          </p>

          {/* highlights */}
          <ul className="mt-4 space-y-2">
            {highlights.map((h) => (
              <li key={h} className="flex items-center gap-2.5 text-sm font-medium text-navy/80">
                <span className="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-brand-green/15 text-brand-green">
                  <FiCheck size={12} strokeWidth={3} />
                </span>
                {h}
              </li>
            ))}
          </ul>

          {/* CTA */}
          <a
            href={WHATSAPP_URL}
            target="_blank"
            rel="noopener noreferrer"
            className="btn-primary mt-6 w-full"
          >
            Enroll Now <FiArrowRight />
          </a>
        </div>
      </motion.div>

      {/* floating accent: limited seats */}
      <motion.div
        animate={{ y: [0, -10, 0] }}
        transition={{ duration: 5, repeat: Infinity, ease: 'easeInOut' }}
        className="absolute -right-5 top-32 hidden rounded-2xl border border-slate-100 bg-white p-3.5 shadow-soft-lg sm:flex sm:items-center sm:gap-3"
      >
        <span className="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-green/15 text-brand-green">
          <FiUsers size={18} />
        </span>
        <div className="leading-tight">
          <p className="text-[11px] text-slate-400">Limited Seats</p>
          <p className="font-display text-sm font-bold text-navy">Filling Fast</p>
        </div>
      </motion.div>

    </div>
  )
}
