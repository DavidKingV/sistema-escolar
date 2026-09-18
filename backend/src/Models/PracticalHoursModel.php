<?php
namespace Vendor\Schoolarsystem\Models;

use Vendor\Schoolarsystem\Core\DatabaseHelper;
use Vendor\Schoolarsystem\DBConnection;

class PracticalHoursModel
{
    private $connection;
    private GoogleCalendarModel $googleCalendar;

    public function __construct(DBConnection $dbConnection)
    {
        $this->connection = $dbConnection->getConnection();
        $this->googleCalendar = new GoogleCalendarModel();
    }

    public function getEventDetails(string $eventId): array
    {
        $event = DatabaseHelper::selectOne(
            $this->connection,
            "
                SELECT
                    practical_hours.*,
                    DATE_FORMAT(start, '%H:%i') AS start,
                    DATE_FORMAT(end, '%H:%i') AS end
                FROM practical_hours
                WHERE googleCalendarId = ?;
            ",
            "s",
            [$eventId]
        );

        if (!$event["success"]) {
            return $event;
        }

        $confirmed = $event["data"]["hours"] !== null;

        return [
            "success" => true,
            "confirmed" => $confirmed,
            "message" => $confirmed
                ? "El evento ya fue confirmado."
                : "Datos obtenidos correctamente.",
            "data" => $event["data"]
        ];
    }

    public function studentsHours(): array
    {
        return DatabaseHelper::selectAll(
            $this->connection,
            "
                SELECT
                    latest.id_student AS studentId,
                    s.nombre,
                    latest.date,
                    latest.start,
                    latest.end,
                    latest.hours,
                    latest.total_hours
                FROM (
                    SELECT
                        ph.*,
                        SUM(hours) OVER (
                            PARTITION BY id_student
                        ) AS total_hours,
                        ROW_NUMBER() OVER (
                            PARTITION BY id_student
                            ORDER BY date DESC
                        ) AS row_number
                    FROM practical_hours ph
                ) latest
                INNER JOIN students s ON latest.id_student = s.id
                WHERE latest.row_number = 1;
            "
        );
    }

    public function getStudentHoursData(int $studentId): array
    {
        return DatabaseHelper::selectAll(
            $this->connection,
            "
                SELECT
                    ph.id,
                    ph.date,
                    ph.start,
                    ph.end,
                    ph.hours,
                    COALESCE(phs.status, 'Pendiente') AS status
                FROM practical_hours ph
                LEFT JOIN practical_hours_status phs
                    ON ph.status_id = phs.id
                WHERE ph.id_student = ?
                ORDER BY ph.date DESC;
            ",
            "i",
            [$studentId]
        );
    }

    public function addEvent(array $eventData): array
    {
        $calendar = $this->googleCalendar->addEventCalendar(
            $eventData["studentName"],
            $eventData["date"],
            $eventData["start"],
            $eventData["end"]
        );

        if (!$calendar["success"]) {
            return $calendar;
        }

        $insert = DatabaseHelper::insert(
            $this->connection,
            "
                INSERT INTO practical_hours (
                    googleCalendarId,
                    id_student,
                    date,
                    start,
                    end
                ) VALUES (?, ?, ?, ?, ?);
            ",
            "sisss",
            [
                $calendar["eventId"],
                $eventData["student"],
                $eventData["date"],
                $eventData["start"],
                $eventData["end"]
            ]
        );

        if (!$insert["success"]) {
            return $insert;
        }

        return [
            "success" => true,
            "message" => "Evento registrado correctamente.",
            "eventId" => $calendar["eventId"]
        ];
    }

    public function confirmHours(array $hoursData): array
    {
        return DatabaseHelper::update(
            $this->connection,
            "
                UPDATE practical_hours
                SET status_id = 1, hours = ?
                WHERE googleCalendarId = ?;
            ",
            "ss",
            [$hoursData["totalHours"], $hoursData["eventId"]]
        );
    }

    public function addStudentHours(array $hoursData): array
    {
        $existingHours = DatabaseHelper::selectOne(
            $this->connection,
            "
                SELECT id
                FROM practical_hours
                WHERE id_student = ? AND date = ?;
            ",
            "is",
            [$hoursData["studentId"], $hoursData["date"]]
        );

        if ($existingHours["success"]) {
            return [
                "success" => false,
                "message" => "Ya existen horas registradas para este alumno en la fecha seleccionada."
            ];
        }

        return DatabaseHelper::insert(
            $this->connection,
            "
                INSERT INTO practical_hours (
                    status_id,
                    id_student,
                    date,
                    start,
                    end,
                    hours
                ) VALUES (?, ?, ?, ?, ?, ?);
            ",
            "iissss",
            [
                1,
                $hoursData["studentId"],
                $hoursData["date"],
                $hoursData["start"],
                $hoursData["end"],
                $hoursData["totalHours"]
            ]
        );
    }

    public function deleteEvent(array $hoursData): array
    {
        return DatabaseHelper::update(
            $this->connection,
            "
                UPDATE practical_hours
                SET status_id = ?, hours = 0
                WHERE googleCalendarId = ?;
            ",
            "is",
            [$hoursData["deleteRazon"], $hoursData["eventId"]]
        );
    }

    public function deleteHour(int $hourId): array
    {
        return DatabaseHelper::delete(
            $this->connection,
            "
                DELETE
                FROM practical_hours
                WHERE id = ?;
            ",
            "i",
            [$hourId]
        );
    }
}
