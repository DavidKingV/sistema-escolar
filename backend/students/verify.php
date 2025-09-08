<?php
declare(strict_types=1);
require_once(__DIR__.'/../vendor/autoload.php');

use Vendor\Schoolarsystem\loadEnv;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

loadEnv::cargar();

class AdvancedStudentsControl{

    public function VerifyIdStudentId(int $studentId, string $studentSecretKey): array{
    
        $secretKey = $_ENV['KEY'];
        
        try {
            // Decodificar el JWT usando la clave secreta
            $decoded = JWT::decode($studentSecretKey, new Key($secretKey, 'HS256'));
    
            // Verificar si el ID del estudiante coincide con el ID en el token
            if ($decoded->studentId == $studentId) {
                return array(
                    "success" => true,
                    "token" => true,
                    "studentId" => $decoded->studentId,
                    "message" => "El id del estudiante coincide con el token"
                );
            } else {
                return array(
                    "success" => false,
                    "token" => false,
                    "message" => "El id del estudiante no coincide con el token"
                );
            }
        } catch (Exception $e) {
            // Manejo de errores si la decodificación falla
            return array(
                "success" => false,
                "token" => false,
                "message" => "Error al decodificar el token: " . $e->getMessage()
            );
        }
       
    }
}

?>