import { lazy, Suspense } from 'react'
import { Modal } from '../ui'

const MaterialViewer = lazy(() => import('./MaterialViewer'))

/** Open any uploaded file in the in-app viewer (PDF, Word, Excel, video, etc.). */
export default function FilePreviewModal({
  open,
  onClose,
  title,
  filePath,
  file_path,
  allowVideoDownload = true,
}) {
  const path = filePath || file_path
  if (!path) return null

  const resource = {
    title: title || 'File',
    file_path: path,
  }

  return (
    <Modal open={open} onClose={onClose} title={resource.title} size="xl">
      <Suspense fallback={<p className="py-12 text-center text-sm text-slate-400">Loading preview…</p>}>
        <MaterialViewer resource={resource} allowVideoDownload={allowVideoDownload} />
      </Suspense>
    </Modal>
  )
}
