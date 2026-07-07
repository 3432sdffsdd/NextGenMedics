import { useState } from 'react'
import { Link, useNavigate, Navigate } from 'react-router-dom'
import { motion } from 'framer-motion'
import { FiMail, FiLock, FiEye, FiEyeOff, FiArrowLeft, FiAlertCircle } from 'react-icons/fi'
import { useAuth } from '../context/AuthContext'
import { dashboardPathByRole } from '../services/api'
import Logo from '../components/common/Logo'

export default function Login() {
  const { login, loading, user } = useAuth()
  const navigate = useNavigate()
  const [form, setForm] = useState({ email: '', password: '' })
  const [showPwd, setShowPwd] = useState(false)
  const [error, setError] = useState('')

  if (user) {
    return <Navigate to={dashboardPathByRole[user.role] || '/'} replace />
  }

  const handleSubmit = async (e) => {
    e.preventDefault()
    setError('')
    const res = await login(form.email, form.password)
    if (res.success) {
      navigate(dashboardPathByRole[res.user.role] || '/')
    } else {
      setError(res.message)
    }
  }

  return (
    <div className="grid min-h-screen lg:grid-cols-2">
      {/* Left: branding panel */}
      <div className="relative hidden overflow-hidden bg-navy lg:block">
        <div className="pointer-events-none absolute -left-20 top-20 h-80 w-80 rounded-full bg-primary/25 blur-3xl" />
        <div className="pointer-events-none absolute bottom-10 right-0 h-72 w-72 rounded-full bg-primary-light/15 blur-3xl" />
        <div className="pointer-events-none absolute inset-0 bg-grid-pattern opacity-30" />
        <div className="relative flex h-full flex-col justify-between p-12">
          <Logo light />
          <div>
            <h2 className="font-display text-4xl font-extrabold leading-tight text-white">
              Welcome back to your <span className="text-primary-light">learning journey</span>.
            </h2>
            <p className="mt-4 max-w-md text-slate-300">
              Access your dashboard, live lectures, regular assessments, mock exams and performance
              analytics — all in one place.
            </p>
            <div className="mt-8 flex gap-8">
              {[
                ['500+', 'Lectures'],
                ['95%', 'Success Rate'],
              ].map(([v, l]) => (
                <div key={l}>
                  <p className="font-display text-2xl font-extrabold text-white">{v}</p>
                  <p className="text-sm text-slate-400">{l}</p>
                </div>
              ))}
            </div>
          </div>
          <p className="text-sm text-slate-500">© {new Date().getFullYear()} NextGen Medics</p>
        </div>
      </div>

      {/* Right: form */}
      <div className="flex items-center justify-center bg-white px-6 py-12">
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.4 }}
          className="w-full max-w-md"
        >
          <Link to="/" className="mb-8 inline-flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-primary">
            <FiArrowLeft /> Back to home
          </Link>

          <div className="lg:hidden">
            <Logo />
          </div>

          <h1 className="mt-6 font-display text-3xl font-extrabold text-navy">Login</h1>
          <p className="mt-2 text-slate-500">
            Sign in with your administrator, teacher, or student account.
          </p>

          {error && (
            <div className="mt-5 flex items-center gap-2 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600">
              <FiAlertCircle /> {error}
            </div>
          )}

          <form onSubmit={handleSubmit} className="mt-7 space-y-5">
            <div>
              <label className="mb-1.5 block text-sm font-semibold text-navy">Email Address</label>
              <div className="relative">
                <FiMail className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" />
                <input
                  type="email"
                  required
                  value={form.email}
                  onChange={(e) => setForm({ ...form, email: e.target.value })}
                  placeholder="you@example.com"
                  className="w-full rounded-2xl border border-slate-200 bg-lightbg py-3.5 pl-11 pr-4 text-navy outline-none transition-all focus:border-primary focus:bg-white focus:ring-2 focus:ring-primary/20"
                />
              </div>
            </div>

            <div>
              <label className="mb-1.5 block text-sm font-semibold text-navy">Password</label>
              <div className="relative">
                <FiLock className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" />
                <input
                  type={showPwd ? 'text' : 'password'}
                  required
                  value={form.password}
                  onChange={(e) => setForm({ ...form, password: e.target.value })}
                  placeholder="••••••••"
                  className="w-full rounded-2xl border border-slate-200 bg-lightbg py-3.5 pl-11 pr-11 text-navy outline-none transition-all focus:border-primary focus:bg-white focus:ring-2 focus:ring-primary/20"
                />
                <button
                  type="button"
                  onClick={() => setShowPwd((v) => !v)}
                  className="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-primary"
                >
                  {showPwd ? <FiEyeOff /> : <FiEye />}
                </button>
              </div>
            </div>

            <div className="flex items-center justify-between text-sm">
              <label className="flex items-center gap-2 text-slate-500">
                <input type="checkbox" className="rounded border-slate-300 text-primary focus:ring-primary" />
                Remember me
              </label>
              <a href="#" className="font-semibold text-primary hover:underline">
                Forgot password?
              </a>
            </div>

            <button type="submit" disabled={loading} className="btn-primary w-full disabled:opacity-60">
              {loading ? 'Signing in…' : 'Sign In'}
            </button>
          </form>

          <p className="mt-6 rounded-xl bg-lightbg px-4 py-3 text-center text-sm text-slate-500">
            Accounts are created by an administrator. Contact support if you need access.
          </p>
        </motion.div>
      </div>
    </div>
  )
}
