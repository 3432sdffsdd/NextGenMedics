import { useEffect } from 'react'
import { motion, AnimatePresence } from 'framer-motion'
import { FiX, FiBookOpen, FiAward, FiCheckCircle } from 'react-icons/fi'
import { FaQuoteLeft } from 'react-icons/fa'

export default function MentorModal({ mentor, onClose }) {
  useEffect(() => {
    const onKey = (e) => e.key === 'Escape' && onClose()
    if (mentor) {
      document.addEventListener('keydown', onKey)
      document.body.style.overflow = 'hidden'
    }
    return () => {
      document.removeEventListener('keydown', onKey)
      document.body.style.overflow = ''
    }
  }, [mentor, onClose])

  return (
    <AnimatePresence>
      {mentor && (
        <motion.div
          initial={{ opacity: 0 }}
          animate={{ opacity: 1 }}
          exit={{ opacity: 0 }}
          onClick={onClose}
          className="fixed inset-0 z-[100] flex items-end justify-center bg-navy/60 backdrop-blur-sm p-0 sm:items-center sm:p-6"
        >
          <motion.div
            initial={{ y: 60, opacity: 0, scale: 0.98 }}
            animate={{ y: 0, opacity: 1, scale: 1 }}
            exit={{ y: 40, opacity: 0, scale: 0.98 }}
            transition={{ type: 'spring', stiffness: 240, damping: 26 }}
            onClick={(e) => e.stopPropagation()}
            className="relative max-h-[92vh] w-full max-w-3xl overflow-hidden rounded-t-3xl bg-white shadow-soft-lg sm:rounded-3xl"
          >
            {/* close */}
            <button
              onClick={onClose}
              className="absolute right-4 top-4 z-10 flex h-10 w-10 items-center justify-center rounded-xl bg-white/90 text-navy shadow-card transition-colors hover:bg-white hover:text-primary"
              aria-label="Close profile"
            >
              <FiX size={20} />
            </button>

            <div className="max-h-[92vh] overflow-y-auto">
              {/* header */}
              <div className="relative bg-navy px-6 pb-6 pt-8 sm:px-9">
                <div className="pointer-events-none absolute -right-16 -top-16 h-48 w-48 rounded-full bg-primary/20 blur-3xl" />
                <div className="relative flex flex-col items-center gap-5 text-center sm:flex-row sm:text-left">
                  <img
                    src={mentor.photo}
                    alt={mentor.name}
                    className="h-28 w-28 shrink-0 rounded-2xl border-4 border-white/10 object-cover shadow-soft-lg"
                  />
                  <div>
                    <span className="inline-flex items-center gap-1.5 rounded-full bg-primary/20 px-3 py-1 text-[11px] font-bold uppercase tracking-wider text-primary-light">
                      {mentor.role}
                    </span>
                    <h3 className="mt-2 font-display text-2xl font-extrabold text-white">{mentor.name}</h3>
                    <p className="mt-1 flex items-center justify-center gap-1.5 text-sm text-primary-light sm:justify-start">
                      <FiBookOpen size={14} /> {mentor.qualification}
                    </p>
                    <p className="mt-1.5 flex items-center justify-center gap-1.5 text-xs text-slate-300 sm:justify-start">
                      <FiAward size={13} className="text-primary-light" /> Specialization: {mentor.specialization}
                    </p>
                  </div>
                </div>
              </div>

              {/* body */}
              <div className="px-6 py-7 sm:px-9">
                {mentor.bio?.map((para, i) => (
                  <p key={i} className="mb-4 text-[15px] leading-relaxed text-slate-600">
                    {para}
                  </p>
                ))}

                {mentor.tagline && (
                  <div className="mt-6 flex items-start gap-3 rounded-2xl border border-primary/20 bg-primary/5 p-5">
                    <FaQuoteLeft className="mt-0.5 shrink-0 text-xl text-primary" />
                    <p className="font-display text-lg font-bold italic text-navy">{mentor.tagline}</p>
                  </div>
                )}

                <button onClick={onClose} className="btn-primary mt-7 w-full sm:w-auto">
                  <FiCheckCircle /> Got it
                </button>
              </div>
            </div>
          </motion.div>
        </motion.div>
      )}
    </AnimatePresence>
  )
}
