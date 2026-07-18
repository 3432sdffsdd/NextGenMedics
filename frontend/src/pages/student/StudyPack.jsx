import { useEffect, useState } from 'react'
import { useSearchParams } from 'react-router-dom'
import { FiChevronRight } from 'react-icons/fi'
import { studentAiService } from '../../services/api'
import Alert from '../../components/dashboard/Alert'

const TABS = [
  { id: 'summary', label: 'Summary' },
  { id: 'mnemonics', label: 'Mnemonics' },
  { id: 'flashcards', label: 'Flashcards' },
  { id: 'cases', label: 'Clinical Cases' },
]

function Block({ text }) {
  if (!text) return <p className="text-sm text-slate-400">Nothing published in this section yet.</p>
  return <pre className="whitespace-pre-wrap font-sans text-sm leading-relaxed text-slate-700">{text}</pre>
}

export default function StudyPack() {
  const [searchParams] = useSearchParams()
  const lectureParam = Number(searchParams.get('lecture') || 0)
  const [lectures, setLectures] = useState([])
  const [selected, setSelected] = useState(null)
  const [pack, setPack] = useState(null)
  const [tab, setTab] = useState('summary')
  const [loading, setLoading] = useState(false)
  const [flip, setFlip] = useState({})

  useEffect(() => {
    studentAiService.revisionLectures().then(({ data }) => {
      const list = data.data || []
      setLectures(list)
      if (!list.length) {
        setSelected(null)
        return
      }
      const fromUrl = lectureParam ? list.find((l) => Number(l.lecture_id) === lectureParam) : null
      setSelected(fromUrl || list[0])
    })
  }, [lectureParam])

  useEffect(() => {
    if (!selected) return
    setLoading(true)
    setPack(null)
    setFlip({})
    studentAiService.studyPack(selected.lecture_id)
      .then(({ data }) => setPack(data.data))
      .finally(() => setLoading(false))
  }, [selected])

  const content = pack?.content || {}

  return (
    <div>
      <div>
        <h2 className="font-display text-2xl font-bold text-navy">Study Pack</h2>
        <p className="text-sm text-slate-500">
          Summary, mnemonics, flashcards and clinical cases published by your teacher.
        </p>
      </div>

      {lectures.length === 0 ? (
        <div className="mt-6"><Alert>No published study packs yet. Ask your teacher to generate and publish Study Tools.</Alert></div>
      ) : (
        <div className="mt-6 grid gap-6 lg:grid-cols-[260px,1fr]">
          <aside className="space-y-2">
            {lectures.map((l) => (
              <button
                key={l.lecture_id}
                type="button"
                onClick={() => setSelected(l)}
                className={`flex w-full items-center justify-between rounded-xl border px-4 py-3 text-left text-sm ${selected?.lecture_id === l.lecture_id ? 'border-primary bg-primary/5 text-navy' : 'border-slate-100 bg-white text-slate-600'}`}
              >
                <span>
                  <span className="block font-medium">{l.lecture_title}</span>
                  <span className="block text-xs text-slate-400">{l.course_title}</span>
                </span>
                <FiChevronRight />
              </button>
            ))}
          </aside>

          <section className="rounded-2xl border border-slate-100 bg-white p-5 shadow-soft">
            {loading && <p className="text-sm text-slate-500">Loading…</p>}
            {!loading && pack && (
              <>
                <h3 className="font-display text-xl font-bold text-navy">{pack.lecture?.title || selected.lecture_title}</h3>
                <div className="mt-4 flex flex-wrap gap-2">
                  {TABS.map((t) => (
                    <button
                      key={t.id}
                      type="button"
                      onClick={() => setTab(t.id)}
                      className={`rounded-full px-3 py-1 text-xs font-semibold ${tab === t.id ? 'bg-primary text-white' : 'bg-slate-100 text-slate-600'}`}
                    >
                      {t.label}
                    </button>
                  ))}
                </div>

                <div className="mt-5">
                  {tab === 'summary' && <Block text={content.summary} />}
                  {tab === 'mnemonics' && (
                    <ul className="space-y-3">
                      {(pack.mnemonics || []).map((m) => (
                        <li key={m.id} className="rounded-xl border border-slate-100 bg-slate-50 p-4 text-sm">
                          <p className="font-medium text-navy">{m.topic || 'Mnemonic'}</p>
                          <p className="mt-1">{m.mnemonic}</p>
                          {m.explanation && <p className="mt-1 text-slate-500">{m.explanation}</p>}
                        </li>
                      ))}
                      {!pack.mnemonics?.length && <Block text="" />}
                    </ul>
                  )}
                  {tab === 'flashcards' && (
                    <div className="grid gap-3 sm:grid-cols-2">
                      {(pack.flashcards || []).map((c) => (
                        <button
                          key={c.id}
                          type="button"
                          onClick={() => setFlip((f) => ({ ...f, [c.id]: !f[c.id] }))}
                          className="rounded-xl border border-slate-100 bg-slate-50 p-4 text-left text-sm"
                        >
                          <p className="text-xs uppercase text-slate-400">{flip[c.id] ? 'Answer' : 'Question'}</p>
                          <p className="mt-1 font-medium text-navy">{flip[c.id] ? c.back : c.front}</p>
                        </button>
                      ))}
                      {!pack.flashcards?.length && <Block text="" />}
                    </div>
                  )}
                  {tab === 'cases' && (
                    <ul className="space-y-3">
                      {(pack.clinical_cases || []).map((c) => (
                        <li key={c.id} className="rounded-xl border border-slate-100 p-4 text-sm">
                          <p className="font-semibold text-navy">{c.title}</p>
                          <p className="mt-2 whitespace-pre-wrap text-slate-600">{c.scenario}</p>
                          {c.diagnosis && <p className="mt-2 text-xs font-medium text-slate-500">Diagnosis: {c.diagnosis}</p>}
                          {c.discussion && <p className="mt-1 text-slate-500">{c.discussion}</p>}
                        </li>
                      ))}
                      {!pack.clinical_cases?.length && <Block text="" />}
                    </ul>
                  )}
                </div>
              </>
            )}
          </section>
        </div>
      )}
    </div>
  )
}
