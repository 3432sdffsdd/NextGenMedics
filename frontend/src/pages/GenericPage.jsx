import { useParams, Link } from 'react-router-dom'
import { motion } from 'framer-motion'
import { FiArrowRight, FiClock } from 'react-icons/fi'
import SectionHeading from '../components/common/SectionHeading'
import { mentors, testimonials } from '../data/siteData'
import MentorsSection from '../components/sections/Mentors'
import TestimonialsSection from '../components/sections/Testimonials'

function titleize(slug = '') {
  return slug
    .split('-')
    .map((w) => w.charAt(0).toUpperCase() + w.slice(1))
    .join(' ')
    .replace('Fcps', 'FCPS')
    .replace('Usmle', 'USMLE')
    .replace('Mrcp', 'MRCP')
}

export default function GenericPage({ section }) {
  const { slug } = useParams()
  const title = titleize(slug)

  // Render actual content for mentors and testimonials
  if (slug === 'mentors') {
    return <MentorsSection />
  }
  if (slug === 'testimonials') {
    return <TestimonialsSection />
  }

  // Default Coming Soon for other pages
  return (
    <div className="bg-white">
      <section className="bg-lightbg py-16 lg:py-24">
        <div className="container-px mx-auto max-w-[1200px]">
          <SectionHeading eyebrow={section} title={title} subtitle={`Explore everything related to ${title} on NextGen Medics.`} />
        </div>
      </section>

      <section className="section-py">
        <div className="container-px mx-auto max-w-[1000px]">
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.45 }}
            className="flex flex-col items-center rounded-3xl border border-slate-100 bg-white p-12 text-center shadow-card"
          >
            <span className="flex h-16 w-16 items-center justify-center rounded-2xl bg-primary/10 text-primary">
              <FiClock size={28} />
            </span>
            <h3 className="mt-6 font-display text-2xl font-bold text-navy">Coming Soon</h3>
            <div className="mt-7 flex flex-wrap justify-center gap-4">
              <Link to="/login" className="btn-primary">
                Login <FiArrowRight />
              </Link>
              <Link to="/contact" className="btn-secondary">Contact Us</Link>
            </div>
          </motion.div>
        </div>
      </section>
    </div>
  )
}
