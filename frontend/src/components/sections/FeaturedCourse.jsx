import { Link } from 'react-router-dom'
import { motion } from 'framer-motion'
import { FiCheck, FiClock, FiLayers, FiArrowRight } from 'react-icons/fi'
import { featuredCourse as c } from '../../data/siteData'

export default function FeaturedCourse() {
  return (
    <section className="section-py bg-lightbg">
      <div className="container-px mx-auto max-w-[1200px]">
        <motion.div
          initial={{ opacity: 0, y: 40 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true, margin: '-80px' }}
          transition={{ duration: 0.55 }}
          className="relative overflow-hidden rounded-[28px] bg-navy p-8 shadow-soft-lg sm:p-12 lg:p-14"
        >
          {/* decorative */}
          <div className="pointer-events-none absolute -right-24 -top-24 h-72 w-72 rounded-full bg-primary/20 blur-3xl" />
          <div className="pointer-events-none absolute inset-0 bg-grid-pattern opacity-30" />

          <div className="relative grid items-center gap-10 lg:grid-cols-2">
            <div>
              <span className="inline-flex items-center gap-2 rounded-full bg-primary/20 px-4 py-1.5 text-xs font-bold uppercase tracking-wider text-primary-light">
                {c.badge}
              </span>
              <h2 className="mt-5 font-display text-3xl font-extrabold leading-tight text-white sm:text-4xl">
                {c.title}
              </h2>
              <p className="mt-2 text-primary-light">{c.subtitle}</p>

              <div className="mt-6 flex flex-wrap items-center gap-5">
                <div className="flex items-baseline gap-2">
                  <span className="font-display text-3xl font-extrabold text-white">{c.price}</span>
                  <span className="text-base text-slate-400 line-through">{c.originalPrice}</span>
                </div>
                <span className="rounded-full bg-success/20 px-3 py-1 text-xs font-bold text-success">20% OFF</span>
              </div>

              <div className="mt-5 flex flex-wrap gap-4 text-sm text-slate-300">
                <span className="inline-flex items-center gap-2">
                  <FiClock className="text-primary-light" /> {c.duration}
                </span>
              </div>

              <Link to="/courses/fcps-part-1" className="btn-primary mt-8">
                View Course Details <FiArrowRight />
              </Link>
            </div>

            <div className="rounded-3xl border border-white/10 bg-white/5 p-7 backdrop-blur-sm">
              <p className="mb-4 font-display text-sm font-bold uppercase tracking-wider text-white">
                Course Highlights
              </p>
              <ul className="space-y-3.5">
                {c.highlights.map((h) => (
                  <li key={h} className="flex items-center gap-3 text-slate-200">
                    <span className="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary text-white">
                      <FiCheck size={13} strokeWidth={3} />
                    </span>
                    <span className="text-sm">{h}</span>
                  </li>
                ))}
              </ul>
            </div>
          </div>
        </motion.div>
      </div>
    </section>
  )
}
