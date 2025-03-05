import { fullCalendar } from './global/fullcalendar/index.js';
import { initializeDataTable } from './global/dataTables.js';
import { loadingSpinner } from './global/alerts.js';

let calendarInstance;
var element = '#calendar';
var api = 'api/inicio.php';

$(function() {

    calendarInstance = fullCalendar(element, {
        googleCalendarApiKey: 'AIzaSyAZYDsaS_Gv_8vievQAPLB4Cd8D6K2AoAM',

        eventSources:[ {
            googleCalendarId: 'atencion-alumnos@esmefis.edu.mx',
            color: '#f9cb7d',
            display: 'block',
            textColor: '#0951f5',
            className: 'hvr-shrink val_c'
            } ] ,

        themeSystem: 'bootstrap5',
        selectable: false,

        initialView: 'dayGridWeek',
        views:{
            dayGridWeek:{
                duration: { days: 8 },
            }
        },
        timeZone: 'local',
        locale: 'es',
        
        hiddenDays: [ 6 ],

        businessHours: [ // specify an array instead
            {
              daysOfWeek: [ 1, 2, 3, 4, 5 ], 
              startTime: '09:00', 
              endTime: '17:00' 
            },
            {
              daysOfWeek: [ 7 ], 
              startTime: '08:00', 
              endTime: '14:00' 
            }
        ],
        headerToolbar: {
            start: 'title', // will normally be on the left. if RTL, will be on the right
            center: '',
            end: '' // will normally be on the right. if RTL, will be on the left
        },
        dateClick: async  function(info) {
            $("#addEventModalLabel").html('Agregar alumno para el '+info.dateStr+'');
            $("#addEventModal").modal('show');
            $.post('modals/addEventModal.php', { date: info.dateStr }, function (data) {
                $('#addEventModalBody').html(data);
            });
        },

        eventClick: async function(info) {
            info.jsEvent.preventDefault();
            $("#eventDetailsModalBody").html('');
            loadingSpinner(true, '#eventDetailsModalBody');
            $('#eventDetailsModal').modal('show');
            $("#eventDetailsModalLabel").html(info.event.title);
            await $.post('modals/addEventModal.php', { eventId: info.event._def.publicId, eventData: info.event._def, dateTime : info.event._instance.range }, function (data) {                
                $('#eventDetailsModalBody').html(data);
            });
        },
    });
    
});
