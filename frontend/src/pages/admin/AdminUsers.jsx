import { useCallback, useEffect, useState } from 'react'
import { FiPlus, FiEdit2, FiTrash2, FiKey, FiPause, FiPlay } from 'react-icons/fi'
import { adminUsersService } from '../../services/api'
import { useConfirm } from '../../context/ConfirmContext'
import { useToast } from '../../context/ToastContext'
import Alert from '../../components/dashboard/Alert'

const emptyForm = { username: '', email: '', first_name: '', last_name: '', phone: '' }

export default function AdminUsers({ role, title }) {
  const confirm = useConfirm()
  const toast = useToast()
  const [users, setUsers] = useState([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')
  const [message, setMessage] = useState('')
  const [showForm, setShowForm] = useState(false)
  const [form, setForm] = useState(emptyForm)
  const [editId, setEditId] = useState(null)
  const [tempPassword, setTempPassword] = useState('')

  const load = useCallback(() => {
    setLoading(true)
    setError('')
    adminUsersService
      .list(role, { per_page: 50 })
      .then(({ data }) => {
        const items = Array.isArray(data.data) ? data.data : []
        setUsers(items)
      })
      .catch((err) => {
        setUsers([])
        setError(err.response?.data?.message || 'Failed to load users. Check API is running.')
      })
      .finally(() => setLoading(false))
  }, [role])

  useEffect(() => { load() }, [load])

  const openCreate = () => {
    setEditId(null)
    setForm(emptyForm)
    setShowForm(true)
    setTempPassword('')
  }

  const openEdit = (user) => {
    setEditId(user.id)
    setForm({
      username: user.username,
      email: user.email,
      first_name: user.first_name,
      last_name: user.last_name,
      phone: user.phone || '',
    })
    setShowForm(true)
  }

  const handleSubmit = async (e) => {
    e.preventDefault()
    setError('')
    setMessage('')
    try {
      if (editId) {
        await adminUsersService.update(editId, form)
        setMessage('User updated successfully')
      } else {
        const { data } = await adminUsersService.create(role, form)
        const created = data.data
        setTempPassword(created?.generated_password || '')
        setMessage('User created. Share the password with the user.')
        if (created?.id) {
          setUsers((prev) => [created, ...prev.filter((u) => u.id !== created.id)])
        }
      }
      setShowForm(false)
      load()
    } catch (err) {
      setError(err.response?.data?.message || 'Operation failed')
    }
  }

  const handleReset = async (id) => {
    if (!(await confirm({ title: 'Reset password', message: 'Reset password for this user?', confirmText: 'Yes, reset', tone: 'primary' }))) return
    try {
      const { data } = await adminUsersService.resetPassword(id)
      setTempPassword(data.data?.password)
      toast.success('Password reset successfully')
      setMessage('Password reset. Share the new password with the user.')
    } catch {
      setError('Reset failed')
      toast.error('Reset failed')
    }
  }

  const handleSuspend = async (id) => {
    await adminUsersService.suspend(id)
    load()
  }

  const handleActivate = async (id) => {
    await adminUsersService.activate(id)
    load()
  }

  const handleDelete = async (id) => {
    if (!(await confirm({ title: 'Delete user', message: 'Are you sure you want to delete this user?', confirmText: 'Yes, Delete', tone: 'danger' }))) return
    try {
      await adminUsersService.delete(id)
      toast.success('Deleted successfully')
      load()
    } catch {
      toast.error('Could not delete user')
    }
  }

  return (
    <div>
      <div className="flex items-center justify-between">
        <div>
          <h2 className="font-display text-xl font-bold text-navy">{title}</h2>
          <p className="text-sm text-slate-500">Create, edit, suspend, and manage {role}s</p>
        </div>
        <button type="button" onClick={openCreate} className="btn-primary text-sm">
          <FiPlus /> Add {title.slice(0, -1)}
        </button>
      </div>

      {error && <div className="mt-4"><Alert>{error}</Alert></div>}
      {message && <div className="mt-4"><Alert type="success">{message}{tempPassword && ` New password: ${tempPassword}`}</Alert></div>}

      {showForm && (
        <form onSubmit={handleSubmit} className="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-soft">
          <h3 className="font-semibold text-navy">{editId ? 'Edit' : 'Create'} {title.slice(0, -1)}</h3>
          <div className="mt-4 grid gap-4 sm:grid-cols-2">
            {['username', 'email', 'first_name', 'last_name', 'phone'].map((field) => (
              <div key={field}>
                <label className="mb-1 block text-xs font-medium text-slate-500 capitalize">
                  {field.replace('_', ' ')}{field === 'phone' ? ' (for WhatsApp class reminders)' : ''}
                </label>
                <input
                  required={field !== 'phone'}
                  type={field === 'email' ? 'email' : 'text'}
                  className="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"
                  value={form[field]}
                  onChange={(e) => setForm({ ...form, [field]: e.target.value })}
                />
              </div>
            ))}
          </div>
          <div className="mt-4 flex gap-3">
            <button type="submit" className="btn-primary text-sm">Save</button>
            <button type="button" onClick={() => setShowForm(false)} className="btn-secondary text-sm">Cancel</button>
          </div>
        </form>
      )}

      <div className="mt-6 overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-soft">
        {loading ? (
          <p className="p-6 text-sm text-slate-500">Loading...</p>
        ) : (
          <table className="w-full text-left text-sm">
            <thead className="border-b border-slate-100 bg-slate-50 text-xs uppercase text-slate-500">
              <tr>
                <th className="px-4 py-3">Name</th>
                <th className="px-4 py-3">Email</th>
                <th className="px-4 py-3">Username</th>
                <th className="px-4 py-3">Status</th>
                <th className="px-4 py-3 text-right">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {users.map((u) => (
                <tr key={u.id} className="hover:bg-slate-50">
                  <td className="px-4 py-3 font-medium text-navy">{u.first_name} {u.last_name}</td>
                  <td className="px-4 py-3 text-slate-600">{u.email}</td>
                  <td className="px-4 py-3 text-slate-600">{u.username}</td>
                  <td className="px-4 py-3">
                    <span className={`rounded-full px-2 py-0.5 text-xs font-medium ${
                      u.status === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'
                    }`}>
                      {u.status}
                    </span>
                  </td>
                  <td className="px-4 py-3">
                    <div className="flex justify-end gap-1">
                      <button type="button" onClick={() => openEdit(u)} className="rounded-lg p-2 text-slate-500 hover:bg-slate-100" title="Edit">
                        <FiEdit2 size={16} />
                      </button>
                      <button type="button" onClick={() => handleReset(u.id)} className="rounded-lg p-2 text-slate-500 hover:bg-slate-100" title="Reset password">
                        <FiKey size={16} />
                      </button>
                      {u.status === 'active' ? (
                        <button type="button" onClick={() => handleSuspend(u.id)} className="rounded-lg p-2 text-amber-600 hover:bg-amber-50" title="Suspend">
                          <FiPause size={16} />
                        </button>
                      ) : (
                        <button type="button" onClick={() => handleActivate(u.id)} className="rounded-lg p-2 text-green-600 hover:bg-green-50" title="Activate">
                          <FiPlay size={16} />
                        </button>
                      )}
                      <button type="button" onClick={() => handleDelete(u.id)} className="rounded-lg p-2 text-red-500 hover:bg-red-50" title="Delete">
                        <FiTrash2 size={16} />
                      </button>
                    </div>
                  </td>
                </tr>
              ))}
              {users.length === 0 && (
                <tr><td colSpan={5} className="px-4 py-8 text-center text-slate-400">No users yet. Create one above.</td></tr>
              )}
            </tbody>
          </table>
        )}
      </div>
    </div>
  )
}
