<?php
require_once(__DIR__.'/../vendor/autoload.php');

session_start();

use Vendor\Schoolarsystem\auth;
use Vendor\Schoolarsystem\loadEnv;
use Vendor\Schoolarsystem\DBConnection;

class SubjectsControl{
    
    private $connection;

    public function __construct(DBConnection $dbConnection) {
        $this->connection = $dbConnection->getConnection();
    }
    
    public function GetSubjects(){
        $VerifySession = auth::check();
        if(!$VerifySession['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }else{
            $query = "SELECT DISTINCT 
    subjects.nombre, 
    subjects.descripcion, 
    subjects.id AS id_subject, 
    carreers_subjects.id_carreer AS id_carrera, 
    carreers.nombre AS nombre_carrera, 
    subject_child.nombre AS nombre_subject_child, 
    subject_child.id AS id_subjet_child
FROM subjects 
LEFT JOIN carreers_subjects ON subjects.id = carreers_subjects.id_subject 
LEFT JOIN carreers ON carreers_subjects.id_carreer = carreers.id 
LEFT JOIN subject_child ON subjects.id = subject_child.id_subject;";
            $result = mysqli_query($this->connection, $query); 
            
            if (!$result) {
                return array("success" => false, "message" => "Error al obtener las materias");
            }else{
                $subjects = array();
                if($result->num_rows > 0){
                    while($row = $result->fetch_assoc()){
                        $subjects[] = array(
                            'success' => true, 
                            'id' => $row['id_subject'],
                            'name' => $row['nombre'],
                            'id_carrer' => $row['id_carrera'], //Se mostrará el id de la carrera en la tabla de materias, para que el usuario pueda identificar a qué carrera pertenece la materia
                            'id_child' => $row['id_subjet_child'] ?? 'No asignado', //Si no hay un child asignado, se mostrará 'No asignado
                            'child' => $row['nombre_subject_child'] ?? 'No asignado',
                            'career' => $row['nombre_carrera'], 
                            'description' => $row['descripcion']
                        );
                    }
                }else{
                    $subjects[] = array("success" => false, "message" => "No se encontraron materias");
                }
                $this->connection->close();
                return $subjects;
            }
        }
    }

    public function GetSubjectData($subjectId){
        $VerifySession = auth::check();
        if(!$VerifySession['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }else{
            $query = "SELECT * FROM subjects WHERE id = ?";
            $stmt = $this->connection->prepare($query);
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
                    "key" => $row['clave'],
                    "name" => $row['nombre'],
                    "description" => $row['descripcion'],
                );
                $this->connection->close();
                return $subject;
            }
        }
    }

    public function AddSubject($subjectDataArray){
        $VerifySession = auth::check();
        if(!$VerifySession['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }else{
            $query = "INSERT INTO subjects (clave, nombre, descripcion) VALUES (?, ?, ?)";
            $stmt = $this->connection->prepare($query);
            $stmt->bind_param("sss", $subjectDataArray['subjectKey'], $subjectDataArray['subjectName'], $subjectDataArray['subjectDes']);
            $stmt->execute();

            if($stmt->affected_rows > 0){
                $this->connection->close();
                return array("success" => true, "message" => "Materia agregada correctamente");
            }else{
                return array("success" => false, "message" => "Error al agregar la materia");
            }
        }
    }

    public function UpdateSubjectData($subjectDataEditArray){
        $VerifySession = auth::check();
        if(!$VerifySession['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }else{
            $query = "UPDATE subjects SET clave = ?, nombre = ?, descripcion = ? WHERE id = ?";
            $stmt = $this->connection->prepare($query);
            $stmt->bind_param("ssi", $subjectDataArray['subjectKeyEdit'], $subjectDataEditArray['subjectNameEdit'], $subjectDataEditArray['descriptionSubjectEdit'], $subjectDataEditArray['idSubjectDB']);
            $stmt->execute();

            if($stmt->affected_rows > 0){
                $this->connection->close();
                return array("success" => true, "message" => "Datos de la materia actualizados correctamente");
            }else{
                return array("success" => false, "message" => "Error al actualizar los datos de la materia");
            }
        }
    }

    public function DeleteSubject($subjectId){
        $VerifySession = auth::check();
        if(!$VerifySession['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }else{
            $query = "DELETE FROM subjects WHERE id = ?";
            $stmt = $this->connection->prepare($query);
            $stmt->bind_param("i", $subjectId);
            $stmt->execute();

            if($stmt->affected_rows > 0){
                $this->connection->close();
                return array("success" => true, "message" => "Materia eliminada correctamente");
            }else{
                return array("success" => false, "message" => "Error al eliminar la materia");
            }
        }
    }
}


class SubjectsControlChild extends SubjectsControl{
     
    private $connection;

    public function __construct(DBConnection $dbConnection) {
        $this->connection = $dbConnection->getConnection();
    }

    public function AddSubjectChild($subjectChildDataArray){
        
        $VerifySession = auth::check();
        if(!$VerifySession['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }else{
            $query = "INSERT INTO subject_child (id_subject, clave, nombre, descripcion) VALUES (?, ?, ?)";
            $stmt = $this->connection->prepare($query);
            $stmt->bind_param("iss", $subjectChildDataArray['idMainSubject'], $subjectChildDataArray['subjectChildKey'], $subjectChildDataArray['subjectChildName'], $subjectChildDataArray['descriptionChildSubject']);
            $stmt->execute();

            $newlyCreatedId = $this->connection->insert_id;

            if($stmt->affected_rows > 0){
                $secondQuery = "UPDATE carreers_subjects SET id_child_subject = ? WHERE id_subject = ? AND id_carreer = ?";
                $secondStmt = $this->connection->prepare($secondQuery);
                $secondStmt->bind_param("iii", $newlyCreatedId, $subjectChildDataArray['idMainSubject'], $subjectChildDataArray['carrerId']);

                $secondStmt->execute();

                if($secondStmt->affected_rows > 0){
                    $this->connection->close();
                    return array("success" => true, "message" => "Materia y submateria agregadas correctamente");
                }else{
                    return array("success" => false, "message" => "Error al agregar la submateria a la carrera");
                }
            }else{
                return array("success" => false, "message" => "Error al agregar la materia a la carrera");
            }
        }
    }

    public function GetSubjectChildData($subjectFatherId, $subjectChildId){
        $VerifySession = auth::check();
        if(!$VerifySession['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }else{
            $query = "SELECT * FROM subject_child WHERE id = ? AND id_subject = ?";
            $stmt = $this->connection->prepare($query);
            $stmt->bind_param("ii", $subjectChildId , $subjectFatherId);
            $stmt->execute();

            $result = $stmt->get_result();
            
            if(!$result){
                return array("success" => false, "message" => "Error al obtener la materia");
            }else{
                $row = $result->fetch_assoc();
                $subject = array(
                    "success" => true,
                    "id" => $row['id'],
                    "id_subject" => $row['id_subject'],
                    "name" => $row['nombre'],
                    "description" => $row['descripcion'],
                );
                $this->connection->close();
                return $subject;
            }
        }
    }

    public function UpdateSubjectChild ($subjectChildDataEditArray){
        $VerifySession = auth::check();
        if(!$VerifySession['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }else{
            $query = "UPDATE subject_child SET clave = ?, nombre = ?, descripcion = ? WHERE id = ? AND id_subject = ?";
            $stmt = $this->connection->prepare($query);
            $stmt->bind_param("ssii", $subjectChildDataArray['subjectChildKey'], $subjectChildDataEditArray['subjectChildNameInfo'], $subjectChildDataEditArray['descriptionChildSubjectInfo'], $subjectChildDataEditArray['idMainSubjectInfo'], $subjectChildDataEditArray['idChildSubjectInfo']);
            $stmt->execute();

            if($stmt->affected_rows > 0){
                $this->connection->close();
                return array("success" => true, "message" => "Datos de la materia actualizados correctamente");
            }else{
                return array("success" => false, "message" => "Error al actualizar los datos de la materia");
            }
        }
    }
    
}

?>