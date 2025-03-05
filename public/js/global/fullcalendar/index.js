export function fullCalendar(element, options) {
    let calendarEl = $(element)[0];
    let calendar = new FullCalendar.Calendar(calendarEl, options);
    calendar.render();
    return calendar;
}