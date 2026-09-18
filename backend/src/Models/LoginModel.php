<?php
namespace Vendor\Schoolarsystem\Models;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Vendor\Schoolarsystem\DBConnection;
use Vendor\Schoolarsystem\Core\DatabaseHelper;

class LoginModel
{
    private $connection;

    public function __construct(DBConnection $dbConnection)
    {
        $this->connection = $dbConnection->getConnection();
    }

    public function login(string $user, string $pass): array
    {
        $userData = DatabaseHelper::selectOne(
            $this->connection,
            "
                SELECT id, password, hashed_password
                FROM login_users
                WHERE user = ?;
            ",
            "s",
            [$user]
        );

        if (!$userData["success"]) {
            return [
                "success" => false,
                "message" => "Usuario no encontrado.",
                "data" => null
            ];
        }

        $userData = $userData["data"];

        $userId = $userData["id"];
        $storedPassword = $userData["password"];
        $storedHashedPassword = $userData["hashed_password"];

        // Compatibilidad con contraseñas antiguas sin hash
        if (
            $storedHashedPassword === null &&
            $storedPassword === $pass
        ) {
            $newHash = password_hash($pass, PASSWORD_DEFAULT);

            $update = DatabaseHelper::update(
                $this->connection,
                "
                    UPDATE login_users
                    SET hashed_password = ?
                    WHERE id = ?;
                ",
                "si",
                [$newHash, $userId]
            );

            if (!$update["success"]) {
                return [
                    "success" => false,
                    "message" => "No fue posible actualizar la contraseña.",
                    "data" => null
                ];
            }

            return [
                "success" => true,
                "message" => "Inicio de sesión exitoso.",
                "data" => [
                    "userId" => $userId
                ]
            ];
        }

        // Contraseña almacenada mediante hash
        if (
            $storedHashedPassword !== null &&
            password_verify($pass, $storedHashedPassword)
        ) {
            return [
                "success" => true,
                "message" => "Inicio de sesión exitoso.",
                "data" => [
                    "userId" => $userId
                ]
            ];
        }

        return [
            "success" => false,
            "message" => "Contraseña incorrecta.",
            "data" => null
        ];
    }

    public function logout(): array
    {
        session_start();

        if (!isset($_SESSION["adnanhussainturki/microsoft"])) {
            session_unset();
            session_destroy();

            // Eliminar todas las cookies
            foreach ($_COOKIE as $key => $value) {
                setcookie($key, '', time() - 3600, '/');
            }

            return [
                "success" => true,
                "microsoftLogout" => false,
                "message" => "Sesión cerrada."
            ];
        } else {
            session_unset();
            session_destroy();

            return [
                "success" => true,
                "microsoftLogout" => true,
                "message" => "Cerrando sesión de Microsoft."
            ];
        }
    }

    public function verifySession(string $jwt): array
    {
        $secret_key = $_ENV['KEY'];
        // Verificar si hay una sesión de PHP iniciada y si hay una cookie con el JWT
        if (isset($_SESSION['userId']) && isset($_COOKIE['auth'])) {
            try {
                // Decodificar el JWT proporcionado en la cookie
                $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));
                // Verificar si el JWT decodificado coincide con la sesión de PHP
                if ($_SESSION['userId'] == $decoded->userId) {
                    return [
                        "success" => true,
                        "message" => "Sesión válida",
                        "userId" => $decoded->userId
                    ];
                } else {
                    return [
                        "success" => false,
                        "message" => "Sesión inválida."
                    ];
                }
            } catch (\Exception $e) {
                return [
                    "success" => false,
                    "message" => "Sesión inválida."
                ];
            }
        }

        // Si no se encontró una sesión de PHP y un JWT válido simultáneamente, devolver un error
        return [
            "success" => false,
            "message" => "Sesión inválida."
        ];
    }

    public function verifyUserPassword(int $userId, string $password): bool
    {
        $user = DatabaseHelper::selectOne(
            $this->connection,
            "
                SELECT password, hashed_password
                FROM login_users
                WHERE id = ?;
            ",
            "i",
            [$userId]
        );

        if (!$user["success"]) {
            return false;
        }

        $storedPassword = $user["data"]["password"];
        $storedHashedPassword = $user["data"]["hashed_password"];

        // Compatibilidad con contraseñas antiguas sin hash
        if (
            $storedHashedPassword === null &&
            $storedPassword === $password
        ) {
            $newHash = password_hash($password, PASSWORD_DEFAULT);

            $update = DatabaseHelper::update(
                $this->connection,
                "
                    UPDATE login_users
                    SET hashed_password = ?
                    WHERE id = ?;
                ",
                "si",
                [$newHash, $userId]
            );

            return $update["success"];
        }

        // Contraseña almacenada mediante hash
        return $storedHashedPassword !== null
            && password_verify($password, $storedHashedPassword);
    }

}
