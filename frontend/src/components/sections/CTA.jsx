import { Link } from 'react-router-dom'
import { motion } from 'framer-motion'
import { FiArrowRight } from 'react-icons/fi'
import { FaWhatsapp } from 'react-icons/fa'
import { whatsappLink } from '../../data/siteData'

const CONTACT_URL = whatsappLink(
  'Hi NextGen Medics! I would like to know more about your FCPS Part 1 courses.'
)

export default function CTA() {
  return (
    <section className="section-py bg-white">
      <div className="container-px mx-auto max-w-[1200px]">
        <motion.div
          initial={{ opacity: 0, scale: 0.96 }}
          whileInView={{ opacity: 1, scale: 1 }}
          viewport={{ once: true, margin: '-80px' }}
          transition={{ duration: 0.55 }}
          className="relative overflow-hidden rounded-[28px] bg-navy px-8 py-16 text-center shadow-soft-lg sm:px-16 sm:py-20"
        >
          <div className="pointer-events-none absolute -left-20 -top-20 h-72 w-72 rounded-full bg-primary/20 blur-3xl" />
          <div className="pointer-events-none absolute -bottom-24 -right-16 h-80 w-80 rounded-full bg-primary-light/15 blur-3xl" />
          <div className="pointer-events-none absolute inset-0 bg-grid-pattern opacity-30" />

          <div className="relative mx-auto max-w-2xl">
            <h2 className="font-display text-3xl font-extrabold leading-tight text-white sm:text-4xl lg:text-[44px]">
              Ready To Clear FCPS Part 1?
            </h2>
            <p className="mx-auto mt-4 max-w-xl text-base text-slate-300 sm:text-lg">
              Join thousands of successful students learning with NextGen Medics and take the next
              step toward becoming a specialist.
            </p>
            <div className="mt-9 flex flex-wrap justify-center gap-4">
              <a href={CONTACT_URL} target="_blank" rel="noopener noreferrer" className="btn-primary">
                <FaWhatsapp size={18} /> Contact Us <FiArrowRight />
              </a>
              <Link to="/courses/fcps-part-1" className="btn-ghost border border-white/20">
                Explore Courses
              </Link>
            </div>
          </div>
        </motion.div>
      </div>
    </section>
  )
}
