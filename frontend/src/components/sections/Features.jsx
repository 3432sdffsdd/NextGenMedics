import { motion } from 'framer-motion'
import { features } from '../../data/siteData'
import SectionHeading from '../common/SectionHeading'

export default function Features() {
  return (
    <section className="section-py bg-white">
      <div className="container-px mx-auto max-w-[1200px]">
        <SectionHeading
          eyebrow="Why Choose NextGen Medics"
          title="Everything You Need To"
          highlight="Succeed"
          subtitle="A complete ecosystem built to help you learn smarter, practice harder and perform better on exam day."
        />

        <div className="mt-14 grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-3">
          {features.map((f, i) => {
            const Icon = f.icon
            return (
              <motion.div
                key={f.title}
                initial={{ opacity: 0, y: 30 }}
                whileInView={{ opacity: 1, y: 0 }}
                viewport={{ once: true, margin: '-60px' }}
                transition={{ duration: 0.45, delay: (i % 3) * 0.08 }}
                className={`group relative overflow-hidden rounded-3xl border border-slate-100 bg-white p-7 shadow-card transition-all duration-300 hover:-translate-y-1.5 hover:border-primary/30 hover:shadow-soft-lg ${f.span || ''}`}
              >
                <div className="pointer-events-none absolute -right-8 -top-8 h-28 w-28 rounded-full bg-primary/5 transition-transform duration-500 group-hover:scale-150" />
                <span className="relative flex h-14 w-14 items-center justify-center rounded-2xl bg-primary/10 text-primary transition-colors duration-300 group-hover:bg-primary group-hover:text-white">
                  <Icon size={24} />
                </span>
                <h3 className="relative mt-5 font-display text-lg font-bold text-navy">{f.title}</h3>
                <p className="relative mt-2.5 text-sm leading-relaxed text-slate-500">{f.desc}</p>
              </motion.div>
            )
          })}
        </div>
      </div>
    </section>
  )
}
