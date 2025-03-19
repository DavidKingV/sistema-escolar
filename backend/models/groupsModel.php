<?php
require __DIR__.'/../../php/vendor/autoload.php';

use Vendor\Schoolarsystem\DBConnection;

class GroupsModel{
    private $connection;
    

    public function __construct(DBConnection $dbConnection) {
        $this->connection = $dbConnection->getConnection();      
    }

    public function getNoGroupStudentsList($search = '', $page = 1, $limit = 30){
        try {
            // Query base
            $sql = "SELECT id, nombre FROM students WHERE 1=1 AND id_group IS NULL";
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

    public function getGroupsCount($search = '') {
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

    public function addSchedule($data){
        try {
            $sql = "INSERT INTO schedules (id_group, title, date, start, end, description) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->connection->prepare($sql);
            
            if(!$stmt) {
                throw new Exception("Error al preparar la consulta");
            }

            $stmt->bind_param('isssss', $data['groupId'], $data['title'], $data['date'], $data['inputStart'], $data['inputEnd'], $data['description']);
            $stmt->execute();
            $stmt->close();

            return ['success' => true, 'message' => 'Horario agregado correctamente'];
        } catch(Exception $e) {
            return ['success' => false, 'message' => 'Error al agregar el horario' . $e->getMessage()];
        }
    }

    public function getSchedulesGroup($groupId){
        try {
            $sql = "SELECT * FROM schedules WHERE id_group = ?";
            $stmt = $this->connection->prepare($sql);
            
            if(!$stmt) {
                throw new Exception("Error al preparar la consulta");
            }

            $stmt->bind_param('i', $groupId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                return array(['success' => false, 'message' => 'No hay horarios para este grupo']);
            }
            
            $schedules = [];
            while ($row = $result->fetch_assoc()) {
                $schedules[] = [
                    'success' => true,
                    'id' => $row['id'],
                    'title' => $row['title'],
                    'date' => $row['date'],
                    'start' => $row['start'],
                    'end' => $row['end'],
                    'description' => $row['description']
                ];
            };

            $stmt->close();

            return $schedules;
        } catch(Exception $e) {
            return array(['success' => false, 'message' => 'Error al obtener los horarios' . $e->getMessage()]);
        }
    }
}