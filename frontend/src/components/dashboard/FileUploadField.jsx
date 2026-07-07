import { useRef, useState } from 'react'
import { FiUploadCloud, FiX } from 'react-icons/fi'
import { formatBytes } from '../../utils/files'

/**
 * Reusable file picker with drag-and-drop, selected-file preview, and an
 * optional upload progress bar. Controlled via `file` / `onFile`.
 */
export default function FileUploadField({
  file,
  onFile,
  accept,
  uploading = false,
  progress = 0,
  hint = 'PDF, PPTX, PPT, Word, Video, Images or ZIP',
  disabled = false,
}) {
  const inputRef = useRef(null)
  const [dragging, setDragging] = useState(false)

  const pick = (f) => { if (f) onFile(f) }

  return (
    <div>
      <div
        role="button"
        tabIndex={0}
        onClick={() => !disabled && inputRef.current?.click()}
        onKeyDown={(e) => (e.key === 'Enter' || e.key === ' ') && !disabled && inputRef.current?.click()}
        onDragOver={(e) => { e.preventDefault(); setDragging(true) }}
        onDragLeave={() => setDragging(false)}
        onDrop={(e) => { e.preventDefault(); setDragging(false); pick(e.dataTransfer.files?.[0]) }}
        className={`flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed px-4 py-6 text-center transition-colors ${
          dragging ? 'border-primary bg-primary/5' : 'border-slate-200 hover:border-slate-300'
        } ${disabled ? 'cursor-not-allowed opacity-60' : ''}`}
      >
        <FiUploadCloud className="text-slate-400" size={26} />
        <p className="mt-2 text-sm font-medium text-slate-600">
          <span className="text-primary">Click to upload</span> or drag &amp; drop
        </p>
        <p className="mt-0.5 text-xs text-slate-400">{hint}</p>
        <input
          ref={inputRef}
          type="file"
          accept={accept}
          className="hidden"
          onChange={(e) => pick(e.target.files?.[0])}
          disabled={disabled}
        />
      </div>

      {file && (
        <div className="mt-3 rounded-xl border border-slate-100 bg-slate-50 p-3">
          <div className="flex items-center justify-between gap-3">
            <div className="min-w-0">
              <p className="truncate text-sm font-medium text-navy">{file.name}</p>
              <p className="text-xs text-slate-400">{formatBytes(file.size)}</p>
            </div>
            {!uploading && (
              <button type="button" onClick={() => onFile(null)} className="text-slate-400 hover:text-red-500" aria-label="Remove file">
                <FiX size={18} />
              </button>
            )}
          </div>
          {uploading && (
            <div className="mt-2">
              <div className="h-1.5 w-full overflow-hidden rounded-full bg-slate-200">
                <div className="h-full rounded-full bg-primary transition-all" style={{ width: `${progress}%` }} />
              </div>
              <p className="mt-1 text-right text-xs text-slate-400">{progress}%</p>
            </div>
          )}
        </div>
      )}
    </div>
  )
}
