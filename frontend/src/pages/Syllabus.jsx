import { motion } from 'framer-motion'
import { FiDownload, FiChevronRight, FiFileText } from 'react-icons/fi'
import { syllabus } from '../data/siteData'
import SectionHeading from '../components/common/SectionHeading'

export default function Syllabus() {
  return (
    <div className="bg-white">
      {/* header */}
      <section className="py-16 lg:py-20">
        <div className="container-px mx-auto max-w-[1100px]">
          <SectionHeading
            title="FCPS"
            highlight="Syllabus"
            subtitle="Download the latest FCPS Part I & Part II syllabus published by CPSP and build your preparation strategy around the official curriculum."
          />
        </div>
      </section>

      {/* select specialty */}
      <section className="relative overflow-hidden bg-primary py-16 lg:py-20">
        <div className="pointer-events-none absolute -left-20 top-0 h-72 w-72 rounded-full border border-white/10" />
        <div className="pointer-events-none absolute -right-24 bottom-0 h-96 w-96 rounded-full border border-white/10" />
        <div className="pointer-events-none absolute right-20 top-10 h-60 w-60 rounded-full border border-white/10" />

        <div className="container-px relative mx-auto grid max-w-[1100px] items-center gap-10 lg:grid-cols-2">
          <div>
            <h2 className="font-display text-3xl font-extrabold text-white sm:text-4xl">
              Select Your Specialty
            </h2>
            <p className="mt-4 max-w-md text-white/80">
              Download the complete CPSP syllabus and guidelines by selecting your specialty.
            </p>
          </div>

          <div className="grid gap-5 sm:grid-cols-2">
            {syllabus.specialties.map((s, i) => (
              <motion.a
                key={s.name}
                href={s.file}
                target="_blank"
                rel="noopener noreferrer"
                initial={{ opacity: 0, y: 24 }}
                whileInView={{ opacity: 1, y: 0 }}
                viewport={{ once: true }}
                transition={{ duration: 0.4, delay: i * 0.1 }}
                whileHover={{ y: -5 }}
                className={`group flex flex-col items-center rounded-3xl p-7 text-center shadow-soft-lg transition-shadow ${
                  i === 0 ? 'bg-white' : 'bg-amber-200'
                }`}
              >
                <span className="font-display text-3xl font-extrabold text-navy">{s.num}</span>
                <p className="mt-3 text-sm font-semibold text-navy/80">{s.name}</p>
                <span className="mt-4 inline-flex items-center gap-1.5 text-sm font-bold text-primary group-hover:gap-2.5 transition-all">
                  <FiDownload size={15} /> Download <FiChevronRight size={15} />
                </span>
              </motion.a>
            ))}
          </div>
        </div>
      </section>

      {/* assessment structure */}
      <section className="section-py">
        <div className="container-px mx-auto max-w-[1100px]">
          <SectionHeading
            title="FCPS Part I"
            highlight="Assessment Structure"
            subtitle="The FCPS Part I exam consists of two papers. Paper I is common across all specialties, while Paper II is designed according to the candidate's chosen specialty."
          />

          {/* Paper 1 */}
          <div className="mt-14">
            <h3 className="font-display text-2xl font-extrabold text-primary">Paper I</h3>
            <p className="mt-1 text-sm text-slate-500">Common across all specialties</p>
            <div className="mt-6 grid gap-px overflow-hidden rounded-3xl border border-slate-100 bg-slate-100 sm:grid-cols-2 lg:grid-cols-4">
              {syllabus.paper1.map((item, i) => (
                <motion.div
                  key={item.title}
                  initial={{ opacity: 0, y: 20 }}
                  whileInView={{ opacity: 1, y: 0 }}
                  viewport={{ once: true }}
                  transition={{ duration: 0.4, delay: i * 0.08 }}
                  className="bg-white p-6"
                >
                  <h4 className="font-display text-base font-bold text-navy">{item.title}</h4>
                  <p className="mt-3 text-sm leading-relaxed text-slate-500">{item.desc}</p>
                </motion.div>
              ))}
            </div>
          </div>

          {/* Paper 2 */}
          <div className="mt-12">
            <h3 className="font-display text-2xl font-extrabold text-primary">Paper II</h3>
            <p className="mt-1 text-sm text-slate-500">Designed according to the chosen specialty</p>
            <div className="mt-6 grid gap-px overflow-hidden rounded-3xl border border-slate-100 bg-slate-100 sm:grid-cols-2 lg:grid-cols-4">
              {syllabus.paper2.map((item, i) => (
                <motion.div
                  key={item}
                  initial={{ opacity: 0, y: 20 }}
                  whileInView={{ opacity: 1, y: 0 }}
                  viewport={{ once: true }}
                  transition={{ duration: 0.4, delay: i * 0.08 }}
                  className="flex items-start gap-3 bg-white p-6"
                >
                  <span className="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                    <FiFileText size={16} />
                  </span>
                  <p className="text-sm font-medium leading-relaxed text-navy/80">{item}</p>
                </motion.div>
              ))}
            </div>
          </div>
        </div>
      </section>
    </div>
  )
}
