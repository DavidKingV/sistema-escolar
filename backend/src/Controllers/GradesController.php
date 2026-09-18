<?php
namespace Vendor\Schoolarsystem\Controllers;

use Vendor\Schoolarsystem\auth;
use Vendor\Schoolarsystem\DBConnection;
use Vendor\Schoolarsystem\Core\Response;
use Vendor\Schoolarsystem\Core\Validation;
use Vendor\Schoolarsystem\Core\View;
use Vendor\Schoolarsystem\Models\GradesModel;
use Vendor\Schoolarsystem\PermissionHelper;

class GradesController
{
    private DBConnection $connection;
    private GradesModel $grades;

    public function __construct()
    {
        $this->connection = DBConnection::getInstance();
        $this->grades = new GradesModel($this->connection);
    }

    public function getMakeOverGrades(mixed $makeOverId = null): Response
    {
        if ($forbidden = $this->authorize(['view_students', 'edit_students'])) {
            return $forbidden;
        }

        if ($error = Validation::id($makeOverId)) {
            return Response::json($error, 422);
        }

        return $this->modelResponse(
            $this->grades->getMakeOverGrades((int) $makeOverId)
        );
    }

    public function makeOverExamModal(array $modalData): Response
    {
        if ($forbidden = $this->authorize(['edit_students'])) {
            return $forbidden;
        }

        foreach (['studentId', 'subjectId', 'gradeId'] as $field) {
            if ($error = Validation::id($modalData[$field] ?? null)) {
                return $this->modalValidationError($error['message']);
            }
        }

        $subjectChildId = $modalData['subjectChildId'] ?? null;

        if ($subjectChildId !== null && $subjectChildId !== '') {
            if ($error = Validation::id($subjectChildId)) {
                return $this->modalValidationError($error['message']);
            }
            $subjectChildId = (int) $subjectChildId;
        } else {
            $subjectChildId = null;
        }

        $studentId = (int) $modalData['studentId'];
        $subjectId = (int) $modalData['subjectId'];
        $gradeId = (int) $modalData['gradeId'];
        $gradeTarget = $this->grades->getGradeTarget(
            $studentId,
            $subjectId,
            $subjectChildId,
            $gradeId
        );

        if (!$gradeTarget['success']) {
            return $this->modelResponse($gradeTarget);
        }

        return Response::html(View::modal('makeOverExam.Modal.php', [
            'studentId' => $studentId,
            'subjectId' => $subjectId,
            'subjectChildId' => $subjectChildId,
            'subjectName' => $gradeTarget['data']['subjectName'],
            'subjectChildName' => $gradeTarget['data']['subjectChildName'] ?? '',
            'gradeId' => $gradeId
        ]));
    }

    public function viewMakeOverModal(array $modalData): Response
    {
        if ($forbidden = $this->authorize(['view_students', 'edit_students'])) {
            return $forbidden;
        }

        $makeOverId = $modalData['makeOverId']
            ?? $modalData['makeOverChildId']
            ?? null;

        if ($error = Validation::id($makeOverId)) {
            return $this->modalValidationError($error['message']);
        }

        $makeOverId = (int) $makeOverId;
        $makeOver = $this->grades->getMakeOverGrades($makeOverId);

        if (!$makeOver['success']) {
            return $this->modelResponse($makeOver);
        }

        return Response::html(View::modal('viewMakeOver.Modal.php', [
            'makeOverId' => $makeOverId
        ]));
    }

    public function addMakeOverGrade(array $makeOverData): Response
    {
        if ($forbidden = $this->authorize(['edit_students'])) {
            return $forbidden;
        }

        $data = $makeOverData['makeOverData'] ?? $makeOverData;

        if (is_string($data)) {
            parse_str($data, $data);
        }

        if (!is_array($data)) {
            $data = [];
        }

        if ($error = Validation::requiredArray($data)) {
            return Response::json($error, 422);
        }

        $validation = $this->validateMakeOverData($data);

        if (!$validation['success']) {
            return Response::json($validation, 422);
        }

        return $this->modelResponse(
            $this->grades->addMakeOverGrade($validation['data']),
            201
        );
    }

    private function modalValidationError(string $message): Response
    {
        return Response::json([
            'success' => false,
            'message' => $message
        ], 422);
    }

    private function authorize(array $permissions): ?Response
    {
        $user = auth::user();

        if (
            PermissionHelper::canAccess(
                $permissions,
                $user['permissions'] ?? [],
                $user['isAdmin'] ?? false
            )
        ) {
            return null;
        }

        return Response::json([
            'success' => false,
            'message' => 'No cuenta con permisos para administrar calificaciones.'
        ], 403);
    }

    private function validateMakeOverData(array $data): array
    {
        $validatedData = [];

        foreach (['studentId', 'subjectId', 'gradeId'] as $field) {
            if ($error = Validation::id($data[$field] ?? null)) {
                return $error;
            }

            $validatedData[$field] = (int) $data[$field];
        }

        $subjectChildId = $data['subjectChildId'] ?? null;

        if ($subjectChildId !== null && $subjectChildId !== '') {
            if ($error = Validation::id($subjectChildId)) {
                return $error;
            }

            $validatedData['subjectChildId'] = (int) $subjectChildId;
        } else {
            $validatedData['subjectChildId'] = null;
        }

        $continuousGrade = $this->validateGradeValue(
            $data['continuousGrade'] ?? $data['continuosGrade'] ?? null,
            'La calificación continua'
        );

        if (!$continuousGrade['success']) {
            return $continuousGrade;
        }

        $examGrade = $this->validateGradeValue(
            $data['examGrade'] ?? null,
            'La calificación del examen'
        );

        if (!$examGrade['success']) {
            return $examGrade;
        }

        $validatedData['continuousGrade'] = $continuousGrade['value'];
        $validatedData['examGrade'] = $examGrade['value'];
        $validatedData['finalGrade'] = round(
            ($continuousGrade['value'] + $examGrade['value']) / 2,
            2
        );

        return [
            'success' => true,
            'data' => $validatedData
        ];
    }

    private function validateGradeValue(mixed $value, string $label): array
    {
        if (
            (!is_int($value) && !is_float($value) && !is_string($value))
            || (is_string($value) && trim($value) === '')
            || !is_numeric($value)
        ) {
            return [
                'success' => false,
                'message' => "$label debe ser un número."
            ];
        }

        $grade = (float) $value;

        if (!is_finite($grade) || $grade < 0 || $grade > 10) {
            return [
                'success' => false,
                'message' => "$label debe estar entre 0 y 10."
            ];
        }

        return [
            'success' => true,
            'value' => $grade
        ];
    }

    private function modelResponse(array $response, int $successStatus = 200): Response
    {
        if ($response['success'] ?? false) {
            return Response::json($response, $successStatus);
        }

        $statusCode = match ($response['code'] ?? null) {
            'MAKEOVER_NOT_FOUND',
            'GRADE_RECORD_NOT_FOUND' => 404,
            'MAKEOVER_ALREADY_EXISTS',
            'GRADE_NOT_ELIGIBLE' => 409,
            default => 500,
        };

        return Response::json($response, $statusCode);
    }
}
