<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';

use Vendor\Schoolarsystem\loadEnv;
use myPHPnotes\Microsoft\Auth;
use myPHPnotes\Microsoft\Handlers\Session;
use Vendor\Schoolarsystem\MicrosoftActions;

loadEnv::cargar();

$tenant = Session::get("tenant_id") ?? $_ENV['TENANT_ID'];
$clientId = Session::get("client_id") ?? $_ENV['CLIENT_ID'];
$clientSecret = Session::get("client_secret") ?? $_ENV['CLIENT_SECRET'];
$redirectUri = Session::get("redirect_uri") ?? $_ENV['CALLBACK_PATH'];
$scopes = Session::get("scopes") ?? ["User.ReadBasic.All", "offline_access"];

$microsoft = new Auth($tenant, $clientId, $clientSecret, $redirectUri, $scopes);

$baseUrl = $_ENV['BASE_URL'] ?? '.';
$isReauthentication = Session::get('reauth_in_progress') === true;
$expectedUserId = (string) (Session::get('reauth_expected_user_id') ?? '');
$returnUrl = (string) (Session::get('reauth_return_url') ?? ($baseUrl . '/dashboard.php'));
$previousAccessToken = Session::get('accessToken');

try {
    $tokens = $microsoft->getToken($_REQUEST['code'], Session::get("state"));

    if (empty($tokens->access_token)) {
        throw new Exception('No se recibió un token de acceso de Microsoft.');
    }

    $microsoft->setAccessToken($tokens->access_token);
    $microsoftUser = MicrosoftActions::getUserId($tokens->access_token);

    if (!($microsoftUser['success'] ?? false) || empty($microsoftUser['userId'])) {
        throw new Exception('No fue posible identificar al usuario de Microsoft.');
    }

    $microsoftUserId = (string) $microsoftUser['userId'];

    if ($isReauthentication && !hash_equals($expectedUserId, $microsoftUserId)) {
        if ($previousAccessToken) {
            $microsoft->setAccessToken($previousAccessToken);
        }

        throw new Exception('La cuenta confirmada no coincide con la sesión actual.');
    }

    Session::set('microsoft_access_token', $tokens->access_token);
    $_SESSION['logged_in'] = true;
    $_SESSION['userId'] = $microsoftUserId;
    $_SESSION['authSource'] = 'microsoft';
    $_SESSION['authenticatedAt'] = $_SESSION['authenticatedAt'] ?? time();
    $_SESSION['microsoftReauthenticatedUserId'] = $microsoftUserId;
    $_SESSION['microsoftReauthenticatedAt'] = time();

    Session::unset('reauth_in_progress');
    Session::unset('reauth_expected_user_id');
    Session::unset('reauth_return_url');

    if ($isReauthentication) {
        $separator = str_contains($returnUrl, '?') ? '&' : '?';
        header('Location: ' . $returnUrl . $separator . 'reauth=microsoft-success');
        exit;
    }

    header("Location: {$baseUrl}/dashboard.php");
    exit;
} catch (\Throwable $e) {
    if ($isReauthentication && $previousAccessToken) {
        $microsoft->setAccessToken($previousAccessToken);
    }

    Session::unset('reauth_in_progress');
    Session::unset('reauth_expected_user_id');
    Session::unset('reauth_return_url');

    if ($isReauthentication) {
        $separator = str_contains($returnUrl, '?') ? '&' : '?';
        header('Location: ' . $returnUrl . $separator . 'reauth=microsoft-failed');
        exit;
    }

    header("Location: {$baseUrl}/login.php?error=authentication_failed");
    exit;
}
?>
