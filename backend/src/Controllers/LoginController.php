<?php
namespace Vendor\Schoolarsystem\Controllers;

use Firebase\JWT\JWT;
use Vendor\Schoolarsystem\DBConnection;
use Vendor\Schoolarsystem\Core\Response;
use Vendor\Schoolarsystem\Core\Validation;
use Vendor\Schoolarsystem\Models\LoginModel;

class LoginController
{
    private DBConnection $connection;
    private LoginModel $login;

    public function __construct()
    {
        $this->connection = DBConnection::getInstance();
        $this->login = new LoginModel($this->connection);
    }

    public function index(): Response
    {
        return Response::text('', 302)->header(
            'Location',
            ($_ENV['BASE_URL'] ?? '.') . '/login.php'
        );
    }

    public function login(string $user, string $password): array
    {
        if ($error = Validation::string($user)) {
            return $error;
        }

        if ($error = Validation::password($password)) {
            return $error;
        }

        // Verificar credenciales
        $login = $this->login->login($user, $password);

        if (!$login['success']) {
            return $login;
        }

        // Configuración
        $lifeTime = $_ENV['LIFE_TME'] ?? 3600;
        $secretKey = $_ENV['KEY'];

        // Crear sesión
        session_set_cookie_params($lifeTime);

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $_SESSION['userId'] = $login['data']['userId'];

        // Crear JWT
        $payload = [
            "userId" => $login['data']['userId'],
            "userName" => $user,
            "iat" => time(),
            "exp" => time() + $lifeTime
        ];

        $jwt = JWT::encode($payload, $secretKey, 'HS256');

        // Crear cookie
        setcookie(
            "auth",
            $jwt,
            [
                "expires" => time() + $lifeTime,
                "path" => "/",
                "secure" => isset($_SERVER['HTTPS']),
                "httponly" => true,
                "samesite" => "Lax"
            ]
        );

        return [
            "success" => true,
            "message" => "Inicio de sesión exitoso.",
            "redirect" => ($_ENV['BASE_URL'] ?? '.') . "/dashboard.php",
            "userId" => $login['data']['userId']
        ];
    }

    public function logout(): array
    {
        $logout = $this->login->logout();

        $logout['redirect'] = ($_ENV['BASE_URL'] ?? '.') . '/login.php?sesion=close';
        $logout['microsoft_redirect'] = ($_ENV['BASE_URL'] ?? '.') . '/login.php';

        return $logout;
    }

    public function verifySession(string $jwt): array
    {
        if ($error = Validation::string($jwt)) {
            return $error;
        }

        return $this->login->verifySession($jwt);
    }

    public function verifyUserPassword(int $userId, string $password): bool
    {
        return $this->login->verifyUserPassword($userId, $password);
    }
}
