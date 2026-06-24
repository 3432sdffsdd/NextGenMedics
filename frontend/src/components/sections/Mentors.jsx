import { useState } from 'react'
import { motion } from 'framer-motion'
import { FiBookOpen, FiArrowRight } from 'react-icons/fi'
import { FaLinkedinIn, FaFacebookF, FaInstagram } from 'react-icons/fa'
import { mentors } from '../../data/siteData'
import SectionHeading from '../common/SectionHeading'
import MentorModal from './MentorModal'

export default function Mentors() {
  const [active, setActive] = useState(null)

  return (
    <section className="section-py bg-white">
      <div className="container-px mx-auto max-w-[1200px]">
        <SectionHeading
          eyebrow="Meet Our Instructors"
          title="Learn From The"
          highlight="Best"
          subtitle="Our mentors are accomplished medical educators committed to your success."
        />

        <div className="mx-auto mt-14 grid max-w-3xl gap-6 md:grid-cols-2">
          {mentors.map((m, i) => (
            <motion.div
              key={m.name}
              initial={{ opacity: 0, y: 30 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true, margin: '-60px' }}
              transition={{ duration: 0.45, delay: i * 0.1 }}
              className="group overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-card transition-all duration-300 hover:-translate-y-1.5 hover:shadow-soft-lg"
            >
              <div className="relative overflow-hidden">
                <span className="absolute left-4 top-4 z-10 rounded-full bg-white/90 px-3 py-1 text-xs font-semibold text-primary-dark backdrop-blur">
                  {m.role}
                </span>
                <img
                  src={m.photo}
                  alt={m.name}
                  className="h-64 w-full object-cover object-top transition-transform duration-500 group-hover:scale-105"
                />
              </div>
              <div className="p-6">
                <h3 className="font-display text-lg font-bold text-navy">{m.name}</h3>
                <p className="mt-1 flex items-center gap-1.5 text-sm text-primary">
                  <FiBookOpen size={14} /> {m.qualification}
                </p>
                <p className="mt-3 text-sm leading-relaxed text-slate-500">{m.experience}</p>
                <p className="mt-3 text-xs font-semibold uppercase tracking-wide text-slate-400">
                  Specialization: <span className="text-navy">{m.specialization}</span>
                </p>

                <div className="mt-5 flex items-center justify-between">
                  <div className="flex gap-2">
                    {[FaLinkedinIn, FaFacebookF, FaInstagram].map((Icon, idx) => (
                      <a
                        key={idx}
                        href="#"
                        className="flex h-8 w-8 items-center justify-center rounded-lg bg-lightbg text-slate-500 transition-colors hover:bg-primary hover:text-white"
                      >
                        <Icon size={13} />
                      </a>
                    ))}
                  </div>
                  <button
                    onClick={() => setActive(m)}
                    className="inline-flex items-center gap-1.5 text-sm font-semibold text-primary hover:gap-2.5 transition-all"
                  >
                    View Profile <FiArrowRight size={14} />
                  </button>
                </div>
              </div>
            </motion.div>
          ))}
        </div>
      </div>

      <MentorModal mentor={active} onClose={() => setActive(null)} />
    </section>
  )
}
