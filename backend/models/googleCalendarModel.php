<?php
require __DIR__.'/../../php/vendor/autoload.php';

use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Vendor\Schoolarsystem\loadEnv;

date_default_timezone_set('America/Monterrey');
setlocale(LC_TIME, 'es_ES.UTF-8');
setlocale(LC_TIME, 'spanish');

loadEnv::cargar();

class GoogleCalendarModel{

    public function addEventCalendar($tittle, $date, $startEvent, $endEvent){
        putenv('GOOGLE_APPLICATION_CREDENTIALS=' . __DIR__ . '/calendario-alumnos.json');

        $client = new Google_Client();
        $client->useApplicationDefaultCredentials();
        $client->setScopes(['https://www.googleapis.com/auth/calendar']);  
        $calendarService = new Google_Service_Calendar($client);  

        $datetimeStart = new DateTime($date . ' ' . $startEvent);
        $datetimeEnd = new DateTime($date . ' ' . $endEvent);

        $timeStartFormat =$datetimeStart->format(\DateTime::RFC3339);
        $timeEndFormat = $datetimeEnd->format(\DateTime::RFC3339);

        $event = new Google_Service_Calendar_Event();
        $event->setSummary($tittle);
        $event->setDescription('Alumno '.$tittle. ' se registra para practicas clinicas');

        $start = new Google_Service_Calendar_EventDateTime();
        $start->setDateTime($timeStartFormat);

        $event->setStart($start);

        $end = new Google_Service_Calendar_EventDateTime();
        $end->setDateTime($timeEndFormat);

        $event->setEnd($end);

        try{
            $createdEvent = $calendarService->events->insert($_ENV['CALENDAR_ID'], $event);
            $eventId = $createdEvent->getId();
            $eventLink = $createdEvent->getHtmlLink();

            return [
                'success' => true,
                'eventId' => $eventId,
                'eventLink' => $eventLink
            ];
        }catch (Exception $e){
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];

            http_response_code(500);
            exit;
        }
    }

}