import Hero from '../components/sections/Hero'
import Stats from '../components/sections/Stats'
import Features from '../components/sections/Features'
import FeaturedCourse from '../components/sections/FeaturedCourse'
import Mentors from '../components/sections/Mentors'
import Testimonials from '../components/sections/Testimonials'
import CTA from '../components/sections/CTA'

export default function Home() {
  return (
    <>
      <Hero />
      <Stats />
      <Features />
      <FeaturedCourse />
      <Mentors />
      <Testimonials />
      <CTA />
    </>
  )
}
