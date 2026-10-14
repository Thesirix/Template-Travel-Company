import { Calendar } from '@fullcalendar/core'
import multiMonthPlugin from '@fullcalendar/multimonth'


let calendar = new Calendar(calendarEl, {
    plugins: [multiMonthPlugin],
    initialView: 'multiMonthYear',
    events: [
        {
            title  : 'event1',
            start  : '2023-09-01'
        },
        {
            title  : 'event2',
            start  : '2023-09-01',
            end    : '2023-09-15'
        },
        {
            title  : 'event3',
            start  : '2010-01-09T12:30:00',
        }
    ]

})
