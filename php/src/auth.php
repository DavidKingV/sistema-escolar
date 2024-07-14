<?php
namespace Vendor\Schoolarsystem;
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
                return array('success' => true, 'userId' => $decoded->userId);
            } catch (\Exception $e) {
                return array('success' => false, 'message' => $e->getMessage());
            }
        } else if(isset($microsoftAccessToken)){
            return array('success' => true, 'accessToken' => $microsoftAccessToken);
        }
        else{
            return array('success' => false, 'message' => 'No tiene permisos para realizar esta acción');
        }
    }

}