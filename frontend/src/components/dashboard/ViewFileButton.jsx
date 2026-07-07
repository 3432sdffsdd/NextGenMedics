import { useState } from 'react'
import { FiEye } from 'react-icons/fi'
import FilePreviewModal from './FilePreviewModal'

/** Opens PDF / Word / Excel / video in the in-app viewer instead of downloading. */
export default function ViewFileButton({
  title,
  filePath,
  file_path,
  allowVideoDownload = true,
  className = 'inline-flex items-center gap-1.5 text-primary hover:underline',
  size = 14,
  label = 'View',
}) {
  const [open, setOpen] = useState(false)
  const path = filePath || file_path
  if (!path) return null

  return (
    <>
      <button type="button" onClick={() => setOpen(true)} className={className}>
        <FiEye size={size} /> {label}
      </button>
      <FilePreviewModal
        open={open}
        onClose={() => setOpen(false)}
        title={title}
        filePath={path}
        allowVideoDownload={allowVideoDownload}
      />
    </>
  )
}
