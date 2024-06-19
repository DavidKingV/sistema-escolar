<?php
require_once(__DIR__.'/../vendor/autoload.php');

use Vendor\Schoolarsystem\loadEnv;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

loadEnv::cargar();

class AdvancedStudentsControl{

    public function VerifyIdStudentId($studentId, $studentSecretKey){
        
    
        $secret_key = $_ENV['KEY'];
        //comparar el id del estudiante con el id del token
         $decoded = JWT::decode($studentSecretKey, new Key($secret_key, 'HS256'));

        if($decoded->sId == $studentId){
            return array("success" => true, "token" => true, "studentId" => $decoded->sId, "message" => "El id del estudiante coincide con el token");
        }else{
            return array("success" => false, "token" => false, "message" => "El id del estudiante no coincide con el token");
        }   
       
    }
}

?>