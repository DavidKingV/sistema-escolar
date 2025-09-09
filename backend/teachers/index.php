<?php
require_once(__DIR__.'/../vendor/autoload.php');

session_start();

use Vendor\Schoolarsystem\auth;
use Vendor\Schoolarsystem\loadEnv;
use Vendor\Schoolarsystem\DBConnection;

class TeachersControl{

    private $connection;

    public function __construct(DBConnection $dbConnection) {
        $this->connection = $dbConnection->getConnection();
    }
    
        public function GetTeachers(){
            $VerifySession = auth::check();
            if(!$VerifySession['success']){
                return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
            }else{
                $query = "SELECT * FROM teachers";
                $result = mysqli_query($this->connection, $query);
                
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
                        $teachers[] = array("success" => false, "message" => "No se encontraron alumnos registrados");
                    }
                    $this->connection->close();
                    return $teachers;
                }
            }
        }

        public function GetTeacher($idTeacher){
            $VerifySession = auth::check();
            if(!$VerifySession['success']){
                return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
            }else{
                $query = "SELECT * FROM teachers WHERE id = ?";
                $stmt = $this->connection->prepare($query);
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
                $this->connection->close();
                return $teacher;
            }
        }

        public function AddTeacher($teacherData){
            $VerifySession = auth::check();
            if(!$VerifySession['success']){
                return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
            }else{
                $query = "INSERT INTO teachers (nombre, genero, nacimiento, estado_civil, telefono, email) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $this->connection->prepare($query);
                $stmt->bind_param("ssssss", $teacherData['teacherName'], $teacherData['teacherGender'], $teacherData['teacherBirthday'], $teacherData['teacherState'], $teacherData['teacherPhone'], $teacherData['teacherEmail']);
                $stmt->execute();

                if($stmt->affected_rows > 0){
                    $stmt->close();
                    $this->connection->close();
                    return array("success" => true, "message" => "Profesor registrado correctamente");
                }else{
                    $stmt->close();
                    $this->connection->close();
                    return array("success" => false, "message" => "Error al registrar el profesor, por favor intente de nuevo más tarde");
                }
            }
        }

        public function UpdateTeacherData($teacherData){
            $VerifySession = auth::check();
            if(!$VerifySession['success']){
                return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
            }else{
                $query = "UPDATE teachers SET nombre = ?, genero = ?, nacimiento = ?, estado_civil = ?, telefono = ?, email = ? WHERE id = ?";
                $stmt = $this->connection->prepare($query);
                $stmt->bind_param("ssssssi", $teacherData['teacherNameEdit'], $teacherData['teacherGenderEdit'], $teacherData['teacherBirthdayEdit'], $teacherData['teacherStateEdit'], $teacherData['teacherPhoneEdit'], $teacherData['teacherEmailEdit'], $teacherData['idTeacherEdit']);
                $stmt->execute();

                if($stmt->affected_rows > 0){
                    $stmt->close();
                    $this->connection->close();
                    return array("success" => true, "message" => "Datos actualizados correctamente");
                }else{
                    $stmt->close();
                    $this->connection->close();
                    return array("success" => false, "message" => "Error al actualizar los datos del profesor, por favor intente de nuevo más tarde");
                }
               
            }
        }

        public function DeleteTeacher($teacherId){
            $VerifySession = auth::check();
            if(!$VerifySession['success']){
                return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
            }else{
                $query = "DELETE FROM teachers WHERE id = ?";
                $stmt = $this->connection->prepare($query);
                $stmt->bind_param("i", $teacherId);
                $stmt->execute();

                if($stmt->affected_rows > 0){
                    $stmt->close();
                    $this->connection->close();
                    return array("success" => true, "message" => "Profesor eliminado correctamente");
                }else{
                    $stmt->close();
                    $this->connection->close();
                    return array("success" => false, "message" => "Error al eliminar el profesor, por favor intente de nuevo más tarde");
                }
            }
        }

        function GetTeachersUsers(){
            $VerifySession = auth::check();
            if(!$VerifySession['success']){
                return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
            }else{
                $query = "SELECT teachers.id, teachers.nombre, login_teachers.user, login_teachers.status FROM teachers LEFT JOIN login_teachers ON teachers.id = login_teachers.id_teacher";
                $result = mysqli_query($this->connection, $query);
                
                if(!$result){
                    return array("success" => false, "message" => "Error al obtener los datos de los profesores, por favor intente de nuevo más tarde");
                }else{
                    $teachers = array();
                    if($result->num_rows > 0){
                        while($row = $result->fetch_assoc()){
                            $teachers[] = array(
                                "success" => true,
                                "name" => $row['nombre'],
                                "id" => $row['id'],
                                "user" => $row['user'],
                                "status" => $row['status']
                            );
                        }
                    }else{
                        $teachers[] = array(
                            "success" => false,
                            "message" => "No se encontraron profesores registrados"
                        );
                    }
                    $this->connection->close();
                    return $teachers;
                }
            }
        }

        function VerifyTeacherUser($teacherUserAdd){
            $VerifySession = auth::check();
            if(!$VerifySession['success']){
                return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
            }else{
                $query = "SELECT user FROM login_teachers WHERE user = ?";
                $stmt = $this->connection->prepare($query);
                $stmt->bind_param("s", $teacherUserAdd);
                $stmt->execute();
                $result = $stmt->get_result();
                if($result->num_rows > 0){
                    return array("success" => true, "user" => false,"message" => "El usuario ya existe");
                }else{
                    return array("success" => true, "user" => true,"message" => "Usuario disponible");
                }
            }
        }

        function AddTeacherUser($teacherUserAddArray){
            $VerifySession = auth::check();
            if(!$VerifySession['success']){
                return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
            }else{
                $status = "Activo";
                $query = "INSERT INTO login_teachers (id_teacher, user, password, status) VALUES (?, ?, ?, ?)";
                $stmt = $this->connection->prepare($query);
                $stmt->bind_param("isss", $teacherUserAddArray['teacherUserId'], $teacherUserAddArray['teacherUserAdd'], $teacherUserAddArray['teacherUserPass'], $status);
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

        function DesactivateTeacherUser($teacherUserId){
            $VerifySession = auth::check();
            if(!$VerifySession['success']){
                return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
            }else{
                $status = "Inactivo";
                $query = "UPDATE login_teachers SET status = ? WHERE id_teacher = ?";
                $stmt = $this->connection->prepare($query);
                $stmt->bind_param("si", $status, $teacherUserId);
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

        function ReactivateTeacherUser($teacherUserId){
            $VerifySession = auth::check();
            if(!$VerifySession['success']){
                return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
            }else{
                $status = "Activo";
                $query = "UPDATE login_teachers SET status = ? WHERE id_teacher = ?";
                $stmt = $this->connection->prepare($query);
                $stmt->bind_param("si", $status, $teacherUserId);
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

        function UpdateTeacherUserData($teacherUserDataArray){
            $VerifySession = auth::check();
            if(!$VerifySession['success']){
                return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
            }else{
                $query = "UPDATE login_teachers SET user = ?, password = ? WHERE id_teacher = ?";
                $stmt = $this->connection->prepare($query);
                $stmt->bind_param("ssi", $teacherUserDataArray['teacherUserAddEdit'], $teacherUserDataArray['teacherUserPassEdit'], $teacherUserDataArray['teacherUserIdEdit']);
                $stmt->execute();

                if($stmt->affected_rows > 0){
                    $stmt->close();
                    $this->connection->close();
                    return array("success" => true, "message" => "Datos actualizados correctamente");
                }else{
                    $stmt->close();
                    $this->connection->close();
                    return array("success" => false, "message" => "Error al actualizar los datos del usuario, por favor intente de nuevo más tarde");
                }
            }
        }
}