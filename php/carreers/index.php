<?php
require_once(__DIR__.'/../../../vendor/autoload.php');
include __DIR__.'/../db.php';
include __DIR__.'/../login/index.php';

session_start();

use \Firebase\JWT\JWT;
use Firebase\JWT\Key;

$sesionControl = new LoginControl($con);
if(isset($_COOKIE['auth'])){
    $sesion = $sesionControl->VerifySession($_COOKIE['auth']);
}else{
    $sesion = array("success" => false);
}

class CareersControl{
    private $con;
    private $sesion;

    public function __construct($con, $sesion){
        $this->con = $con;
        $this->sesion = $sesion;
    }

    public function getCareers(){
       if(!$this->sesion['success']){
           return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
         }else{
            $sql = "SELECT * FROM carreers";
            $query = $this->con->query($sql);

            if(!$query){
                return array("success" => false, "message" => "Error al obtener las carreras");
            }else{
                $careers = array();
                while($row = $query->fetch_assoc()){
                    $careers[] = array(
                        "success" => true,  
                        "id" => $row['id'],
                        "name" => $row['nombre'],
                        "area" => $row['area'],
                        "subarea" => $row['subarea'],
                        "description" => $row['descripcion']
                    );
                }
                $this->con->close();
                return $careers;
            }
        }
    }

    public function getCareer($idCarreer){
        if(!$this->sesion['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }else{
            $sql = "SELECT * FROM carreers WHERE id = ?";
            $stmt = $this->con->prepare($sql);
            $stmt->bind_param('i', $idCarreer);
            $stmt->execute();

            $result = $stmt->get_result(); //
            if($result->num_rows === 0) {
                return array("success" => false, "message" => "Carrera no encontrada");
            }
            $row = $result->fetch_assoc(); // Obtener la fila asociativa

            $carreer = array(
                "success" => true,
                "id" => $row['id'],
                "name" => $row['nombre'],
                "area" => $row['area'],
                "subarea" => $row['subarea'],
                "description" => $row['descripcion']
            );
            $stmt->close();
            $this->con->close();
    
            return $carreer;
        }
    }

    public function addCarreer($carreerData){
        if(!$this->sesion['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }else{
            $sql = "INSERT INTO carreers (nombre, area, subarea, descripcion) VALUES (?, ?, ?, ?)";
            $stmt = $this->con->prepare($sql);
            $stmt->bind_param('ssss', $carreerData['careerName'], $carreerData['careerArea'], $carreerData['careerSubarea'], $carreerData['careerDes']);
            $stmt->execute();

            if($stmt->affected_rows > 0){
                $stmt->close();
                $this->con->close();
                return array("success" => true, "message" => "Carrera agregada correctamente");
            }else{
                $stmt->close();
                $this->con->close();
                return array("success" => false, "message" => "Error al agregar la carrera");
            }
        }
    }

    public function updateCarreer($carreerDataEditArray){
        if(!$this->sesion['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }else{
            $sql = "UPDATE carreers SET nombre = ?, area = ?, subarea = ?, descripcion = ? WHERE id = ?";
            $stmt = $this->con->prepare($sql);
            $stmt->bind_param('ssssi', $carreerDataEditArray['careerNameEdit'], $carreerDataEditArray['carreerAreaEdit'], $carreerDataEditArray['careerSubareaEdit'], $carreerDataEditArray['careerComentsEdit'], $carreerDataEditArray['idCarreerDB']);
            $stmt->execute();

            if($stmt->affected_rows > 0){
                $stmt->close();
                $this->con->close();
                return array("success" => true, "message" => "Carrera actualizada correctamente");
            }else{
                $stmt->close();
                $this->con->close();
                return array("success" => false, "message" => "Error al actualizar la carrera");
            }
        }
    }

    public function deleteCarreer($idCarreer){
        if(!$this->sesion['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }else{
            $sql = "DELETE FROM carreers WHERE id = ?";
            $stmt = $this->con->prepare($sql);
            $stmt->bind_param('i', $idCarreer);
            $stmt->execute();

            if($stmt->affected_rows > 0){
                $stmt->close();
                $this->con->close();
                return array("success" => true, "message" => "Carrera eliminada correctamente");
            }else{
                $stmt->close();
                $this->con->close();
                return array("success" => false, "message" => "Error al eliminar la carrera");
            }
        }
    }
}