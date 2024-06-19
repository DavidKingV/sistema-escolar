<?php
require_once(__DIR__.'/../vendor/autoload.php');
session_start();

use Vendor\Schoolarsystem\auth;
use Vendor\Schoolarsystem\loadEnv;
use Vendor\Schoolarsystem\DBConnection;

class CareersControl{
    private $connection;

    public function __construct(DBConnection $dbConnection) {
        $this->connection = $dbConnection->getConnection();
    }

    public function getCareers(){
        $VerifySession = auth::verify($_COOKIE['auth'] ?? NULL);
       if(!$VerifySession['success']){
           return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }else{
            $sql = "SELECT * FROM carreers";
            $query = $this->connection->query($sql);

            if(!$query){
                return array("success" => false, "message" => "Error al obtener las carreras");
            }else{
                $careers = array();
                if($query->num_rows > 0){
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
                }else{
                    $careers[] = array("success" => false, "message" => "No hay carreras registradas");
                }
                $this->connection->close();
                return $careers;
            }
        }
    }

    public function getCareer($idCarreer){
        $VerifySession = auth::verify($_COOKIE['auth'] ?? NULL);
       if(!$VerifySession['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }else{
            $sql = "SELECT * FROM carreers WHERE id = ?";
            $stmt = $this->connection->prepare($sql);
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
            $this->connection->close();
    
            return $carreer;
        }
    }

    public function addCarreer($carreerData){
        $VerifySession = auth::verify($_COOKIE['auth'] ?? NULL);
       if(!$VerifySession['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }else{
            $sql = "INSERT INTO carreers (nombre, area, subarea, descripcion) VALUES (?, ?, ?, ?)";
            $stmt = $this->connection->prepare($sql);
            $stmt->bind_param('ssss', $carreerData['careerName'], $carreerData['careerArea'], $carreerData['careerSubarea'], $carreerData['careerDes']);
            $stmt->execute();

            if($stmt->affected_rows > 0){
                $stmt->close();
                $this->connection->close();
                return array("success" => true, "message" => "Carrera agregada correctamente");
            }else{
                $stmt->close();
                $this->connection->close();
                return array("success" => false, "message" => "Error al agregar la carrera");
            }
        }
    }

    public function updateCarreer($carreerDataEditArray){
        $VerifySession = auth::verify($_COOKIE['auth'] ?? NULL);
       if(!$VerifySession['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }else{
            $sql = "UPDATE carreers SET nombre = ?, area = ?, subarea = ?, descripcion = ? WHERE id = ?";
            $stmt = $this->connection->prepare($sql);
            $stmt->bind_param('ssssi', $carreerDataEditArray['careerNameEdit'], $carreerDataEditArray['carreerAreaEdit'], $carreerDataEditArray['careerSubareaEdit'], $carreerDataEditArray['careerComentsEdit'], $carreerDataEditArray['idCarreerDB']);
            $stmt->execute();

            if($stmt->affected_rows > 0){
                $stmt->close();
                $this->connection->close();
                return array("success" => true, "message" => "Carrera actualizada correctamente");
            }else{
                $stmt->close();
                $this->connection->close();
                return array("success" => false, "message" => "Error al actualizar la carrera");
            }
        }
    }

    public function deleteCarreer($idCarreer){
        $VerifySession = auth::verify($_COOKIE['auth'] ?? NULL);
       if(!$VerifySession['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }else{
            $sql = "DELETE FROM carreers WHERE id = ?";
            $stmt = $this->connection->prepare($sql);
            $stmt->bind_param('i', $idCarreer);
            $stmt->execute();

            if($stmt->affected_rows > 0){
                $stmt->close();
                $this->connection->close();
                return array("success" => true, "message" => "Carrera eliminada correctamente");
            }else{
                $stmt->close();
                $this->connection->close();
                return array("success" => false, "message" => "Error al eliminar la carrera");
            }
        }
    }
}