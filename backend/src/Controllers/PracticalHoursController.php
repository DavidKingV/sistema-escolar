<?php
namespace Vendor\Schoolarsystem\Controllers;

use Vendor\Schoolarsystem\auth;
use Vendor\Schoolarsystem\DBConnection;
use Vendor\Schoolarsystem\Core\Validation;
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
}
