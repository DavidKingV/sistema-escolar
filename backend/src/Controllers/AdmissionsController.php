<?php
namespace Vendor\Schoolarsystem\Controllers;

use Vendor\Schoolarsystem\auth;
use Vendor\Schoolarsystem\DBConnection;
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

    public function getAllNewAdmissions(): array
    {
        $response = $this->admissions->getAllNewAdmissions();

        if (!$response['success']) {
            return $response;
        }

        $user = auth::user();

        return [
            "success" => true,
            "permissions" => [
                "canApproveAdmissions" => PermissionHelper::canAccess(
                    ['approve_admissions'],
                    $user['permissions'] ?? [],
                    $user['isAdmin'] ?? false
                )
            ],
            "data" => $response['data'],
        ];
    }

    public function deleteAdmission(int $id): array
    {
        if ($error = Validation::id($id)) {
            return $error;
        }

        return $this->admissions->deleteAdmission($id);
    }
}