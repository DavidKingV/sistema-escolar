<?php
require __DIR__.'/../../php/vendor/autoload.php';

use Vendor\Schoolarsystem\DBConnection;

class SubjectsModel{
    private $connection;

    public function __construct(DBConnection $dbConnection) {
        $this->connection = $dbConnection->getConnection();
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

    public function addSubjectCareer(array $subjectData): array {
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
                'error'   => 'No se pudo agregar la materia'
            ];
    
        } catch (Exception $e) {
            return [
                'success' => false,
                'error'   => 'Error al agregar la materia: ' . $e->getMessage()
            ];
        }
    }
}