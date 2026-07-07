import { useRef } from 'react'
import { FiPlus, FiTrash2, FiUploadCloud } from 'react-icons/fi'
import { formatBytes } from '../../utils/files'

let _id = 0
export function newUploadItem(file, title = '') {
  const base = file?.name?.replace(/\.[^.]+$/, '') || ''
  return { id: `up-${++_id}`, file, title: title || base }
}

/**
 * Multiple files, each with its own title. Controlled via `items` / `onChange`.
 * items: [{ id, file, title }]
 */
export default function MultiFileUploadField({
  items = [],
  onChange,
  accept,
  uploading = false,
  disabled = false,
  hint = 'Select one or more files — set a title for each',
}) {
  const inputRef = useRef(null)

  const addFiles = (list) => {
    if (!list?.length) return
    const next = [...items]
    Array.from(list).forEach((f) => next.push(newUploadItem(f)))
    onChange(next)
  }

  const updateTitle = (id, title) => {
    onChange(items.map((x) => (x.id === id ? { ...x, title } : x)))
  }

  const remove = (id) => onChange(items.filter((x) => x.id !== id))

  return (
    <div className="space-y-3">
      <div
        role="button"
        tabIndex={0}
        onClick={() => !disabled && !uploading && inputRef.current?.click()}
        onKeyDown={(e) => (e.key === 'Enter' || e.key === ' ') && !disabled && inputRef.current?.click()}
        className={`flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed px-4 py-5 text-center transition-colors ${
          disabled || uploading ? 'cursor-not-allowed opacity-60' : 'border-slate-200 hover:border-primary/40 hover:bg-primary/5'
        }`}
      >
        <FiUploadCloud className="text-slate-400" size={24} />
        <p className="mt-2 text-sm font-medium text-slate-600">
          <span className="text-primary">Add files</span> — multiple allowed
        </p>
        <p className="mt-0.5 text-xs text-slate-400">{hint}</p>
        <input
          ref={inputRef}
          type="file"
          accept={accept}
          multiple
          className="hidden"
          disabled={disabled || uploading}
          onChange={(e) => { addFiles(e.target.files); e.target.value = '' }}
        />
      </div>

      {items.length > 0 && (
        <ul className="space-y-2">
          {items.map((item) => (
            <li key={item.id} className="rounded-xl border border-slate-100 bg-slate-50 p-3">
              <div className="flex flex-wrap items-start gap-2">
                <div className="min-w-0 flex-1">
                  <p className="truncate text-xs font-medium text-slate-500">{item.file?.name}</p>
                  <p className="text-xs text-slate-400">{formatBytes(item.file?.size)}</p>
                  <input
                    value={item.title}
                    onChange={(e) => updateTitle(item.id, e.target.value)}
                    placeholder="Title for students (required)"
                    disabled={uploading}
                    className="mt-2 w-full rounded-lg border border-slate-200 px-2.5 py-1.5 text-sm"
                  />
                </div>
                {!uploading && (
                  <button type="button" onClick={() => remove(item.id)} className="rounded-lg p-2 text-slate-400 hover:bg-red-50 hover:text-red-500" aria-label="Remove">
                    <FiTrash2 size={16} />
                  </button>
                )}
              </div>
            </li>
          ))}
        </ul>
      )}

      {items.length > 0 && !uploading && (
        <button
          type="button"
          onClick={() => inputRef.current?.click()}
          className="inline-flex items-center gap-1.5 text-xs font-semibold text-primary hover:underline"
        >
          <FiPlus size={14} /> Add more files
        </button>
      )}
    </div>
  )
}
