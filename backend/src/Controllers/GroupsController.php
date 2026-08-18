<?php
namespace Vendor\Schoolarsystem\Controllers;

use Vendor\Schoolarsystem\auth;
use Vendor\Schoolarsystem\DBConnection;
use Vendor\Schoolarsystem\PermissionHelper;
use Vendor\Schoolarsystem\Core\Validation;
use Vendor\Schoolarsystem\Models\GroupsModel;
use Vendor\Schoolarsystem\Models\LoginModel;

class GroupsController
{
    private DBConnection $connection;
    private GroupsModel $groups;
    private LoginModel $login;

    public function __construct()
    {
        $this->connection = DBConnection::getInstance();
        $this->groups = new GroupsModel($this->connection);
        $this->login = new LoginModel($this->connection);
    }

    public function getGroupById(int $groupId): array
    {
        if ($error = Validation::id($groupId)) {
            return $error;
        }

        return $this->groups->getGroupById($groupId);
    }

    public function getAllGroups(): array
    {
        $response = $this->groups->getAllGroups();

        if (!$response['success']) {
            return $response;
        }

        $user = auth::user();

        return [
            "success" => $response['success'],
            "permissions" => [
                "canManageGroups" => PermissionHelper::canAccess(
                    ['edit_groups', 'delete_groups'],
                    $user['permissions'] ?? [],
                    $user['isAdmin'] ?? false
                )
            ],
            "data" => $response['data'],
            "message" => $response['message'] ?? ''
        ];
    }

    public function addGroup(array $groupData): array
    {
        if ($error = Validation::requiredArray($groupData)) {
            return $error;
        }

        return $this->groups->addGroup($groupData);
    }

    public function updateGroup(array $groupUpdateData): array
    {
        if ($error = Validation::requiredArray($groupUpdateData)) {
            return $error;
        }

        return $this->groups->updateGroup($groupUpdateData);
    }

    public function deleteGroupById(int $groupId, string $password): array
    {
        if ($error = Validation::id($groupId)) {
            return $error;
        }

        if ($error = Validation::password($password)) {
            return $error;
        }

        $userId = $_SESSION['userId'];

        if (!$this->login->verifyUserPassword($userId, $password)) {
            return [
                "success" => false,
                "message" => "Contraseña incorrecta."
            ];
        }

        return $this->groups->deleteGroupById($groupId);
    }

    public function getStudentsByGroupId(int $groupId): array
    {
        if ($error = Validation::id($groupId)) {
            return $error;
        }

        return $this->groups->getStudentsByGroupId($groupId);
    }

    public function addStudentToGroup(int $groupId, array $studentId): array
    {
        if ($error = Validation::id($groupId)) {
            return $error;
        }

        if ($error = Validation::requiredArray($studentId)) {
            return $error;
        }

        return $this->groups->addStudentToGroup($groupId, $studentId);
    }

    public function removeStudentFromGroup(int $groupId, int $studentId, string $password): array
    {
        if ($error = Validation::id($groupId)) {
            return $error;
        }

        if ($error = Validation::id($studentId)) {
            return $error;
        }

        if ($error = Validation::password($password)) {
            return $error;
        }

        $userId = $_SESSION['userId'];

        if (!$this->login->verifyUserPassword($userId, $password)) {
            return [
                "success" => false,
                "message" => "Contraseña incorrecta."
            ];
        }

        return $this->groups->removeStudentFromGroup($groupId, $studentId);
    }

    public function getCarreersForGroupCreation(): array
    {
        return $this->groups->getCarreersForGroupCreation();
    }

    public function getDuplicateStudents(): array
    {
        return $this->groups->getDuplicateStudents();
    }

    public function getStudentDuplicateGroups(int $studentId): array
    {
        if ($error = Validation::id($studentId)) {
            return $error;
        }

        return $this->groups->getStudentDuplicateGroups($studentId);
    }

    public function resolveDuplicate(int $studentId, int $correctGroupId): array
    {
        if ($error = Validation::id($studentId)) {
            return $error;
        }

        if ($error = Validation::id($correctGroupId)) {
            return $error;
        }

        return $this->groups->resolveDuplicate($studentId, $correctGroupId);
    }

    // *****************************************************************************************
    // API Methods
    // *****************************************************************************************

    public function getNoGroupStudentsList(): array
    {
        $search = $_GET["search"] ?? "";
        $page = max(1, (int) ($_GET["page"] ?? 1));
        $limit = 30;
        $groupId = (int) ($_GET["groupId"] ?? 0);

        $students = $this->groups->getNoGroupStudentsList(
            $search,
            $page,
            $limit,
            $groupId
        );

        $total = $this->groups->getGroupsCount($search);

        $results = array_map(
            static fn($student) => [
                "id" => $student["id"],
                "text" => $student["nombre"]
            ],
            $students
        );

        return [
            "results" => $results,
            "pagination" => [
                "more" => ($page * $limit) < $total
            ],
            "total_count" => $total
        ];
    }

    public function getGroupCareer(int $studentId): array
    {
        if ($error = Validation::id($studentId)) {
            return $error;
        }

        return $this->groups->getGroupCareer($studentId);
    }

    public function getGroupSchedules(int $groupId): array
    {
        if ($error = Validation::id($groupId)) {
            return $error;
        }

        return $this->groups->getGroupSchedules($groupId);
    }

    public function addSchedule(array $scheduleData): array
    {
        if ($error = Validation::requiredArray($scheduleData)) {
            return $error;
        }

        $addSchedule = $this->groups->addSchedule($scheduleData);
        return $addSchedule;
    }
}
