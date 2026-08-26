import { useEffect, useRef } from 'react';
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import listPlugin from '@fullcalendar/list';
import interactionPlugin from '@fullcalendar/interaction';

function addDays(date, days) {
    const result = new Date(date);
    result.setDate(result.getDate() + days);
    return result;
}

function buildEvents(today) {
    return [
        { title: 'Past Event', start: addDays(today, -2).toISOString().split('T')[0], classNames: ['fc-event-info'] },
        { title: 'All Day Event', start: addDays(today, 2).toISOString().split('T')[0], classNames: ['fc-event-info'] },
        { title: 'Long Event', start: addDays(today, 2).toISOString().split('T')[0], end: addDays(today, 5).toISOString().split('T')[0], classNames: ['fc-event-primary'] },
        { title: 'Confirm tech stack', start: addDays(today, 0).toISOString().split('T')[0] + 'T10:00:00', end: addDays(today, 0).toISOString().split('T')[0] + 'T18:00:00', classNames: ['fc-event-success'] },
        { groupId: '999', title: 'Coding session', start: addDays(today, 1).toISOString().split('T')[0] + 'T16:00:00', classNames: ['fc-event-secondary'] },
        { groupId: '999', title: 'Coding session', start: addDays(today, 8).toISOString().split('T')[0] + 'T16:00:00', classNames: ['fc-event-secondary'] },
        { title: 'Conference', start: addDays(today, 9).toISOString().split('T')[0], end: addDays(today, 10).toISOString().split('T')[0], classNames: ['fc-event-primary'] },
        { title: 'Meeting', start: addDays(today, 9).toISOString().split('T')[0] + 'T10:30:00', end: addDays(today, 9).toISOString().split('T')[0] + 'T12:30:00', classNames: ['fc-event-error'] },
        { title: 'Lunch', start: addDays(today, 9).toISOString().split('T')[0] + 'T12:40:00', classNames: ['fc-event-warning'] },
        { title: 'Meeting', start: addDays(today, 9).toISOString().split('T')[0] + 'T14:30:00', classNames: ['fc-event-error'] },
        { title: 'Picnic', start: addDays(today, 12).toISOString().split('T')[0], classNames: ['fc-event-success'] },
        { title: 'Yoga', start: addDays(today, 15).toISOString().split('T')[0], classNames: ['fc-event-info'] },
        { title: 'Credit Card Payment', start: addDays(today, 23).toISOString().split('T')[0], end: addDays(today, 24).toISOString().split('T')[0], classNames: ['fc-event-warning'] },
        { title: 'Meeting with client', start: addDays(today, 27).toISOString().split('T')[0], classNames: ['fc-event-success'] },
        { start: addDays(today, 17).toISOString().split('T')[0], end: addDays(today, 20).toISOString().split('T')[0], display: 'background', classNames: ['fc-event-disabled'] },
    ];
}

export default function FullCalendarWidget() {
    const containerRef = useRef(null);
    const calendarRef = useRef(null);
    const today = new Date();

    useEffect(() => {
        if (!containerRef.current || calendarRef.current) return;

        let selectedEvent = null;
        let selectedDateInfo = null;
        const events = buildEvents(today);
        const blockedStart = addDays(today, 17).getTime();
        const blockedEnd = addDays(today, 20).getTime();

        const calendar = new Calendar(containerRef.current, {
            plugins: [dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin],
            initialView: 'dayGridMonth',
            initialDate: today.toISOString().split('T')[0],
            editable: true,
            dragScroll: true,
            dayMaxEvents: 2,
            direction: 'ltr',
            eventResizableFromStart: true,
            selectable: true,
            headerToolbar: {
                left: 'prev,next title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth',
            },
            buttonText: { month: 'Month', week: 'Week', day: 'Day', list: 'List' },
            events,
            select(info) {
                const selectedStart = info.start.getTime();
                const selectedEnd = info.end ? info.end.getTime() : selectedStart;
                if ((selectedStart < blockedEnd && selectedEnd > blockedStart)) {
                    alert('Events cannot be added in the blocked date range.');
                    calendar.unselect();
                    return;
                }
                selectedEvent = null;
                selectedDateInfo = info;
                const title = window.prompt('Event title', '');
                if (title) {
                    calendar.addEvent({
                        title,
                        start: selectedDateInfo.startStr,
                        end: selectedDateInfo.endStr,
                        allDay: true,
                    });
                }
            },
            eventClick(info) {
                const title = window.prompt('Event title', info.event.title);
                if (title) {
                    info.event.setProp('title', title);
                }
            },
            eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
            allDayText: 'All day',
        });

        calendar.render();
        calendarRef.current = calendar;

        return () => {
            calendar.destroy();
            calendarRef.current = null;
        };
    }, []);

    return (
        <div id="calendar-custom" ref={containerRef} style={{ minHeight: 400 }}></div>
    );
}