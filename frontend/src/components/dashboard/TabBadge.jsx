/** Red badge for unread tab notifications. */
export default function TabBadge({ count = 0 }) {
  if (!count || count <= 0) return null
  return (
    <span className="ml-1.5 inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-red-500 px-1.5 text-[10px] font-bold leading-none text-white">
      {count > 9 ? '9+' : count}
    </span>
  )
}
