<?php
namespace Vendor\Schoolarsystem\Controllers;

use Vendor\Schoolarsystem\DBConnection;
use Vendor\Schoolarsystem\Models\AdmissionsModel;
use Vendor\Schoolarsystem\auth;

class AdmissionsController{
    private $connection;
    private $admissions;

    public function __construct(DBConnection $dbConnection) {
        $this->connection = $dbConnection;
        $this->admissions = new AdmissionsModel($dbConnection);
    }

    public function getAllNewAdmissions(){
        $verifySession = auth::check();
        if(!$verifySession['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }
        return $this->admissions->getAllNewAdmissions();
    }

    public function deleteAdmission($id){
        $verifySession = auth::check();
        if(!$verifySession['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }
        return $this->admissions->deleteAdmission($id);
    }
}