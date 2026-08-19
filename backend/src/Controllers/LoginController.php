<?php
namespace Vendor\Schoolarsystem\Controllers;

use Firebase\JWT\JWT;
use myPHPnotes\Microsoft\Auth as MicrosoftAuth;
use myPHPnotes\Microsoft\Handlers\Session as MicrosoftSession;
use Vendor\Schoolarsystem\auth;
use Vendor\Schoolarsystem\DBConnection;
use Vendor\Schoolarsystem\Core\Response;
use Vendor\Schoolarsystem\Core\Validation;
use Vendor\Schoolarsystem\loadEnv;
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
        $_SESSION['authSource'] = 'local';
        $_SESSION['authenticatedAt'] = time();

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

    public function startMicrosoftReauthentication(?string $returnUrl = null): Response
    {
        $identity = auth::user();

        if (
            !($identity['success'] ?? false)
            || ($identity['authSource'] ?? null) !== 'microsoft'
        ) {
            return Response::json([
                'success' => false,
                'message' => 'La reautenticación de Microsoft no está disponible para esta sesión.'
            ], 403);
        }

        loadEnv::cargar();

        $tenant = (string) ($_ENV['TENANT_ID'] ?? '');
        $clientId = (string) ($_ENV['CLIENT_ID'] ?? '');
        $clientSecret = (string) ($_ENV['CLIENT_SECRET'] ?? '');
        $callback = (string) ($_ENV['CALLBACK_PATH'] ?? '');
        $scopes = ['User.ReadBasic.All', 'offline_access'];

        if ($tenant === '' || $clientId === '' || $clientSecret === '' || $callback === '') {
            return Response::json([
                'success' => false,
                'message' => 'La autenticación de Microsoft no está configurada correctamente.'
            ], 500);
        }

        $microsoft = new MicrosoftAuth(
            $tenant,
            $clientId,
            $clientSecret,
            $callback,
            $scopes
        );

        MicrosoftSession::set('state', bin2hex(random_bytes(32)));
        MicrosoftSession::set('reauth_in_progress', true);
        MicrosoftSession::set('reauth_expected_user_id', (string) $identity['userId']);
        MicrosoftSession::set('reauth_return_url', $this->safeReturnUrl($returnUrl));

        return Response::text('', 302)->header(
            'Location',
            $microsoft->getAuthUrl() . '&prompt=login'
        );
    }

    private function safeReturnUrl(?string $returnUrl): string
    {
        $baseUrl = rtrim((string) ($_ENV['BASE_URL'] ?? ''), '/');
        $fallback = $baseUrl . '/dashboard.php';

        if (
            $returnUrl === null
            || $returnUrl === ''
            || preg_match('/[\r\n]/', $returnUrl)
        ) {
            return $fallback;
        }

        if (str_starts_with($returnUrl, '/') && !str_starts_with($returnUrl, '//')) {
            return $returnUrl;
        }

        return $fallback;
    }
}
