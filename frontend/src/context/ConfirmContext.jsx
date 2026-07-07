import { createContext, useCallback, useContext, useRef, useState } from 'react'
import { FiAlertTriangle } from 'react-icons/fi'
import Modal from '../components/ui/Modal'
import Button from '../components/ui/Button'

const ConfirmContext = createContext(null)

const DEFAULTS = {
  title: 'Are you sure?',
  message: 'This action cannot be undone.',
  confirmText: 'Yes, Delete',
  cancelText: 'Cancel',
  tone: 'danger', // danger | primary
}

export function ConfirmProvider({ children }) {
  const [state, setState] = useState(null)
  const [busy, setBusy] = useState(false)
  const resolver = useRef(null)

  const confirm = useCallback((opts = {}) => {
    setState({ ...DEFAULTS, ...opts })
    return new Promise((resolve) => { resolver.current = resolve })
  }, [])

  const close = useCallback((result) => {
    resolver.current?.(result)
    resolver.current = null
    setState(null)
    setBusy(false)
  }, [])

  const onConfirm = () => {
    setBusy(true)
    close(true)
  }

  return (
    <ConfirmContext.Provider value={confirm}>
      {children}
      <Modal
        open={!!state}
        onClose={() => close(false)}
        title={state?.title}
        footer={state && (
          <>
            <Button variant="outline" onClick={() => close(false)}>{state.cancelText}</Button>
            <Button variant={state.tone === 'danger' ? 'danger' : 'primary'} loading={busy} onClick={onConfirm}>
              {state.confirmText}
            </Button>
          </>
        )}
      >
        {state && (
          <div className="flex gap-4">
            <div className={`grid h-11 w-11 shrink-0 place-items-center rounded-full ${state.tone === 'danger' ? 'bg-red-50 text-red-500' : 'bg-blue-50 text-blue-500'}`}>
              <FiAlertTriangle size={22} />
            </div>
            <p className="pt-1 text-sm leading-relaxed text-slate-600">{state.message}</p>
          </div>
        )}
      </Modal>
    </ConfirmContext.Provider>
  )
}

export function useConfirm() {
  const ctx = useContext(ConfirmContext)
  if (!ctx) throw new Error('useConfirm must be used within ConfirmProvider')
  return ctx
}
