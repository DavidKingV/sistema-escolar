<?php
namespace Vendor\Schoolarsystem\Controllers;

use Firebase\JWT\JWT;
use Vendor\Schoolarsystem\auth;
use Vendor\Schoolarsystem\DBConnection;
use Vendor\Schoolarsystem\PermissionHelper;
use Vendor\Schoolarsystem\Core\Validation;
use Vendor\Schoolarsystem\Models\StudentsModel;
use Vendor\Schoolarsystem\Models\LoginModel;

class StudentsController
{
    private DBConnection $connection;
    private StudentsModel $students;
    private LoginModel $login;

    public function __construct()
    {
        $this->connection = DBConnection::getInstance();
        $this->students = new StudentsModel($this->connection);
        $this->login = new LoginModel($this->connection);
    }

    public function getStudentById(int $studentId): array
    {
        if ($error = Validation::id($studentId)) {
            return $error;
        }

        return $this->students->getStudentById($studentId);
    }

    public function getAllStudents(): array
    {
        $response = $this->students->getAllStudents();

        if (!$response['success']) {
            return $response;
        }

        $secretKey = $_ENV['KEY'];
        $students = [];

        foreach ($response['data'] as $student) {
            $payload = [
                "studentId" => $student['id']
            ];

            $encodeJWT = JWT::encode(
                $payload,
                $secretKey,
                'HS256'
            );

            $students[] = [
                'success' => true,
                'encodeJWT' => $encodeJWT,
                'studentId' => $student['id'],
                'no_control' => $student['no_control'],
                'noControlSep' => $student['noControlSEP'],
                'name' => $student['nombre'],
                'phone' => $student['telefono'],
                'email' => $student['email'],
                'group_name' => $student['nombre_grupo'],
                'group_key' => $student['clave_grupo'],
                'academicalStatus' => $student['academical_status']
            ];
        }

        $user = auth::user();

        return [
            "success" => true,
            "permissions" => [
                "canManageStudents" => PermissionHelper::canAccess(
                    ['edit_students', 'delete_students'],
                    $user['permissions'] ?? [],
                    $user['isAdmin'] ?? false
                )
            ],
            "data" => $students,
            "message" => $response['message'] ?? ''
        ];
    }

    public function addStudent(array $studentData): array
    {
        if ($error = Validation::requiredArray($studentData)) {
            return $error;
        }

        if ($studentData['controlSepNumber'] === '') {
            $studentData['controlSepNumber'] = null;
        }

        $studentData['studentPhone'] =
            $studentData['countryCode'] . $studentData['studentPhone'];

        return $this->students->addStudent($studentData);
    }

    public function updateStudent(array $studentUpdateData): array
    {
        if ($error = Validation::requiredArray($studentUpdateData)) {
            return $error;
        }

        if (empty($studentUpdateData['controlSepNumber'])) {
            $studentUpdateData['controlSepNumber'] = null;
        }

        return $this->students->updateStudent($studentUpdateData);
    }

    public function deleteStudentById(int $studentId, string $password): array
    {
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

        return $this->students->deleteStudentById($studentId);
    }

    public function getAllStudentsUsers(): array
    {
        return $this->students->getAllStudentsUsers();
    }

    public function addStudentUser(array $studentUserData): array
    {
        if ($error = Validation::requiredArray($studentUserData)) {
            return $error;
        }

        return $this->students->addStudentUser($studentUserData);
    }

    public function updateStudentUser(array $studentUserUpdateData): array
    {
        if ($error = Validation::requiredArray($studentUserUpdateData)) {
            return $error;
        }

        return $this->students->updateStudentUser($studentUserUpdateData);
    }

    public function desactivateStudentUser(int $studentId): array
    {
        if ($error = Validation::id($studentId)) {
            return $error;
        }

        return $this->students->desactivateStudentUser($studentId);
    }

    public function reactivateStudentUser(int $studentId): array
    {
        if ($error = Validation::id($studentId)) {
            return $error;
        }

        return $this->students->reactivateStudentUser($studentId);
    }

    public function getMicrosoftStudentsUsers(): array
    {
        return $this->students->getMicrosoftStudentsUsers();
    }

    public function findMicrosoftUser(string $studentName): array
    {
        if ($error = Validation::string($studentName)) {
            return $error;
        }

        $user = auth::user();

        $accessToken = $user['accessToken'] ?? null;

        return $this->students->findMicrosoftUser(
            $studentName,
            $accessToken
        );
    }

    public function assignMicrosoftUserToStudent(array $studentUserData): array
    {
        if ($error = Validation::requiredArray($studentUserData)) {
            return $error;
        }

        $data = $studentUserData['dataStudentUser'] ?? $studentUserData;

        if (is_string($data)) {
            parse_str($data, $data);
        }

        if (!is_array($data)) {
            return [
                "success" => false,
                "message" => "Los datos del usuario de Microsoft son inválidos."
            ];
        }

        $normalizedData = [
            "studentId" => $data["studentId"] ?? null,
            "microsoftUserId" => $data["microsoftUserId"]
                ?? $data["microsoftId"]
                ?? null,
            "microsoftDisplayName" => $data["microsoftDisplayName"]
                ?? $data["displayName"]
                ?? null,
            "microsoftEmail" => $data["microsoftEmail"]
                ?? $data["mail"]
                ?? null
        ];

        if (in_array(null, $normalizedData, true)) {
            return [
                "success" => false,
                "message" => "Faltan datos para asignar el usuario de Microsoft."
            ];
        }

        return $this->students->assignMicrosoftUserToStudent($normalizedData);
    }

    public function verifyStudentToken(int $studentId, string $token): array
    {
        if ($error = Validation::id($studentId)) {
            return $error;
        }

        if ($error = Validation::string($token)) {
            return $error;
        }

        return $this->students->verifyStudentToken($studentId, $token);
    }

    public function getStudentName(int $studentId): array
    {
        if ($error = Validation::id($studentId)) {
            return $error;
        }

        return $this->students->getStudentName($studentId);
    }

    public function verifyStudentGroup(int $studentIdGroup): array
    {
        if ($error = Validation::id($studentIdGroup)) {
            return $error;
        }

        return $this->students->verifyStudentGroup($studentIdGroup);
    }

    public function getSubjectNames(int $carrerId): array
    {
        if ($error = Validation::id($carrerId)) {
            return $error;
        }

        return $this->students->getSubjectNames($carrerId);
    }

    public function getStudentGrades(int $studentId): array
    {
        if ($error = Validation::id($studentId)) {
            return $error;
        }

        return $this->students->getStudentGrades($studentId);
    }

    public function getChildSubjectNames(int $idSubject): array
    {
        if ($error = Validation::id($idSubject)) {
            return $error;
        }

        return $this->students->getChildSubjectNames($idSubject);
    }

    public function addStudentGrade(array $studentGradeData): array
    {
        if ($error = Validation::requiredArray($studentGradeData)) {
            return $error;
        }

        return $this->students->addStudentGrade($studentGradeData);
    }

    public function getGroupNames(): array
    {
        return $this->students->getGroupNames();
    }

    public function addStudentToGroup(array $studentGroupData): array
    {
        if ($error = Validation::requiredArray($studentGroupData)) {
            return $error;
        }

        return $this->students->addStudentToGroup($studentGroupData);
    }

    public function verifyStudentUser(string $studentUser): array
    {
        if ($error = Validation::string($studentUser)) {
            return $error;
        }

        return $this->students->verifyStudentUser($studentUser);
    }

    public function getStudentsNames(): array
    {
        return $this->students->getStudentsNames();
    }

    // *****************************************************************************************
    // API Methods
    // *****************************************************************************************

    public function updateStatus(array $statusData): array
    {
        if ($error = Validation::requiredArray($statusData)) {
            return $error;
        }

        return $this->students->updateStatus($statusData);
    }

    public function getStudentsListSelect(): array
    {
        $search = $_GET["search"] ?? "";
        $page = max(1, (int) ($_GET["page"] ?? 1));
        $limit = 30;

        $students = $this->students->getStudentsListSelect(
            $search,
            $page,
            $limit
        );

        if (!$students["success"]) {
            return [
                "results" => [],
                "pagination" => ["more" => false],
                "total_count" => 0
            ];
        }

        $total = $this->students->getStudentsCount($search);

        $results = array_map(
            static fn($student) => [
                "id" => $student["id"],
                "text" => $student["nombre"]
            ],
            $students["data"]
        );

        return [
            "results" => $results,
            "pagination" => [
                "more" => ($page * $limit) < $total
            ],
            "total_count" => $total
        ];
    }
}
