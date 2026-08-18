<?php
namespace Vendor\Schoolarsystem\Controllers;

use Vendor\Schoolarsystem\DBConnection;
use Vendor\Schoolarsystem\Core\Validation;
use Vendor\Schoolarsystem\Models\SubjectsModel;
use Vendor\Schoolarsystem\Models\LoginModel;

class SubjectsController
{
    private DBConnection $connection;
    private SubjectsModel $subjects;
    private LoginModel $login;

    public function __construct()
    {
        $this->connection = DBConnection::getInstance();
        $this->subjects = new SubjectsModel($this->connection);
        $this->login = new LoginModel($this->connection);
    }

    public function getSubjectById(int $subjectId): array
    {
        if ($error = Validation::id($subjectId)) {
            return $error;
        }

        return $this->subjects->getSubjectById($subjectId);
    }

    public function getAllSubjects(): array
    {
        return $this->subjects->getAllSubjects();
    }

    public function addSubject(array $subjectData): array
    {
        if ($error = Validation::requiredArray($subjectData)) {
            return $error;
        }

        return $this->subjects->addSubject($subjectData);
    }

    public function updateSubject(array $subjectUpdateData): array
    {
        if ($error = Validation::requiredArray($subjectUpdateData)) {
            return $error;
        }

        return $this->subjects->updateSubject($subjectUpdateData);
    }

    public function deleteSubjectById(int $subjectId, string $password): array
    {
        if ($error = Validation::id($subjectId)) {
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

        return $this->subjects->deleteSubjectById($subjectId);
    }

    public function getChildSubjectFindById(int $subjectChildId, int $subjectFatherId): array
    {
        if ($error = Validation::id($subjectChildId)) {
            return $error;
        }

        if ($error = Validation::id($subjectFatherId)) {
            return $error;
        }

        return $this->subjects->getChildSubjectFindById($subjectChildId, $subjectFatherId);
    }

    public function addSubjectChild(array $subjectChildData): array
    {
        if ($error = Validation::requiredArray($subjectChildData)) {
            return $error;
        }

        return $this->subjects->addSubjectChild($subjectChildData);
    }

    public function updateSubjectChild(array $subjectChildUpdateData): array
    {
        if ($error = Validation::requiredArray($subjectChildUpdateData)) {
            return $error;
        }

        return $this->subjects->updateSubjectChild($subjectChildUpdateData);
    }

    public function deleteSubjectChildById(int $subjectChildId, string $password): array
    {
        if ($error = Validation::id($subjectChildId)) {
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

        return $this->subjects->deleteSubjectChildById($subjectChildId);
    }

    // *****************************************************************************************
    // API Methods
    // *****************************************************************************************

    public function getSubjectsListSelect(int $careerId): array
    {
        $search = $_POST['search'] ?? '';
        $page = intval($_POST['page'] ?? 1);
        $limit = 30;

        $subjectsList = $this->subjects->getSubjectsListSelect(
            $search,
            $page,
            $limit,
            $careerId
        );

        if (!$subjectsList['success']) {
            return [
                'results' => [],
                'pagination' => ['more' => false],
                'total_count' => 0
            ];
        }

        $subjectsTotal = $this->subjects->getSubjectsCount(
            $search,
            $careerId
        );

        $subjects = array_map(
            static fn($subject) => [
                'id' => $subject['id'],
                'text' => $subject['nombre']
            ],
            $subjectsList['data']
        );

        return [
            'results' => $subjects,
            'pagination' => [
                'more' => ($page * $limit) < $subjectsTotal
            ],
            'total_count' => $subjectsTotal
        ];
    }

    public function getChildSubject(int $subjectId): array
    {
        if ($error = Validation::id($subjectId)) {
            return $error;
        }

        $response = $this->subjects->getChildSubject($subjectId);

        if (!$response['success'] || $response['data'] === []) {
            return [[
                'success' => false,
                'message' => $response['message']
                    ?? 'No se encontraron materias hijas.'
            ]];
        }

        return array_map(
            static fn($subject) => [
                'success' => true,
                'childSubjectId' => $subject['id'],
                'childSubjectClave' => $subject['clave'],
                'childSubjectName' => $subject['nombre']
            ],
            $response['data']
        );
    }

    public function subjectsListTable(int $careerId): array
    {
        if ($error = Validation::id($careerId)) {
            return $error;
        }

        return $this->subjects->subjectsListTable($careerId);
    }

    public function addSubjectCareer(array $subjectData): array
    {
        if ($error = Validation::requiredArray($subjectData)) {
            return $error;
        }

        return $this->subjects->addSubjectCareer($subjectData);
    }

}
