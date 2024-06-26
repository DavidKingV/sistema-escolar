<?php
require_once(__DIR__.'/../vendor/autoload.php');

session_start();

use Vendor\Schoolarsystem\auth;
use Vendor\Schoolarsystem\loadEnv;
use Vendor\Schoolarsystem\DBConnection;
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

loadEnv::cargar();

class StudentsControl {
    private $connection;

    public function __construct(DBConnection $dbConnection) {
        $this->connection = $dbConnection->getConnection();
    }
    public function GetStudents(){

        $VerifySession = auth::verify($_COOKIE['auth'] ?? NULL);
            if(!$VerifySession['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }else{
            $sql = "SELECT groups.nombre as nombre_grupo, students.* FROM students LEFT JOIN groups ON students.id_group = groups.id";
            $query = $this->connection->query($sql);

            if(!$query){
                return array("success" => false, "message" => "Error al obtener los alumnos, por favor intente de nuevo más tarde");
            }else{
                $students = array();
                $secretKey = $_ENV['KEY'];
                if($query->num_rows > 0){                    
                    while($row = $query->fetch_assoc()){
                        $payload = [
                            "sId" => $row['id']
                        ];                    
                        $token = JWT::encode($payload, $secretKey, 'HS256');

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
                $this->connection->close();

                return $students;
            }
        }  

    }


    function GetStudent($studentId){

        $VerifySession = auth::verify($_COOKIE['auth'] ?? NULL);
            if(!$VerifySession['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }else{
            $sql = "SELECT * FROM students WHERE id = ?";
            $stmt = $this->connection->prepare($sql);
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
                $this->connection->close();

                return $student;
            }

        }

    }

    function AddStudent($studentDataArray){
            
        $VerifySession = auth::verify($_COOKIE['auth'] ?? NULL);
            if(!$VerifySession['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }else{
            $sql = "INSERT INTO students (no_control, nombre, genero, nacimiento, estado_civil, nacionalidad, curp, telefono, email) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->connection->prepare($sql);
            $stmt->bind_param('sssssssss', $studentDataArray['controlNumber'], $studentDataArray['studentName'], $studentDataArray['studentGender'], $studentDataArray['studentBirthday'], $studentDataArray['studentState'], $studentDataArray['studentNation'], $studentDataArray['studentCurp'], $studentDataArray['studentPhone'], $studentDataArray['studentEmail']);
            $stmt->execute();
    
            if($stmt->affected_rows > 0){
                $stmt->close();
                $this->connection->close();
                return array("success" => true, "message" => "Alumno registrado correctamente");
            }else{
                $stmt->close();
                $this->connection->close();
                return array("success" => false, "message" => "Error al registrar el alumno, por favor intente de nuevo más tarde");
            }
        }   
    }

    function UpdateStudent($studentDataArray){

        $VerifySession = auth::verify($_COOKIE['auth'] ?? NULL);
            if(!$VerifySession['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }else{
            $sql = "UPDATE students SET no_control = ?, nombre = ?, genero = ?, nacimiento = ?, estado_civil = ?, nacionalidad = ?, curp = ?, telefono = ?, email = ? WHERE id = ?";
            $stmt = $this->connection->prepare($sql);
            $stmt->bind_param('sssssssssi', $studentDataArray['controlNumber'], $studentDataArray['studentName'], $studentDataArray['studentGender'], $studentDataArray['studentBirthday'], $studentDataArray['studentState'], $studentDataArray['studentNation'], $studentDataArray['studentCurp'], $studentDataArray['studentPhone'], $studentDataArray['studentEmail'], $studentDataArray['idStudentDB']);
            $stmt->execute();

            if($stmt->affected_rows > 0){
                $stmt->close();
                $this->connection->close();
                return array("success" => true, "message" => "Alumno actualizado correctamente");
            }else{
                $stmt->close();
                $this->connection->close();
                return array("success" => false, "message" => "Error al actualizar el alumno, por favor intente de nuevo más tarde");
            }
        }
    }
        

    function DeleteStudent($studentId){

        $VerifySession = auth::verify($_COOKIE['auth'] ?? NULL);
            if(!$VerifySession['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }else{
            $sql = "DELETE FROM students WHERE id = ?";
            $stmt = $this->connection->prepare($sql);
            $stmt->bind_param('i', $studentId);
            $stmt->execute();

            if($stmt->affected_rows > 0){
                $stmt->close();
                $this->connection->close();
                return array("success" => true, "message" => "Alumno eliminado correctamente");
            }else{
                $stmt->close();
                $this->connection->close();
                return array("success" => false, "message" => "Error al eliminar el alumno, por favor intente de nuevo más tarde");
            }
        }

    }

    function GetStudentsUsers(){

        $VerifySession = auth::verify($_COOKIE['auth'] ?? NULL);
            if(!$VerifySession['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }else{
            $sql = "SELECT students.id, students.no_control, students.nombre, login_students.user, login_students.status FROM students LEFT JOIN login_students ON students.id = login_students.student_id";
            $query = $this->connection->query($sql);

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
                    $students[] = array("success" => false, "message" => "No se encontraron alumnos registrados");
                }
                $this->connection->close();

                return $students;
            }
        }  

    }

    function VerifyStudentUser($studentUser){
            
        $VerifySession = auth::verify($_COOKIE['auth'] ?? NULL);
            if(!$VerifySession['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }else{
            $sql = "SELECT * FROM login_students WHERE user = ?";
            $stmt = $this->connection->prepare($sql);
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
                
        $VerifySession = auth::verify($_COOKIE['auth'] ?? NULL);
            if(!$VerifySession['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }else{
            $status = 'Activo'; 
            $sql = "INSERT INTO login_students (student_id, user, password, status) VALUES (?, ?, ?, ?)";
            $stmt = $this->connection->prepare($sql);
            $stmt->bind_param('isss', $studentDataArray['studentUserId'], $studentDataArray['studentUserAdd'], $studentDataArray['studentUserPass'], $status);
            $stmt->execute();
            
            if($stmt->affected_rows > 0){
                $stmt->close();
                $this->connection->close();
                return array("success" => true, "message" => "Usuario registrado correctamente");
            }else{
                $stmt->close();
                $this->connection->close();
                return array("success" => false, "message" => "Error al registrar el usuario, por favor intente de nuevo más tarde");
            }
        }
    }

    function UpdateStudentUser($studentEditDataArray){
            
        $VerifySession = auth::verify($_COOKIE['auth'] ?? NULL);
            if(!$VerifySession['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }else{
            $sql = "UPDATE login_students SET user = ?, password = ? WHERE student_id = ?";
            $stmt = $this->connection->prepare($sql);
            $stmt->bind_param('ssi', $studentEditDataArray['studentUserAddEdit'], $studentEditDataArray['studentUserPassEdit'], $studentEditDataArray['studentUserIdEdit']);
            $stmt->execute();
    
            if($stmt->affected_rows > 0){
                $stmt->close();
                $this->connection->close();
                return array("success" => true, "message" => "Usuario actualizado correctamente");
            }else{
                $stmt->close();
                $this->connection->close();
                return array("success" => false, "message" => "Error al actualizar el usuario, por favor intente de nuevo más tarde");
            }
        }
    }

    function DesactivateStudentUser($studentId){
            
        $VerifySession = auth::verify($_COOKIE['auth'] ?? NULL);
            if(!$VerifySession['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }else{
            $status = 'Inactivo';
            $sql = "UPDATE login_students SET status = ? WHERE student_id = ?";
            $stmt = $this->connection->prepare($sql);
            $stmt->bind_param('si', $status, $studentId);
            $stmt->execute();
    
            if($stmt->affected_rows > 0){
                $stmt->close();
                $this->connection->close();
                return array("success" => true, "message" => "Usuario desactivado correctamente");
            }else{
                $stmt->close();
                $this->connection->close();
                return array("success" => false, "message" => "Error al desactivar el usuario, por favor intente de nuevo más tarde");
            }
        }
    }

    function ReactivateStudentUser($studentId){
            
        $VerifySession = auth::verify($_COOKIE['auth'] ?? NULL);
            if(!$VerifySession['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }else{
            $status = 'Activo';
            $sql = "UPDATE login_students SET status = ? WHERE student_id = ?";
            $stmt = $this->connection->prepare($sql);
            $stmt->bind_param('si', $status, $studentId);
            $stmt->execute();
    
            if($stmt->affected_rows > 0){
                $stmt->close();
                $this->connection->close();
                return array("success" => true, "message" => "Usuario reactivado correctamente");
            }else{
                $stmt->close();
                $this->connection->close();
                return array("success" => false, "message" => "Error al reactivar el usuario, por favor intente de nuevo más tarde");
            }
        }
    }

    public function GetSubjectsNames($careerId){
            
            $VerifySession = auth::verify($_COOKIE['auth'] ?? NULL);
            if(!$VerifySession['success']){
                return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
            }else{
                $sql = "SELECT carreers_subjects.id_carreer, carreers_subjects.id_subject, carreers_subjects.id_child_subject, subjects.nombre, subject_child.nombre AS nombre_hijo FROM carreers_subjects INNER JOIN subjects ON carreers_subjects.id_subject = subjects.id LEFT JOIN subject_child ON carreers_subjects.id_child_subject = subject_child.id WHERE carreers_subjects.id_carreer = ?";
                $stmt = $this->connection->prepare($sql);
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
                                'name_subject' => $row['nombre'],
                                'id_child_subject' => $row['id_child_subject'],
                                'name_child_subject' => $row['nombre_hijo']
                            );
                        }
                    }else{
                        $subjects[] = array("success" => false, "message" => "No se encontraron materias registradas");
                    }
                    $stmt->close();
                    $this->connection->close();
    
                    return $subjects;
                }
            }
    }

    public function VerifyGroupStudent($studentIdGroup){
            
            $VerifySession = auth::verify($_COOKIE['auth'] ?? NULL);
            if(!$VerifySession['success']){
                return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
            }else{
                $sql = "SELECT students.id_group, groups.id_carreer FROM students INNER JOIN groups ON students.id_group = groups.id WHERE students.id = ?";
                $stmt = $this->connection->prepare($sql);
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
            
            $VerifySession = auth::verify($_COOKIE['auth'] ?? NULL);
            if(!$VerifySession['success']){
                return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
            }else{
                if($gradeDataArray['subjectChild'] != ""){
                    $id_subject_child= $gradeDataArray['subjectChild'];
                    $id_subject = NULL;
                }else{
                    $id_subject= $gradeDataArray['subject'];
                    $id_subject_child= NULL;
                }

                $sql = "INSERT INTO student_grades (id_student, id_subject, id_subject_child, continuos_grade, exam_grade, final_grade) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $this->connection->prepare($sql);
                $stmt->bind_param('iiiiii', $gradeDataArray['studentIdDB'], $id_subject, $id_subject_child, $gradeDataArray['gradeCont'], $gradeDataArray['gradetest'], $gradeDataArray['gradefinal']);
                $stmt->execute();
        
                if($stmt->affected_rows > 0){
                    $stmt->close();
                    $this->connection->close();
                    return array("success" => true, "message" => "Calificación registrada correctamente");
                }else{
                    $stmt->close();
                    $this->connection->close();
                    return array("success" => false, "message" => "Error al registrar la calificación, por favor intente de nuevo más tarde");
                }
            }
    }

}

