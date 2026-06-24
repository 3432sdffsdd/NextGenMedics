import { motion } from 'framer-motion'

export default function SectionHeading({ eyebrow, title, highlight, subtitle, center = true }) {
  return (
    <motion.div
      initial={{ opacity: 0, y: 24 }}
      whileInView={{ opacity: 1, y: 0 }}
      viewport={{ once: true, margin: '-80px' }}
      transition={{ duration: 0.5 }}
      className={`max-w-2xl ${center ? 'mx-auto text-center' : ''}`}
    >
      {eyebrow && (
        <p className="mb-3 text-sm font-bold uppercase tracking-[0.15em] text-primary">{eyebrow}</p>
      )}
      <h2 className="heading-xl text-3xl sm:text-4xl lg:text-[42px] lg:leading-[1.15]">
        {title} {highlight && <span className="text-primary">{highlight}</span>}
      </h2>
      {subtitle && <p className="mt-4 text-base text-slate-500 sm:text-lg">{subtitle}</p>}
    </motion.div>
  )
}
