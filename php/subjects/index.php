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

class SubjectsControl{
    
    private $con;
    private $sesion;

    public function __construct($con, $sesion){
        $this->con = $con;
        $this->sesion = $sesion;
    }
    
    public function GetSubjects(){
        if(!$this->sesion['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }else{
            $query = "SELECT * FROM subjects";
            $result = mysqli_query($this->con, $query);
            
            if(!$result){
                return array("success" => false, "message" => "Error al obtener las materias");
            }else{
                $subjects = array();
                if($result->num_rows > 0){
                    while($row = $result->fetch_assoc()){
                        $subjects[] = array(
                            "success" => true,
                            "id" => $row['id'],
                            "name" => $row['nombre'],
                            "description" => $row['descripcion'],
                        );
                    }
                }else{
                    $subjects[] = array("success" => false, "message" => "No se encontraron materias");
                }
                $this->con->close();
                return $subjects;
            }
        }
    }

    public function GetSubjectData($subjectId){
        if(!$this->sesion['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }else{
            $query = "SELECT * FROM subjects WHERE id = ?";
            $stmt = $this->con->prepare($query);
            $stmt->bind_param("i", $subjectId);
            $stmt->execute();

            $result = $stmt->get_result();
            
            if(!$result){
                return array("success" => false, "message" => "Error al obtener la materia");
            }else{
                $row = $result->fetch_assoc();
                $subject = array(
                    "success" => true,
                    "id" => $row['id'],
                    "name" => $row['nombre'],
                    "description" => $row['descripcion'],
                );
                $this->con->close();
                return $subject;
            }
        }
    }

    public function AddSubject($subjectDataArray){
        if(!$this->sesion['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }else{
            $query = "INSERT INTO subjects (nombre, descripcion) VALUES (?, ?)";
            $stmt = $this->con->prepare($query);
            $stmt->bind_param("ss", $subjectDataArray['subjectName'], $subjectDataArray['subjectDes']);
            $stmt->execute();

            if($stmt->affected_rows > 0){
                $this->con->close();
                return array("success" => true, "message" => "Materia agregada correctamente");
            }else{
                return array("success" => false, "message" => "Error al agregar la materia");
            }
        }
    }

    public function UpdateSubjectData($subjectDataEditArray){
        if(!$this->sesion['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }else{
            $query = "UPDATE subjects SET nombre = ?, descripcion = ? WHERE id = ?";
            $stmt = $this->con->prepare($query);
            $stmt->bind_param("ssi", $subjectDataEditArray['subjectNameEdit'], $subjectDataEditArray['descriptionSubjectEdit'], $subjectDataEditArray['idSubjectDB']);
            $stmt->execute();

            if($stmt->affected_rows > 0){
                $this->con->close();
                return array("success" => true, "message" => "Datos de la materia actualizados correctamente");
            }else{
                return array("success" => false, "message" => "Error al actualizar los datos de la materia");
            }
        }
    }

    public function DeleteSubject($subjectId){
        if(!$this->sesion['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }else{
            $query = "DELETE FROM subjects WHERE id = ?";
            $stmt = $this->con->prepare($query);
            $stmt->bind_param("i", $subjectId);
            $stmt->execute();

            if($stmt->affected_rows > 0){
                $this->con->close();
                return array("success" => true, "message" => "Materia eliminada correctamente");
            }else{
                return array("success" => false, "message" => "Error al eliminar la materia");
            }
        }
    }
}