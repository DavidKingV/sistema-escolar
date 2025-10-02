<?php
namespace Vendor\Schoolarsystem\Models;

use Vendor\Schoolarsystem\DBConnection;

class SubjectsModel{
    private $connection;

    public function __construct(DBConnection $dbConnection) {
        $this->connection = $dbConnection->getConnection();
    }

    public function fetchSubjects(){
        try {
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

            $result = $this->connection->query($query);

            if(!$result){
                return array("success" => false, "message" => "Error al obtener las materias");
            }

            $subjects = array();

            if($result->num_rows > 0){
                while($row = $result->fetch_assoc()){
                    $subjects[] = array(
                        'success' => true,
                        'id' => $row['id_subject'],
                        'name' => $row['nombre'],
                        'id_carrer' => $row['id_carrera'],
                        'id_child' => $row['id_subjet_child'] ?? 'No asignado',
                        'child' => $row['nombre_subject_child'] ?? 'No asignado',
                        'career' => $row['nombre_carrera'],
                        'description' => $row['descripcion']
                    );
                }
            }else{
                $subjects[] = array("success" => false, "message" => "No se encontraron materias");
            }

            $result->free();

            return $subjects;

        } catch (Exception $e) {
            return array("success" => false, "message" => "Error al obtener las materias");
        }
    }

    public function findSubjectById($subjectId){
        try {
            $query = "SELECT * FROM subjects WHERE id = ?";
            $stmt = $this->connection->prepare($query);

            if(!$stmt){
                return array("success" => false, "message" => "Error al preparar la consulta de materias");
            }

            $stmt->bind_param("i", $subjectId);
            $stmt->execute();

            $result = $stmt->get_result();

            if(!$result){
                $stmt->close();
                return array("success" => false, "message" => "Error al obtener la materia");
            }

            if($result->num_rows === 0){
                $stmt->close();
                return array("success" => false, "message" => "Materia no encontrada");
            }

            $row = $result->fetch_assoc();
            $stmt->close();

            return array(
                "success" => true,
                "id" => $row['id'],
                "key" => $row['clave'],
                "name" => $row['nombre'],
                "description" => $row['descripcion'],
            );

        } catch (Exception $e) {
            return array("success" => false, "message" => "Error al obtener la materia");
        }
    }

    public function createSubject($subjectDataArray){
        try {
            $query = "INSERT INTO subjects (clave, nombre, descripcion) VALUES (?, ?, ?)";
            $stmt = $this->connection->prepare($query);

            if(!$stmt){
                return array("success" => false, "message" => "Error al preparar la creación de la materia");
            }

            $stmt->bind_param("sss", $subjectDataArray['subjectKey'], $subjectDataArray['subjectName'], $subjectDataArray['subjectDes']);
            $stmt->execute();

            $affectedRows = $stmt->affected_rows;
            $stmt->close();

            if($affectedRows > 0){
                return array("success" => true, "message" => "Materia agregada correctamente");
            }

            return array("success" => false, "message" => "Error al agregar la materia");

        } catch (Exception $e) {
            return array("success" => false, "message" => "Error al agregar la materia");
        }
    }

    public function updateSubject($subjectDataEditArray){
        try {
            $query = "UPDATE subjects SET clave = ?, nombre = ?, descripcion = ? WHERE id = ?";
            $stmt = $this->connection->prepare($query);

            if(!$stmt){
                return array("success" => false, "message" => "Error al preparar la actualización de la materia");
            }

            $stmt->bind_param(
                "sssi",
                $subjectDataEditArray['subjectKeyEdit'],
                $subjectDataEditArray['subjectNameEdit'],
                $subjectDataEditArray['descriptionSubjectEdit'],
                $subjectDataEditArray['idSubjectDB']
            );

            $stmt->execute();

            $affectedRows = $stmt->affected_rows;
            $stmt->close();

            if($affectedRows > 0){
                return array("success" => true, "message" => "Datos de la materia actualizados correctamente");
            }

            return array("success" => false, "message" => "Error al actualizar los datos de la materia");

        } catch (Exception $e) {
            return array("success" => false, "message" => "Error al actualizar los datos de la materia");
        }
    }

    public function deleteSubject($subjectId){
        try {
            $query = "DELETE FROM subjects WHERE id = ?";
            $stmt = $this->connection->prepare($query);

            if(!$stmt){
                return array("success" => false, "message" => "Error al preparar la eliminación de la materia");
            }

            $stmt->bind_param("i", $subjectId);
            $stmt->execute();

            $affectedRows = $stmt->affected_rows;
            $stmt->close();

            if($affectedRows > 0){
                return array("success" => true, "message" => "Materia eliminada correctamente");
            }

            return array("success" => false, "message" => "Error al eliminar la materia");

        } catch (Exception $e) {
            return array("success" => false, "message" => "Error al eliminar la materia");
        }
    }

    public function createSubjectChild($subjectChildDataArray){
        try {
            $query = "INSERT INTO subject_child (id_subject, clave, nombre, descripcion) VALUES (?, ?, ?, ?)";
            $stmt = $this->connection->prepare($query);

            if(!$stmt){
                return array("success" => false, "message" => "Error al preparar la creación de la submateria");
            }

            $stmt->bind_param(
                "isss",
                $subjectChildDataArray['idMainSubject'],
                $subjectChildDataArray['subjectChildKey'],
                $subjectChildDataArray['subjectChildName'],
                $subjectChildDataArray['descriptionChildSubject']
            );

            $stmt->execute();

            $newlyCreatedId = $this->connection->insert_id;
            $stmt->close();

            if($newlyCreatedId <= 0){
                return array("success" => false, "message" => "Error al agregar la materia a la carrera");
            }

            $secondQuery = "UPDATE carreers_subjects SET id_child_subject = ? WHERE id_subject = ? AND id_carreer = ?";
            $secondStmt = $this->connection->prepare($secondQuery);

            if(!$secondStmt){
                return array("success" => false, "message" => "Error al preparar la asignación de la submateria");
            }

            $secondStmt->bind_param(
                "iii",
                $newlyCreatedId,
                $subjectChildDataArray['idMainSubject'],
                $subjectChildDataArray['carrerId']
            );

            $secondStmt->execute();

            if ($secondStmt->errno) {
                return array("success" => false, "message" => "Error en UPDATE: " . $secondStmt->error);
            }

            $affectedRows = $secondStmt->affected_rows;
            $secondStmt->close();

            // Si se ejecutó bien, aunque no haya cambiado nada
            if($affectedRows >= 0){
                return array("success" => true, "message" => "Materia y submateria agregadas correctamente");
            }

        } catch (Exception $e) {
            return array("success" => false, "message" => "Error al agregar la submateria a la carrera". $e->getMessage());
        }
    }

    public function findSubjectChild($subjectFatherId, $subjectChildId){
        try {
            $query = "SELECT * FROM subject_child WHERE id = ? AND id_subject = ?";
            $stmt = $this->connection->prepare($query);

            if(!$stmt){
                return array("success" => false, "message" => "Error al preparar la consulta de submateria");
            }

            $stmt->bind_param("ii", $subjectChildId , $subjectFatherId);
            $stmt->execute();

            $result = $stmt->get_result();

            if(!$result){
                $stmt->close();
                return array("success" => false, "message" => "Error al obtener la materia");
            }

            if($result->num_rows === 0){
                $stmt->close();
                return array("success" => false, "message" => "Submateria no encontrada");
            }

            $row = $result->fetch_assoc();
            $stmt->close();

            return array(
                "success" => true,
                "id" => $row['id'],
                "id_subject" => $row['id_subject'],
                "name" => $row['nombre'],
                "description" => $row['descripcion'],
            );

        } catch (Exception $e) {
            return array("success" => false, "message" => "Error al obtener la materia");
        }
    }

    public function updateSubjectChild($subjectChildDataEditArray){
        try {
            $key = $subjectChildDataEditArray['subjectChildKey'] ?? null;

            if($key !== null && $key !== ''){
                $query = "UPDATE subject_child SET clave = ?, nombre = ?, descripcion = ? WHERE id = ? AND id_subject = ?";
                $stmt = $this->connection->prepare($query);

                if(!$stmt){
                    return array("success" => false, "message" => "Error al preparar la actualización de la submateria");
                }

                $stmt->bind_param(
                    "sssii",
                    $key,
                    $subjectChildDataEditArray['subjectChildNameInfo'],
                    $subjectChildDataEditArray['descriptionChildSubjectInfo'],
                    $subjectChildDataEditArray['idChildSubjectInfo'],
                    $subjectChildDataEditArray['descriptionChildSubjectInfo']
                );
            }else{
                $query = "UPDATE subject_child SET nombre = ?, descripcion = ? WHERE id = ? AND id_subject = ?";
                $stmt = $this->connection->prepare($query);

                if(!$stmt){
                    return array("success" => false, "message" => "Error al preparar la actualización de la submateria");
                }

                $stmt->bind_param(
                    "ssii",
                    $subjectChildDataEditArray['subjectChildNameInfo'],
                    $subjectChildDataEditArray['descriptionChildSubjectInfo'],
                    $subjectChildDataEditArray['idMainSubjectInfo'],
                    $subjectChildDataEditArray['idChildSubjectInfo']
                );
            }

            $stmt->execute();

            if ($stmt->errno) {
                return array("success" => false, "message" => "Error en UPDATE: " . $stmt->error);
            }

            $affectedRows = $stmt->affected_rows;
            $stmt->close();

            // Si la query corrió sin error, aunque no cambió nada
            if($affectedRows >= 0){
                return array("success" => true, "message" => "Datos de la materia actualizados correctamente");
            }

            return array("success" => false, "message" => "Error desconocido al actualizar los datos de la materia");

        } catch (Exception $e) {
            return array("success" => false, "message" => "Error al actualizar los datos de la materia");
        }
    }

    public function deleteSubjectChild($subjectChildId){
        try {
            $query = "DELETE FROM subject_child WHERE id = ?";
            $stmt = $this->connection->prepare($query);
            
            if(!$stmt){
                return array("success" => false, "message" => "Error al preparar la eliminación de la submateria");
            }

            $stmt->bind_param("i", $subjectChildId);
            $stmt->execute();

            $affectedRows = $stmt->affected_rows;
            $stmt->close();

            if($affectedRows > 0){
                return array("success" => true, "message" => "Submateria eliminada correctamente");
            }

            return array("success" => false, "message" => "Error al eliminar la submateria");

        } catch (Exception $e) {
            return array("success" => false, "message" => "Error al eliminar la submateria");
        }
    }

    public function getChildSubject($subjectId) {
        try {
            $sql = "SELECT id, clave, nombre FROM subject_child WHERE id_subject = ?";
            $stmt = $this->connection->prepare($sql);
            $stmt->bind_param('i', $subjectId);
            $stmt->execute();
            
            $result = $stmt->get_result();
            $childSubjects = [];
    
            if ($result->num_rows === 0) {
                $childSubjects[] = [
                    "success" => false,
                    "message" => "No se encontraron materias hijas"
                ];
            } else {
                while ($row = $result->fetch_assoc()) {
                    // Puedes agregar más validaciones o transformar datos si es necesario
                    $childSubjects[] = [
                        "success" => true,
                        "childSubjectId" => $row['id'],
                        "childSubjectClave" => $row['clave'],
                        "childSubjectName" => $row['nombre']
                    ];
                }
            }
    
            $stmt->close();
            $this->connection->close();
            return $childSubjects;
        } catch (Exception $e) {
            // Aquí se podría loguear el error para mayor detalle
            return null;
        }
    }

    public function getSubjectsListSelect($search = '', $page = 1, $limit = 30, $careerId){
        try {
            // Query base
            $sql = "SELECT s.id, s.clave, s.nombre FROM subjects s LEFT JOIN carreers_subjects sc ON s.id = sc.id_subject AND sc.id_carreer = ? WHERE 1=1 AND sc.id_subject IS NULL";
            $params = [$careerId];
            $types = "i";
            
            // Agregar búsqueda si existe
            if (!empty($search)) {
                $sql .= " AND nombre LIKE ?";
                $params[] = "%$search%";
                $types .= "s";
            }
            
            // Agregar ordenamiento
            $sql .= " ORDER BY nombre ASC";
            
            // Agregar paginación
            $offset = ($page - 1) * $limit;
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            $types .= "ii";
            
            $stmt = $this->connection->prepare($sql);
            
            if ($stmt) {
                if (!empty($params)) {
                    $stmt->bind_param($types, ...$params);
                }
                $stmt->execute();
                $result = $stmt->get_result();
                $stmt->close();
                return $result;
            }
            return null;
        } catch(Exception $e) {
            return null;
        }
    }

    public function getSubjectsCount($search = '') {
        try {
            $sql = "SELECT COUNT(*) as total FROM subjects WHERE 1=1";
            $params = [];
            $types = "";
            
            if (!empty($search)) {
                $sql .= " AND nombre LIKE ?";
                $params[] = "%$search%";
                $types .= "s";
            }
            
            $stmt = $this->connection->prepare($sql);
            
            if ($stmt) {
                if (!empty($params)) {
                    $stmt->bind_param($types, ...$params);
                }
                $stmt->execute();
                $result = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                return $result['total'];
            }
            return 0;
        } catch(Exception $e) {
            return 0;
        }
    }

    public function subjectsListTable($careerId) {
        try {
            // Definir la consulta SQL con alias para mayor claridad.
            $sql = "SELECT 
                        s.id, 
                        s.clave AS claveSubject,
                        sc.clave AS claveSubjectChild,
                        s.nombre,
                        sc.nombre AS subject_child_nombre
                    FROM subjects s
                    INNER JOIN carreers_subjects cs 
                        ON s.id = cs.id_subject AND cs.id_carreer = ?
                    LEFT JOIN subject_child sc 
                        ON cs.id_child_subject = sc.id;";
                        
            // Preparar la consulta
            $stmt = $this->connection->prepare($sql);

            if (!$stmt) {
                throw new Exception("Error en la preparación de la consulta: " . $this->connection->error);
            }
            
            // Asignar los parámetros y ejecutar la consulta
            $stmt->bind_param('i', $careerId);
            $stmt->execute();
            
            // Obtener el resultado
            $result = $stmt->get_result();
            $subjects = array();
            
            // Verificar si se encontraron registros
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $subjects[] = array(
                        'success' => true,
                        'id' => $row['id'],
                        'claveSubject' => $row['claveSubject'] ?? 'Sin clave',
                        'claveSubjectChild' => $row['claveSubjectChild'] ?? 'Sin materias hijas',
                        'nombre' => $row['nombre'],
                        'subject_child_nombre' => $row['subject_child_nombre'] ?? 'Sin materias hijas'
                    );
                }
            } else {
                // Si no hay registros, se puede retornar un arreglo con el mensaje o simplemente un arreglo vacío.
                $subjects[] = array(
                    'success' => false,
                    'message' => 'No se encontraron materias'
                );
            }
            
            // Cerrar la sentencia una vez que ya no es necesaria
            $stmt->close();
            
            return $subjects;
            
        } catch(Exception $e) {
            return array(
                'success' => false,
                'message' => 'Error al obtener materias: ' . $e->getMessage()
            );
        }
    }

    public function addSubjectCareer($subjectData) {
        // Convertir y validar los datos de entrada
        $subject    = (int)$subjectData['subjectName'];
        $childSubject = isset($subjectData['childSubjectName']) ? (int)$subjectData['childSubjectName'] : NULL;
        $careerId   = (int)$subjectData['careerId'];
    
        $sql = "INSERT INTO carreers_subjects (id_subject, id_child_subject, id_carreer) VALUES (?, ?, ?)";
        
        try {
            $stmt = $this->connection->prepare($sql);
            if (!$stmt) {
                throw new Exception("Error en la preparación de la consulta: " . $this->connection->error);
            }
    
            // Se utiliza bind_param para enlazar los valores, asumiendo que las columnas son de tipo entero.
            // Si 'childSubject' puede ser null, es recomendable revisar la configuración de la BD para permitirlo.
            if (!$stmt->bind_param('iii', $subject, $childSubject, $careerId)) {
                throw new Exception("Error al enlazar los parámetros: " . $stmt->error);
            }
            
            if (!$stmt->execute()) {
                throw new Exception("Error en la ejecución de la consulta: " . $stmt->error);
            }
            
            $affectedRows = $stmt->affected_rows;
            $stmt->close();
    
            if ($affectedRows > 0) {
                return [
                    'success' => true,
                    'message' => 'Materia agregada correctamente'
                ];
            }
            return [
                'success' => false,
                'message'   => 'No se pudo agregar la materia'
            ];
    
        } catch (Exception $e) {
            return [
                'success' => false,
                'message'   => 'Error al agregar la materia: comprueba que se haya elegido una materia y su submateria'
            ];
        }
    }
}
