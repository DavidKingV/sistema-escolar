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
}