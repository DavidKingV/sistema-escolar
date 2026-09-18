<?php
namespace Vendor\Schoolarsystem\Controllers;

use DateTimeImmutable;
use Vendor\Schoolarsystem\auth;
use Vendor\Schoolarsystem\DBConnection;
use Vendor\Schoolarsystem\PermissionHelper;
use Vendor\Schoolarsystem\Core\Response;
use Vendor\Schoolarsystem\Core\SensitiveActionAuthorizer;
use Vendor\Schoolarsystem\Core\Validation;
use Vendor\Schoolarsystem\Core\View;
use Vendor\Schoolarsystem\Models\GroupsModel;

class GroupsController
{
    private DBConnection $connection;
    private GroupsModel $groups;
    private SensitiveActionAuthorizer $sensitiveActions;

    public function __construct()
    {
        $this->connection = DBConnection::getInstance();
        $this->groups = new GroupsModel($this->connection);
        $this->sensitiveActions = new SensitiveActionAuthorizer();
    }

    public function getGroupById(mixed $groupId = null): Response
    {
        if ($forbidden = $this->authorize(['manage_groups'])) {
            return $forbidden;
        }

        if ($error = Validation::id($groupId)) {
            return Response::json($error, 422);
        }

        return $this->modelResponse($this->groups->getGroupById((int) $groupId));
    }

    public function groupsEditModal(array $modalData): Response
    {
        if ($forbidden = $this->authorize(['edit_groups'])) {
            return $forbidden;
        }

        if ($error = Validation::id($modalData['groupId'] ?? null)) {
            return Response::json($error, 422);
        }

        $groupId = (int) $modalData['groupId'];
        $group = $this->groups->getGroupById($groupId);

        if (!($group['success'] ?? false)) {
            return $this->modelResponse($group);
        }

        return Response::html(View::modal('GroupsEditModal.php', [
            'groupId' => $groupId
        ]));
    }

    public function groupDuplicatesModal(): Response
    {
        if ($forbidden = $this->authorize(['manage_groups', 'edit_groups'])) {
            return $forbidden;
        }

        return Response::html(View::modal('groupDuplicates.modal.php'));
    }

    public function getAllGroups(): Response
    {
        if ($forbidden = $this->authorize(['manage_groups'])) {
            return $forbidden;
        }

        $response = $this->groups->getAllGroups();

        if (!($response['success'] ?? false)) {
            return $this->modelResponse($response);
        }

        $user = auth::user();
        $permissions = $user['permissions'] ?? [];
        $isAdmin = $user['isAdmin'] ?? false;
        $response['permissions'] = [
            'canManageGroups' => PermissionHelper::canAccess(
                ['edit_groups', 'delete_groups'],
                $permissions,
                $isAdmin
            ),
            'canEditGroups' => PermissionHelper::canAccess(
                ['edit_groups'],
                $permissions,
                $isAdmin
            ),
            'canDeleteGroups' => PermissionHelper::canAccess(
                ['delete_groups'],
                $permissions,
                $isAdmin
            )
        ];

        return Response::json($response);
    }

    public function addGroup(array $groupData): Response
    {
        if ($forbidden = $this->authorize(['add_groups'])) {
            return $forbidden;
        }

        $validation = $this->validateGroupData($groupData, false);

        if (!$validation['success']) {
            return Response::json($validation, 422);
        }

        return $this->modelResponse(
            $this->groups->addGroup($validation['data']),
            201
        );
    }

    public function updateGroup(array $groupUpdateData): Response
    {
        if ($forbidden = $this->authorize(['edit_groups'])) {
            return $forbidden;
        }

        $validation = $this->validateGroupData($groupUpdateData, true);

        if (!$validation['success']) {
            return Response::json($validation, 422);
        }

        return $this->modelResponse($this->groups->updateGroup($validation['data']));
    }

    public function deleteGroupById(
        mixed $groupId = null,
        ?string $password = null
    ): Response {
        if ($forbidden = $this->authorize(['delete_groups'])) {
            return $forbidden;
        }

        if ($error = Validation::id($groupId)) {
            return Response::json($error, 422);
        }

        if ($authorization = $this->sensitiveAuthorization($password)) {
            return $authorization;
        }

        return $this->modelResponse($this->groups->deleteGroupById((int) $groupId));
    }

    public function getStudentsByGroupId(mixed $groupId = null): Response
    {
        if ($forbidden = $this->authorize(['manage_groups'])) {
            return $forbidden;
        }

        if ($error = Validation::id($groupId)) {
            return Response::json($error, 422);
        }

        return $this->modelResponse(
            $this->groups->getStudentsByGroupId((int) $groupId)
        );
    }

    public function addStudentToGroup(
        mixed $groupId = null,
        mixed $studentId = null
    ): Response {
        if ($forbidden = $this->authorize(['manage_groups', 'edit_groups'])) {
            return $forbidden;
        }

        if ($error = Validation::id($groupId)) {
            return Response::json($error, 422);
        }

        if (!is_array($studentId) || $studentId === []) {
            return Response::json([
                'success' => false,
                'message' => 'Debe seleccionar al menos un alumno.'
            ], 422);
        }

        $studentIds = [];

        foreach ($studentId as $id) {
            if (Validation::id($id)) {
                return Response::json([
                    'success' => false,
                    'message' => 'La lista de alumnos contiene un ID inválido.'
                ], 422);
            }

            $studentIds[] = (int) $id;
        }

        return $this->modelResponse(
            $this->groups->addStudentToGroup(
                (int) $groupId,
                array_values(array_unique($studentIds))
            )
        );
    }

    public function removeStudentFromGroup(
        mixed $groupId = null,
        mixed $studentId = null,
        ?string $password = null
    ): Response {
        if ($forbidden = $this->authorize(['manage_groups', 'edit_groups'])) {
            return $forbidden;
        }

        foreach ([$groupId, $studentId] as $value) {
            if ($error = Validation::id($value)) {
                return Response::json($error, 422);
            }
        }

        if ($authorization = $this->sensitiveAuthorization($password)) {
            return $authorization;
        }

        return $this->modelResponse(
            $this->groups->removeStudentFromGroup((int) $groupId, (int) $studentId)
        );
    }

    public function getCarreersForGroupCreation(): Response
    {
        if ($forbidden = $this->authorize(['manage_groups', 'add_groups', 'edit_groups'])) {
            return $forbidden;
        }

        return $this->modelResponse($this->groups->getCarreersForGroupCreation());
    }

    public function getDuplicateStudents(): Response
    {
        if ($forbidden = $this->authorize(['manage_groups', 'edit_groups'])) {
            return $forbidden;
        }

        return $this->modelResponse($this->groups->getDuplicateStudents());
    }

    public function getStudentDuplicateGroups(mixed $studentId = null): Response
    {
        if ($forbidden = $this->authorize(['manage_groups', 'edit_groups'])) {
            return $forbidden;
        }

        if ($error = Validation::id($studentId)) {
            return Response::json($error, 422);
        }

        return $this->modelResponse(
            $this->groups->getStudentDuplicateGroups((int) $studentId)
        );
    }

    public function resolveDuplicate(
        mixed $studentId = null,
        mixed $correctGroupId = null
    ): Response {
        if ($forbidden = $this->authorize(['manage_groups', 'edit_groups'])) {
            return $forbidden;
        }

        foreach ([$studentId, $correctGroupId] as $value) {
            if ($error = Validation::id($value)) {
                return Response::json($error, 422);
            }
        }

        return $this->modelResponse(
            $this->groups->resolveDuplicate((int) $studentId, (int) $correctGroupId)
        );
    }

    public function getNoGroupStudentsList(): Response
    {
        if ($forbidden = $this->authorize(['manage_groups', 'edit_groups'])) {
            return $forbidden;
        }

        $search = $_GET['search'] ?? '';
        $page = $_GET['page'] ?? 1;
        $groupId = $_GET['groupId'] ?? null;

        if (!is_string($search)) {
            return Response::json([
                'success' => false,
                'message' => 'El criterio de búsqueda debe ser texto.'
            ], 422);
        }

        $search = trim($search);

        if (mb_strlen($search) > 100) {
            return Response::json([
                'success' => false,
                'message' => 'El criterio de búsqueda no puede exceder 100 caracteres.'
            ], 422);
        }

        if (Validation::id($page)) {
            return Response::json([
                'success' => false,
                'message' => 'La página debe ser un entero positivo.'
            ], 422);
        }

        if ($error = Validation::id($groupId)) {
            return Response::json($error, 422);
        }

        $limit = 30;
        $response = $this->groups->getNoGroupStudentsList(
            $search,
            (int) $page,
            $limit,
            (int) $groupId
        );

        if (!($response['success'] ?? false)) {
            return $this->modelResponse($response);
        }

        return Response::json([
            'results' => array_map(
                static fn(array $student): array => [
                    'id' => $student['id'],
                    'text' => $student['nombre']
                ],
                $response['data']['students']
            ),
            'pagination' => [
                'more' => ((int) $page * $limit) < $response['data']['total']
            ],
            'total_count' => $response['data']['total']
        ]);
    }

    public function getGroupCareer(mixed $studentId = null): Response
    {
        if ($forbidden = $this->authorize(['manage_groups', 'add_payments'])) {
            return $forbidden;
        }

        if ($error = Validation::id($studentId)) {
            return Response::json($error, 422);
        }

        return $this->modelResponse($this->groups->getGroupCareer((int) $studentId));
    }

    public function getGroupSchedules(mixed $groupId = null): Response
    {
        if ($forbidden = $this->authorize(['manage_groups'])) {
            return $forbidden;
        }

        if ($error = Validation::id($groupId)) {
            return Response::json($error, 422);
        }

        return $this->modelResponse($this->groups->getGroupSchedules((int) $groupId));
    }

    public function addSchedule(array $scheduleData): Response
    {
        if ($forbidden = $this->authorize(['manage_groups', 'edit_groups'])) {
            return $forbidden;
        }

        $data = $scheduleData['scheduleData'] ?? $scheduleData;

        if (is_string($data)) {
            parse_str($data, $data);
        }

        if (!is_array($data)) {
            $data = [];
        }

        $validation = $this->validateScheduleData($data);

        if (!$validation['success']) {
            return Response::json($validation, 422);
        }

        return $this->modelResponse(
            $this->groups->addSchedule($validation['data']),
            201
        );
    }

    private function authorize(array $permissions): ?Response
    {
        $user = auth::user();

        if (PermissionHelper::canAccess(
            $permissions,
            $user['permissions'] ?? [],
            $user['isAdmin'] ?? false
        )) {
            return null;
        }

        return Response::json([
            'success' => false,
            'message' => 'No cuenta con permisos para administrar grupos.'
        ], 403);
    }

    private function sensitiveAuthorization(?string $password): ?Response
    {
        $authorization = $this->sensitiveActions->authorize($password);

        if ($authorization['success']) {
            return null;
        }

        $statusCode = match ($authorization['code'] ?? null) {
            'UNAUTHENTICATED' => 401,
            'PASSWORD_REQUIRED' => 422,
            'MICROSOFT_REAUTH_REQUIRED' => 428,
            default => 403,
        };

        return Response::json($authorization, $statusCode);
    }

    private function validateGroupData(array $data, bool $updating): array
    {
        if ($error = Validation::requiredArray($data)) {
            return $error;
        }

        $map = $updating
            ? [
                'carreerNameGroupEdit' => ['Carrera', 'id', 0],
                'keyGroupEdit' => ['Clave', 'string', 10],
                'nameGroupEdit' => ['Nombre', 'string', 50],
                'startDateEdit' => ['Fecha de inicio', 'date', 0],
                'endDateEdit' => ['Fecha de término', 'date', 0],
                'descriptionGroupEdit' => ['Descripción', 'string', 255],
            ]
            : [
                'carreerNameGroup' => ['Carrera', 'id', 0],
                'keyGroup' => ['Clave', 'string', 10],
                'nameGroup' => ['Nombre', 'string', 50],
                'startDate' => ['Fecha de inicio', 'date', 0],
                'endDate' => ['Fecha de término', 'date', 0],
                'descriptionGroup' => ['Descripción', 'string', 255],
            ];

        $validated = [];

        if ($updating) {
            if ($error = Validation::id($data['idGroupDB'] ?? null)) {
                return $error;
            }
            $validated['idGroupDB'] = (int) $data['idGroupDB'];
        }

        foreach ($map as $field => [$label, $type, $maximumLength]) {
            $value = $data[$field] ?? null;

            if ($type === 'id') {
                if ($error = Validation::id($value)) {
                    return ['success' => false, 'message' => "$label: {$error['message']}"];
                }
                $validated[$field] = (int) $value;
                continue;
            }

            if (!is_string($value) || trim($value) === '') {
                return ['success' => false, 'message' => "$label es obligatorio."];
            }

            $value = trim($value);

            if ($type === 'date') {
                if (!$this->isValidDate($value)) {
                    return ['success' => false, 'message' => "$label debe tener el formato YYYY-MM-DD y ser válida."];
                }
            } else {
                if (mb_strlen($value) < 3) {
                    return ['success' => false, 'message' => "$label debe contener al menos 3 caracteres."];
                }

                if (mb_strlen($value) > $maximumLength) {
                    return ['success' => false, 'message' => "$label no puede exceder $maximumLength caracteres."];
                }

                if (preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', $value)) {
                    return ['success' => false, 'message' => "$label contiene caracteres no permitidos."];
                }
            }

            $validated[$field] = $value;
        }

        $startField = $updating ? 'startDateEdit' : 'startDate';
        $endField = $updating ? 'endDateEdit' : 'endDate';

        if ($validated[$endField] < $validated[$startField]) {
            return [
                'success' => false,
                'message' => 'La fecha de término no puede ser anterior a la fecha de inicio.'
            ];
        }

        return ['success' => true, 'data' => $validated];
    }

    private function validateScheduleData(array $data): array
    {
        if ($error = Validation::requiredArray($data)) {
            return $error;
        }

        if ($error = Validation::id($data['groupId'] ?? null)) {
            return $error;
        }

        $validated = ['groupId' => (int) $data['groupId']];

        foreach (['title' => 200, 'description' => 2000] as $field => $maximumLength) {
            $value = $data[$field] ?? null;

            if (!is_string($value) || trim($value) === '') {
                return ['success' => false, 'message' => ucfirst($field) . ' es obligatorio.'];
            }

            $value = trim($value);

            if (mb_strlen($value) > $maximumLength) {
                return ['success' => false, 'message' => ucfirst($field) . " no puede exceder $maximumLength caracteres."];
            }

            $validated[$field] = $value;
        }

        $date = $data['date'] ?? null;

        if (!is_string($date) || !$this->isValidDate($date)) {
            return ['success' => false, 'message' => 'La fecha debe tener el formato YYYY-MM-DD y ser válida.'];
        }

        $validated['date'] = $date;

        foreach (['inputStart' => 'Hora de inicio', 'inputEnd' => 'Hora de fin'] as $field => $label) {
            $value = $data[$field] ?? null;

            if (!is_string($value) || preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d(?::[0-5]\d)?$/D', $value) !== 1) {
                return ['success' => false, 'message' => "$label debe tener un formato válido de 24 horas."];
            }

            $validated[$field] = strlen($value) === 5 ? "$value:00" : $value;
        }

        if ($validated['inputEnd'] <= $validated['inputStart']) {
            return ['success' => false, 'message' => 'La hora de fin debe ser posterior a la hora de inicio.'];
        }

        return ['success' => true, 'data' => $validated];
    }

    private function isValidDate(string $date): bool
    {
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);

        return $parsed !== false && $parsed->format('Y-m-d') === $date;
    }

    private function modelResponse(array $response, int $successStatus = 200): Response
    {
        if ($response['success'] ?? false) {
            return Response::json($response, $successStatus);
        }

        $statusCode = match ($response['code'] ?? null) {
            'GROUP_NOT_FOUND',
            'CAREER_NOT_FOUND',
            'STUDENT_NOT_FOUND',
            'MEMBERSHIP_NOT_FOUND' => 404,
            'GROUP_HAS_STUDENTS',
            'NO_CHANGES',
            'STUDENT_ALREADY_ASSIGNED',
            'DUPLICATE_NOT_FOUND',
            'INVALID_CORRECT_GROUP' => 409,
            default => 500,
        };

        return Response::json($response, $statusCode);
    }
}
