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

class StudentsControl {
    private $con;
    private $sesion;

    public function __construct($con, $sesion){
        $this->con = $con;
        $this->sesion = $sesion;
    }

    public function GetStudents(){

        if(!$this->sesion['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }else{
            $sql = "SELECT * FROM students";
            $query = $this->con->query($sql);

            if(!$query){
                return array("success" => false, "message" => "Error al obtener los alumnos, por favor intente de nuevo más tarde");
            }else{
                $students = array();
                if($query->num_rows > 0){
                    while($row = $query->fetch_assoc()){
                        $students[] = array(
                            'success' => true,
                            'id' => $row['id'],
                            'no_control' => $row['no_control'],
                            'name' => $row['nombre'],
                            'phone' => $row['telefono'],
                            'email' => $row['email']
                        );
                    }
                }else{
                    return array("success" => false, "message" => "No se encontraron alumnos registrados");
                }
                $this->con->close();

                return $students;
            }
        }  

    }


    function GetStudent($studentId){

        if(!$this->sesion['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }else{
            $sql = "SELECT * FROM students WHERE id = ?";
            $stmt = $this->con->prepare($sql);
            $stmt->bind_param('i', $studentId);
            $stmt->execute();
            $query = $stmt->get_result();

            if(!$query){
                return array("success" => false, "message" => "Error al obtener los datos del alumno, por favor intente de nuevo más tarde");
            }else{
                $row = $query->fetch_assoc();
                $student = array(
                    'success' => true,
                    'id' => $row['id'],
                    'no_control' => $row['no_control'],
                    'name' => $row['nombre'],
                    'gender' => $row['genero'],
                    'birthdate' => $row['nacimiento'],
                    'civil_status' => $row['estado_civil'],
                    'nationality' => $row['nacionalidad'],
                    'curp' => $row['curp'],
                    'phone' => $row['telefono'],
                    'email' => $row['email']
                );
                $stmt->close();
                $this->con->close();

                return $student;
            }

        }

    }

    function AddStudent($studentDataArray){
            
        if(!$this->sesion['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }else{
            $sql = "INSERT INTO students (no_control, nombre, genero, nacimiento, estado_civil, nacionalidad, curp, telefono, email) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->con->prepare($sql);
            $stmt->bind_param('sssssssss', $studentDataArray['controlNumber'], $studentDataArray['studentName'], $studentDataArray['studentGender'], $studentDataArray['studentBirthday'], $studentDataArray['studentState'], $studentDataArray['studentNation'], $studentDataArray['studentCurp'], $studentDataArray['studentPhone'], $studentDataArray['studentEmail']);
            $stmt->execute();
    
            if($stmt->affected_rows > 0){
                $stmt->close();
                $this->con->close();
                return array("success" => true, "message" => "Alumno registrado correctamente");
            }else{
                $stmt->close();
                $this->con->close();
                return array("success" => false, "message" => "Error al registrar el alumno, por favor intente de nuevo más tarde");
            }
        }   
    }

    function UpdateStudent($studentDataArray){

        if(!$this->sesion['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }else{
            $sql = "UPDATE students SET no_control = ?, nombre = ?, genero = ?, nacimiento = ?, estado_civil = ?, nacionalidad = ?, curp = ?, telefono = ?, email = ? WHERE id = ?";
            $stmt = $this->con->prepare($sql);
            $stmt->bind_param('sssssssssi', $studentDataArray['controlNumber'], $studentDataArray['studentName'], $studentDataArray['studentGender'], $studentDataArray['studentBirthday'], $studentDataArray['studentState'], $studentDataArray['studentNation'], $studentDataArray['studentCurp'], $studentDataArray['studentPhone'], $studentDataArray['studentEmail'], $studentDataArray['idStudentDB']);
            $stmt->execute();

            if($stmt->affected_rows > 0){
                $stmt->close();
                $this->con->close();
                return array("success" => true, "message" => "Alumno actualizado correctamente");
            }else{
                $stmt->close();
                $this->con->close();
                return array("success" => false, "message" => "Error al actualizar el alumno, por favor intente de nuevo más tarde");
            }
        }
    }
        

    function DeleteStudent($studentId){

        if(!$this->sesion['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }else{
            $sql = "DELETE FROM students WHERE id = ?";
            $stmt = $this->con->prepare($sql);
            $stmt->bind_param('i', $studentId);
            $stmt->execute();

            if($stmt->affected_rows > 0){
                $stmt->close();
                $this->con->close();
                return array("success" => true, "message" => "Alumno eliminado correctamente");
            }else{
                $stmt->close();
                $this->con->close();
                return array("success" => false, "message" => "Error al eliminar el alumno, por favor intente de nuevo más tarde");
            }
        }

    }

    function GetStudentsUsers(){

        if(!$this->sesion['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }else{
            $sql = "SELECT students.id, students.no_control, students.nombre, login_students.user, login_students.status FROM students LEFT JOIN login_students ON students.id = login_students.student_id";
            $query = $this->con->query($sql);

            if(!$query){
                return array("success" => false, "message" => "Error al obtener los alumnos, por favor intente de nuevo más tarde");
            }else{
                $students = array();
                if($query->num_rows > 0){
                    while($row = $query->fetch_assoc()){
                        $students[] = array(
                            'success' => true,
                            'id' => $row['id'],
                            'no_control' => $row['no_control'],
                            'name' => $row['nombre'],
                            'user' => $row['user'],
                            'status' => $row['status']
                        );
                    }
                }else{
                    return array("success" => false, "message" => "No se encontraron alumnos registrados");
                }
                $this->con->close();

                return $students;
            }
        }  

    }

    function VerifyStudentUser($studentUser){
            
            if(!$this->sesion['success']){
                return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
            }else{
                $sql = "SELECT * FROM login_students WHERE user = ?";
                $stmt = $this->con->prepare($sql);
                $stmt->bind_param('s', $studentUser);
                $stmt->execute();
                $query = $stmt->get_result();
    
                if($query->num_rows > 0){
                    return array("success" => true, "user" => true,"message" => "El usuario ya existe");
                }else{
                    return array("success" => true, "user" => false,"message" => "Usuario disponible");
                }
            }
    }

}