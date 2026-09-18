<?php
namespace Vendor\Schoolarsystem\Controllers;

use Vendor\Schoolarsystem\auth;
use Vendor\Schoolarsystem\DBConnection;
use Vendor\Schoolarsystem\Core\Response;
use Vendor\Schoolarsystem\Core\Validation;
use Vendor\Schoolarsystem\PermissionHelper;
use Vendor\Schoolarsystem\Models\AdmissionsModel;

class AdmissionsController
{
    private DBConnection $connection;
    private AdmissionsModel $admissions;

    public function __construct()
    {
        $this->connection = DBConnection::getInstance();
        $this->admissions = new AdmissionsModel($this->connection);
    }

    public function getAllNewAdmissions(): Response
    {
        $response = $this->admissions->getAllNewAdmissions();

        if (!$response['success']) {
            return Response::json($response, 500);
        }

        $user = auth::user();

        return Response::json([
            "success" => true,
            "permissions" => [
                "canApproveAdmissions" => $this->canManageAdmissions($user)
            ],
            "data" => $response['data'],
        ]);
    }

    public function deleteAdmission(mixed $id = null): Response
    {
        $user = auth::user();

        if (!$this->canManageAdmissions($user)) {
            return Response::json([
                "success" => false,
                "message" => "No cuenta con permisos para eliminar solicitudes de admisión."
            ], 403);
        }

        if ($error = Validation::id($id)) {
            return Response::json($error, 422);
        }

        $response = $this->admissions->deleteAdmission((int) $id);

        if (!$response['success']) {
            $statusCode = ($response['message'] ?? '') === 'No se encontró el registro a eliminar.'
                ? 404
                : 500;

            return Response::json($response, $statusCode);
        }

        return Response::json($response);
    }

    private function canManageAdmissions(array $user): bool
    {
        return PermissionHelper::canAccess(
            ['approve_admissions'],
            $user['permissions'] ?? [],
            $user['isAdmin'] ?? false
        );
    }
}
