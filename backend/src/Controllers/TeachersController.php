<?php
namespace Vendor\Schoolarsystem\Controllers;

use Vendor\Schoolarsystem\DBConnection;
use Vendor\Schoolarsystem\Core\SensitiveActionAuthorizer;
use Vendor\Schoolarsystem\Core\Validation;
use Vendor\Schoolarsystem\Models\TeachersModel;

class TeachersController
{
    private DBConnection $connection;
    private TeachersModel $teachers;
    private SensitiveActionAuthorizer $sensitiveActions;

    public function __construct()
    {
        $this->connection = DBConnection::getInstance();
        $this->teachers = new TeachersModel($this->connection);
        $this->sensitiveActions = new SensitiveActionAuthorizer();
    }

    public function getTeacherById(int $teacherId): array
    {
        if ($error = Validation::id($teacherId)) {
            return $error;
        }

        return $this->teachers->getTeacherById($teacherId);
    }

    public function getAllTeachers(): array
    {
        return $this->teachers->getAllTeachers();
    }

    public function addTeacher(array $teacherData): array
    {
        if ($error = Validation::requiredArray($teacherData)) {
            return $error;
        }

        return $this->teachers->addTeacher($teacherData);
    }

    public function updateTeacher(array $teacherUpdateData): array
    {
        if ($error = Validation::requiredArray($teacherUpdateData)) {
            return $error;
        }

        return $this->teachers->updateTeacher($teacherUpdateData);
    }

    public function deleteTeacherById(int $teacherId, ?string $password = null): array
    {
        if ($error = Validation::id($teacherId)) {
            return $error;
        }

        $authorization = $this->sensitiveActions->authorize($password);

        if (!$authorization['success']) {
            return $authorization;
        }

        return $this->teachers->deleteTeacherById($teacherId);
    }

    public function getAllTeachersUsers(): array
    {
        return $this->teachers->getAllTeachersUsers();
    }

    public function addTeacherUser(array $teacherUserData): array
    {
        if ($error = Validation::requiredArray($teacherUserData)) {
            return $error;
        }

        return $this->teachers->addTeacherUser($teacherUserData);
    }

    public function updateTeacherUserData(array $teacherUserUpdateData): array
    {
        if ($error = Validation::requiredArray($teacherUserUpdateData)) {
            return $error;
        }

        return $this->teachers->updateTeacherUserData($teacherUserUpdateData);
    }

    public function verifyTeacherByUser(string $teacherUser): array
    {
        if ($error = Validation::string($teacherUser)) {
            return $error;
        }

        return $this->teachers->verifyTeacherByUser($teacherUser);
    }

    public function desactivateTeacherUser(int $teacherUserId): array
    {
        if ($error = Validation::id($teacherUserId)) {
            return $error;
        }

        return $this->teachers->desactivateTeacherUser($teacherUserId);
    }

    public function reactivateTeacherUser(int $teacherUserId): array
    {
        if ($error = Validation::id($teacherUserId)) {
            return $error;
        }

        return $this->teachers->reactivateTeacherUser($teacherUserId);
    }
}
