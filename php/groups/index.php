<?php
require_once(__DIR__.'/../vendor/autoload.php');
session_start();

use Vendor\Schoolarsystem\auth;
use Vendor\Schoolarsystem\loadEnv;
use Vendor\Schoolarsystem\DBConnection;

class GroupsControl{

    private $connection;

    public function __construct(DBConnection $dbConnection) {
        $this->connection = $dbConnection->getConnection();
    }
    
        public function GetGroups(){
            $VerifySession = auth::verify($_COOKIE['auth'] ?? NULL);
            if(!$VerifySession['success']){
                return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
            }else{
                $query = "SELECT carreers.nombre as nombre_carrera, groups.* FROM groups INNER JOIN carreers ON groups.id_carreer = carreers.id";
                $result = mysqli_query($this->connection, $query);                
                
                if(!$result){
                    return array("success" => false, "message" => "Error al obtener los grupos");
                }else{
                    $groups = array();
                    if($result->num_rows > 0){
                        while($row = $result->fetch_assoc()){
                            $groups[] = array(
                                "success" => true,
                                "id" => $row['id'],
                                "id_carreer" => $row['nombre_carrera'],
                                "key" => $row['clave'],
                                "name" => $row['nombre'],
                                "startDate" => $row['fecha_inicio'],
                                "endDate" => $row['fecha_termino']
                            );
                        }
                    }else{
                        $groups[] = array("success" => false, "message" => "No se encontraron grupos");
                    }
                    $this->connection->close();
                    return $groups;
                }
            }
        }

        public function GetGroupsStudents($groupId){
            $VerifySession = auth::verify($_COOKIE['auth'] ?? NULL);
            if(!$VerifySession['success']){
                return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
            }else{
                $query = "SELECT students.id as student_id, students.nombre as student_name, students.id_group as student_group_id, groups.id as group_id FROM students INNER JOIN groups ON students.id_group = groups.id WHERE students.id_group = $groupId";
                $result = mysqli_query($this->connection, $query);                
                
                if(!$result){
                    return array("success" => false, "message" => "Error al obtener los grupos");
                }else{
                    $groups = array();
                    if($result->num_rows > 0){
                        while($row = $result->fetch_assoc()){
                            $groups[] = array(
                                "success" => true,
                                "student_id" => $row['student_id'], // Cambiar a "id
                                "id_group" => $row['group_id'],
                                "student_name" => $row['student_name'],
                                "student_group_id" => $row['student_group_id']
                            );
                        }
                    }else{
                        $groups[] = array("success" => false, "message" => "No se encontraron alumnos en el grupo");
                    }
                    $this->connection->close();
                    return $groups;
                }
            }
        }

        public function GetStudentsNames(){
            $VerifySession = auth::verify($_COOKIE['auth'] ?? NULL);
            if(!$VerifySession['success']){
                return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
            }else{
                $query = "SELECT id, nombre FROM students WHERE id_group IS NULL";
                $result = mysqli_query($this->connection, $query);                
                
                if(!$result){
                    return array("success" => false, "message" => "Error al obtener los grupos");
                }else{
                    $students = array();
                    if($result->num_rows > 0){
                        while($row = $result->fetch_assoc()){
                            $students[] = array(
                                "success" => true,
                                "id" => $row['id'],
                                "name" => $row['nombre']
                            );
                        }
                    }else{
                        $students = array("success" => false, "message" => "No se encontraron grupos");
                    }
                    $this->connection->close();
                    return $students;
                }
            }
        }

        public function GetGroupData($groupId){
            $VerifySession = auth::verify($_COOKIE['auth'] ?? NULL);
            if(!$VerifySession['success']){
                return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
            }else{
                $query = "SELECT carreers.nombre as nombre_carrera, carreers.id as id_carreerCarreer, groups.* FROM groups INNER JOIN carreers ON groups.id_carreer = carreers.id WHERE groups.id = ?";
                $stmt = $this->connection->prepare($query);
                $stmt->bind_param('i', $groupId);    
                $stmt->execute();
                
                $result = $stmt->get_result();
                $groups = array();
                if($result->num_rows > 0){
                    while($row = $result->fetch_assoc()){
                        $groups = array(
                            "success" => true,
                            "id" => $row['id'],
                            "id_carreer" => $row['id_carreerCarreer'],
                            "carreer_name" => $row['nombre_carrera'],
                            "key" => $row['clave'],
                            "name" => $row['nombre'],
                            "startDate" => $row['fecha_inicio'],
                            "endDate" => $row['fecha_termino'],
                            "description" => $row['descripcion']
                        );
                    }
                    
                }else{
                    return array("success" => false, "message" => "No se encontró el grupo");        
                }
                $this->connection->close();
                return $groups;
            }
        }

        public function GetGroupsJson(){
            $VerifySession = auth::verify($_COOKIE['auth'] ?? NULL);
            if(!$VerifySession['success']){
                return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
            }else{
                $query = "SELECT id, nombre, area, subarea FROM carreers";
                $stmt = $this->connection->prepare($query);   
                $stmt->execute();
                
                $result = $stmt->get_result();
                
                if($result->num_rows > 0){
                    $structuredData = [];
                    foreach ($result as $row) {
                        $id = $row['id'];
                        $area = $row['area'];
                        $subarea = $row['subarea'];
                        $nombre = $row['nombre'];
                    
                        if (!isset($structuredData[$area])) {
                            $structuredData[$area] = [];
                        }
                    
                        if (!isset($structuredData[$area][$subarea])) {
                            $structuredData[$area][$subarea] = [];
                        }
                    
                        $structuredData[$area][$subarea][] = ['id' => $id, 'nombre' => $nombre];
                    }
                    $this->connection->close();
                    return $structuredData;
                    
                }else{
                    return array("success" => false, "message" => "No se encontró el grupo");        
                }
                
            }
        }
    
        public function AddGroup($groupDataArray){
            $VerifySession = auth::verify($_COOKIE['auth'] ?? NULL);
            if(!$VerifySession['success']){
                return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
            }else{
                $sql = "INSERT INTO groups (id_carreer, clave, nombre, fecha_inicio, fecha_termino, descripcion) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $this->connection->prepare($sql);
                $stmt->bind_param('isssss', $groupDataArray['carreerNameGroup'], $groupDataArray['keyGroup'], $groupDataArray['nameGroup'], $groupDataArray['startDate'], $groupDataArray['endDate'], $groupDataArray['descriptionGroup']);
                $stmt->execute();
    
                if($stmt->affected_rows > 0){
                    $stmt->close();
                    $this->connection->close();
                    return array("success" => true, "message" => "Grupo agregado correctamente");
                }else{
                    $stmt->close();
                    $this->connection->close();
                    return array("success" => false, "message" => "Error al agregar el grupo");
                }
            }
        }
    
        public function UpdateGroup($groupDataEditArray){
            $VerifySession = auth::verify($_COOKIE['auth'] ?? NULL);
            if(!$VerifySession['success']){
                return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
            }else{
                $sql = "UPDATE groups SET id_carreer = ?, clave = ?, nombre = ?, fecha_inicio = ?, fecha_termino = ?, descripcion = ? WHERE id = ?";
                $stmt = $this->connection->prepare($sql);
                $stmt->bind_param('ssssssi', $groupDataEditArray['idCarreerHidden'], $groupDataEditArray['keyGroupEdit'], $groupDataEditArray['nameGroupEdit'], $groupDataEditArray['startDateEdit'], $groupDataEditArray['endDateEdit'], $groupDataEditArray['descriptionGroupEdit'], $groupDataEditArray['idGroupDB']);
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
    
        public function DeleteGroup($groupId){
            $VerifySession = auth::verify($_COOKIE['auth'] ?? NULL);
            if(!$VerifySession['success']){
                return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
            }else{
                $query = "DELETE FROM groups WHERE id = ?";
                $stmt = $this->connection->prepare($query);
                $stmt->bind_param('i', $groupId);
                $stmt->execute();
                if($stmt->affected_rows > 0){
                    $stmt->close();
                    $this->connection->close();
                    return array("success" => true, "message" => "Grupo eliminado correctamente");
                }else{
                    $stmt->close();
                    $this->connection->close();
                    return array("success" => false, "message" => "Error al eliminar el grupo");
                }
            }
        }

        public function AddStudentGroup($groupId, $studentId){
            $VerifySession = auth::verify($_COOKIE['auth'] ?? NULL);
            if(!$VerifySession['success']){
                return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
            }else{
                foreach ($studentId as $id) {
                    $ids[] = $id;
                }
                
                // Convertimos el array de IDs a una cadena separada por comas
                $ids_str = implode(',', $ids);
                
                // Creamos la consulta SQL
                $query = "UPDATE students SET id_group = ? WHERE id IN ($ids_str)";
                $stmt = $this->connection->prepare($query);
                
                // Bind de parámetros
                $stmt->bind_param('i', $groupId);
                
                if ($stmt->execute()) {
                    $stmt->close();
                    $this->connection->close();
                    return array("success" => true, "message" => "Estudiantes agregados al grupo correctamente");
                } else {
                    $stmt->close();
                    $this->connection->close();
                    return array("success" => false, "message" => "Error al agregar los estudiantes al grupo");
                }
            }
        }

        public function DeleteStudentGroup($studentId){
            $VerifySession = auth::verify($_COOKIE['auth'] ?? NULL);
            if(!$VerifySession['success']){
                return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
            }else{
                
                $query = "UPDATE students SET id_group = NULL WHERE id = ?";
                $stmt = $this->connection->prepare($query);
                $stmt->bind_param('i', $studentId);
                $stmt->execute();
                
                if ($stmt->affected_rows > 0) {
                    $stmt->close();
                    $this->connection->close();
                    return array("success" => true, "message" => "Estudiantes eliminados del grupo correctamente");
                } else {
                    $error = $stmt->error;
                    $stmt->close();
                    $this->connection->close();
                    return array("success" => false, "message" => "Error al eliminar los estudiantes del grupo".$error);
                }
            }
        }
}