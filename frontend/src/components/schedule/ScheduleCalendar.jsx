import { useEffect, useMemo, useRef, useState } from 'react'
import FullCalendar from '@fullcalendar/react'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import listPlugin from '@fullcalendar/list'
import interactionPlugin from '@fullcalendar/interaction'
import { resolveScheduleStatus, statusColor, toIso, fmtTime } from '../../utils/scheduleStatus'
import './calendar.css'

/**
 * FullCalendar with status colors, class times, taller cards,
 * and day-hover tooltips (lecture + topic).
 */
export default function ScheduleCalendar({
  schedules = [],
  editable = false,
  onEventClick,
  onDatesSet,
  onEventDrop,
  initialView = 'dayGridMonth',
  initialDate,
}) {
  const calRef = useRef(null)
  const byDateRef = useRef({})
  const tipRef = useRef(null)
  const [tick, setTick] = useState(0)
  const [tip, setTip] = useState(null)

  useEffect(() => {
    const id = setInterval(() => setTick((t) => t + 1), 60_000)
    return () => clearInterval(id)
  }, [])

  const byDate = useMemo(() => {
    const map = {}
    for (const s of schedules) {
      const d = String(s.class_date || '').slice(0, 10)
      if (!d) continue
      if (!map[d]) map[d] = []
      map[d].push(s)
    }
    return map
  }, [schedules])

  byDateRef.current = byDate

  const events = useMemo(
    () =>
      schedules.map((s) => {
        const status = resolveScheduleStatus(s)
        const color = statusColor(status)
        return {
          id: `${s.id}-${status}-${s.class_date}-${s.start_time}-${s.end_time}-${tick}`,
          title: s.lecture_title,
          start: toIso(s.class_date, s.start_time),
          end: toIso(s.class_date, s.end_time),
          backgroundColor: color,
          borderColor: color,
          textColor: '#ffffff',
          classNames: [`ngm-status-${status}`],
          extendedProps: { schedule: { ...s, status }, status, color },
        }
      }),
    [schedules, tick]
  )

  const handleDrop = (info) => {
    const s = info.event.extendedProps.schedule
    const start = info.event.start
    const end = info.event.end || new Date(start.getTime() + 60 * 60 * 1000)
    const pad = (n) => String(n).padStart(2, '0')
    const class_date = `${start.getFullYear()}-${pad(start.getMonth() + 1)}-${pad(start.getDate())}`
    const start_time = `${pad(start.getHours())}:${pad(start.getMinutes())}:00`
    const end_time = `${pad(end.getHours())}:${pad(end.getMinutes())}:00`
    onEventDrop?.(s, { class_date, start_time, end_time }, info.revert)
  }

  const paintEvent = (info) => {
    const color = info.event.extendedProps.color || info.event.backgroundColor
    if (!color) return
    info.el.style.setProperty('background-color', color, 'important')
    info.el.style.setProperty('border-color', color, 'important')
    info.el.style.color = '#ffffff'
  }

  const hideTip = () => setTip(null)

  const showTipForDay = (dateStr, clientX, clientY) => {
    const list = byDateRef.current[dateStr] || []
    if (!list.length) {
      setTip(null)
      return
    }
    setTip({
      dateStr,
      x: clientX,
      y: clientY,
      items: list.map((s) => ({
        lecture: s.lecture_title || 'Lecture',
        topic: s.topic_covered || s.subject || '',
        time: s.start_time
          ? (s.end_time ? `${fmtTime(s.start_time)} – ${fmtTime(s.end_time)}` : fmtTime(s.start_time))
          : '',
      })),
    })
  }

  const dayCellDidMount = (arg) => {
    const dateStr = arg.dateStr
      || `${arg.date.getFullYear()}-${String(arg.date.getMonth() + 1).padStart(2, '0')}-${String(arg.date.getDate()).padStart(2, '0')}`

    const onEnter = (e) => showTipForDay(dateStr, e.clientX, e.clientY)
    const onMove = (e) => {
      if (!(byDateRef.current[dateStr] || []).length) return
      setTip((prev) => (prev ? { ...prev, x: e.clientX, y: e.clientY } : prev))
    }
    const onLeave = (e) => {
      if (tipRef.current && tipRef.current.contains(e.relatedTarget)) return
      hideTip()
    }

    arg.el.addEventListener('mouseenter', onEnter)
    arg.el.addEventListener('mousemove', onMove)
    arg.el.addEventListener('mouseleave', onLeave)
  }

  return (
    <div className="ngm-calendar">
      <FullCalendar
        ref={calRef}
        plugins={[dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin]}
        initialView={initialView}
        initialDate={initialDate}
        headerToolbar={{
          left: 'prev,next today',
          center: 'title',
          right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek',
        }}
        buttonText={{ today: 'Today', month: 'Month', week: 'Week', day: 'Day', list: 'Agenda' }}
        events={events}
        editable={editable}
        eventDurationEditable={false}
        eventStartEditable={editable}
        dayMaxEvents={3}
        displayEventTime={false}
        nowIndicator
        height="auto"
        stickyHeaderDates
        slotMinTime="06:00:00"
        slotMaxTime="23:00:00"
        eventDidMount={paintEvent}
        dayCellDidMount={dayCellDidMount}
        eventClick={(info) => {
          info.jsEvent.preventDefault()
          onEventClick?.(info.event.extendedProps.schedule)
        }}
        eventDrop={handleDrop}
        datesSet={(arg) => onDatesSet?.(arg.startStr.slice(0, 10), arg.endStr.slice(0, 10))}
        eventContent={(arg) => {
          const s = arg.event.extendedProps.schedule
          const color = arg.event.extendedProps.color || statusColor(arg.event.extendedProps.status)
          const timeLabel = s?.start_time
            ? (s.end_time ? `${fmtTime(s.start_time)} – ${fmtTime(s.end_time)}` : fmtTime(s.start_time))
            : ''
          return (
            <div className="ngm-ev" style={{ backgroundColor: color, color: '#fff' }}>
              {timeLabel && <div className="ngm-ev-meta">{timeLabel}</div>}
              <div className="ngm-ev-title">{arg.event.title}</div>
              {s?.topic_covered && <div className="ngm-ev-meta">{s.topic_covered}</div>}
              {!s?.topic_covered && s?.teacher_name && (
                <div className="ngm-ev-meta ngm-ev-teacher">{s.teacher_name}</div>
              )}
            </div>
          )
        }}
      />

      {tip && (
        <div
          ref={tipRef}
          className="ngm-day-tip"
          style={{ left: Math.min(tip.x + 14, window.innerWidth - 280), top: tip.y + 14 }}
          role="tooltip"
        >
          <p className="ngm-day-tip-date">
            {new Date(`${tip.dateStr}T00:00:00`).toLocaleDateString(undefined, {
              weekday: 'short',
              month: 'short',
              day: 'numeric',
            })}
          </p>
          <ul className="ngm-day-tip-list">
            {tip.items.map((item, i) => (
              <li key={i}>
                <strong>{item.lecture}</strong>
                {item.topic ? <span className="ngm-day-tip-topic">{item.topic}</span> : null}
                {item.time ? <span className="ngm-day-tip-time">{item.time}</span> : null}
              </li>
            ))}
          </ul>
        </div>
      )}
    </div>
  )
}
