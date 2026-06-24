import { useState } from 'react'
import { motion, AnimatePresence } from 'framer-motion'
import { FiChevronDown, FiMail, FiPhone } from 'react-icons/fi'
import { FaWhatsapp } from 'react-icons/fa'
import { faqs, contactInfo, whatsappLink } from '../data/siteData'
import SectionHeading from '../components/common/SectionHeading'

export default function HelpCenter() {
  const [openIndex, setOpenIndex] = useState(null)

  const toggle = (i) => setOpenIndex(openIndex === i ? null : i)

  return (
    <div className="bg-white">
      {/* header */}
      <section className="py-16 lg:py-20">
        <div className="container-px mx-auto max-w-[1100px]">
          <SectionHeading
            title="Help"
            highlight="Center"
            subtitle="Find answers to common questions about our courses, enrollment, and platform features."
          />
        </div>
      </section>

      {/* faqs */}
      <section className="section-py">
        <div className="container-px mx-auto max-w-[800px]">
          <div className="space-y-4">
            {faqs.map((faq, i) => (
              <motion.div
                key={faq.question}
                initial={{ opacity: 0, y: 16 }}
                whileInView={{ opacity: 1, y: 0 }}
                viewport={{ once: true }}
                transition={{ duration: 0.3, delay: i * 0.05 }}
                className="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-card"
              >
                <button
                  onClick={() => toggle(i)}
                  className="flex w-full items-center justify-between gap-4 px-6 py-5 text-left transition-colors hover:bg-lightbg"
                >
                  <span className="font-display text-base font-bold text-navy sm:text-lg">{faq.question}</span>
                  <motion.span
                    animate={{ rotate: openIndex === i ? 180 : 0 }}
                    transition={{ duration: 0.2 }}
                    className="shrink-0 text-primary"
                  >
                    <FiChevronDown size={20} />
                  </motion.span>
                </button>
                <AnimatePresence>
                  {openIndex === i && (
                    <motion.div
                      initial={{ height: 0, opacity: 0 }}
                      animate={{ height: 'auto', opacity: 1 }}
                      exit={{ height: 0, opacity: 0 }}
                      transition={{ duration: 0.25 }}
                      className="border-t border-slate-100 bg-lightbg px-6 py-5"
                    >
                      <p className="text-sm leading-relaxed text-slate-600 sm:text-base">{faq.answer}</p>
                    </motion.div>
                  )}
                </AnimatePresence>
              </motion.div>
            ))}
          </div>
        </div>
      </section>

      {/* contact */}
      <section className="section-py bg-navy">
        <div className="container-px mx-auto max-w-[1100px]">
          <div className="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
            <div className="rounded-3xl border border-white/10 bg-white/5 p-6 backdrop-blur">
              <span className="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/20 text-primary-light">
                <FiMail size={22} />
              </span>
              <h3 className="mt-4 font-display text-lg font-bold text-white">Email Us</h3>
              <p className="mt-2 text-sm text-slate-300">Send us your queries anytime</p>
              <a
                href={`mailto:${contactInfo.email}`}
                className="mt-4 inline-block text-sm font-semibold text-primary-light hover:underline"
              >
                {contactInfo.email}
              </a>
            </div>

            <div className="rounded-3xl border border-white/10 bg-white/5 p-6 backdrop-blur">
              <span className="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/20 text-primary-light">
                <FiPhone size={22} />
              </span>
              <h3 className="mt-4 font-display text-lg font-bold text-white">Call Us</h3>
              <p className="mt-2 text-sm text-slate-300">Mon-Sat, 9am - 6pm</p>
              <a
                href={`tel:${contactInfo.phone.replace(/\s/g, '')}`}
                className="mt-4 inline-block text-sm font-semibold text-primary-light hover:underline"
              >
                {contactInfo.phone}
              </a>
            </div>

            <div className="rounded-3xl border border-white/10 bg-white/5 p-6 backdrop-blur">
              <span className="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/20 text-primary-light">
                <FaWhatsapp size={22} />
              </span>
              <h3 className="mt-4 font-display text-lg font-bold text-white">WhatsApp</h3>
              <p className="mt-2 text-sm text-slate-300">Quick responses via chat</p>
              <a
                href={whatsappLink('Hi NextGen Medics! I have a question.')}
                target="_blank"
                rel="noopener noreferrer"
                className="mt-4 inline-block text-sm font-semibold text-primary-light hover:underline"
              >
                {contactInfo.whatsapp}
              </a>
            </div>
          </div>
        </div>
      </section>
    </div>
  )
}
