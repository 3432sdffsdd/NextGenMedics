import { createContext, useContext, useState, useCallback } from 'react'
import { authService } from '../services/api'

const AuthContext = createContext(null)

export function AuthProvider({ children }) {
  const [user, setUser] = useState(() => {
    try {
      const stored = localStorage.getItem('ngm_user')
      return stored ? JSON.parse(stored) : null
    } catch {
      localStorage.removeItem('ngm_user')
      return null
    }
  })
  const [loading, setLoading] = useState(false)

  const login = useCallback(async (email, password) => {
    setLoading(true)
    // Clear any stale auth state so a leftover/expired token can't interfere.
    localStorage.removeItem('ngm_token')
    localStorage.removeItem('ngm_user')
    try {
      const { data } = await authService.login(email.trim(), password)
      localStorage.setItem('ngm_token', data.token)
      localStorage.setItem('ngm_user', JSON.stringify(data.user))
      setUser(data.user)
      return { success: true, user: data.user }
    } catch (err) {
      const status = err.response?.status
      const data = err.response?.data
      const serverMessage = data && typeof data === 'object' ? data.message : null

      let message
      if (status === 401) {
        message = serverMessage || 'Invalid email or password.'
      } else if (data?.errors) {
        message = 'Please check your email and password.'
      } else if (err.code === 'ERR_NETWORK') {
        message = 'Cannot reach the server. Make sure the backend API is running.'
      } else if (status) {
        // A non-401 HTTP error (e.g. 500) with no usable JSON message.
        message = serverMessage || `Login failed (HTTP ${status}). Please try again.`
      } else {
        message = 'Something went wrong. Please try again.'
      }

      if (import.meta.env.DEV) {
        console.error('[login] failed', { status, code: err.code, data })
      }
      return { success: false, message }
    } finally {
      setLoading(false)
    }
  }, [])

  const logout = useCallback(() => {
    authService.logout()
    setUser(null)
    window.location.href = '/'
  }, [])

  return (
    <AuthContext.Provider value={{ user, loading, login, logout }}>
      {children}
    </AuthContext.Provider>
  )
}

// eslint-disable-next-line react-refresh/only-export-components
export function useAuth() {
  const ctx = useContext(AuthContext)
  if (!ctx) throw new Error('useAuth must be used within AuthProvider')
  return ctx
}
