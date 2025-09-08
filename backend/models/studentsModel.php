<?php
require __DIR__.'/../vendor/autoload.php';

use Vendor\Schoolarsystem\DBConnection;

class StudentsModel{
    private $connection;

    public function __construct(DBConnection $dbConnection) {
        $this->connection = $dbConnection->getConnection();
    }

    public function getStudentName($studentId){
        try{
            $sql = "SELECT nombre FROM students WHERE id = ?";
            $stmt = $this->connection->prepare($sql);
            
            if (!$stmt) {
                throw new Exception('Error al preparar la consulta SQL: ' . $this->connection->error);
            }

            $stmt->bind_param('i', $studentId);

            if (!$stmt->execute()) {
                throw new Exception('Error al ejecutar la consulta SQL: ' . $stmt->error);
            }

            $result = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            return [
                'success' => true,
                'studentName' => $result['nombre'],
            ];
        }catch(Exception $e){
            return [
                'success' => false,
                'message' => 'Error al obtener el nombre del estudiante',
                'error' => $e->getMessage()
            ];
        }
    }

    public function getStudentsListSelect($search = '', $page = 1, $limit = 30){
        try {
            // Query base
            $sql = "SELECT * FROM students WHERE 1=1";
            $params = [];
            $types = "";
            
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

    public function getStudentsCount($search = '') {
        try {
            $sql = "SELECT COUNT(*) as total FROM students WHERE 1=1";
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

    public function updateStatus($statusData){
        try{
            $sql = "UPDATE students SET academical_status = ? WHERE id = ?";
            $stmt = $this->connection->prepare($sql);
            
            if (!$stmt) {
                throw new Exception('Error al preparar la consulta SQL: ' . $this->connection->error);
            }

            $stmt->bind_param('ii', $statusData['studentStatus'], $statusData['studentId']);

            if (!$stmt->execute()) {
                throw new Exception('Error al ejecutar la consulta SQL: ' . $stmt->error);
            }

            if ($stmt->affected_rows == 0) {
                return [
                    'success' => false,
                    'message' => 'No se encontró la cita',
                    'error' => 'No se encontró la cita'
                ];
            }

            $stmt->close();

            return [
                'success' => true,
                'message' => 'Estatus actualizado correctamente'
            ];
        }catch(Exception $e){
            return [
                'success' => false,
                'message' => 'Error al actualizar el estatus',
                'error' => $e->getMessage()
            ];
        }
    }
}