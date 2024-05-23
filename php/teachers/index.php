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

class TeachersControl{
    
        private $con;
        private $sesion;
    
        public function __construct($con, $sesion){
            $this->con = $con;
            $this->sesion = $sesion;
        }
    
        public function GetTeachers(){
            if(!$this->sesion['success']){
                return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
            }else{
                $query = "SELECT * FROM teachers";
                $result = mysqli_query($this->con, $query);
                
                if(!$result){
                    return array("success" => false, "message" => "Error al obtener los datos de los profesores,por favor intente de nuevo más tarde");
                }else{
                    $teachers = array();
                    if($result->num_rows > 0){
                        while($row = $result->fetch_assoc()){
                            $teachers[] = array(
                                "success" => true,
                                "id" => $row['id'],
                                "name" => $row['nombre'],
                                "phone" => $row['telefono'],
                                "email" => $row['email']
                            );
                        }
                    }else{
                        return array("success" => false, "message" => "No se encontraron alumnos registrados");
                    }
                    $this->con->close();
                    return $teachers;
                }
            }
        }

        public function GetTeacher($idTeacher){
            if(!$this->sesion['success']){
                return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
            }else{
                $query = "SELECT * FROM teachers WHERE id = ?";
                $stmt = $this->con->prepare($query);
                $stmt->bind_param("i", $idTeacher);
                $stmt->execute();
                $result = $stmt->get_result();
                $teacher = array();
                if($result->num_rows > 0){
                    while($row = $result->fetch_assoc()){
                        $teacher = array(
                            "success" => true,
                            "id" => $row['id'],
                            "name" => $row['nombre'],
                            "gender" => $row['genero'],                            
                            "birthdate" => $row['nacimiento'],
                            "civil_status" => $row['estado_civil'],
                            "phone" => $row['telefono'],
                            "email" => $row['email'],
                        );
                    }
                }else{
                    return array("success" => false, "message" => "No se encontró el profesor solicitado");
                }
                $stmt->close();
                $this->con->close();
                return $teacher;
            }
        }

        public function UpdateTeacherData($teacherData){
            if(!$this->sesion['success']){
                return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
            }else{
                $query = "UPDATE teachers SET nombre = ?, genero = ?, nacimiento = ?, estado_civil = ?, telefono = ?, email = ? WHERE id = ?";
                $stmt = $this->con->prepare($query);
                $stmt->bind_param("ssssssi", $teacherData['teacherNameEdit'], $teacherData['teacherGenderEdit'], $teacherData['teacherBirthdayEdit'], $teacherData['teacherStateEdit'], $teacherData['teacherPhoneEdit'], $teacherData['teacherEmailEdit'], $teacherData['idTeacherEdit']);
                $stmt->execute();

                if($stmt->affected_rows > 0){
                    $stmt->close();
                    $this->con->close();
                    return array("success" => true, "message" => "Datos actualizados correctamente");
                }else{
                    $stmt->close();
                    $this->con->close();
                    return array("success" => false, "message" => "Error al actualizar los datos del profesor, por favor intente de nuevo más tarde");
                }
               
            }
        }

        public function DeleteTeacher($teacherId){
            if(!$this->sesion['success']){
                return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
            }else{
                $query = "DELETE FROM teachers WHERE id = ?";
                $stmt = $this->con->prepare($query);
                $stmt->bind_param("i", $teacherId);
                $stmt->execute();

                if($stmt->affected_rows > 0){
                    $stmt->close();
                    $this->con->close();
                    return array("success" => true, "message" => "Profesor eliminado correctamente");
                }else{
                    $stmt->close();
                    $this->con->close();
                    return array("success" => false, "message" => "Error al eliminar el profesor, por favor intente de nuevo más tarde");
                }
            }
        }
}