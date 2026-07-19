import { useEffect, useRef, useState } from 'react'
import mammoth from 'mammoth'
import { FiDownload } from 'react-icons/fi'
import { downloadMedia, fetchDocumentPreview, fetchMediaBlob, fetchMediaText, mediaUrl } from '../../services/api'
import { extOf, fileKind } from '../../utils/files'
import StreamingVideo from './StreamingVideo'

function Loading() {
  return (
    <div className="flex items-center justify-center py-16">
      <span className="inline-block h-8 w-8 animate-spin rounded-full border-2 border-primary border-t-transparent" />
      <span className="ml-3 text-sm text-slate-500">Loading preview…</span>
    </div>
  )
}

function ErrorBox({ message }) {
  return (
    <div className="rounded-xl border border-red-100 bg-red-50 px-4 py-6 text-sm text-red-700">
      <p className="font-medium">Could not open this file</p>
      <p className="mt-1 text-red-600">{message}</p>
    </div>
  )
}

function PdfPreview({ resource }) {
  const containerRef = useRef(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')

  useEffect(() => {
    let cancelled = false
    let pdfDoc = null

    ;(async () => {
      try {
        const blob = await fetchMediaBlob(resource.file_path)
        const data = await blob.arrayBuffer()
        const pdfjs = await import('pdfjs-dist')
        const workerMod = await import('pdfjs-dist/build/pdf.worker.min.mjs?url')
        pdfjs.GlobalWorkerOptions.workerSrc = workerMod.default
        const pdf = await pdfjs.getDocument({ data }).promise
        if (cancelled) {
          pdf.destroy()
          return
        }
        pdfDoc = pdf

        const container = containerRef.current
        if (!container) return
        container.replaceChildren()

        const scale = window.innerWidth < 640 ? 1 : 1.35
        for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
          if (cancelled) return
          const page = await pdf.getPage(pageNum)
          const viewport = page.getViewport({ scale })
          const canvas = document.createElement('canvas')
          canvas.width = viewport.width
          canvas.height = viewport.height
          canvas.className = 'mx-auto mb-4 block max-w-full rounded-lg bg-white shadow-sm'
          const ctx = canvas.getContext('2d')
          if (!ctx) throw new Error('Could not render PDF')
          container.appendChild(canvas)
          await page.render({ canvasContext: ctx, viewport }).promise
        }
      } catch (e) {
        if (!cancelled) setError(e.message || 'Could not preview PDF')
      } finally {
        if (!cancelled) setLoading(false)
      }
    })()

    return () => {
      cancelled = true
      pdfDoc?.destroy?.()
      containerRef.current?.replaceChildren?.()
    }
  }, [resource.file_path])

  if (error) return <ErrorBox message={error} />

  return (
    <div>
      {loading && <Loading />}
      <div
        ref={containerRef}
        className={`max-h-[75vh] min-h-[200px] overflow-auto rounded-xl border border-slate-100 bg-slate-200/60 p-3 ${loading ? 'hidden' : ''}`}
      />
      {!loading && <ViewToolbar resource={resource} />}
    </div>
  )
}

function ViewToolbar({ resource, allowDownload = true }) {
  const [downloading, setDownloading] = useState(false)
  if (!allowDownload || !resource?.file_path) return null

  const handleDownload = async () => {
    setDownloading(true)
    try {
      await downloadMedia(resource.file_path, resource.title)
    } catch (e) {
      alert(e.message || 'Download failed')
    } finally {
      setDownloading(false)
    }
  }

  return (
    <div className="mt-3 flex justify-end border-t border-slate-100 pt-3">
      <button type="button" onClick={handleDownload} disabled={downloading} className="btn-secondary text-sm disabled:opacity-60">
        <FiDownload /> {downloading ? 'Downloading…' : 'Download file'}
      </button>
    </div>
  )
}

function TextPreview({ resource }) {
  const [text, setText] = useState('')
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')

  useEffect(() => {
    let cancelled = false
    fetchMediaText(resource.file_path)
      .then((t) => { if (!cancelled) setText(t) })
      .catch((e) => { if (!cancelled) setError(e.message) })
      .finally(() => { if (!cancelled) setLoading(false) })
    return () => { cancelled = true }
  }, [resource.file_path])

  if (loading) return <Loading />
  if (error) return <ErrorBox message={error} />
  return (
    <div>
      <pre className="max-h-[70vh] overflow-auto rounded-xl border border-slate-100 bg-slate-50 p-4 text-sm leading-relaxed text-slate-800 whitespace-pre-wrap">{text}</pre>
      <ViewToolbar resource={resource} />
    </div>
  )
}

function ExcelPreview({ resource }) {
  const [html, setHtml] = useState('')
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')

  useEffect(() => {
    let cancelled = false
    ;(async () => {
      try {
        const blob = await fetchMediaBlob(resource.file_path)
        const buf = await blob.arrayBuffer()
        const XLSX = await import('xlsx')
        const wb = XLSX.read(buf, { type: 'array' })
        const sheet = wb.Sheets[wb.SheetNames[0]]
        if (!sheet) throw new Error('No data in spreadsheet')
        const table = XLSX.utils.sheet_to_html(sheet, { id: 'sheet-preview', editable: false })
        if (!cancelled) setHtml(table)
      } catch (e) {
        if (!cancelled) setError(e.message || 'Could not preview Excel file')
      } finally {
        if (!cancelled) setLoading(false)
      }
    })()
    return () => { cancelled = true }
  }, [resource.file_path])

  if (loading) return <Loading />
  if (error) return <ErrorBox message={error} />
  return (
    <div>
      <div
        className="max-h-[70vh] overflow-auto rounded-xl border border-slate-100 bg-white p-2 text-sm [&_table]:w-full [&_table]:border-collapse [&_td]:border [&_td]:border-slate-200 [&_td]:px-2 [&_td]:py-1 [&_th]:border [&_th]:border-slate-200 [&_th]:bg-slate-50 [&_th]:px-2 [&_th]:py-1"
        dangerouslySetInnerHTML={{ __html: html }}
      />
      <ViewToolbar resource={resource} />
    </div>
  )
}

function WordPreview({ resource }) {
  const [html, setHtml] = useState('')
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')
  const ext = extOf(resource.file_path)

  useEffect(() => {
    if (!['doc', 'docx'].includes(ext)) {
      setLoading(false)
      setError('Unsupported Word format')
      return undefined
    }

    let cancelled = false
    ;(async () => {
      try {
        if (ext === 'docx') {
          try {
            const blob = await fetchMediaBlob(resource.file_path)
            const buf = await blob.arrayBuffer()
            const result = await mammoth.convertToHtml({ arrayBuffer: buf })
            if (!cancelled) {
              setHtml(result.value || '<p>Empty document</p>')
              return
            }
          } catch {
            /* fall back to server preview */
          }
        }
        const previewHtml = await fetchDocumentPreview(resource.file_path)
        if (!cancelled) setHtml(previewHtml)
      } catch (e) {
        if (!cancelled) setError(e.message || 'Could not preview Word file')
      } finally {
        if (!cancelled) setLoading(false)
      }
    })()
    return () => { cancelled = true }
  }, [resource.file_path, ext])

  if (loading) return <Loading />
  if (error) return <ErrorBox message={error} />
  return (
    <div>
      <div
        className="prose prose-sm max-h-[70vh] max-w-none overflow-auto rounded-xl border border-slate-100 bg-white p-6"
        dangerouslySetInnerHTML={{ __html: html }}
      />
      <ViewToolbar resource={resource} />
    </div>
  )
}

function SlidesPreview({ resource }) {
  const [html, setHtml] = useState('')
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')
  const ext = extOf(resource.file_path)

  useEffect(() => {
    if (!['ppt', 'pptx'].includes(ext)) {
      setLoading(false)
      setError('Unsupported PowerPoint format')
      return undefined
    }

    let cancelled = false
    fetchDocumentPreview(resource.file_path)
      .then((previewHtml) => { if (!cancelled) setHtml(previewHtml) })
      .catch((e) => { if (!cancelled) setError(e.message || 'Could not preview PowerPoint file') })
      .finally(() => { if (!cancelled) setLoading(false) })
    return () => { cancelled = true }
  }, [resource.file_path, ext])

  if (loading) return <Loading />
  if (error) return <ErrorBox message={error} />
  return (
    <div>
      <div
        className="max-h-[75vh] space-y-4 overflow-auto rounded-xl border border-slate-100 bg-slate-100/80 p-4 [&_.lms-slide]:rounded-xl [&_.lms-slide]:border [&_.lms-slide]:border-orange-100 [&_.lms-slide]:bg-white [&_.lms-slide]:shadow-sm [&_.lms-slide-content]:space-y-2 [&_.lms-slide-content]:p-5 [&_.lms-slide-content_p]:text-sm [&_.lms-slide-content_p]:leading-relaxed [&_.lms-slide-content_p]:text-slate-700 [&_.lms-slide-empty]:text-slate-400 [&_.lms-slide-header]:border-b [&_.lms-slide-header]:border-orange-50 [&_.lms-slide-header]:bg-orange-50/80 [&_.lms-slide-header]:px-4 [&_.lms-slide-header]:py-2 [&_.lms-slide-header]:text-xs [&_.lms-slide-header]:font-semibold [&_.lms-slide-header]:uppercase [&_.lms-slide-header]:tracking-wide [&_.lms-slide-header]:text-orange-700 [&_.lms-slide-img]:mx-auto [&_.lms-slide-img]:max-h-80 [&_.lms-slide-img]:max-w-full [&_.lms-slide-img]:rounded-lg [&_.lms-slide-img]:object-contain"
        dangerouslySetInnerHTML={{ __html: html }}
      />
      <ViewToolbar resource={resource} />
    </div>
  )
}

/** Inline view for course materials — PDF/docs use direct media URL; video supports seeking via byte-range. */
export default function MaterialViewer({ resource, allowVideoDownload = true }) {
  const { label } = fileKind(resource)
  const viewUrl = resource?.file_path ? mediaUrl(resource.file_path) : null

  if (!resource?.file_path && !resource?.external_url) {
    return <ErrorBox message="No file attached to this material." />
  }

  if (label === 'Video') {
    return (
      <StreamingVideo
        filePath={resource.file_path}
        fileSize={resource.file_size}
        title={resource.title}
        allowVideoDownload={allowVideoDownload}
        resourceId={resource.id || null}
      />
    )
  }

  if (label === 'PDF') {
    return <PdfPreview resource={resource} />
  }

  if (label === 'Image' && viewUrl) {
    return (
      <div>
        <img src={viewUrl} alt={resource.title} className="mx-auto max-h-[75vh] rounded-xl object-contain" />
        <ViewToolbar resource={resource} />
      </div>
    )
  }

  if (label === 'HTML' && viewUrl) {
    return (
      <div>
        <iframe title={resource.title} src={viewUrl} className="h-[75vh] w-full rounded-xl border border-slate-100 bg-white" />
        <ViewToolbar resource={resource} />
      </div>
    )
  }

  if (label === 'Text') return <TextPreview resource={resource} />
  if (label === 'Excel') return <ExcelPreview resource={resource} />
  if (label === 'Word') return <WordPreview resource={resource} />
  if (label === 'Slides') return <SlidesPreview resource={resource} />

  if (viewUrl) {
    return (
      <div>
        <iframe title={resource.title} src={viewUrl} className="h-[75vh] w-full rounded-xl border border-slate-100 bg-white" />
        <ViewToolbar resource={resource} />
      </div>
    )
  }

  return <ErrorBox message="Preview is not available for this file type." />
}
