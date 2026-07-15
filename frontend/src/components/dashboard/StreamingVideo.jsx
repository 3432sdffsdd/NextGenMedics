import { useCallback, useEffect, useRef, useState } from 'react'
import { mediaEndpoint, mediaUrl } from '../../services/api'

const FULL_LOAD_MAX = 200 * 1024 * 1024 // 200 MB — blob load enables reliable seek

const PLAYBACK_SPEEDS = [0.75, 1, 1.25, 1.5, 1.75, 2]

const speedLabel = (rate) => (rate === 1 ? '1× (Normal)' : `${rate}×`)

function Loading({ message, progress }) {
  return (
    <div className="flex flex-col items-center justify-center py-16">
      <span className="inline-block h-8 w-8 animate-spin rounded-full border-2 border-primary border-t-transparent" />
      <span className="mt-3 text-sm text-slate-500">{message}</span>
      {progress != null && progress > 0 && (
        <div className="mt-3 w-full max-w-xs">
          <div className="h-1.5 overflow-hidden rounded-full bg-slate-200">
            <div className="h-full bg-primary transition-all" style={{ width: `${progress}%` }} />
          </div>
          <p className="mt-1 text-center text-xs text-slate-400">{progress}%</p>
        </div>
      )}
    </div>
  )
}

/** MP4 player with byte-range streaming; optional full download for reliable seeking. */
export default function StreamingVideo({ filePath, fileSize = 0, title, allowVideoDownload = true }) {
  const videoRef = useRef(null)
  const blobUrlRef = useRef('')
  const [src, setSrc] = useState(null)
  const [status, setStatus] = useState('loading') // loading | ready | error
  const [bufferHint, setBufferHint] = useState('')
  const [seekLimited, setSeekLimited] = useState(false)
  const [fullLoading, setFullLoading] = useState(false)
  const [fullProgress, setFullProgress] = useState(0)
  const [error, setError] = useState('')
  const [playbackRate, setPlaybackRate] = useState(1)

  const directUrl = mediaUrl(filePath)
  const canFullLoad = fileSize > 0 && fileSize <= FULL_LOAD_MAX

  useEffect(() => {
    setSrc(directUrl)
    setStatus('ready')
    setError('')
    setSeekLimited(false)
    setBufferHint('')
    setPlaybackRate(1)
  }, [directUrl, filePath])

  useEffect(() => {
    const v = videoRef.current
    if (v) v.playbackRate = playbackRate
  }, [playbackRate, src])

  useEffect(() => () => {
    if (blobUrlRef.current) {
      URL.revokeObjectURL(blobUrlRef.current)
      blobUrlRef.current = ''
    }
  }, [])

  const loadFullVideo = useCallback(async () => {
    if (!filePath || fullLoading) return
    setFullLoading(true)
    setFullProgress(0)
    setError('')
    try {
      const url = mediaEndpoint(filePath)
      const token = localStorage.getItem('ngm_token')
      const res = await fetch(url, {
        credentials: 'same-origin',
        headers: token ? { Authorization: `Bearer ${token}` } : {},
      })
      if (!res.ok) throw new Error('Could not load video')

      const total = Number(res.headers.get('Content-Length')) || fileSize || 0
      const reader = res.body?.getReader()
      const chunks = []
      let received = 0

      if (reader) {
        while (true) {
          const { done, value } = await reader.read()
          if (done) break
          chunks.push(value)
          received += value.length
          if (total > 0) setFullProgress(Math.min(99, Math.round((received / total) * 100)))
        }
      } else {
        chunks.push(new Uint8Array(await res.arrayBuffer()))
      }

      const mime = (res.headers.get('Content-Type') || 'video/mp4').split(';')[0].trim()
      const blob = new Blob(chunks, { type: mime })
      if (blobUrlRef.current) URL.revokeObjectURL(blobUrlRef.current)
      blobUrlRef.current = URL.createObjectURL(blob)
      const v = videoRef.current
      const t = v?.currentTime || 0
      setSrc(blobUrlRef.current)
      setSeekLimited(false)
      setBufferHint('')
      requestAnimationFrame(() => {
        if (v) {
          v.currentTime = t
          v.playbackRate = playbackRate
          v.play().catch(() => {})
        }
      })
    } catch (e) {
      setError(e.message || 'Could not load full video')
    } finally {
      setFullLoading(false)
      setFullProgress(100)
    }
  }, [filePath, fileSize, fullLoading, playbackRate])

  const onLoadedMetadata = (e) => {
    const v = e.target
    v.playbackRate = playbackRate
    if (!v.duration || !Number.isFinite(v.duration)) return
    if (v.seekable.length > 0) {
      const end = v.seekable.end(v.seekable.length - 1)
      if (end < v.duration - 10) {
        setSeekLimited(true)
      }
    }
  }

  if (status === 'loading' && !src) {
    return <Loading message="Preparing video…" />
  }

  if (error && !src) {
    return (
      <div className="rounded-xl border border-red-100 bg-red-50 px-4 py-6 text-sm text-red-700">
        {error}
      </div>
    )
  }

  return (
    <div>
      {fullLoading && <Loading message="Loading full video for skipping…" progress={fullProgress} />}

      {!fullLoading && (
        <>
          <video
            ref={videoRef}
            key={src}
            controls
            playsInline
            preload="auto"
            className="w-full rounded-xl bg-black"
            src={src}
            onLoadedMetadata={onLoadedMetadata}
            onRateChange={(e) => {
              const rate = e.currentTarget.playbackRate
              if (PLAYBACK_SPEEDS.includes(rate) && rate !== playbackRate) setPlaybackRate(rate)
            }}
            onWaiting={() => setBufferHint('Buffering…')}
            onSeeking={() => setBufferHint('Loading…')}
            onCanPlay={() => setBufferHint('')}
            onSeeked={() => setBufferHint('')}
            onError={() => setError('Video could not be played. Try “Enable skipping” below or log in again.')}
            {...(!allowVideoDownload
              ? { controlsList: 'nodownload noplaybackrate', disablePictureInPicture: true }
              : { controlsList: 'noplaybackrate' })}
          >
            <track kind="captions" />
          </video>

          <div className="mt-3 flex flex-wrap items-center gap-2">
            <span className="text-xs font-medium text-slate-500">Speed</span>
            <div className="flex flex-wrap gap-1.5">
              {PLAYBACK_SPEEDS.map((rate) => (
                <button
                  key={rate}
                  type="button"
                  onClick={() => setPlaybackRate(rate)}
                  className={`rounded-lg px-2.5 py-1 text-xs font-semibold transition ${
                    playbackRate === rate
                      ? 'bg-primary text-white shadow-sm'
                      : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
                  }`}
                  aria-pressed={playbackRate === rate}
                >
                  {speedLabel(rate)}
                </button>
              ))}
            </div>
          </div>

          {bufferHint && (
            <p className="mt-2 text-xs text-slate-400">{bufferHint}</p>
          )}

          {seekLimited && canFullLoad && !blobUrlRef.current && (
            <div className="mt-3 rounded-xl border border-amber-100 bg-amber-50 px-4 py-3 text-sm text-amber-900">
              <p className="font-medium">Skipping ahead may not work until the video is fully loaded.</p>
              <button type="button" onClick={loadFullVideo} className="btn-primary mt-2 text-xs">
                Enable skipping (load full video)
              </button>
            </div>
          )}

          {seekLimited && !canFullLoad && (
            <p className="mt-2 text-xs text-amber-700">
              This video file is not web-optimized. Ask your teacher to re-upload the MP4 (export with &quot;fast start&quot; / web streaming enabled).
            </p>
          )}

          {!allowVideoDownload && (
            <p className="mt-2 text-xs text-slate-400">View only — download is disabled for students.</p>
          )}
        </>
      )}

      {error && src && (
        <p className="mt-2 text-xs text-red-600">{error}</p>
      )}
    </div>
  )
}
