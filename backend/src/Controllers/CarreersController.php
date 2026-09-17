<?php
namespace Vendor\Schoolarsystem\Controllers;

use Vendor\Schoolarsystem\auth;
use Vendor\Schoolarsystem\DBConnection;
use Vendor\Schoolarsystem\Core\Response;
use Vendor\Schoolarsystem\Core\SensitiveActionAuthorizer;
use Vendor\Schoolarsystem\Core\Validation;
use Vendor\Schoolarsystem\Core\View;
use Vendor\Schoolarsystem\Models\CarreersModel;
use Vendor\Schoolarsystem\PermissionHelper;

class CarreersController
{
    private DBConnection $connection;
    private CarreersModel $carreers;
    private SensitiveActionAuthorizer $sensitiveActions;

    public function __construct()
    {
        $this->connection = DBConnection::getInstance();
        $this->carreers = new CarreersModel($this->connection);
        $this->sensitiveActions = new SensitiveActionAuthorizer();
    }

    public function getCarreerById(mixed $carreerId = null): Response
    {
        if ($forbidden = $this->authorizeManagement()) {
            return $forbidden;
        }

        if ($error = Validation::id($carreerId)) {
            return Response::json($error, 422);
        }

        return $this->modelResponse(
            $this->carreers->getCarreerById((int) $carreerId)
        );
    }

    public function careersSubjectsModal(array $modalData): Response
    {
        if ($forbidden = $this->authorizeManagement()) {
            return $forbidden;
        }

        if ($error = Validation::id($modalData['careerId'] ?? null)) {
            return Response::json($error, 422);
        }

        $careerId = (int) $modalData['careerId'];
        $career = $this->carreers->getCarreerById($careerId);

        if (!$career['success']) {
            return $this->modelResponse($career);
        }

        return Response::html(View::modal('careersSubjects.Modal.php', [
            'careerId' => $careerId
        ]));
    }

    public function careerEditModal(): Response
    {
        if ($forbidden = $this->authorizeManagement()) {
            return $forbidden;
        }

        return Response::html(View::modal('careerEdit.modal.php'));
    }

    public function getAllCarreers(): Response
    {
        if ($forbidden = $this->authorizeManagement()) {
            return $forbidden;
        }

        return $this->modelResponse($this->carreers->getAllCarreers());
    }

    public function addCarreer(array $carreerData): Response
    {
        if ($forbidden = $this->authorizeManagement()) {
            return $forbidden;
        }

        $validation = $this->validateCareerFields($carreerData, [
            'careerName' => ['Nombre', 200],
            'careerArea' => ['Área', 100],
            'careerSubarea' => ['Subárea', 100],
            'careerDes' => ['Descripción', 400],
        ]);

        if (!$validation['success']) {
            return Response::json($validation, 422);
        }

        return $this->modelResponse(
            $this->carreers->addCarreer($validation['data']),
            201
        );
    }

    public function updateCarreer(array $carreerUpdateData): Response
    {
        if ($forbidden = $this->authorizeManagement()) {
            return $forbidden;
        }

        if ($error = Validation::id($carreerUpdateData['idCarreerDB'] ?? null)) {
            return Response::json($error, 422);
        }

        $validation = $this->validateCareerFields($carreerUpdateData, [
            'careerNameEdit' => ['Nombre', 200],
            'carreerAreaEdit' => ['Área', 100],
            'careerSubareaEdit' => ['Subárea', 100],
            'careerComentsEdit' => ['Descripción', 400],
        ]);

        if (!$validation['success']) {
            return Response::json($validation, 422);
        }

        $data = $validation['data'];
        $data['idCarreerDB'] = (int) $carreerUpdateData['idCarreerDB'];

        return $this->modelResponse($this->carreers->updateCarreer($data));
    }

    public function deleteCarreerById(
        mixed $carreerId = null,
        ?string $password = null
    ): Response
    {
        if ($forbidden = $this->authorizeManagement()) {
            return $forbidden;
        }

        if ($error = Validation::id($carreerId)) {
            return Response::json($error, 422);
        }

        $authorization = $this->sensitiveActions->authorize($password);

        if (!$authorization['success']) {
            $statusCode = match ($authorization['code'] ?? null) {
                'UNAUTHENTICATED' => 401,
                'PASSWORD_REQUIRED' => 422,
                'MICROSOFT_REAUTH_REQUIRED' => 428,
                default => 403,
            };

            return Response::json($authorization, $statusCode);
        }

        return $this->modelResponse(
            $this->carreers->deleteCarreerById((int) $carreerId)
        );
    }

    private function authorizeManagement(): ?Response
    {
        $user = auth::user();

        if (
            PermissionHelper::canAccess(
                ['manage_carrers'],
                $user['permissions'] ?? [],
                $user['isAdmin'] ?? false
            )
        ) {
            return null;
        }

        return Response::json([
            'success' => false,
            'message' => 'No cuenta con permisos para administrar carreras.'
        ], 403);
    }

    private function validateCareerFields(array $data, array $fields): array
    {
        if ($error = Validation::requiredArray($data)) {
            return $error;
        }

        $validatedData = [];

        foreach ($fields as $field => [$label, $maximumLength]) {
            $value = $data[$field] ?? null;

            if (!is_string($value) || trim($value) === '') {
                return [
                    'success' => false,
                    'message' => "$label es obligatorio."
                ];
            }

            $value = trim($value);

            if (mb_strlen($value) > $maximumLength) {
                return [
                    'success' => false,
                    'message' => "$label no puede exceder $maximumLength caracteres."
                ];
            }

            $validatedData[$field] = $value;
        }

        return [
            'success' => true,
            'data' => $validatedData
        ];
    }

    private function modelResponse(array $response, int $successStatus = 200): Response
    {
        if ($response['success'] ?? false) {
            return Response::json($response, $successStatus);
        }

        $statusCode = match ($response['message'] ?? '') {
            'No se encontró el registro solicitado.',
            'No se encontró el registro a eliminar.' => 404,
            'No se detectaron cambios para guardar.' => 409,
            default => 500,
        };

        return Response::json($response, $statusCode);
    }
}
