<?php

namespace Vendor\Schoolarsystem\Core;

use Vendor\Schoolarsystem\auth;
use Vendor\Schoolarsystem\DBConnection;
use Vendor\Schoolarsystem\Models\LoginModel;

class SensitiveActionAuthorizer
{
    private const MICROSOFT_REAUTHENTICATION_TTL = 300;

    private LoginModel $login;

    public function __construct()
    {
        $this->login = new LoginModel(DBConnection::getInstance());
    }

    public function authorize(?string $password = null): array
    {
        $identity = auth::user();

        if (!($identity['success'] ?? false)) {
            return [
                'success' => false,
                'code' => 'UNAUTHENTICATED',
                'message' => 'No se ha iniciado sesión o la sesión ha expirado.'
            ];
        }

        $authSource = $identity['authSource'] ?? null;

        if ($authSource === 'local') {
            return $this->authorizeLocalUser($identity, $password);
        }

        if ($authSource === 'microsoft') {
            return $this->authorizeMicrosoftUser($identity);
        }

        return [
            'success' => false,
            'code' => 'INVALID_AUTH_SOURCE',
            'message' => 'No fue posible identificar el método de autenticación.'
        ];
    }

    private function authorizeLocalUser(array $identity, ?string $password): array
    {
        if ($error = Validation::password($password)) {
            $error['code'] = 'PASSWORD_REQUIRED';
            return $error;
        }

        $userId = $identity['userId'] ?? null;

        if (!is_numeric($userId) || !$this->login->verifyUserPassword((int) $userId, $password)) {
            return [
                'success' => false,
                'code' => 'INVALID_PASSWORD',
                'message' => 'Contraseña incorrecta.'
            ];
        }

        return [
            'success' => true,
            'message' => 'Identidad verificada.',
            'authSource' => 'local'
        ];
    }

    private function authorizeMicrosoftUser(array $identity): array
    {
        $userId = (string) ($identity['userId'] ?? '');
        $reauthenticatedUserId = (string) ($_SESSION['microsoftReauthenticatedUserId'] ?? '');
        $reauthenticatedAt = (int) ($_SESSION['microsoftReauthenticatedAt'] ?? 0);

        if (
            $userId !== ''
            && hash_equals($userId, $reauthenticatedUserId)
            && $reauthenticatedAt >= time() - self::MICROSOFT_REAUTHENTICATION_TTL
        ) {
            return [
                'success' => true,
                'message' => 'Identidad verificada con Microsoft.',
                'authSource' => 'microsoft'
            ];
        }

        return [
            'success' => false,
            'code' => 'MICROSOFT_REAUTH_REQUIRED',
            'message' => 'Confirma tu identidad con Microsoft para continuar.',
            'reauthRequired' => true,
            'reauthUrl' => $this->microsoftReauthenticationUrl()
        ];
    }

    private function microsoftReauthenticationUrl(): string
    {
        $baseUrl = rtrim((string) ($_ENV['BASE_URL'] ?? ''), '/');
        $referer = (string) ($_SERVER['HTTP_REFERER'] ?? '');
        $refererPath = parse_url($referer, PHP_URL_PATH);
        $refererQuery = parse_url($referer, PHP_URL_QUERY);
        $returnUrl = is_string($refererPath) && $refererPath !== ''
            ? $refererPath . ($refererQuery ? '?' . $refererQuery : '')
            : $baseUrl . '/dashboard.php';

        return $baseUrl
            . '/auth/microsoft/reauth?returnUrl='
            . rawurlencode($returnUrl);
    }
}
