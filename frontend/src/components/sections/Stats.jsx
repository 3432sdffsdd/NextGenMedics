import { motion } from 'framer-motion'
import { FiCheck } from 'react-icons/fi'
import { keyHighlights } from '../../data/siteData'

export default function Stats() {
  return (
    <section className="container-px relative z-10 mx-auto -mt-10 max-w-[1200px] lg:-mt-16">
      <motion.div
        initial={{ opacity: 0, y: 30 }}
        whileInView={{ opacity: 1, y: 0 }}
        viewport={{ once: true }}
        transition={{ duration: 0.5 }}
        className="grid grid-cols-1 gap-3 rounded-3xl border border-slate-100 bg-white p-6 shadow-soft-lg sm:grid-cols-2 lg:grid-cols-5 lg:gap-2 lg:p-8"
      >
        {keyHighlights.map((item, i) => (
          <motion.div
            key={item}
            initial={{ opacity: 0, y: 16 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.4, delay: i * 0.08 }}
            className="group flex items-center gap-3 rounded-2xl px-3 py-2.5 transition-colors hover:bg-lightbg lg:flex-col lg:gap-3 lg:border-r lg:border-slate-100 lg:px-4 lg:py-2 lg:text-center lg:last:border-0"
          >
            <span className="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-brand-green/10 text-brand-green transition-colors duration-300 group-hover:bg-brand-green group-hover:text-white">
              <FiCheck size={20} strokeWidth={3} />
            </span>
            <p className="text-sm font-semibold leading-snug text-navy">{item}</p>
          </motion.div>
        ))}
      </motion.div>
    </section>
  )
}
