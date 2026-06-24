import { useState } from 'react'
import { motion, AnimatePresence } from 'framer-motion'
import {
  FiCheckCircle, FiClock, FiBarChart2, FiAward, FiArrowRight, FiBookOpen, FiCalendar,
} from 'react-icons/fi'
import { FaWhatsapp, FaQuoteLeft } from 'react-icons/fa'
import { featuredCourse, courseDetail, mentors, whatsappLink } from '../data/siteData'

const TABS = ['Description', 'Curriculum', 'Instructor']

const ENROLL_URL = whatsappLink(
  "Hi NextGen Medics! I'm interested in enrolling in the FCPS 1 Preparation Course (October 2026 attempt). Please share the details."
)

function DescriptionTab() {
  return (
    <div>
      <div className="rounded-3xl border border-slate-100 bg-lightbg p-7 sm:p-8">
        <h3 className="font-display text-2xl font-extrabold text-navy">What You'll Get</h3>
        <div className="mt-6 grid gap-x-8 gap-y-4 sm:grid-cols-2">
          {courseDetail.whatYouGet.map((item) => (
            <div key={item} className="flex items-start gap-3">
              <span className="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary text-white">
                <FiCheckCircle size={14} />
              </span>
              <span className="text-[15px] font-medium uppercase tracking-wide text-navy/80">{item}</span>
            </div>
          ))}
        </div>
      </div>

      <div className="mt-8 space-y-4">
        {courseDetail.description.map((para, i) => (
          <p key={i} className="text-[15px] leading-relaxed text-slate-600 sm:text-base">
            {para}
          </p>
        ))}
      </div>
    </div>
  )
}

function CurriculumTab() {
  return (
    <div className="flex flex-col items-center justify-center rounded-3xl border border-dashed border-slate-200 bg-lightbg py-20 text-center">
      <span className="flex h-16 w-16 items-center justify-center rounded-2xl bg-primary/10 text-primary">
        <FiCalendar size={28} />
      </span>
      <h3 className="mt-5 font-display text-xl font-bold text-navy">Coming Soon</h3>
    </div>
  )
}

function InstructorTab() {
  return (
    <div className="space-y-8">
      {mentors.map((m) => (
        <div
          key={m.name}
          className="overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-card"
        >
          <div className="grid gap-0 md:grid-cols-[120px_1fr]">
            <div className="relative bg-navy">
              <img src={m.photo} alt={m.name} className="h-40 w-full object-cover object-top md:h-[160px]" />
            </div>
            <div className="p-7 sm:p-8">
              <span className="inline-flex items-center gap-1.5 rounded-full bg-primary/10 px-3 py-1 text-[11px] font-bold uppercase tracking-wider text-primary-dark">
                {m.role}
              </span>
              <h3 className="mt-3 font-display text-2xl font-extrabold text-navy">{m.name}</h3>
              <p className="mt-1 flex items-center gap-1.5 text-sm font-medium text-primary">
                <FiBookOpen size={14} /> {m.qualification}
              </p>
              <p className="mt-1.5 flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-slate-400">
                <FiAward size={13} className="text-primary" /> Specialization: {m.specialization}
              </p>

              <div className="mt-5 space-y-3.5">
                {m.bio?.map((para, i) => (
                  <p key={i} className="text-sm leading-relaxed text-slate-600">{para}</p>
                ))}
              </div>

              {m.tagline && (
                <div className="mt-5 flex items-start gap-3 rounded-2xl border border-primary/20 bg-primary/5 p-4">
                  <FaQuoteLeft className="mt-0.5 shrink-0 text-lg text-primary" />
                  <p className="font-display text-base font-bold italic text-navy">{m.tagline}</p>
                </div>
              )}
            </div>
          </div>
        </div>
      ))}
    </div>
  )
}

export default function CourseDetail() {
  const [tab, setTab] = useState('Description')

  return (
    <div className="bg-white">
      {/* header */}
      <section className="relative overflow-hidden bg-navy">
        <div className="pointer-events-none absolute -right-24 -top-24 h-72 w-72 rounded-full bg-primary/20 blur-3xl" />
        <div className="pointer-events-none absolute inset-0 bg-grid-pattern opacity-30" />
        <div className="container-px relative mx-auto max-w-[1100px] py-14 lg:py-20">
          <span className="inline-flex items-center gap-2 rounded-full bg-primary/20 px-4 py-1.5 text-xs font-bold uppercase tracking-wider text-primary-light">
            <FiAward size={12} /> {featuredCourse.badge}
          </span>
          <h1 className="mt-4 font-display text-3xl font-extrabold leading-tight text-white sm:text-4xl lg:text-5xl">
            FCPS 1 Preparation Course
          </h1>
          <p className="mt-2 text-lg text-primary-light">For October 2026 Attempt</p>

          <div className="mt-6 flex flex-wrap items-center gap-5">
            <div className="flex items-baseline gap-2">
              <span className="font-display text-3xl font-extrabold text-white">{featuredCourse.price}</span>
              <span className="text-base text-slate-400 line-through">{featuredCourse.originalPrice}</span>
            </div>
            <span className="rounded-full bg-brand-green/20 px-3 py-1 text-xs font-bold text-brand-greenLight">Save 20%</span>
            <span className="inline-flex items-center gap-2 text-sm text-slate-300">
              <FiClock className="text-primary-light" /> {featuredCourse.duration}
            </span>
          </div>

          <a href={ENROLL_URL} target="_blank" rel="noopener noreferrer" className="btn-primary mt-8">
            <FaWhatsapp size={18} /> Enroll Now <FiArrowRight />
          </a>
        </div>
      </section>

      {/* tabs */}
      <section className="section-py">
        <div className="container-px mx-auto max-w-[1100px]">
          <div className="flex gap-2 border-b border-slate-200">
            {TABS.map((t) => (
              <button
                key={t}
                onClick={() => setTab(t)}
                className={`relative px-5 py-3 text-sm font-semibold transition-colors sm:text-base ${
                  tab === t ? 'text-primary' : 'text-slate-500 hover:text-navy'
                }`}
              >
                {t}
                {tab === t && (
                  <motion.span
                    layoutId="courseTabUnderline"
                    className="absolute inset-x-0 -bottom-px h-0.5 rounded-full bg-primary"
                  />
                )}
              </button>
            ))}
          </div>

          <div className="mt-8">
            <AnimatePresence mode="wait">
              <motion.div
                key={tab}
                initial={{ opacity: 0, y: 12 }}
                animate={{ opacity: 1, y: 0 }}
                exit={{ opacity: 0, y: -8 }}
                transition={{ duration: 0.25 }}
              >
                {tab === 'Description' && <DescriptionTab />}
                {tab === 'Curriculum' && <CurriculumTab />}
                {tab === 'Instructor' && <InstructorTab />}
              </motion.div>
            </AnimatePresence>
          </div>
        </div>
      </section>
    </div>
  )
}
