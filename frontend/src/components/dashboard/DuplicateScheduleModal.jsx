import { useEffect, useState } from 'react'
import { Modal, Button } from '../ui'

/** Pick a target date when duplicating a scheduled class. */
export default function DuplicateScheduleModal({ open, defaultDate, onClose, onConfirm }) {
  const [date, setDate] = useState(defaultDate || '')
  const [busy, setBusy] = useState(false)

  useEffect(() => {
    if (open) setDate(defaultDate || '')
  }, [open, defaultDate])

  const handleConfirm = async () => {
    if (!date) return
    setBusy(true)
    try { await onConfirm(date) } finally { setBusy(false) }
  }

  return (
    <Modal open={open} onClose={onClose} title="Duplicate class" subtitle="Choose the date for the duplicated session"
      footer={<>
        <Button variant="outline" onClick={onClose} disabled={busy}>Cancel</Button>
        <Button onClick={handleConfirm} loading={busy} disabled={!date}>Duplicate</Button>
      </>}>
      <label className="text-sm font-medium text-slate-600">New date</label>
      <input type="date" value={date} onChange={(e) => setDate(e.target.value)}
        className="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" />
    </Modal>
  )
}
