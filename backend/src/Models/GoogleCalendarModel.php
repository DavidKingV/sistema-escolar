<?php
namespace Vendor\Schoolarsystem\Models;


use Vendor\Schoolarsystem\loadEnv;

date_default_timezone_set('America/Monterrey');
setlocale(LC_TIME, 'es_ES.UTF-8');
setlocale(LC_TIME, 'spanish');

loadEnv::cargar();

class GoogleCalendarModel
{

    public function addEventCalendar(
        string $title,
        string $date,
        string $startEvent,
        string $endEvent
    ): array {
        putenv('GOOGLE_APPLICATION_CREDENTIALS=' . __DIR__ . '/calendario-alumnos.json');

        $client = new \Google\Client();
        $client->useApplicationDefaultCredentials();
        $client->setScopes(['https://www.googleapis.com/auth/calendar']);
        $calendarService = new \Google\Service\Calendar($client);

        $datetimeStart = new \DateTime($date . ' ' . $startEvent);
        $datetimeEnd = new \DateTime($date . ' ' . $endEvent);

        $timeStartFormat = $datetimeStart->format(\DateTime::RFC3339);
        $timeEndFormat = $datetimeEnd->format(\DateTime::RFC3339);

        $event = new \Google\Service\Calendar\Event();
        $event->setSummary($title);
        $event->setDescription(
            'Alumno ' . $title . ' se registra para prácticas clínicas'
        );

        $start = new \Google\Service\Calendar\EventDateTime();
        $start->setDateTime($timeStartFormat);

        $event->setStart($start);

        $end = new \Google\Service\Calendar\EventDateTime();
        $end->setDateTime($timeEndFormat);

        $event->setEnd($end);

        try {
            $createdEvent = $calendarService->events->insert($_ENV['CALENDAR_ID'], $event);
            $eventId = $createdEvent->getId();
            $eventLink = $createdEvent->getHtmlLink();

            return [
                'success' => true,
                'eventId' => $eventId,
                'eventLink' => $eventLink
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'No fue posible registrar el evento en Google Calendar.'
            ];
        }
    }

}
