import { Link } from 'react-router-dom'
import { FiArrowLeft } from 'react-icons/fi'

export default function NotFound() {
  return (
    <div className="flex min-h-[70vh] flex-col items-center justify-center bg-white px-6 text-center">
      <p className="font-display text-8xl font-extrabold text-primary">404</p>
      <h1 className="mt-4 font-display text-3xl font-bold text-navy">Page Not Found</h1>
      <p className="mt-3 max-w-md text-slate-500">
        The page you're looking for doesn't exist or has been moved.
      </p>
      <Link to="/" className="btn-primary mt-8">
        <FiArrowLeft /> Back to Home
      </Link>
    </div>
  )
}
