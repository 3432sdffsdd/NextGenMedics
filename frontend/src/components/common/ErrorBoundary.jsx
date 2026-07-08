import { Component } from 'react'

export default class ErrorBoundary extends Component {
  state = { error: null }

  static getDerivedStateFromError(error) {
    return { error }
  }

  render() {
    if (this.state.error) {
      const msg = this.state.error?.message || String(this.state.error)
      return (
        <div className="flex min-h-screen flex-col items-center justify-center bg-slate-50 px-6 py-12 text-center">
          <h1 className="font-display text-xl font-bold text-navy">Something went wrong</h1>
          <p className="mt-2 max-w-md text-sm text-slate-600">
            The page could not load. Try refreshing, or clear site data and log in again.
          </p>
          {import.meta.env.DEV && (
            <pre className="mt-4 max-w-lg overflow-auto rounded-lg bg-red-50 p-3 text-left text-xs text-red-700">{msg}</pre>
          )}
          <button
            type="button"
            onClick={() => window.location.reload()}
            className="btn-primary mt-6 text-sm"
          >
            Reload page
          </button>
        </div>
      )
    }
    return this.props.children
  }
}
