import { useState } from 'react'
import { FiUpload, FiLink, FiEye } from 'react-icons/fi'
import { assignmentService } from '../../services/api'
import { useToast } from '../../context/ToastContext'
import { Button } from '../ui'
import FileUploadField from './FileUploadField'

export default function AssignmentHtmlImport({ externalUrl, onExternalUrl, htmlFile, onHtmlFile, preview, onPreview }) {
  const toast = useToast()
  const [parsing, setParsing] = useState(false)

  const parse = async () => {
    if (!htmlFile && !externalUrl.trim()) {
      return toast.error('Upload an HTML file or enter a link first')
    }
    setParsing(true)
    try {
      const fd = new FormData()
      if (htmlFile) fd.append('attachment', htmlFile)
      if (externalUrl.trim()) fd.append('external_url', externalUrl.trim())
      const { data } = await assignmentService.parseHtml(fd)
      onPreview(data.data || { valid: [], invalid: [], summary: {} })
      const s = data.data?.summary || {}
      if (!s.valid) {
        toast.error('No valid MCQs found. Check your HTML format.')
      } else {
        toast.success(`Found ${s.valid} valid question${s.valid !== 1 ? 's' : ''}`)
      }
    } catch (err) {
      toast.error(err.response?.data?.message || 'Could not parse HTML')
      onPreview(null)
    } finally {
      setParsing(false)
    }
  }

  return (
    <div className="space-y-4 rounded-xl border border-indigo-100 bg-indigo-50/40 p-4">
      <p className="text-sm font-semibold text-navy">Interactive MCQ test (HTML or link)</p>
      <p className="text-xs text-slate-500">
        Each question needs text, options A–D, and ANSWER: B (or Correct Answer: B). Works with HTML files or hosted quiz pages.
      </p>

      <div>
        <label className="text-sm font-medium text-slate-600">External link (optional)</label>
        <div className="relative mt-1">
          <FiLink className="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
          <input
            type="url"
            value={externalUrl}
            onChange={(e) => onExternalUrl(e.target.value)}
            placeholder="https://example.com/quiz.html"
            className="w-full rounded-xl border border-slate-200 py-2 pl-9 pr-3 text-sm"
          />
        </div>
      </div>

      <div>
        <label className="text-sm font-medium text-slate-600">Or upload HTML file</label>
        <div className="mt-1">
          <FileUploadField
            file={htmlFile}
            onFile={onHtmlFile}
            accept=".html,.htm"
            hint="Upload your .html file with MCQs"
          />
        </div>
      </div>

      <Button type="button" variant="secondary" size="sm" icon={FiEye} onClick={parse} loading={parsing}>
        Preview parsed questions
      </Button>

      {preview?.summary?.valid > 0 && (
        <div className="rounded-xl border border-green-200 bg-green-50/80 p-3 text-sm text-green-800">
          <strong>{preview.summary.valid}</strong> question{preview.summary.valid !== 1 ? 's' : ''} ready to import
          {preview.summary.invalid > 0 && (
            <span className="text-amber-700"> · {preview.summary.invalid} invalid skipped</span>
          )}
        </div>
      )}

      {preview?.invalid?.length > 0 && (
        <div className="max-h-32 overflow-y-auto rounded-xl border border-amber-200 bg-amber-50/60 p-3 text-xs text-amber-800">
          {preview.invalid.slice(0, 5).map((q) => (
            <p key={q.number}><strong>Q{q.number}:</strong> {(q.errors || []).join(' · ')}</p>
          ))}
        </div>
      )}
    </div>
  )
}
