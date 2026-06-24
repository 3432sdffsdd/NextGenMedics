import { motion } from 'framer-motion'
import { FaWhatsapp } from 'react-icons/fa'
import { whatsappLink } from '../../data/siteData'

export default function FloatingWhatsApp() {
  return (
    <motion.a
      href={whatsappLink('Hi NextGen Medics! I have a question.')}
      target="_blank"
      rel="noopener noreferrer"
      initial={{ scale: 0, opacity: 0 }}
      animate={{ scale: 1, opacity: 1 }}
      transition={{ type: 'spring', stiffness: 200, damping: 18, delay: 0.5 }}
      whileHover={{ scale: 1.1 }}
      whileTap={{ scale: 0.95 }}
      className="fixed bottom-6 left-6 z-50 flex h-12 w-12 items-center justify-center rounded-full bg-[#25D366] text-white shadow-soft-lg hover:shadow-glow sm:bottom-8 sm:left-8 sm:h-14 sm:w-14"
      aria-label="Chat on WhatsApp"
    >
      <FaWhatsapp size={24} className="sm:size-28" />
    </motion.a>
  )
}
