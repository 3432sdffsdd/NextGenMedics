import {
  FiFileText, FiFilm, FiImage, FiFile, FiArchive, FiLink, FiPaperclip,
} from 'react-icons/fi'

export function formatBytes(bytes) {
  if (!bytes || bytes <= 0) return ''
  const units = ['B', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(1024))
  return `${(bytes / Math.pow(1024, i)).toFixed(i === 0 ? 0 : 1)} ${units[i]}`
}

export function extOf(nameOrPath = '') {
  const m = String(nameOrPath).toLowerCase().match(/\.([a-z0-9]+)(?:\?|#|$)/)
  return m ? m[1] : ''
}

/** Human label + icon + color tone for a resource based on type/extension. */
export function fileKind(resource = {}) {
  const ext = extOf(resource.file_path || resource.original_filename || resource.title || '')
  const type = resource.type
  if (type === 'video' || ['mp4', 'webm', 'mov'].includes(ext)) return { label: 'Video', Icon: FiFilm, tone: 'text-rose-500 bg-rose-50' }
  if (ext === 'pdf' || type === 'pdf') return { label: 'PDF', Icon: FiFileText, tone: 'text-red-500 bg-red-50' }
  if (['ppt', 'pptx'].includes(ext) || type === 'slides') return { label: 'Slides', Icon: FiFile, tone: 'text-orange-500 bg-orange-50' }
  if (['doc', 'docx'].includes(ext)) return { label: 'Word', Icon: FiFileText, tone: 'text-blue-500 bg-blue-50' }
  if (['xls', 'xlsx', 'csv'].includes(ext)) return { label: 'Excel', Icon: FiFileText, tone: 'text-green-600 bg-green-50' }
  if (['html', 'htm'].includes(ext)) return { label: 'HTML', Icon: FiFileText, tone: 'text-indigo-500 bg-indigo-50' }
  if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) return { label: 'Image', Icon: FiImage, tone: 'text-emerald-500 bg-emerald-50' }
  if (['txt', 'md'].includes(ext)) return { label: 'Text', Icon: FiFileText, tone: 'text-slate-600 bg-slate-100' }
  if (ext === 'zip') return { label: 'Archive', Icon: FiArchive, tone: 'text-amber-500 bg-amber-50' }
  if (resource.external_url) return { label: 'Link', Icon: FiLink, tone: 'text-violet-500 bg-violet-50' }
  return { label: 'File', Icon: FiPaperclip, tone: 'text-slate-500 bg-slate-100' }
}

export const ACCEPT_ALL = '.pdf,.ppt,.pptx,.doc,.docx,.html,.htm,.txt,.md,.jpg,.jpeg,.png,.gif,.webp,.zip,.xlsx,.xls,.csv,.mp4,.webm,.mov'

export function formatDateTime(value) {
  if (!value) return ''
  const d = new Date(value.replace(' ', 'T'))
  if (isNaN(d)) return value
  return d.toLocaleString(undefined, { dateStyle: 'medium', timeStyle: 'short' })
}

export function formatDate(value) {
  if (!value) return ''
  const d = new Date(value.replace(' ', 'T'))
  if (isNaN(d)) return value
  return d.toLocaleDateString(undefined, { dateStyle: 'medium' })
}

/** Append multiple files + titles to FormData (PHP: files[] / file_titles[]). */
export function appendTitledFiles(fd, items, { fileKey = 'files[]', titleKey = 'file_titles[]' } = {}) {
  ;(items || []).forEach((item) => {
    if (!item?.file) return
    fd.append(fileKey, item.file)
    fd.append(titleKey, (item.title || '').trim() || item.file.name)
  })
}
