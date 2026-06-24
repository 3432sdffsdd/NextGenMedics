import { Link } from 'react-router-dom'
import { motion } from 'framer-motion'
import { FiCheck, FiArrowRight, FiPlay } from 'react-icons/fi'
import { heroFeatures } from '../../data/siteData'
import HeroCourseCard from './HeroCourseCard'

const container = {
  hidden: {},
  show: { transition: { staggerChildren: 0.08, delayChildren: 0.1 } },
}
const fadeUp = {
  hidden: { opacity: 0, y: 24 },
  show: { opacity: 1, y: 0, transition: { duration: 0.5 } },
}

export default function Hero() {
  return (
    <section className="relative overflow-hidden bg-gradient-to-b from-lightbg via-white to-white">
      {/* decorative blobs */}
      <div className="pointer-events-none absolute -right-40 -top-40 h-[500px] w-[500px] rounded-full bg-primary/10 blur-3xl" />
      <div className="pointer-events-none absolute -left-32 top-40 h-80 w-80 rounded-full bg-primary-light/10 blur-3xl" />
      <div className="pointer-events-none absolute inset-0 bg-grid-pattern opacity-60" />

      <div className="container-px relative mx-auto grid max-w-[1400px] items-center gap-12 py-16 lg:grid-cols-2 lg:gap-8 lg:py-24">
        <motion.div variants={container} initial="hidden" animate="show">
          <motion.div variants={fadeUp} className="eyebrow mb-6">
            <span className="h-2 w-2 rounded-full bg-primary" />
            Pakistan's Trusted FCPS Preparation Platform
          </motion.div>

          <motion.h1 variants={fadeUp} className="heading-xl text-4xl leading-[1.1] sm:text-5xl lg:text-6xl">
            Master FCPS Part 1 <br />
            With <span className="text-primary">Confidence</span>
          </motion.h1>

          <motion.p variants={fadeUp} className="mt-5 max-w-lg text-base leading-relaxed text-slate-500 sm:text-lg">
            Comprehensive preparation with expert guidance, high-quality resources and smart
            practice to help you achieve your dream of becoming a Specialist.
          </motion.p>

          <motion.div variants={fadeUp} className="mt-6 flex flex-wrap items-center gap-2.5">
            {['High Yield', 'Structured', 'Success'].map((word, i) => (
              <span key={word} className="flex items-center gap-2.5">
                <span className="rounded-full border border-brand-green/25 bg-brand-green/10 px-3.5 py-1.5 text-sm font-bold uppercase tracking-wide text-brand-greenDark">
                  {word}
                </span>
                {i < 2 && <span className="h-1.5 w-1.5 rounded-full bg-slate-300" />}
              </span>
            ))}
          </motion.div>

          <motion.ul variants={fadeUp} className="mt-7 grid max-w-lg grid-cols-1 gap-x-6 gap-y-3 sm:grid-cols-2">
            {heroFeatures.map((f) => (
              <li key={f} className="flex items-center gap-2.5 text-sm font-medium text-navy/80">
                <span className="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-primary/15 text-primary">
                  <FiCheck size={12} strokeWidth={3} />
                </span>
                {f}
              </li>
            ))}
          </motion.ul>

          <motion.div variants={fadeUp} className="mt-9 flex flex-wrap gap-4">
            <Link to="/courses/fcps-part-1" className="btn-primary">
              Explore Courses <FiArrowRight />
            </Link>
            <Link to="/resources/free-videos" className="btn-secondary">
              <FiPlay className="text-primary" /> Free Resources
            </Link>
          </motion.div>

          <motion.div variants={fadeUp} className="mt-8 flex items-center gap-4">
            <div className="flex -space-x-3">
              {[
                'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=80&h=80&fit=crop&crop=faces',
                'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=80&h=80&fit=crop&crop=faces',
                'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?w=80&h=80&fit=crop&crop=faces',
                'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?w=80&h=80&fit=crop&crop=faces',
              ].map((src, i) => (
                <img key={i} src={src} alt="" className="h-9 w-9 rounded-full border-2 border-white object-cover" />
              ))}
            </div>
            <p className="text-sm text-slate-500">
              <span className="font-bold text-navy">Trusted by students</span> across Pakistan
            </p>
          </motion.div>
        </motion.div>

        <motion.div
          initial={{ opacity: 0, scale: 0.94, y: 20 }}
          animate={{ opacity: 1, scale: 1, y: 0 }}
          transition={{ duration: 0.7, delay: 0.2 }}
          className="relative"
        >
          <HeroCourseCard />
        </motion.div>
      </div>
    </section>
  )
}
