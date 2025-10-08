<?php
namespace Vendor\Schoolarsystem\Models;

use Vendor\Schoolarsystem\DBConnection;
use Vendor\Schoolarsystem\auth;
use Vendor\Schoolarsystem\PermissionHelper;

class AdmissionsModel {
    private $connection;

    public function __construct(DBConnection $dbConnection) {
        $this->connection = $dbConnection->getConnection();
    }

    public function getAllNewAdmissions() {
        $VerifySession = auth::check();
        $isAdmin       = $VerifySession['isAdmin'] ?? false;
        $userPerms     = $VerifySession['permissions'] ?? [];

        try {
            $stmt = $this->connection->prepare("
                SELECT 
                    id, firstName, lastNames, gender, birthday, placeBirth,
                    nationality, curp, age, civilStatus, adress, phone, email,
                    lastStudies, program, pdf, controlNo
                FROM registrationApplications
                WHERE approved = 0
            ");

            $stmt->execute();
            $result = $stmt->get_result();

            if (!$result) {
                return [
                    "success" => false,
                    "message" => "Error al obtener las solicitudes de inscripción"
                ];
            }

            if ($result->num_rows === 0) {
                return [
                    "success" => false,
                    "message" => "No hay solicitudes de inscripción disponibles"
                ];
            }

            $hasActions = PermissionHelper::canAccess(['approve_admissions'], $userPerms, $isAdmin);

            $response = [];
            while ($row = $result->fetch_assoc()) {
                // Agregamos permisos al resultado
                $row['actions'] = $hasActions;
                $response[] = $row;
            }

            return [
                "success" => true,
                "data"    => $response
            ];

        } catch (\Exception $e) {
            // Nota: cambiaste a PDOException, pero usas mysqli. Mejor usar \Exception.
            return [
                "success" => false,
                "message" => "Error en la consulta: " . $e->getMessage()
            ];
        } finally {
            if (isset($stmt)) {
                $stmt->close();
            }
        }
    }

}