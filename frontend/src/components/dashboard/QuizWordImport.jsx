import { useRef, useState } from 'react'
import { FiUpload, FiTrash2, FiEdit2, FiCheck, FiX } from 'react-icons/fi'
import { quizService } from '../../services/api'
import { useToast } from '../../context/ToastContext'
import QuizFormatGuide from './QuizFormatGuide'
import { Button } from '../ui'

const LETTERS = ['A', 'B', 'C', 'D']

export default function QuizWordImport({ quizId, onImported }) {
  const toast = useToast()
  const fileRef = useRef(null)
  const [parsing, setParsing] = useState(false)
  const [importing, setImporting] = useState(false)
  const [preview, setPreview] = useState([])
  const [invalid, setInvalid] = useState([])
  const [editing, setEditing] = useState(null)

  const handleFile = async (e) => {
    const file = e.target.files?.[0]
    if (!file) return toast.error('Please select a file')
    const ext = file.name.split('.').pop()?.toLowerCase()
    if (!['docx', 'doc', 'txt'].includes(ext)) {
      return toast.error('Upload a .docx, .doc, or .txt quiz file')
    }

    setParsing(true)
    try {
      const { data } = await quizService.parseWord(file)
      const result = data.data || {}
      setPreview((result.valid || []).map((q, i) => ({ ...q, _key: `v-${i}-${Date.now()}` })))
      setInvalid(result.invalid || [])
      if (!(result.valid || []).length) {
        toast.error(data.message || 'Could not parse quiz format — use numbered questions, A–D options, and Answer: X on separate lines')
      } else if ((result.invalid || []).length) {
        toast.error(`${result.invalid.length} question(s) failed validation — valid ones are shown below`)
      }
    } catch (err) {
      toast.error(err.response?.data?.message || 'Could not parse document')
    } finally {
      setParsing(false)
      if (fileRef.current) fileRef.current.value = ''
    }
  }

  const removePreview = (key) => setPreview((list) => list.filter((q) => q._key !== key))

  const saveEdit = () => {
    if (!editing) return
    const errors = []
    if (!editing.question_text?.trim()) errors.push('Question text is required')
    const opts = editing.options || []
    if (opts.filter((o) => o.option_text?.trim()).length < 4) errors.push('Need 4 options')
    if (!opts.some((o) => o.is_correct)) errors.push('Mark one correct option')
    if (errors.length) return toast.error(errors[0])

    setPreview((list) => list.map((q) => (q._key === editing._key ? { ...editing } : q)))
    setEditing(null)
    toast.success('Question updated')
  }

  const importAll = async () => {
    if (!preview.length) return toast.error('No valid questions to import')
    setImporting(true)
    try {
      const payload = preview.map(({ question_text, explanation, options }) => ({
        question_text,
        explanation: explanation || null,
        options,
      }))
      const { data } = await quizService.importQuestions(quizId, payload)
      toast.success(data.message || `${data.data?.imported || preview.length} question(s) imported`)
      setPreview([])
      setInvalid([])
      onImported?.()
    } catch (err) {
      toast.error(err.response?.data?.message || 'Import failed')
    } finally {
      setImporting(false)
    }
  }

  return (
    <div className="space-y-4">
      <QuizFormatGuide compact />

      <div className="flex flex-wrap items-center gap-3">
        <label className="inline-flex cursor-pointer items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-navy hover:bg-slate-50">
          <FiUpload size={16} />
          {parsing ? 'Parsing…' : 'Upload .txt or .docx quiz'}
          <input ref={fileRef} type="file" accept=".docx,.doc,.txt" className="hidden" onChange={handleFile} disabled={parsing} />
        </label>
        {preview.length > 0 && (
          <Button size="sm" onClick={importAll} loading={importing}>
            Import {preview.length} question{preview.length !== 1 ? 's' : ''}
          </Button>
        )}
      </div>

      <p className="text-xs text-slate-500">
        Upload your quiz as <strong>.docx</strong> or <strong>.txt</strong> — same format, no changes needed on your side.
      </p>

      {invalid.length > 0 && (
        <div className="rounded-xl border border-amber-200 bg-amber-50/60 p-4">
          <p className="text-sm font-semibold text-amber-800">{invalid.length} invalid question(s) — not imported</p>
          <ul className="mt-2 space-y-1.5">
            {invalid.map((q) => (
              <li key={`inv-${q.number}`} className="text-xs text-amber-700">
                <strong>Question {q.number}:</strong> {(q.errors || []).join(' · ')}
              </li>
            ))}
          </ul>
        </div>
      )}

      {preview.length > 0 && (
        <div className="space-y-3">
          <p className="text-sm font-semibold text-navy">Preview ({preview.length} valid)</p>
          {preview.map((q, i) => (
            <div key={q._key} className="rounded-xl border border-slate-100 bg-white p-4 shadow-soft">
              {editing?._key === q._key ? (
                <EditForm editing={editing} setEditing={setEditing} onSave={saveEdit} onCancel={() => setEditing(null)} />
              ) : (
                <>
                  <div className="flex items-start justify-between gap-2">
                    <p className="text-sm font-medium text-navy">{i + 1}. {q.question_text}</p>
                    <div className="flex shrink-0 gap-1">
                      <button type="button" onClick={() => setEditing({ ...q, options: q.options.map((o) => ({ ...o })) })}
                        className="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-primary" aria-label="Edit">
                        <FiEdit2 size={14} />
                      </button>
                      <button type="button" onClick={() => removePreview(q._key)}
                        className="rounded-lg p-1.5 text-slate-400 hover:bg-red-50 hover:text-red-500" aria-label="Delete">
                        <FiTrash2 size={14} />
                      </button>
                    </div>
                  </div>
                  <div className="mt-2 grid gap-1 sm:grid-cols-2">
                    {(q.options || []).map((o, j) => (
                      <div key={j} className={`rounded-lg px-2.5 py-1 text-xs ${o.is_correct ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-50 text-slate-600'}`}>
                        {LETTERS[j]}. {o.option_text}
                      </div>
                    ))}
                  </div>
                  {q.explanation && <p className="mt-2 text-xs text-slate-500">{q.explanation}</p>}
                </>
              )}
            </div>
          ))}
        </div>
      )}
    </div>
  )
}

function EditForm({ editing, setEditing, onSave, onCancel }) {
  return (
    <div className="space-y-3">
      <textarea value={editing.question_text} rows={2} onChange={(e) => setEditing((x) => ({ ...x, question_text: e.target.value }))}
        className="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" />
      {(editing.options || []).map((o, i) => (
        <div key={i} className="flex items-center gap-2">
          <input type="radio" checked={!!o.is_correct}
            onChange={() => setEditing((x) => ({
              ...x,
              options: x.options.map((opt, j) => ({ ...opt, is_correct: j === i ? 1 : 0 })),
            }))} className="h-4 w-4" />
          <span className="w-5 text-xs font-medium text-slate-400">{LETTERS[i]}</span>
          <input value={o.option_text} onChange={(e) => setEditing((x) => ({
            ...x,
            options: x.options.map((opt, j) => j === i ? { ...opt, option_text: e.target.value } : opt),
          }))} className="flex-1 rounded-lg border border-slate-200 px-3 py-1.5 text-sm" />
        </div>
      ))}
      <input value={editing.explanation || ''} placeholder="Explanation (optional)"
        onChange={(e) => setEditing((x) => ({ ...x, explanation: e.target.value }))}
        className="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" />
      <div className="flex gap-2">
        <Button size="sm" icon={FiCheck} onClick={onSave}>Save</Button>
        <Button size="sm" variant="outline" icon={FiX} onClick={onCancel}>Cancel</Button>
      </div>
    </div>
  )
}
