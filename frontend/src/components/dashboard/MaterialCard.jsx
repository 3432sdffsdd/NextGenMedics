import { useState } from 'react'
import { FiEye, FiDownload, FiEdit2, FiTrash2, FiExternalLink } from 'react-icons/fi'
import { downloadMedia } from '../../services/api'
import { useToast } from '../../context/ToastContext'
import { fileKind, formatBytes, formatDate } from '../../utils/files'

export default function MaterialCard({ resource, canManage = false, allowVideoDownload = true, onEdit, onDelete, onView }) {
  const toast = useToast()
  const [downloading, setDownloading] = useState(false)
  const { label, Icon, tone } = fileKind(resource)
  const isLink = !!resource.external_url && !resource.file_path
  const showDownload = resource.file_path && (label !== 'Video' || allowVideoDownload)

  const handleDownload = async (e) => {
    e.preventDefault()
    if (!resource.file_path || downloading) return
    setDownloading(true)
    try {
      await downloadMedia(resource.file_path, resource.title)
    } catch (err) {
      toast.error(err.message || 'Download failed')
    } finally {
      setDownloading(false)
    }
  }

  return (
    <div className="group flex flex-col rounded-2xl border border-slate-100 bg-white p-4 shadow-soft transition-shadow hover:shadow-md">
      <div className="flex items-start gap-3">
        <div className={`grid h-11 w-11 shrink-0 place-items-center rounded-xl ${tone}`}>
          <Icon size={20} />
        </div>
        <div className="min-w-0 flex-1">
          <p className="truncate font-medium text-navy" title={resource.title}>{resource.title}</p>
          <div className="mt-0.5 flex flex-wrap items-center gap-x-2 gap-y-0.5 text-xs text-slate-400">
            <span className="rounded-full bg-slate-100 px-2 py-0.5 font-medium text-slate-500">{label}</span>
            {resource.file_size ? <span>{formatBytes(resource.file_size)}</span> : null}
            {resource.created_at ? <span>· {formatDate(resource.created_at)}</span> : null}
          </div>
          {resource.uploader_name && <p className="mt-0.5 text-xs text-slate-400">by {resource.uploader_name}</p>}
        </div>
      </div>

      <div className="mt-4 flex flex-wrap items-center gap-2 border-t border-slate-50 pt-3">
        {isLink ? (
          <a href={resource.external_url} target="_blank" rel="noreferrer" className="inline-flex items-center gap-1.5 rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-medium text-navy hover:bg-slate-200">
            <FiExternalLink size={14} /> Open link
          </a>
        ) : (
          <>
            <button type="button" onClick={() => onView?.(resource)} className="inline-flex items-center gap-1.5 rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-medium text-navy hover:bg-slate-200">
              <FiEye size={14} /> View
            </button>
            {showDownload && (
              <button type="button" onClick={handleDownload} disabled={downloading} className="inline-flex items-center gap-1.5 rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-medium text-navy hover:bg-slate-200 disabled:opacity-60">
                <FiDownload size={14} /> {downloading ? 'Downloading…' : 'Download'}
              </button>
            )}
          </>
        )}
        {canManage && (
          <div className="ml-auto flex items-center gap-1">
            <button type="button" onClick={() => onEdit?.(resource)} className="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-primary" aria-label="Edit">
              <FiEdit2 size={15} />
            </button>
            <button type="button" onClick={() => onDelete?.(resource)} className="rounded-lg p-1.5 text-slate-400 hover:bg-red-50 hover:text-red-500" aria-label="Delete">
              <FiTrash2 size={15} />
            </button>
          </div>
        )}
      </div>
    </div>
  )
}
