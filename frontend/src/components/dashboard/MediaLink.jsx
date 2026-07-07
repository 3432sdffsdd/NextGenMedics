import { FiDownload } from 'react-icons/fi'
import { mediaUrl } from '../../services/api'

export default function MediaLink({ path, type, title, className = '' }) {
  if (!path) return null

  if (path.startsWith('http')) {
    return (
      <a href={path} target="_blank" rel="noreferrer" className={`text-sm text-primary hover:underline ${className}`}>
        {title || 'Open'}
      </a>
    )
  }

  const streamUrl = mediaUrl(path)
  const downloadUrl = mediaUrl(path, { download: true })
  const isVideo = type === 'video'

  if (isVideo) {
    return (
      <div className={`mt-2 space-y-2 ${className}`}>
        <video
          key={streamUrl}
          controls
          preload="metadata"
          playsInline
          className="w-full max-w-2xl rounded-xl bg-black"
          src={streamUrl}
        >
          <track kind="captions" />
          Your browser does not support video playback.
        </video>
        <a
          href={downloadUrl}
          download
          className="inline-flex items-center gap-2 rounded-xl bg-slate-100 px-4 py-2 text-sm font-medium text-navy hover:bg-slate-200"
        >
          <FiDownload size={16} /> Download video
        </a>
      </div>
    )
  }

  return (
    <a
      href={downloadUrl}
      download
      target="_blank"
      rel="noreferrer"
      className={`inline-flex items-center gap-2 text-sm font-medium text-primary hover:underline ${className}`}
    >
      <FiDownload size={14} /> {title || 'Download file'}
    </a>
  )
}
