import { motion } from 'framer-motion'
import { FiTrendingUp, FiAward, FiCheckCircle, FiVideo } from 'react-icons/fi'

const subjects = [
  { name: 'Medicine', value: 88 },
  { name: 'Surgery', value: 76 },
  { name: 'Physiology', value: 82 },
  { name: 'Biochemistry', value: 69 },
]

const sparkline = [30, 45, 38, 60, 52, 75, 68, 90]

export default function DashboardMockup() {
  return (
    <div className="relative mx-auto max-w-xl">
      {/* main dashboard card */}
      <div className="relative rounded-3xl border border-slate-100 bg-white p-5 shadow-soft-lg sm:p-6">
        <div className="mb-5 flex items-center justify-between">
          <div>
            <p className="text-xs text-slate-400">Welcome back</p>
            <h3 className="font-display text-base font-bold text-navy">My Learning Dashboard</h3>
          </div>
          <div className="flex items-center gap-2">
            <span className="h-2.5 w-2.5 rounded-full bg-success" />
            <img
              src="https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=80&h=80&fit=crop&crop=faces"
              alt="user"
              className="h-9 w-9 rounded-full object-cover"
            />
          </div>
        </div>

        <div className="grid grid-cols-2 gap-3">
          {/* progress */}
          <div className="rounded-2xl bg-lightbg p-4">
            <p className="text-xs text-slate-400">Overall Progress</p>
            <p className="mt-1 font-display text-3xl font-extrabold text-navy">78%</p>
            <div className="mt-2 h-2 w-full overflow-hidden rounded-full bg-slate-200">
              <motion.div
                initial={{ width: 0 }}
                whileInView={{ width: '78%' }}
                viewport={{ once: true }}
                transition={{ duration: 1.2, delay: 0.3 }}
                className="h-full rounded-full bg-gradient-to-r from-primary to-primary-light"
              />
            </div>
          </div>

          {/* weekly activity sparkline */}
          <div className="rounded-2xl bg-navy p-4 text-white">
            <div className="flex items-center justify-between">
              <p className="text-xs text-slate-300">This Week</p>
              <span className="flex items-center gap-1 text-xs font-semibold text-primary-light">
                <FiTrendingUp size={12} /> +12%
              </span>
            </div>
            <div className="mt-3 flex h-12 items-end gap-1">
              {sparkline.map((h, i) => (
                <motion.div
                  key={i}
                  initial={{ height: 0 }}
                  whileInView={{ height: `${h}%` }}
                  viewport={{ once: true }}
                  transition={{ duration: 0.6, delay: i * 0.06 }}
                  className="flex-1 rounded-t bg-gradient-to-t from-primary to-primary-light"
                />
              ))}
            </div>
          </div>
        </div>

        {/* stat chips */}
        <div className="mt-3 grid grid-cols-3 gap-3">
          {[
            { label: 'MCQs Solved', value: '12,450', accent: 'text-primary' },
            { label: 'Mock Tests', value: '28', accent: 'text-navy' },
            { label: 'Avg Score', value: '92%', accent: 'text-success' },
          ].map((s) => (
            <div key={s.label} className="rounded-2xl border border-slate-100 p-3 text-center">
              <p className={`font-display text-lg font-extrabold ${s.accent}`}>{s.value}</p>
              <p className="text-[11px] text-slate-400">{s.label}</p>
            </div>
          ))}
        </div>

        {/* subject performance */}
        <div className="mt-3 rounded-2xl bg-lightbg p-4">
          <p className="mb-3 text-xs font-semibold text-navy">Subject Performance</p>
          <div className="space-y-2.5">
            {subjects.map((s, i) => (
              <div key={s.name} className="flex items-center gap-3">
                <span className="w-20 shrink-0 text-[11px] text-slate-500">{s.name}</span>
                <div className="h-1.5 flex-1 overflow-hidden rounded-full bg-slate-200">
                  <motion.div
                    initial={{ width: 0 }}
                    whileInView={{ width: `${s.value}%` }}
                    viewport={{ once: true }}
                    transition={{ duration: 1, delay: 0.2 + i * 0.1 }}
                    className="h-full rounded-full bg-primary"
                  />
                </div>
                <span className="w-8 text-right text-[11px] font-semibold text-navy">{s.value}%</span>
              </div>
            ))}
          </div>
        </div>
      </div>

      {/* floating card: mock result */}
      <motion.div
        animate={{ y: [0, -12, 0] }}
        transition={{ duration: 6, repeat: Infinity, ease: 'easeInOut' }}
        className="absolute -left-6 top-24 hidden rounded-2xl border border-slate-100 bg-white p-3.5 shadow-soft-lg sm:flex sm:items-center sm:gap-3"
      >
        <span className="flex h-10 w-10 items-center justify-center rounded-xl bg-success/15 text-success">
          <FiAward size={18} />
        </span>
        <div>
          <p className="text-[11px] text-slate-400">Mock Test Result</p>
          <p className="font-display text-sm font-bold text-navy">Passed · 92%</p>
        </div>
      </motion.div>

      {/* floating card: quiz score */}
      <motion.div
        animate={{ y: [0, 12, 0] }}
        transition={{ duration: 6, repeat: Infinity, ease: 'easeInOut', delay: 1.5 }}
        className="absolute -right-4 bottom-28 hidden rounded-2xl border border-slate-100 bg-white p-3.5 shadow-soft-lg sm:flex sm:items-center sm:gap-3"
      >
        <span className="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/15 text-primary">
          <FiCheckCircle size={18} />
        </span>
        <div>
          <p className="text-[11px] text-slate-400">Quiz Score</p>
          <p className="font-display text-sm font-bold text-navy">45 / 50</p>
        </div>
      </motion.div>

      {/* floating card: live class */}
      <motion.div
        animate={{ y: [0, -10, 0] }}
        transition={{ duration: 5, repeat: Infinity, ease: 'easeInOut', delay: 0.8 }}
        className="absolute -right-6 top-6 hidden rounded-2xl bg-primary p-3.5 text-white shadow-glow md:flex md:items-center md:gap-3"
      >
        <span className="flex h-9 w-9 items-center justify-center rounded-xl bg-white/20">
          <FiVideo size={16} />
        </span>
        <div>
          <p className="text-[11px] text-white/80">Live Class</p>
          <p className="text-sm font-bold">Physiology · Now</p>
        </div>
      </motion.div>
    </div>
  )
}
