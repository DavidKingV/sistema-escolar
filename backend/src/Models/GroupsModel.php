<?php
namespace Vendor\Schoolarsystem\Models;

use Vendor\Schoolarsystem\DBConnection;
use Vendor\Schoolarsystem\auth;

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

    public function getGroups(){
        $VerifySession = auth::check();
        if(!$VerifySession['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }

        $sql = "SELECT carreers.nombre as nombre_carrera, groups.* FROM groups INNER JOIN carreers ON groups.id_carreer = carreers.id";
        $result = $this->connection->query($sql);

        if(!$result){
            $this->connection->close();
            return array("success" => false, "message" => "Error al obtener los grupos");
        }

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

        $result->free();
        $this->connection->close();

        return $groups;
    }

    public function getGroupsStudents($groupId){
        $VerifySession = auth::check();
        if(!$VerifySession['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }

        $sql = "SELECT students.id as student_id, students.nombre as student_name, students.id_group as student_group_id, groups.id as group_id FROM students INNER JOIN groups ON students.id_group = groups.id WHERE students.id_group = ?";
        $stmt = $this->connection->prepare($sql);

        if(!$stmt){
            $this->connection->close();
            return array("success" => false, "message" => "Error al obtener los grupos");
        }

        $stmt->bind_param('i', $groupId);
        $stmt->execute();

        $result = $stmt->get_result();

        $groups = array();
        if($result->num_rows > 0){
            while($row = $result->fetch_assoc()){
                $groups[] = array(
                    "success" => true,
                    "student_id" => $row['student_id'],
                    "id_group" => $row['group_id'],
                    "student_name" => $row['student_name'],
                    "student_group_id" => $row['student_group_id']
                );
            }
        }else{
            $groups[] = array("success" => false, "message" => "No se encontraron alumnos en el grupo");
        }

        $stmt->close();
        $this->connection->close();

        return $groups;
    }

    public function getStudentsNames(){
        $VerifySession = auth::check();
        if(!$VerifySession['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }

        $sql = "SELECT id, nombre FROM students WHERE id_group IS NULL";
        $result = $this->connection->query($sql);

        if(!$result){
            $this->connection->close();
            return array("success" => false, "message" => "Error al obtener los grupos");
        }

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

        $result->free();
        $this->connection->close();

        return $students;
    }

    public function getGroupData($groupId){
        $VerifySession = auth::check();
        if(!$VerifySession['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }

        $sql = "SELECT carreers.nombre as nombre_carrera, carreers.id as id_carreerCarreer, groups.* FROM groups INNER JOIN carreers ON groups.id_carreer = carreers.id WHERE groups.id = ?";
        $stmt = $this->connection->prepare($sql);

        if(!$stmt){
            $this->connection->close();
            return array("success" => false, "message" => "Error al obtener los grupos");
        }

        $stmt->bind_param('i', $groupId);
        $stmt->execute();

        $result = $stmt->get_result();

        if($result->num_rows === 0){
            $stmt->close();
            $this->connection->close();
            return array("success" => false, "message" => "No se encontró el grupo");
        }

        $group = array();
        while($row = $result->fetch_assoc()){
            $group = array(
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

        $stmt->close();
        $this->connection->close();

        return $group;
    }

    public function getGroupsJson(){
        $VerifySession = auth::check();
        if(!$VerifySession['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }

        $sql = "SELECT id, nombre, area, subarea FROM carreers";
        $stmt = $this->connection->prepare($sql);

        if(!$stmt){
            $this->connection->close();
            return array("success" => false, "message" => "Error al obtener los grupos");
        }

        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows === 0){
            $stmt->close();
            $this->connection->close();
            return array("success" => false, "message" => "No se encontró el grupo");
        }

        $structuredData = [];
        foreach($result as $row){
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

        $stmt->close();
        $this->connection->close();

        return $structuredData;
    }

    public function addGroup($groupDataArray){
        $VerifySession = auth::check();
        if(!$VerifySession['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }

        $sql = "INSERT INTO groups (id_carreer, clave, nombre, fecha_inicio, fecha_termino, descripcion) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->connection->prepare($sql);

        if(!$stmt){
            $this->connection->close();
            return array("success" => false, "message" => "Error al agregar el grupo");
        }

        $stmt->bind_param('isssss', $groupDataArray['carreerNameGroup'], $groupDataArray['keyGroup'], $groupDataArray['nameGroup'], $groupDataArray['startDate'], $groupDataArray['endDate'], $groupDataArray['descriptionGroup']);
        $stmt->execute();

        if($stmt->affected_rows > 0){
            $stmt->close();
            $this->connection->close();
            return array("success" => true, "message" => "Grupo agregado correctamente");
        }

        $stmt->close();
        $this->connection->close();
        return array("success" => false, "message" => "Error al agregar el grupo");
    }

    public function updateGroup($groupDataEditArray){
        $VerifySession = auth::check();
        if(!$VerifySession['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }

        $sql = "UPDATE groups SET id_carreer = ?, clave = ?, nombre = ?, fecha_inicio = ?, fecha_termino = ?, descripcion = ? WHERE id = ?";
        $stmt = $this->connection->prepare($sql);

        if(!$stmt){
            $this->connection->close();
            return array("success" => false, "message" => "Error al actualizar la carrera");
        }

        $stmt->bind_param('isssssi', $groupDataEditArray['idCarreerHidden'], $groupDataEditArray['keyGroupEdit'], $groupDataEditArray['nameGroupEdit'], $groupDataEditArray['startDateEdit'], $groupDataEditArray['endDateEdit'], $groupDataEditArray['descriptionGroupEdit'], $groupDataEditArray['idGroupDB']);
        $stmt->execute();

        if($stmt->affected_rows > 0){
            $stmt->close();
            $this->connection->close();
            return array("success" => true, "message" => "Carrera actualizada correctamente");
        }

        $stmt->close();
        $this->connection->close();
        return array("success" => false, "message" => "Error al actualizar la carrera");
    }

    public function deleteGroup($groupId){
        $VerifySession = auth::check();
        if(!$VerifySession['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }

        $sql = "DELETE FROM groups WHERE id = ?";
        $stmt = $this->connection->prepare($sql);

        if(!$stmt){
            $this->connection->close();
            return array("success" => false, "message" => "Error al eliminar el grupo");
        }

        $stmt->bind_param('i', $groupId);
        $stmt->execute();

        if($stmt->affected_rows > 0){
            $stmt->close();
            $this->connection->close();
            return array("success" => true, "message" => "Grupo eliminado correctamente");
        }

        $stmt->close();
        $this->connection->close();
        return array("success" => false, "message" => "Error al eliminar el grupo");
    }

    public function addStudentGroup($groupId, $studentId){
        $VerifySession = auth::check();
        if(!$VerifySession['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }

        if(!is_array($studentId) || empty($studentId)){
            $this->connection->close();
            return array("success" => false, "message" => "No se proporcionaron estudiantes válidos");
        }

        $ids = array_map('intval', $studentId);
        $ids_str = implode(',', $ids);

        $sql = "UPDATE students SET id_group = ? WHERE id IN ($ids_str)";
        $stmt = $this->connection->prepare($sql);

        if(!$stmt){
            $this->connection->close();
            return array("success" => false, "message" => "Error al agregar los estudiantes al grupo");
        }

        $stmt->bind_param('i', $groupId);

        if($stmt->execute()){
            $stmt->close();
            $this->connection->close();
            return array("success" => true, "message" => "Estudiantes agregados al grupo correctamente");
        }

        $stmt->close();
        $this->connection->close();
        return array("success" => false, "message" => "Error al agregar los estudiantes al grupo");
    }

    public function deleteStudentGroup($studentId){
        $VerifySession = auth::check();
        if(!$VerifySession['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }

        $sql = "UPDATE students SET id_group = NULL WHERE id = ?";
        $stmt = $this->connection->prepare($sql);

        if(!$stmt){
            $this->connection->close();
            return array("success" => false, "message" => "Error al eliminar los estudiantes del grupo");
        }

        $stmt->bind_param('i', $studentId);
        $stmt->execute();

        if($stmt->affected_rows > 0){
            $stmt->close();
            $this->connection->close();
            return array("success" => true, "message" => "Estudiantes eliminados del grupo correctamente");
        }

        $error = $stmt->error;
        $stmt->close();
        $this->connection->close();
        return array("success" => false, "message" => "Error al eliminar los estudiantes del grupo".$error);
    }
}
