import { Link } from 'react-router-dom'
import { FiMail, FiPhone, FiMapPin } from 'react-icons/fi'
import { FaWhatsapp, FaFacebookF, FaInstagram, FaYoutube, FaLinkedinIn } from 'react-icons/fa'
import Logo from '../common/Logo'
import { footerLinks, contactInfo } from '../../data/siteData'

const socials = [
  { Icon: FaFacebookF, href: '#', label: 'Facebook' },
  { Icon: FaInstagram, href: '#', label: 'Instagram' },
  { Icon: FaYoutube, href: '#', label: 'YouTube' },
  { Icon: FaLinkedinIn, href: '#', label: 'LinkedIn' },
  { Icon: FaWhatsapp, href: '#', label: 'WhatsApp' },
]

function LinkColumn({ title, links }) {
  return (
    <div>
      <h4 className="mb-4 font-display text-sm font-bold uppercase tracking-wider text-white">{title}</h4>
      <ul className="space-y-2.5">
        {links.map((l) => (
          <li key={l.label}>
            <Link to={l.to} className="text-sm text-slate-400 transition-colors hover:text-primary-light">
              {l.label}
            </Link>
          </li>
        ))}
      </ul>
    </div>
  )
}

export default function Footer() {
  return (
    <footer className="bg-navy text-white">
      <div className="container-px mx-auto max-w-[1400px] py-16 lg:py-20">
        <div className="grid gap-12 lg:grid-cols-12">
          <div className="lg:col-span-4">
            <Logo light withTagline />
            <p className="mt-5 max-w-xs text-sm leading-relaxed text-slate-400">
              Pakistan's trusted FCPS Part 1 preparation platform. Learn smart, practice harder
              and become the specialist you aspire to be.
            </p>
            <div className="mt-6 flex gap-3">
              {socials.map(({ Icon, href, label }) => (
                <a
                  key={label}
                  href={href}
                  aria-label={label}
                  className="flex h-10 w-10 items-center justify-center rounded-xl bg-white/5 text-slate-300 transition-all hover:bg-primary hover:text-white"
                >
                  <Icon size={16} />
                </a>
              ))}
            </div>
          </div>

          <div className="grid grid-cols-2 gap-8 sm:grid-cols-3 lg:col-span-5">
            <LinkColumn title="Quick Links" links={footerLinks.quickLinks} />
            <LinkColumn title="Resources" links={footerLinks.resources} />
            <LinkColumn title="Support" links={footerLinks.support} />
          </div>

          <div className="lg:col-span-3">
            <h4 className="mb-4 font-display text-sm font-bold uppercase tracking-wider text-white">Contact Us</h4>
            <ul className="space-y-3.5 text-sm text-slate-400">
              <li className="flex items-center gap-3">
                <FiPhone className="text-primary" /> {contactInfo.phone}
              </li>
              <li className="flex items-center gap-3">
                <FaWhatsapp className="text-primary" /> {contactInfo.whatsapp}
              </li>
              <li className="flex items-center gap-3">
                <FiMail className="text-primary" /> {contactInfo.email}
              </li>
              <li className="flex items-center gap-3">
                <FiMapPin className="text-primary" /> {contactInfo.location}
              </li>
            </ul>
          </div>
        </div>
      </div>

      <div className="border-t border-white/10">
        <div className="container-px mx-auto flex max-w-[1400px] flex-col items-center justify-between gap-3 py-6 text-sm text-slate-500 sm:flex-row">
          <p>© {new Date().getFullYear()} NextGen Medics. All rights reserved.</p>
          <p>Designed for future specialists in Pakistan.</p>
        </div>
      </div>
    </footer>
  )
}
