<?php

namespace Vendor\Schoolarsystem;

use GuzzleHttp\Client;

class MicrosoftActions{

    private $connection;
        
    public function __construct($connection){
        $this->connection = $connection;
    }

    public static function getAccessToken(){
        $tenant_id = getenv('TENANT_ID');
        $client_id = getenv('CLIENT_ID');
        $client_secret = getenv('CLIENT_SECRET');
        $url = "https://login.microsoftonline.com/$tenant_id/oauth2/v2.0/token";
        $data = array(
            'grant_type' => 'client_credentials',
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'scope' => 'https://graph.microsoft.com/.default'
        );
        $options = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data)
            )
        );
        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        $response = json_decode($result, true);
        return $response['access_token'];
    }

    public static function getUserId($accessToken){
        $client = new Client();

        try{
            $userIdResponse = $client->get('https://graph.microsoft.com/v1.0/me', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken
                ]
            ]);
            $userIdRes = json_decode($userIdResponse->getBody()->getContents());
            $userId = $userIdRes->id;

            return ['success' => true, 'userId' => $userId, 'error' => null];
        }catch (\GuzzleHttp\Exception\ClientException $e) {
            if ($e->getResponse()->getStatusCode() == 401) {
                $errorMessage = 'Error: ' . $e->getMessage();
                return ['success' => false, 'userId' => null, 'error' => $errorMessage];
            } else {
                // maneja otros códigos de error si es necesario
                $errorMessage = 'Error: ' . $e->getMessage();
                return ['success' => false, 'userId' => null, 'error' => $errorMessage];
            }
        }
    }

    public static function getUserName($accessToken){

        $client = new Client();

        try{
            $nameResponse = $client->get('https://graph.microsoft.com/v1.0/me', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken
                ]
            ]);
            $userNameRes = json_decode($nameResponse->getBody()->getContents());
            $userName = $userNameRes->displayName;
    
            return $userName;
        }catch (\GuzzleHttp\Exception\ClientException $e) {
            if ($e->getResponse()->getStatusCode() == 401) {
                // redirige al usuario a la página de inicio de sesión o a donde desees
                return 'Error: ' . $e->getMessage();
                exit();
            } else {
                // maneja otros códigos de error si es necesario
                return 'Error: ' . $e->getMessage();
            }
        }
 
    }

    public static function getProfilePhoto($accessToken){
        $client = new Client();

        try{
            $photoResponse = $client->get('https://graph.microsoft.com/v1.0/me/photo/$value', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'image/jpeg'
                ]
            ]);
            $photo = $photoResponse->getBody()->getContents();

            // Codificar la imagen en Base64
            $base64Image = base64_encode($photo);    
            // Crear una URL de datos (data URL)
            $dataUrl = 'data:image/jpeg;base64,' . $base64Image;
    
            return $dataUrl;
        }catch (\GuzzleHttp\Exception\ClientException $e) {
            if ($e->getResponse()->getStatusCode() == 401) {
                // redirige al usuario a la página de inicio de sesión o a donde desees
                return 'Error: ' . $e->getMessage();
                exit();
            } else {
                // maneja otros códigos de error si es necesario
                return 'Error: ' . $e->getMessage();
            }
        }
    }

    public function getUserRegistration($userId){

        $sql = "SELECT * FROM microsoft_admins WHERE microsoft_id = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("s", $userId);
        $stmt->execute();

        $result = $stmt->get_result();

        if($result->num_rows > 0){
            $stmt->close();
            $row = $result->fetch_assoc();
            return true;
        }else{
            return false;
        }
        
    }

    public static function getStudents($access_token){
        $url = "https://graph.microsoft.com/v1.0/education/classes/".getenv('CLASS_ID')."/members";
        $options = array(
            'http' => array(
                'header'  => "Authorization: Bearer $access_token\r\n",
                'method'  => 'GET'
            )
        );
        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        $response = json_decode($result, true);
        return $response['value'];
    }

}

?>