import { useEffect, useState } from 'react'
import { FiBookOpen, FiBookmark, FiDownload, FiZap, FiChevronRight } from 'react-icons/fi'
import { studentAiService } from '../../services/api'
import Alert from '../../components/dashboard/Alert'
import McqPlayer from '../../components/dashboard/McqPlayer'

const SECTIONS = [
  { key: 'high_yield_points', label: 'High-Yield Points', tone: 'bg-primary/5 text-navy', dot: 'bg-primary' },
  { key: 'clinical_pearls', label: 'Clinical Pearls', tone: 'bg-teal-50 text-teal-800', dot: 'bg-teal-500' },
  { key: 'common_mistakes', label: 'Common Mistakes', tone: 'bg-red-50 text-red-700', dot: 'bg-red-500' },
  { key: 'memory_tricks', label: 'Memory Tricks / Mnemonics', tone: 'bg-amber-50 text-amber-800', dot: 'bg-amber-500' },
  { key: 'key_takeaways', label: 'Key Takeaways', tone: 'bg-green-50 text-green-800', dot: 'bg-green-500' },
]

export default function RevisionCenter() {
  const [lectures, setLectures] = useState([])
  const [selected, setSelected] = useState(null)
  const [data, setData] = useState(null)
  const [loading, setLoading] = useState(false)
  const [practice, setPractice] = useState(null)

  useEffect(() => {
    studentAiService.revisionLectures().then(({ data }) => {
      setLectures(data.data || [])
      if ((data.data || []).length) setSelected(data.data[0])
    })
  }, [])

  useEffect(() => {
    if (!selected) return
    setLoading(true)
    setData(null)
    studentAiService.revisionContent(selected.lecture_id)
      .then(({ data }) => setData(data.data))
      .finally(() => setLoading(false))
  }, [selected])

  const openPractice = async () => {
    const { data: res } = await studentAiService.mcqPractice(selected.lecture_id)
    setPractice(res.data || [])
  }

  const toggleBookmark = async () => {
    const { data: res } = await studentAiService.toggleBookmark({ content_type: 'lecture', content_id: selected.lecture_id })
    setData((d) => ({ ...d, bookmarked: res.data.bookmarked }))
  }

  const content = data?.content

  return (
    <div>
      <div className="flex items-end justify-between">
        <div>
          <h2 className="font-display text-2xl font-bold text-navy">Revision Center</h2>
          <p className="text-sm text-slate-500">Teacher-approved revision material for FCPS Part-I.</p>
        </div>
      </div>

      {lectures.length === 0 ? (
        <div className="mt-6"><Alert>No revision material published yet.</Alert></div>
      ) : (
        <div className="mt-6 grid gap-6 lg:grid-cols-[280px,1fr]">
          <aside className="space-y-2">
            {lectures.map((l) => (
              <button key={l.lecture_id} type="button" onClick={() => setSelected(l)}
                className={`flex w-full items-center justify-between rounded-xl border px-4 py-3 text-left text-sm transition ${selected?.lecture_id === l.lecture_id ? 'border-primary bg-primary/5 text-navy' : 'border-slate-100 bg-white text-slate-600 hover:border-slate-200'}`}>
                <span>
                  <span className="block font-medium">{l.lecture_title}</span>
                  <span className="block text-xs text-slate-400">{l.course_title}</span>
                </span>
                <FiChevronRight className="shrink-0" />
              </button>
            ))}
          </aside>

          <section>
            {practice ? (
              <div className="rounded-2xl border border-slate-100 bg-white p-6 shadow-soft">
                <McqPlayer questions={practice} source="practice" title={`${selected.lecture_title} — Practice`} onClose={() => setPractice(null)} />
              </div>
            ) : loading ? (
              <p className="text-slate-400">Loading revision material…</p>
            ) : content ? (
              <div className="space-y-6">
                <div className="flex flex-wrap items-center justify-between gap-3">
                  <h3 className="font-display text-xl font-bold text-navy">{selected.lecture_title}</h3>
                  <div className="flex gap-2">
                    <button type="button" onClick={openPractice} className="flex items-center gap-1.5 rounded-xl bg-primary px-3 py-2 text-sm font-semibold text-white"><FiZap /> Practice MCQs</button>
                    <button type="button" onClick={toggleBookmark} className={`flex items-center gap-1.5 rounded-xl border px-3 py-2 text-sm ${data.bookmarked ? 'border-primary bg-primary/5 text-primary' : 'border-slate-200 text-slate-500'}`}><FiBookmark /> {data.bookmarked ? 'Saved' : 'Save'}</button>
                    <button type="button" onClick={() => window.print()} className="flex items-center gap-1.5 rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-500"><FiDownload /> PDF</button>
                  </div>
                </div>

                {content.summary && (
                  <Card title="Summary" icon={<FiBookOpen />}>
                    <p className="whitespace-pre-wrap text-sm leading-relaxed text-slate-600">{content.summary}</p>
                  </Card>
                )}

                {content.revision_notes && (
                  <Card title="High-Yield Revision Notes">
                    <p className="whitespace-pre-wrap text-sm leading-relaxed text-slate-600">{content.revision_notes}</p>
                  </Card>
                )}

                {SECTIONS.filter((s) => (content[s.key] || []).length > 0).map((s) => (
                  <Card key={s.key} title={s.label}>
                    <ul className="space-y-2">
                      {content[s.key].map((item, i) => (
                        <li key={i} className={`flex gap-2 rounded-xl px-3 py-2 text-sm ${s.tone}`}>
                          <span className={`mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full ${s.dot}`} />
                          <span>{item}</span>
                        </li>
                      ))}
                    </ul>
                  </Card>
                ))}

                {(content.key_definitions || []).length > 0 && (
                  <Card title="Important Definitions">
                    <dl className="space-y-3">
                      {content.key_definitions.map((d, i) => (
                        <div key={i} className="rounded-xl bg-slate-50 p-3">
                          <dt className="text-sm font-semibold text-navy">{d.term}</dt>
                          <dd className="mt-1 text-sm text-slate-600">{d.definition}</dd>
                        </div>
                      ))}
                    </dl>
                  </Card>
                )}
              </div>
            ) : (
              <Alert>No content available for this lecture.</Alert>
            )}
          </section>
        </div>
      )}
    </div>
  )
}

function Card({ title, icon, children }) {
  return (
    <div className="rounded-2xl border border-slate-100 bg-white p-6 shadow-soft">
      <h4 className="mb-3 flex items-center gap-2 font-display font-bold text-navy">{icon}{title}</h4>
      {children}
    </div>
  )
}
