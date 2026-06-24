import { useState, useEffect } from 'react'
import { motion, AnimatePresence } from 'framer-motion'
import { FiStar, FiChevronLeft, FiChevronRight } from 'react-icons/fi'
import { FaQuoteRight, FaUserGraduate } from 'react-icons/fa'
import { testimonials } from '../../data/siteData'
import SectionHeading from '../common/SectionHeading'

function Avatar({ gender }) {
  const styles =
    gender === 'female'
      ? 'bg-rose-100 text-rose-500'
      : 'bg-primary/15 text-primary'
  return (
    <span className={`flex h-12 w-12 items-center justify-center rounded-full ${styles}`}>
      <FaUserGraduate size={20} />
    </span>
  )
}

function Stars({ count }) {
  return (
    <div className="flex gap-0.5 text-amber-400">
      {Array.from({ length: count }).map((_, i) => (
        <FiStar key={i} size={16} className="fill-amber-400" />
      ))}
    </div>
  )
}

export default function Testimonials() {
  const [currentSlide, setCurrentSlide] = useState(0)

  // Group testimonials by instructor - Dr Talha first
  const slides = [
    {
      instructor: 'Dr Muhammad Talha Nazeer',
      testimonials: testimonials.filter(t => t.instructor === 'Dr Muhammad Talha Nazeer'),
    },
    {
      instructor: 'Dr Sidrah Shahid',
      testimonials: testimonials.filter(t => t.instructor === 'Dr Sidrah Shahid'),
    },
  ]

  const nextSlide = () => setCurrentSlide((prev) => (prev + 1) % slides.length)
  const prevSlide = () => setCurrentSlide((prev) => (prev - 1 + slides.length) % slides.length)

  // Auto-rotate every 7 seconds
  useEffect(() => {
    const interval = setInterval(() => {
      nextSlide()
    }, 7000)
    return () => clearInterval(interval)
  }, [])

  return (
    <section className="section-py bg-lightbg">
      <div className="container-px mx-auto max-w-[1200px]">
        <SectionHeading
          eyebrow="Student Testimonials"
          title="What Our Students"
          highlight="Say"
          subtitle="Real stories from students who transformed their preparation with NextGen Medics."
        />

        <div className="mt-14">
          <AnimatePresence mode="wait">
            {slides.map((slide, slideIndex) => (
              currentSlide === slideIndex && (
                <motion.div
                  key={slide.instructor}
                  initial={{ opacity: 0, x: 50 }}
                  animate={{ opacity: 1, x: 0 }}
                  exit={{ opacity: 0, x: -50 }}
                  transition={{ duration: 0.4 }}
                  className="relative"
                >
                  <div className="mb-6 text-center">
                    <h3 className="font-display text-xl font-bold text-navy">{slide.instructor}</h3>
                  </div>

                  <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    {slide.testimonials.map((t, i) => (
                      <motion.div
                        key={t.name}
                        initial={{ opacity: 0, y: 20 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ duration: 0.3, delay: i * 0.1 }}
                        className="relative flex flex-col rounded-3xl border border-slate-100 bg-white p-7 shadow-card transition-all duration-300 hover:-translate-y-1.5 hover:shadow-soft-lg"
                      >
                        <FaQuoteRight className="absolute right-7 top-7 text-4xl text-primary/10" />
                        <Stars count={t.rating} />
                        <p className="mt-4 flex-1 text-sm leading-relaxed text-slate-600">"{t.review}"</p>
                        <div className="mt-6 flex items-center gap-3 border-t border-slate-100 pt-5">
                          <Avatar gender={t.gender} />
                          <div>
                            <p className="font-display text-sm font-bold text-navy">{t.name}</p>
                            <p className="text-xs text-slate-500">{t.course}</p>
                          </div>
                        </div>
                      </motion.div>
                    ))}
                  </div>
                </motion.div>
              )
            ))}
          </AnimatePresence>

          {/* Navigation */}
          <div className="mt-10 flex items-center justify-center gap-4">
            <button
              onClick={prevSlide}
              className="flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-navy transition-colors hover:bg-primary hover:text-white"
              aria-label="Previous slide"
            >
              <FiChevronLeft size={20} />
            </button>

            <div className="flex gap-2">
              {slides.map((_, i) => (
                <button
                  key={i}
                  onClick={() => setCurrentSlide(i)}
                  className={`h-2 w-2 rounded-full transition-colors ${
                    currentSlide === i ? 'bg-primary' : 'bg-slate-300'
                  }`}
                  aria-label={`Go to slide ${i + 1}`}
                />
              ))}
            </div>

            <button
              onClick={nextSlide}
              className="flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-navy transition-colors hover:bg-primary hover:text-white"
              aria-label="Next slide"
            >
              <FiChevronRight size={20} />
            </button>
          </div>
        </div>
      </div>
    </section>
  )
}
