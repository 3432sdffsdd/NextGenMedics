import { useEffect, useRef, useState } from 'react'

/**
 * Animate a number from 0 to `end` once it becomes visible.
 * Returns [displayValue, ref] — attach ref to the element to observe.
 */
export default function useCountUp(end = 0, { duration = 1000 } = {}) {
  const [value, setValue] = useState(0)
  const ref = useRef(null)
  const started = useRef(false)

  useEffect(() => {
    const target = Number(end) || 0
    const node = ref.current

    const run = () => {
      if (started.current) return
      started.current = true
      const start = performance.now()
      const tick = (now) => {
        const progress = Math.min((now - start) / duration, 1)
        // easeOutExpo for a snappy finish
        const eased = progress === 1 ? 1 : 1 - Math.pow(2, -10 * progress)
        setValue(Math.round(eased * target))
        if (progress < 1) requestAnimationFrame(tick)
      }
      requestAnimationFrame(tick)
    }

    if (!node || typeof IntersectionObserver === 'undefined') {
      run()
      return
    }

    const observer = new IntersectionObserver(
      (entries) => {
        if (entries.some((e) => e.isIntersecting)) run()
      },
      { threshold: 0.2 }
    )
    observer.observe(node)
    return () => observer.disconnect()
  }, [end, duration])

  return [value, ref]
}
