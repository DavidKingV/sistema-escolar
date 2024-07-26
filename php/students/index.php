<?php
require_once(__DIR__.'/../vendor/autoload.php');

session_start();

use Vendor\Schoolarsystem\auth;
use Vendor\Schoolarsystem\loadEnv;
use Vendor\Schoolarsystem\DBConnection;
use Vendor\Schoolarsystem\MicrosoftActions;
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
                            "studentId" => $row['id']
                        ];                    
                        $encodeJWT = JWT::encode($payload, $secretKey, 'HS256');

                        $students[] = array(
                            'success' => true,
                            'encodeJWT' => $encodeJWT,
                            'studentId' => $row['id'],
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
                $lastStudentId = $stmt->insert_id;
                $stmt->close();                


                if(isset($studentDataArray['microsoftId']) && isset($studentDataArray['microsoftEmail'])){
                    $sqlMicrosoft = "INSERT INTO microsoft_students (id, student_id, displayName, mail) VALUES (?, ?, ?, ?)";
                    $stmtMicrosoft = $this->connection->prepare($sqlMicrosoft);
                    $stmtMicrosoft->bind_param('siss', $studentDataArray['microsoftId'], $lastStudentId, $studentDataArray['studentName'], $studentDataArray['microsoftEmail']);
                    $stmtMicrosoft->execute();

                    if($stmtMicrosoft->affected_rows > 0){
                        $stmtMicrosoft->close();
                        $this->connection->close();
                        return array("success" => true, "message" => "Alumno registrado correctamente");
                    }else{
                        $stmtMicrosoft->close();
                        $this->connection->close();
                        return array("success" => false, "message" => "Error al registrar el alumno, por favor intente de nuevo más tarde");
                    }
                }

                $this->connection->close();        
                return array("success" => true, "message" => "Alumno local registrado correctamente");
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

    public function GetSubjectsNames($carrerId){
            
            $VerifySession = auth::verify($_COOKIE['auth'] ?? NULL);
            if(!$VerifySession['success']){
                return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
            }else{
                $sql = "SELECT carreers_subjects.id_carreer, carreers_subjects.id_subject, carreers_subjects.id_child_subject, subjects.nombre FROM carreers_subjects INNER JOIN subjects ON carreers_subjects.id_subject = subjects.id WHERE carreers_subjects.id_carreer = ?";
                $stmt = $this->connection->prepare($sql);
                $stmt->bind_param('i', $carrerId);
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
                                'id_child_subject' => $row['id_child_subject']
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

    public function GetChildSubjectsNames($idSubject){
            
            $VerifySession = auth::verify($_COOKIE['auth'] ?? NULL);
            if(!$VerifySession['success']){
                return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
            }else{
                $sql = "SELECT * FROM subject_child WHERE id_subject = ? ";
                $stmt = $this->connection->prepare($sql);
                $stmt->bind_param('i', $idSubject);
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
                                'id_child_subject' => $row['id'],
                                'id_subject' => $row['id_subject'],
                                'name_child_subject' => $row['nombre']
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
                    return array("success" => true, "group" => true, "id_carrer" => $query->fetch_assoc()['id_carreer'], "message" => "Alumno con grupo asignado");
                }else{
                    return array("success" => true, "group" => false, "message" => "Alumno sin grupo asignado");
                }
            }
    }

    public function GetStudentGrades($studentId){
        $VerifySession = auth::verify($_COOKIE['auth'] ?? NULL);
        if (!$VerifySession['success']) :
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        endif;

        $sql = "SELECT 
    sg.id AS grade_id,
    s.id AS student_id,
    s.nombre AS student_name,
    sub.id AS subject_id,
    sub.nombre AS subject_name,
    NULL AS subject_child_id,
    NULL AS subject_child_name,
    sg.continuos_grade AS continuous_grade,
    sg.exam_grade AS exam_grade,
    sg.final_grade AS final_grade,
    sg.updated_at AS update_at
    FROM 
        student_grades sg
    JOIN 
        students s ON sg.id_student = s.id
    JOIN 
        subjects sub ON sg.id_subject = sub.id
    WHERE 
        sg.id_student = ?

    UNION

    SELECT 
        sgc.id AS grade_id,
        s.id AS student_id,
        s.nombre AS student_name,
        sub.id AS subject_id,
        sub.nombre AS subject_name,
        sub_child.id AS subject_child_id,
        sub_child.nombre AS subject_child_name,
        sgc.continuos_grade AS continuous_grade,
        sgc.exam_grade AS exam_grade,
        sgc.final_grade AS final_grade,
        sgc.updated_at AS update_at
    FROM 
        student_grades_child sgc
    JOIN 
        students s ON sgc.id_student = s.id
    JOIN 
        subjects sub ON sgc.id_subject = sub.id
    JOIN 
        subject_child sub_child ON sgc.id_subject_child = sub_child.id
    WHERE 
        sgc.id_student = ?";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param('ii', $studentId, $studentId);
        $stmt->execute();
        $query = $stmt->get_result();

        if (!$query) {
            return array("success" => false, "message" => "Error al obtener las calificaciones del alumno, por favor intente de nuevo más tarde");
        } else {
            $grades = array();
            if ($query->num_rows > 0) {
                while ($row = $query->fetch_assoc()) {
                    $grades[] = array(
                        'success' => true,
                        'grade_id' => $row['grade_id'],
                        'student_id' => $row['student_id'],
                        'student_name' => $row['student_name'],
                        'subject_id' => $row['subject_id'],
                        'subject_name' => $row['subject_name'],
                        'subject_child_id' => $row['subject_child_id'],
                        'subject_child_name' => $row['subject_child_name'],
                        'continuous_grade' => $row['continuous_grade'],
                        'exam_grade' => $row['exam_grade'],
                        'final_grade' => $row['final_grade'],
                        'update_at' => $row['update_at']
                    );
                }
            } else {
                $grades[] = array("success" => false, "message" => "No se encontraron calificaciones registradas");
            }
            $stmt->close();
            $this->connection->close();

            return $grades;
        }
    }

    public function AddGradeStudent($gradeDataArray) {
        $VerifySession = auth::verify($_COOKIE['auth'] ?? NULL);
        if (!$VerifySession['success']) :
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        endif;
            try {
                $this->connection->begin_transaction();

                $idStudent = $gradeDataArray['studentId'];
                $idSubject = $gradeDataArray['subject'];

                if (isset($gradeDataArray['subjectChild'])) {
                    $sqlChild  = "INSERT INTO student_grades_child (id_student, id_subject, id_subject_child, continuos_grade, exam_grade, final_grade) VALUES (?, ?, ?, ?, ?, ?)";
                    $stmtChild  = $this->connection->prepare($sqlChild);
                    if ($stmtChild  === false) {
                        return array("success" => false, "message" => 'Error en la preparación de la consulta: ' . $this->connection->error);
                    }
                    $stmtChild ->bind_param('iiiddd', $idStudent, $idSubject, $gradeDataArray['subjectChild'], $gradeDataArray['gradeCont'], $gradeDataArray['gradetest'], $gradeDataArray['gradefinal']);
                    $stmtChild ->execute();
    
                    if ($stmtChild ->affected_rows > 0) {
                        $stmtChild ->close();
    
                        $count = "SELECT SUM(final_grade) as sum_grades, COUNT(*) as total FROM student_grades_child WHERE id_subject = ?";
                        $stmtCount  = $this->connection->prepare($count);
                        $stmtCount ->bind_param('i', $idSubject);
    
                        $stmtCount ->execute();
                        $result = $stmtCount ->get_result();
    
                        $row = $result->fetch_assoc();
                        $sumGrades = $row['sum_grades'];
                        $total = $row['total'];
                        $stmtCount ->close();
    
                        $average = ($total > 0) ? $sumGrades / $total : 0;
    
                        if ($average > 0) {
                            $sqlMain = "INSERT INTO student_grades (id_student, id_subject, continuos_grade, exam_grade, final_grade) 
                                    VALUES (?, ?, ?, ?, ?) 
                                    ON DUPLICATE KEY UPDATE 
                                    continuos_grade = VALUES(continuos_grade), 
                                    exam_grade = VALUES(exam_grade), 
                                    final_grade = VALUES(final_grade)";
                            $stmtMain = $this->connection->prepare($sqlMain);
                            if ($stmtMain === false) {
                                return array("success" => false, "message" => 'Error en la preparación de la consulta: ' . $this->connection->error);
                            }
                            $stmtMain->bind_param('iiddd', $idStudent, $idSubject, $average, $average, $average);
                            $stmtMain->execute();
    
                            if ($stmtMain->affected_rows > 0) {
                                $stmtMain->close();
                                $this->connection->commit();
                                return array("success" => true, "message" => "Calificación registrada correctamente");
                            } else {
                                $stmtMain->close();
                                return array("success" => false, "message" => "Error al registrar la calificación, por favor intente de nuevo más tarde");
                            }
                        } else {
                            $this->connection->commit();
                            return array("success" => true, "message" => "Se registro la calificación correctamente, pero no se pudo calcular el promedio de la materia principal");
                        }
                    } else {
                        $stmtChild->close();
                        return array("success" => false, "message" => $stmt->error);
                    }
                } else {
                    $sql = "INSERT INTO student_grades (id_student, id_subject, continuos_grade, exam_grade, final_grade) VALUES (?, ?, ?, ?, ?)";
                    $stmt = $this->connection->prepare($sql);
                    if ($stmt === false) {
                        return array("success" => false, "message" => 'Error en la preparación de la consulta: ' . $this->connection->error);
                    }
                    $stmt->bind_param('iiddd', $idStudent, $idSubject, $gradeDataArray['gradeCont'], $gradeDataArray['gradetest'], $gradeDataArray['gradefinal']);
                    $stmt->execute();
    
                    if ($stmt->affected_rows > 0) {
                        $stmt->close();
                        $this->connection->commit();
                        return array("success" => true, "message" => "Calificación registrada correctamente");
                    } else {
                        $stmt->close();
                        return array("success" => false, "message" => "Error al registrar la calificación, por favor intente de nuevo más tarde");
                    }
                }
            } catch (mysqli_sql_exception $e) {
                $this->connection->rollback();
                return array("success" => false, "message" => "Error de MySQL: " . $e->getMessage());
            } catch (Exception $e) {
                $this->connection->rollback();
                return array("success" => false, "message" => "Error: " . $e->getMessage());
            }
        
    }

    public function GetGroupsNames(){

        $VerifySession = auth::verify($_COOKIE['auth'] ?? NULL);
            if(!$VerifySession['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }else{
            $sql = "SELECT * FROM groups";
            $query = $this->connection->query($sql);

            if(!$query){
                return array("success" => false, "message" => "Error al obtener los grupos, por favor intente de nuevo más tarde");
            }else{
                $groups = array();
                if($query->num_rows > 0){
                    while($row = $query->fetch_assoc()){
                        $groups[] = array(
                            'success' => true,
                            'id' => $row['id'],
                            'name' => $row['nombre'],
                            'id_career' => $row['id_carreer']
                        );
                    }
                }else{
                    $groups[] = array("success" => false, "message" => "No se encontraron grupos registrados");
                }
                $this->connection->close();

                return $groups;
            }
        }  

    }

    public function addStudentGroup($studentGroupDataArray){
            
        $VerifySession = auth::verify($_COOKIE['auth'] ?? NULL);
            if(!$VerifySession['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }else{
            $sql = "UPDATE students SET id_group = ? WHERE id = ?";
            $stmt = $this->connection->prepare($sql);
            $stmt->bind_param('ii', $studentGroupDataArray['studentIdGroup'], $studentGroupDataArray['studentId']);
            $stmt->execute();
    
            if($stmt->affected_rows > 0){
                $stmt->close();
                $this->connection->close();
                return array("success" => true, "message" => "Grupo asignado correctamente");
            }else{
                $stmt->close();
                $this->connection->close();
                return array("success" => false, "message" => "Error al asignar el grupo, por favor intente de nuevo más tarde");
            }
        }
    }
    
    public function SearchMicrosoftUser($displayName){
        $VerifySession = auth::verify($_COOKIE['auth'] ?? NULL);
            if(!$VerifySession['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
            }else{
                $accessToken = $VerifySession['accessToken']?? NULL;

                if($accessToken != NULL){
                    $searchUser = new MicrosoftActions($this->connection);
                    $search = $searchUser->getStudentByName($accessToken, $displayName);
                    
                    if($search['success']){
                        return array("success" => true, "message" => "Usuario encontrado", "data" => $search);
                    }else{
                        return array("success" => false, "message" => $search['error']);
                    }
                }else{
                    return array("success" => false, "message" => "Debes iniciar sesión en Microsoft para poder enlazar un usuario a una cuenta");
                }                
            }
    }

}

