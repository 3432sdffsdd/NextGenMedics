import { useMemo, useRef } from 'react'
import FullCalendar from '@fullcalendar/react'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import listPlugin from '@fullcalendar/list'
import interactionPlugin from '@fullcalendar/interaction'
import { statusColor, toIso } from '../../utils/scheduleStatus'
import './calendar.css'

/**
 * Themed FullCalendar with month / week / day / agenda(list) views,
 * status color-coding, click-to-detail and drag-drop rescheduling.
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

  const events = useMemo(
    () =>
      schedules.map((s) => {
        const color = statusColor(s.status)
        return {
          id: String(s.id),
          title: s.lecture_title,
          start: toIso(s.class_date, s.start_time),
          end: toIso(s.class_date, s.end_time),
          backgroundColor: color,
          borderColor: color,
          textColor: '#ffffff',
          extendedProps: { schedule: s },
        }
      }),
    [schedules]
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
        nowIndicator
        height="auto"
        stickyHeaderDates
        slotMinTime="06:00:00"
        slotMaxTime="23:00:00"
        eventClick={(info) => {
          info.jsEvent.preventDefault()
          onEventClick?.(info.event.extendedProps.schedule)
        }}
        eventDrop={handleDrop}
        datesSet={(arg) => onDatesSet?.(arg.startStr.slice(0, 10), arg.endStr.slice(0, 10))}
        eventContent={(arg) => {
          const s = arg.event.extendedProps.schedule
          return (
            <div className="ngm-ev">
              <div className="ngm-ev-title">{arg.event.title}</div>
              {s?.teacher_name && <div className="ngm-ev-meta">{s.teacher_name}</div>}
            </div>
          )
        }}
      />
    </div>
  )
}
