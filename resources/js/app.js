import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.withCredentials = true;

// Get CSRF token from meta tag
const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found');
}

// Import Bootstrap JavaScript
import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;
import 'admin-lte/dist/js/adminlte.min.js';
import EasyMDE from 'easymde';
window.EasyMDE = EasyMDE;

// Cropper.js
import Cropper from 'cropperjs';
import 'cropperjs/dist/cropper.css';
window.Cropper = Cropper;

// Tempus Dominus (Bootstrap 5 DateTime Picker)
import { TempusDominus, loadLocale, locale } from '@eonasdan/tempus-dominus';
import '@eonasdan/tempus-dominus/dist/css/tempus-dominus.css';

// Thai locale for Tempus Dominus
const thaiLocale = {
    today: 'วันนี้',
    clear: 'ล้าง',
    close: 'ปิด',
    selectMonth: 'เลือกเดือน',
    previousMonth: 'เดือนก่อน',
    nextMonth: 'เดือนถัดไป',
    selectYear: 'เลือกปี',
    previousYear: 'ปีก่อน',
    nextYear: 'ปีถัดไป',
    selectDecade: 'เลือกทศวรรษ',
    previousDecade: 'ทศวรรษก่อน',
    nextDecade: 'ทศวรรษถัดไป',
    previousCentury: 'ศตวรรษก่อน',
    nextCentury: 'ศตวรรษถัดไป',
    pickHour: 'เลือกชั่วโมง',
    incrementHour: 'เพิ่มชั่วโมง',
    decrementHour: 'ลดชั่วโมง',
    pickMinute: 'เลือกนาที',
    incrementMinute: 'เพิ่มนาที',
    decrementMinute: 'ลดนาที',
    pickSecond: 'เลือกวินาที',
    incrementSecond: 'เพิ่มวินาที',
    decrementSecond: 'ลดวินาที',
    toggleMeridiem: 'สลับ AM/PM',
    selectTime: 'เลือกเวลา',
    selectDate: 'เลือกวันที่',
    dayViewHeaderFormat: { month: 'long', year: 'numeric' },
    locale: 'th',
    hourCycle: 'h23',
    startOfTheWeek: 0,
    dateFormats: {
        LT: 'HH:mm',
        LTS: 'HH:mm:ss',
        L: 'DD/MM/YYYY',
        LL: 'D MMMM YYYY',
        LLL: 'D MMMM YYYY HH:mm',
        LLLL: 'dddd D MMMM YYYY HH:mm'
    }
};

loadLocale(thaiLocale);
locale(thaiLocale.locale);

window.TempusDominus = TempusDominus;
window.TempusDominusLocale = thaiLocale;

// Import and configure Laravel Echo
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

if (import.meta.env.VITE_REVERB_APP_KEY) {
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: import.meta.env.VITE_REVERB_APP_KEY,
        wsHost: import.meta.env.VITE_REVERB_HOST,
        wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
        wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
        forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
        enabledTransports: ['ws', 'wss'],
        authorizer: (channel, options) => {
            return {
                authorize: (socketId, callback) => {
                    axios.post('/broadcasting/auth', {
                        socket_id: socketId,
                        channel_name: channel.name
                    }, {
                        withCredentials: true,
                        headers: {
                            'X-CSRF-TOKEN': token ? token.content : '',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => {
                        console.log('Broadcasting auth success:', response.data);
                        callback(null, response.data);
                    })
                    .catch(error => {
                        console.error('Broadcasting auth error:', error.response?.data || error.message);
                        callback(error);
                    });
                }
            };
        },
    });
} else {
    console.warn('VITE_REVERB_APP_KEY is missing. Echo (real-time features) will not be initialized.');
}

import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import bootstrap5Plugin from '@fullcalendar/bootstrap5';

document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    if (!calendarEl) return;

    function getHeaderToolbar(width) {
        if (width < 500) {
            return {
                left: 'prev,next',
                center: 'title',
                right: 'timeGridDay,timeGridWeek,dayGridMonth'
            };
        } else if (width < 768) {
            return {
                left: 'prev,next',
                center: 'title',
                right: 'dayGridMonth,timeGridDay,listMonth'
            };
        } else {
            return {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            };
        }
    }

    var calendar = new Calendar(calendarEl, {
        plugins: [ dayGridPlugin, timeGridPlugin, interactionPlugin, bootstrap5Plugin ],
        themeSystem: 'bootstrap5',
        initialView: 'dayGridMonth',
        headerToolbar: getHeaderToolbar(calendarEl.offsetWidth),
        events: calendarEl.dataset.eventRoute || '/dash/calendar/events',
        eventClick: function(info) {
            var props = info.event.extendedProps;
            
            // Populate Modal
            document.getElementById('modalRoom').textContent = props.room_name;
            document.getElementById('modalTitle').textContent = props.booking_name;
            document.getElementById('modalOwner').textContent = props.owner_name;
            document.getElementById('modalTime').textContent = props.start_formatted + ' - ' + props.end_formatted;

            // Attendees
            var attendeeList = document.getElementById('modalAttendeesList');
            var attendeeSection = document.getElementById('modalAttendeesSection');
            attendeeList.innerHTML = '';
            
            if (props.attendees && props.attendees.length > 0) {
                 attendeeSection.style.display = 'block';
                 
                 var attendeesData = props.attendees;
                 if (typeof attendeesData === 'string') {
                     attendeesData = attendeesData.split(', ');
                 }
                 
                 attendeesData.forEach(function(att) {
                     var li = document.createElement('li');
                     if (typeof att === 'string') {
                         li.textContent = att;
                     } else {
                         li.textContent = att.user_name || att.user_identify || 'Unknown'; 
                     }
                     attendeeList.appendChild(li);
                 });
            } else {
                attendeeSection.style.display = 'none';
            }

            // Detail Button
            var detailBtn = document.getElementById('modalDetailBtn');
            if (props.can_view_detail) {
                detailBtn.style.display = 'inline-block';
                detailBtn.href = props.detail_url;
            } else {
                detailBtn.style.display = 'none';
            }

            // Show Modal
            var myModal = new bootstrap.Modal(document.getElementById('bookingModal'));
            myModal.show();
        },
        eventTimeFormat: { // like '14:30:00'
            hour: '2-digit',
            minute: '2-digit',
            meridiem: false
        }
    });

    calendar.render();

    // Auto-resize calendar when container size changes (e.g. sidebar toggle)
    const resizeObserver = new ResizeObserver(entries => {
        for (let entry of entries) {
            calendar.updateSize();
            const width = entry.contentRect.width;
            calendar.setOption('headerToolbar', getHeaderToolbar(width));
            
            if (width < 500) {
                calendarEl.classList.add('fc-toolbar-stack');
            } else {
                calendarEl.classList.remove('fc-toolbar-stack');
            }
        }
    });
    resizeObserver.observe(calendarEl);
});
