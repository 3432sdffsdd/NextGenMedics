import { videoTrackingService } from '../services/api'

function detectClient() {
  const ua = navigator.userAgent || ''
  let browser = 'Unknown'
  if (ua.includes('Edg/')) browser = 'Edge'
  else if (ua.includes('Chrome/')) browser = 'Chrome'
  else if (ua.includes('Firefox/')) browser = 'Firefox'
  else if (ua.includes('Safari/') && !ua.includes('Chrome')) browser = 'Safari'
  const os = /Windows/i.test(ua) ? 'Windows' : /Mac/i.test(ua) ? 'macOS' : /Android/i.test(ua) ? 'Android' : /iPhone|iPad/i.test(ua) ? 'iOS' : 'Other'
  const device_type = /Mobi|Android/i.test(ua) ? 'mobile' : /Tablet|iPad/i.test(ua) ? 'tablet' : 'desktop'
  return { browser, os, device_type }
}

const client = detectClient()

export function sendVideoTrack(payload) {
  return videoTrackingService.track({ ...payload, client })
    .then((res) => res.data?.data)
    .catch(() => null)
}

export function sendVideoTrackBeacon(payload) {
  try {
    const token = localStorage.getItem('ngm_token')
    const base = import.meta.env.VITE_API_URL || '/api'
    const url = `${base.replace(/\/$/, '')}/student/video-tracking/track`
    fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        ...(token ? { Authorization: `Bearer ${token}` } : {}),
      },
      body: JSON.stringify({ ...payload, client }),
      keepalive: true,
      credentials: 'same-origin',
    }).catch(() => {})
  } catch { /* ignore */ }
}

export function formatWatchTime(seconds) {
  const s = Math.max(0, Math.floor(Number(seconds) || 0))
  const m = Math.floor(s / 60)
  const r = s % 60
  const h = Math.floor(m / 60)
  const mm = m % 60
  if (h > 0) return `${h}:${String(mm).padStart(2, '0')}:${String(r).padStart(2, '0')}`
  return `${mm}:${String(r).padStart(2, '0')}`
}
