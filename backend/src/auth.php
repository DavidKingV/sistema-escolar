<?php
namespace Vendor\Schoolarsystem;

use Vendor\Schoolarsystem\DBConnection;
use Vendor\Schoolarsystem\MicrosoftActions;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class auth {

    public static function verify($jwt){
        loadEnv::cargar();

        $microsoftAccessToken = $_SESSION["adnanhussainturki/microsoft"]["accessToken"] ?? NULL;
        $secretKey = $_ENV['KEY'] ?? NULL;

        if(isset($_SESSION['userId'])&&isset($_COOKIE['auth'])){
            try {
                $decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));     
                return array('success' => true, 'userId' => $decoded->userId, 'admin' => 'Local');
            } catch (\Exception $e) {
                return array('success' => false, 'message' => $e->getMessage());
            }
        } else if($microsoftAccessToken != NULL){

            $dbConnection = new DBConnection();
            $connection = $dbConnection->getConnection();
            $microsoftActions = new MicrosoftActions($connection);

            $userId = $microsoftActions->getUserId($microsoftAccessToken);
            if($userId['success']){
                $verifyUserRegistration = $microsoftActions->getUserRegistration($userId['userId']);
                if($verifyUserRegistration){
                    return array('success' => true, 'userId' => $userId['userId'], 'admin' => true, 'accessToken' => $microsoftAccessToken);
                }else{
                    return array('success' => true, 'admin' => NULL, 'accessToken' => $microsoftAccessToken);
                }
            }else{
                return array('success' => false, 'message' => $userId['error']);
            }
        }
        else{
            return array('success' => false, 'message' => 'Sesión no iniciada');
        }
    }

    public static function check(){
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $jwt = $_COOKIE['auth'] ?? NULL;

        $result = self::verify($jwt);

        if(!$result['success']){
            setcookie('auth', '', time() - 3600, '/');
            session_unset();
        }

        return $result;
    }

}