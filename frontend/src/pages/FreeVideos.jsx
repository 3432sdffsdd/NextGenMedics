import { motion } from 'framer-motion'
import { FiPlayCircle, FiExternalLink } from 'react-icons/fi'
import SectionHeading from '../components/common/SectionHeading'

const videos = [
  {
    title: 'Recording Link of Pathology Demo Class',
    url: 'https://drive.google.com/file/d/17Srf_U1uQPZod5knA30vo6UZ-_HRKY6V/view?usp=drive_link',
    platform: 'Google Drive',
  },
  {
    title: 'Course Introduction',
    url: 'https://www.loom.com/share/90b70c52bae5444881c995d040354b61',
    platform: 'Loom',
  },
]

export default function FreeVideos() {
  return (
    <div className="bg-white">
      <section className="bg-lightbg py-16 lg:py-20">
        <div className="container-px mx-auto max-w-[1200px]">
          <SectionHeading
            eyebrow="Resources"
            title="Free"
            highlight="Videos"
            subtitle="Watch sample lectures and tutorials to get a glimpse of our teaching style."
          />
        </div>
      </section>

      <section className="section-py">
        <div className="container-px mx-auto max-w-[1000px]">
          <div className="grid gap-6 sm:grid-cols-2">
            {videos.map((video, i) => (
              <motion.a
                key={video.title}
                href={video.url}
                target="_blank"
                rel="noopener noreferrer"
                initial={{ opacity: 0, y: 20 }}
                whileInView={{ opacity: 1, y: 0 }}
                viewport={{ once: true }}
                transition={{ duration: 0.3, delay: i * 0.1 }}
                whileHover={{ y: -4 }}
                className="group flex flex-col overflow-hidden rounded-3xl border border-slate-100 bg-white p-6 shadow-card transition-shadow hover:shadow-soft-lg"
              >
                <div className="flex h-40 items-center justify-center rounded-2xl bg-navy/5">
                  <FiPlayCircle size={48} className="text-primary group-hover:scale-110 transition-transform" />
                </div>
                <div className="mt-4 flex-1">
                  <h3 className="font-display text-lg font-bold text-navy group-hover:text-primary transition-colors">
                    {video.title}
                  </h3>
                  <p className="mt-1 text-sm text-slate-400">{video.platform}</p>
                </div>
                <div className="mt-4 flex items-center gap-2 text-sm font-semibold text-primary">
                  Watch Now <FiExternalLink size={14} />
                </div>
              </motion.a>
            ))}
          </div>
        </div>
      </section>
    </div>
  )
}
