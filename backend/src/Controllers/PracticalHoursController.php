<?php
namespace Vendor\Schoolarsystem\Controllers;

use Vendor\Schoolarsystem\auth;
use Vendor\Schoolarsystem\DBConnection;
use Vendor\Schoolarsystem\Core\Response;
use Vendor\Schoolarsystem\Core\Validation;
use Vendor\Schoolarsystem\Core\View;
use Vendor\Schoolarsystem\PermissionHelper;
use Vendor\Schoolarsystem\Models\PracticalHoursModel;

class PracticalHoursController
{
    private DBConnection $connection;
    private PracticalHoursModel $practicalHours;

    public function __construct()
    {
        $this->connection = DBConnection::getInstance();
        $this->practicalHours = new PracticalHoursModel($this->connection);
    }

    public function getEventDetails(string $eventId): array
    {
        if ($error = Validation::string($eventId)) {
            return $error;
        }

        return $this->practicalHours->getEventDetails($eventId);
    }

    public function addEventModal(array $modalData): Response
    {
        $date = (string) ($modalData['date'] ?? '');

        if ($date !== '' && !$this->isValidDate($date)) {
            return $this->modalValidationError('La fecha del evento no es válida.');
        }

        return Response::html(View::modal('addEvent.Modal.php', [
            'date' => $date
        ]));
    }

    public function eventDetailsModal(array $modalData): Response
    {
        if ($error = Validation::string($modalData['eventId'] ?? null)) {
            return $this->modalValidationError($error['message']);
        }

        return Response::html(View::modal('eventDetails.Modal.php', [
            'eventId' => (string) $modalData['eventId']
        ]));
    }

    public function addHoursModal(array $modalData): Response
    {
        if ($error = Validation::id($modalData['studentId'] ?? null)) {
            return $this->modalValidationError($error['message']);
        }

        return Response::html(View::modal('addHours.Modal.php', [
            'studentId' => (int) $modalData['studentId']
        ]));
    }

    public function seeTotalModal(array $modalData): Response
    {
        if ($error = Validation::id($modalData['studentId'] ?? null)) {
            return $this->modalValidationError($error['message']);
        }

        $totalHours = $modalData['totalHours'] ?? null;

        if (!is_scalar($totalHours) || trim((string) $totalHours) === '') {
            return $this->modalValidationError('El total de horas no es válido.');
        }

        return Response::html(View::modal('seeTotal.Modal.php', [
            'studentId' => (int) $modalData['studentId'],
            'totalHours' => (string) $totalHours
        ]));
    }

    public function studentsHours(): array
    {
        return $this->practicalHours->studentsHours();
    }

    public function getStudentlHoursData(int $studentId): array
    {
        if ($error = Validation::id($studentId)) {
            return $error;
        }

        $response = $this->practicalHours->getStudentHoursData($studentId);

        if (!$response['success']) {
            return $response;
        }

        $user = auth::user();

        return [
            "success" => true,
            "permissions" => [
                "canManageStudents" => PermissionHelper::canAccess(
                    ['delete_practical_hours'],
                    $user['permissions'] ?? [],
                    $user['isAdmin'] ?? false
                )
            ],
            "data" => $response['data'],
            "message" => $response['message'] ?? ''
        ];
    }

    public function addEvent(array $eventData): array
    {
        $eventDataArray = $this->normalizeData($eventData, 'eventData');

        if ($error = Validation::requiredArray($eventDataArray)) {
            return $error;
        }

        return $this->practicalHours->addEvent($eventDataArray);
    }

    public function confirmHours(array $hoursData): array
    {
        $hoursDataArray = $this->normalizeData($hoursData, 'hoursData');

        if ($error = Validation::requiredArray($hoursDataArray)) {
            return $error;
        }

        return $this->practicalHours->confirmHours($hoursDataArray);
    }

    public function addStudentHours(array $hoursData): array
    {
        $hoursDataArray = $this->normalizeData($hoursData, 'data');

        if ($error = Validation::requiredArray($hoursDataArray)) {
            return $error;
        }

        return $this->practicalHours->addStudentHours($hoursDataArray);
    }

    public function deleteEvent(array $hoursData): array
    {
        $hoursDataArray = $this->normalizeData($hoursData, 'hoursData');

        if ($error = Validation::requiredArray($hoursDataArray)) {
            return $error;
        }

        return $this->practicalHours->deleteEvent($hoursDataArray);
    }

    public function deleteHour(int $hourId): array
    {
        if ($error = Validation::id($hourId)) {
            return $error;
        }

        return $this->practicalHours->deleteHour($hourId);
    }

    private function normalizeData(array $requestData, string $key): array
    {
        $data = $requestData[$key] ?? $requestData;

        if (is_string($data)) {
            parse_str($data, $data);
        }

        return is_array($data) ? $data : [];
    }

    private function isValidDate(string $date): bool
    {
        $parsedDate = \DateTimeImmutable::createFromFormat('!Y-m-d', $date);

        return $parsedDate !== false && $parsedDate->format('Y-m-d') === $date;
    }

    private function modalValidationError(string $message): Response
    {
        return Response::json([
            'success' => false,
            'message' => $message
        ], 422);
    }
}
