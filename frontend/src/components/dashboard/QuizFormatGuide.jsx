import { useState } from 'react'
import { FiDownload, FiFileText, FiChevronDown, FiChevronUp } from 'react-icons/fi'
import {
  QUIZ_FORMAT_RULES,
  QUIZ_TEMPLATE_TEXT,
  QUIZ_WORD_TIPS,
  downloadQuizTxtTemplate,
} from '../../utils/quizFormat'
import { quizService } from '../../services/api'
import { useToast } from '../../context/ToastContext'
import { Button } from '../ui'

export default function QuizFormatGuide({ compact = false }) {
  const toast = useToast()
  const [open, setOpen] = useState(!compact)
  const [downloading, setDownloading] = useState('')

  const downloadDocx = async () => {
    setDownloading('docx')
    try {
      const { data } = await quizService.downloadTemplate('docx')
      const url = URL.createObjectURL(data)
      const a = document.createElement('a')
      a.href = url
      a.download = 'quiz-mcq-template.docx'
      a.click()
      URL.revokeObjectURL(url)
    } catch (e) {
      toast.error(e.response?.data?.message || 'Could not download Word template')
    } finally {
      setDownloading('')
    }
  }

  return (
    <div className="rounded-xl border border-slate-200 bg-slate-50/80 p-4">
      <button
        type="button"
        onClick={() => setOpen((v) => !v)}
        className="flex w-full items-center justify-between gap-2 text-left"
      >
        <span className="text-sm font-semibold text-navy">Quiz format (.txt and .docx use the same layout)</span>
        {open ? <FiChevronUp className="shrink-0 text-slate-400" /> : <FiChevronDown className="shrink-0 text-slate-400" />}
      </button>

      {open && (
        <div className="mt-3 space-y-4">
          <div className="flex flex-wrap gap-2">
            <Button variant="outline" size="sm" icon={FiDownload} onClick={downloadQuizTxtTemplate}>
              Download .txt template
            </Button>
            <Button variant="outline" size="sm" icon={FiFileText} onClick={downloadDocx} loading={downloading === 'docx'}>
              Download .docx template
            </Button>
          </div>

          <div className="grid gap-4 sm:grid-cols-2">
            <div>
              <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">Structure (both file types)</p>
              <ol className="mt-2 list-decimal space-y-1 pl-4 text-xs text-slate-600">
                {QUIZ_FORMAT_RULES.map((rule) => (
                  <li key={rule}>{rule}</li>
                ))}
              </ol>
            </div>
            <div>
              <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">Word (.docx) tips</p>
              <ul className="mt-2 list-disc space-y-1 pl-4 text-xs text-slate-600">
                {QUIZ_WORD_TIPS.map((tip) => (
                  <li key={tip}>{tip}</li>
                ))}
              </ul>
            </div>
          </div>

          <div>
            <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">Example (copy this layout)</p>
            <pre className="mt-2 max-h-48 overflow-auto rounded-lg border border-slate-200 bg-white p-3 text-[11px] leading-relaxed text-slate-700 whitespace-pre-wrap">
              {QUIZ_TEMPLATE_TEXT.trim()}
            </pre>
          </div>
        </div>
      )}
    </div>
  )
}
