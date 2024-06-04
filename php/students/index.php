<?php
require_once(__DIR__.'/../vendor/autoload.php');
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
            $sql = "SELECT groups.nombre as nombre_grupo, students.* FROM students LEFT JOIN groups ON students.id_group = groups.id";
            $query = $this->con->query($sql);

            if(!$query){
                return array("success" => false, "message" => "Error al obtener los alumnos, por favor intente de nuevo más tarde");
            }else{
                cargarEnv();
                $students = array();
                $secret_key = $_ENV['SECRET_KEY'];
                if($query->num_rows > 0){                    
                    while($row = $query->fetch_assoc()){
                        $payload = [
                            "sId" => $row['id']
                        ];                    
                        $token = JWT::encode($payload, $secret_key, 'HS256');

                        $students[] = array(
                            'success' => true,
                            'token' => $token,
                            'id' => $row['id'],
                            'no_control' => $row['no_control'],
                            'name' => $row['nombre'],
                            'phone' => $row['telefono'],
                            'email' => $row['email'],
                            'group_name' => $row['nombre_grupo']
                        );
                    }
                }else{
                    $students[] = array("success" => false, "message" => "No se encontraron alumnos registrados");
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
                return array("success" => true, "user" => false,"message" => "El usuario ya existe");
            }else{
                return array("success" => true, "user" => true,"message" => "Usuario disponible");
            }
        }
    }

    function AddStudentUser($studentDataArray){
                
        if(!$this->sesion['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }else{
            $status = 'Activo'; 
            $sql = "INSERT INTO login_students (student_id, user, password, status) VALUES (?, ?, ?, ?)";
            $stmt = $this->con->prepare($sql);
            $stmt->bind_param('isss', $studentDataArray['studentUserId'], $studentDataArray['studentUserAdd'], $studentDataArray['studentUserPass'], $status);
            $stmt->execute();
            
            if($stmt->affected_rows > 0){
                $stmt->close();
                $this->con->close();
                return array("success" => true, "message" => "Usuario registrado correctamente");
            }else{
                $stmt->close();
                $this->con->close();
                return array("success" => false, "message" => "Error al registrar el usuario, por favor intente de nuevo más tarde");
            }
        }
    }

    function UpdateStudentUser($studentEditDataArray){
            
        if(!$this->sesion['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }else{
            $sql = "UPDATE login_students SET user = ?, password = ? WHERE student_id = ?";
            $stmt = $this->con->prepare($sql);
            $stmt->bind_param('ssi', $studentEditDataArray['studentUserAddEdit'], $studentEditDataArray['studentUserPassEdit'], $studentEditDataArray['studentUserIdEdit']);
            $stmt->execute();
    
            if($stmt->affected_rows > 0){
                $stmt->close();
                $this->con->close();
                return array("success" => true, "message" => "Usuario actualizado correctamente");
            }else{
                $stmt->close();
                $this->con->close();
                return array("success" => false, "message" => "Error al actualizar el usuario, por favor intente de nuevo más tarde");
            }
        }
    }

    function DesactivateStudentUser($studentId){
            
        if(!$this->sesion['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }else{
            $status = 'Inactivo';
            $sql = "UPDATE login_students SET status = ? WHERE student_id = ?";
            $stmt = $this->con->prepare($sql);
            $stmt->bind_param('si', $status, $studentId);
            $stmt->execute();
    
            if($stmt->affected_rows > 0){
                $stmt->close();
                $this->con->close();
                return array("success" => true, "message" => "Usuario desactivado correctamente");
            }else{
                $stmt->close();
                $this->con->close();
                return array("success" => false, "message" => "Error al desactivar el usuario, por favor intente de nuevo más tarde");
            }
        }
    }

    function ReactivateStudentUser($studentId){
            
        if(!$this->sesion['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }else{
            $status = 'Activo';
            $sql = "UPDATE login_students SET status = ? WHERE student_id = ?";
            $stmt = $this->con->prepare($sql);
            $stmt->bind_param('si', $status, $studentId);
            $stmt->execute();
    
            if($stmt->affected_rows > 0){
                $stmt->close();
                $this->con->close();
                return array("success" => true, "message" => "Usuario reactivado correctamente");
            }else{
                $stmt->close();
                $this->con->close();
                return array("success" => false, "message" => "Error al reactivar el usuario, por favor intente de nuevo más tarde");
            }
        }
    }

    public function GetSubjectsNames($careerId){
            
            if(!$this->sesion['success']){
                return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
            }else{
                $sql = "SELECT carreers_subjects.id_carreer, carreers_subjects.id_subject, subjects.nombre 
                FROM carreers_subjects 
                INNER JOIN subjects ON carreers_subjects.id_subject = subjects.id 
                WHERE carreers_subjects.id_carreer = ?";
                $stmt = $this->con->prepare($sql);
                $stmt->bind_param('i', $careerId);
                $stmt->execute();
                $query = $stmt->get_result();
    
                if(!$query){
                    return array("success" => false, "message" => "Error al obtener las materias, por favor intente de nuevo más tarde");
                }else{
                    $subjects = array();
                    if($query->num_rows > 0){
                        while($row = $query->fetch_assoc()){
                            $subjects[] = array(
                                'success' => true,
                                'id_career' => $row['id_carreer'],
                                'id_subject' => $row['id_subject'],
                                'name_subject' => $row['nombre']
                            );
                        }
                    }else{
                        $subjects[] = array("success" => false, "message" => "No se encontraron materias registradas");
                    }
                    $stmt->close();
                    $this->con->close();
    
                    return $subjects;
                }
            }
    }

    public function VerifyGroupStudent($studentIdGroup){
            
            if(!$this->sesion['success']){
                return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
            }else{
                $sql = "SELECT students.id_group, groups.id_carreer FROM students INNER JOIN groups ON students.id_group = groups.id WHERE students.id = ?";
                $stmt = $this->con->prepare($sql);
                $stmt->bind_param('i', $studentIdGroup);
                $stmt->execute();
                $query = $stmt->get_result();
    
                if($query->num_rows > 0){
                    return array("success" => true, "group" => true, "id_career" => $query->fetch_assoc()['id_carreer'], "message" => "Alumno con grupo asignado");
                }else{
                    return array("success" => true, "group" => false, "message" => "Alumno sin grupo asignado");
                }
            }
    }

    public function AddGradeStudent($gradeDataArray){
            
            if(!$this->sesion['success']){
                return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
            }else{
                $sql = "INSERT INTO student_grades (id_student, id_subject, continuos_grade, exam_grade, final_grade) VALUES (?, ?, ?, ?, ?)";
                $stmt = $this->con->prepare($sql);
                $stmt->bind_param('iiiii', $gradeDataArray['studentIdDB'], $gradeDataArray['subject'], $gradeDataArray['gradeCont'], $gradeDataArray['gradetest'], $gradeDataArray['gradefinal']);
                $stmt->execute();
        
                if($stmt->affected_rows > 0){
                    $stmt->close();
                    $this->con->close();
                    return array("success" => true, "message" => "Calificación registrada correctamente");
                }else{
                    $stmt->close();
                    $this->con->close();
                    return array("success" => false, "message" => "Error al registrar la calificación, por favor intente de nuevo más tarde");
                }
            }
    }

}

