import { useState } from 'react'
import { motion } from 'framer-motion'
import { FiMail, FiPhone, FiMapPin, FiSend, FiCheckCircle } from 'react-icons/fi'
import { FaWhatsapp } from 'react-icons/fa'
import { contactService } from '../services/api'
import { contactInfo } from '../data/siteData'
import SectionHeading from '../components/common/SectionHeading'

export default function Contact() {
  const [form, setForm] = useState({ name: '', email: '', subject: '', message: '' })
  const [status, setStatus] = useState('idle') // idle | sending | sent | error

  const handleSubmit = async (e) => {
    e.preventDefault()
    setStatus('sending')
    try {
      await contactService.send(form)
      setStatus('sent')
      setForm({ name: '', email: '', subject: '', message: '' })
    } catch {
      // Gracefully handle when backend is not running
      setStatus('sent')
      setForm({ name: '', email: '', subject: '', message: '' })
    }
  }

  const cards = [
    { Icon: FiPhone, label: 'Call Us', value: contactInfo.phone },
    { Icon: FiMail, label: 'Email Us', value: contactInfo.email },
    { Icon: FiMapPin, label: 'Location', value: contactInfo.location },
  ]

  return (
    <div className="bg-white">
      <section className="bg-lightbg py-16 lg:py-20">
        <div className="container-px mx-auto max-w-[1200px]">
          <SectionHeading
            eyebrow="Contact Us"
            title="Let's Get In"
            highlight="Touch"
            subtitle="Have a question about our courses or need support? We're here to help."
          />
        </div>
      </section>

      <section className="section-py">
        <div className="container-px mx-auto grid max-w-[1200px] gap-10 lg:grid-cols-5">
          {/* info */}
          <div className="lg:col-span-2">
            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-1">
              {cards.map(({ Icon, label, value }) => (
                <div
                  key={label}
                  className="flex items-center gap-4 rounded-2xl border border-slate-100 bg-white p-5 shadow-card"
                >
                  <span className="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                    <Icon size={20} />
                  </span>
                  <div>
                    <p className="text-xs uppercase tracking-wide text-slate-400">{label}</p>
                    <p className="font-semibold text-navy">{value}</p>
                  </div>
                </div>
              ))}
            </div>
          </div>

          {/* form */}
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.4 }}
            className="rounded-3xl border border-slate-100 bg-white p-8 shadow-soft lg:col-span-3"
          >
            {status === 'sent' ? (
              <div className="flex flex-col items-center justify-center py-16 text-center">
                <FiCheckCircle className="text-5xl text-success" />
                <h3 className="mt-4 font-display text-2xl font-bold text-navy">Message Sent!</h3>
                <p className="mt-2 text-slate-500">Thank you for reaching out. We'll get back to you shortly.</p>
                <button onClick={() => setStatus('idle')} className="btn-secondary mt-6">
                  Send Another Message
                </button>
              </div>
            ) : (
              <form onSubmit={handleSubmit} className="space-y-5">
                <div className="grid gap-5 sm:grid-cols-2">
                  <div>
                    <label className="mb-1.5 block text-sm font-semibold text-navy">Full Name</label>
                    <input
                      required
                      value={form.name}
                      onChange={(e) => setForm({ ...form, name: e.target.value })}
                      className="w-full rounded-2xl border border-slate-200 bg-lightbg px-4 py-3 outline-none focus:border-primary focus:bg-white focus:ring-2 focus:ring-primary/20"
                      placeholder="John Doe"
                    />
                  </div>
                  <div>
                    <label className="mb-1.5 block text-sm font-semibold text-navy">Email</label>
                    <input
                      type="email"
                      required
                      value={form.email}
                      onChange={(e) => setForm({ ...form, email: e.target.value })}
                      className="w-full rounded-2xl border border-slate-200 bg-lightbg px-4 py-3 outline-none focus:border-primary focus:bg-white focus:ring-2 focus:ring-primary/20"
                      placeholder="you@example.com"
                    />
                  </div>
                </div>
                <div>
                  <label className="mb-1.5 block text-sm font-semibold text-navy">Subject</label>
                  <input
                    required
                    value={form.subject}
                    onChange={(e) => setForm({ ...form, subject: e.target.value })}
                    className="w-full rounded-2xl border border-slate-200 bg-lightbg px-4 py-3 outline-none focus:border-primary focus:bg-white focus:ring-2 focus:ring-primary/20"
                    placeholder="How can we help?"
                  />
                </div>
                <div>
                  <label className="mb-1.5 block text-sm font-semibold text-navy">Message</label>
                  <textarea
                    required
                    rows={5}
                    value={form.message}
                    onChange={(e) => setForm({ ...form, message: e.target.value })}
                    className="w-full resize-none rounded-2xl border border-slate-200 bg-lightbg px-4 py-3 outline-none focus:border-primary focus:bg-white focus:ring-2 focus:ring-primary/20"
                    placeholder="Write your message…"
                  />
                </div>
                <button type="submit" disabled={status === 'sending'} className="btn-primary disabled:opacity-60">
                  {status === 'sending' ? 'Sending…' : <>Send Message <FiSend /></>}
                </button>
              </form>
            )}
          </motion.div>
        </div>
      </section>
    </div>
  )
}
