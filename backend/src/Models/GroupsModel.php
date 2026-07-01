<?php
namespace Vendor\Schoolarsystem\Models;

use Vendor\Schoolarsystem\DBConnection;
use Vendor\Schoolarsystem\PermissionHelper;
use Vendor\Schoolarsystem\auth;
require_once(__DIR__ . '/../../login/index.php');

class GroupsModel
{
    private $connection;
    private $loginControl;


    public function __construct(DBConnection $dbConnection)
    {
        $this->connection = $dbConnection->getConnection();
        $this->loginControl = new \LoginControl($dbConnection);
    }

    public function getNoGroupStudentsList($search = '', $page = 1, $limit = 30, $groupId = 0)
    {
        try {
            // Determinar si el grupo es Curso o Diplomado
            $isCourseOrDiploma = false;

            if ($groupId > 0) {
                $sqlType = "
                SELECT c.subarea 
                FROM groups g
                JOIN carreers c ON g.id_carreer = c.id
                WHERE g.id = ?
            ";
                $stmtType = $this->connection->prepare($sqlType);
                $stmtType->bind_param('i', $groupId);
                $stmtType->execute();
                $resultType = $stmtType->get_result();
                $row = $resultType->fetch_assoc();
                $stmtType->close();

                if ($row) {
                    $subarea = strtolower($row['subarea']);
                    $isCourseOrDiploma = in_array($subarea, ['curso', 'diplomados']);
                }
            }

            if ($isCourseOrDiploma) {
                // Mostrar todos los estudiantes que NO están ya en ESTE grupo
                // pero pueden tener otro grupo de carrera (is_primary)
                $sql = "
                SELECT s.id, s.nombre 
                FROM students s
                WHERE s.id NOT IN (
                    SELECT sg.student_id 
                    FROM student_groups sg 
                    WHERE sg.group_id = ?
                )
            ";
                $params = [$groupId];
                $types = "i";
            } else {
                // Solo estudiantes sin grupo de carrera asignado
                $sql = "SELECT id, nombre FROM students WHERE id_group IS NULL";
                $params = [];
                $types = "";
            }

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
        } catch (Exception $e) {
            return null;
        }
    }

    public function getGroupsCount($search = '')
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM students WHERE id_group IS NULL";
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
        } catch (Exception $e) {
            return 0;
        }
    }

    public function addSchedule($data)
    {
        try {
            $sql = "INSERT INTO schedules (id_group, title, date, start, end, description) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->connection->prepare($sql);

            if (!$stmt) {
                throw new Exception("Error al preparar la consulta");
            }

            $stmt->bind_param('isssss', $data['groupId'], $data['title'], $data['date'], $data['inputStart'], $data['inputEnd'], $data['description']);
            $stmt->execute();
            $stmt->close();

            return ['success' => true, 'message' => 'Horario agregado correctamente'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error al agregar el horario' . $e->getMessage()];
        }
    }

    public function getSchedulesGroup($groupId)
    {
        try {
            $sql = "SELECT * FROM schedules WHERE id_group = ?";
            $stmt = $this->connection->prepare($sql);

            if (!$stmt) {
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
            }
            ;

            $stmt->close();

            return $schedules;
        } catch (Exception $e) {
            return array(['success' => false, 'message' => 'Error al obtener los horarios' . $e->getMessage()]);
        }
    }

    public function getGroups()
    {

        $VerifySession = auth::check();
        $isAdmin = $VerifySession['isAdmin'] ?? false;
        $userPerms = $VerifySession['permissions'] ?? [];

        if (!$VerifySession['success']) {
            return array(
                array(
                    "success" => false,
                    "message" => "No se ha iniciado sesión o la sesión ha expirado"
                )
            );
        }

        $sql = "
            SELECT
                carreers.nombre AS nombre_carrera,
                groups.*,
                COUNT(student_groups.student_id) AS members
            FROM groups
            INNER JOIN carreers
                ON groups.id_carreer = carreers.id
            LEFT JOIN student_groups
                ON student_groups.group_id = groups.id
            GROUP BY groups.id
        ";

        $result = $this->connection->query($sql);

        if (!$result) {
            return array(
                array(
                    "success" => false,
                    "message" => $this->connection->error
                )
            );
        }

        $groups = array();

        if ($result->num_rows > 0) {

            while ($row = $result->fetch_assoc()) {

                $groupsRow = [
                    "success" => true,
                    "id" => $row['id'],
                    "id_carreer" => $row['nombre_carrera'],
                    "key" => $row['clave'],
                    "name" => $row['nombre'],
                    "startDate" => $row['fecha_inicio'],
                    "endDate" => $row['fecha_termino'],
                    "members" => $row['members']
                ];

                if (PermissionHelper::canAccess(['edit_groups', 'delete_groups'], $userPerms, $isAdmin)) {
                    $groupsRow['actions'] = true;
                } else {
                    $groupsRow['actions'] = false;
                }

                $groups[] = $groupsRow;
            }

        } else {

            $groups[] = array(
                "success" => false,
                "message" => "No se encontraron grupos"
            );
        }

        $result->free();
        $this->connection->close();

        return $groups;
    }

    public function getGroupsStudents($groupId)
    {
        $VerifySession = auth::check();
        if (!$VerifySession['success']) {
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }

        $sql = "SELECT 
    s.id AS student_id,
    s.nombre AS student_name,
    sg.group_id AS student_group_id,
    g.id AS group_id,
    g.nombre AS group_name,
    sg.is_primary
FROM students s
INNER JOIN student_groups sg ON s.id = sg.student_id
INNER JOIN groups g ON sg.group_id = g.id
WHERE sg.group_id = ?;";
        $stmt = $this->connection->prepare($sql);

        if (!$stmt) {
            $this->connection->close();
            return array("success" => false, "message" => "Error al obtener los grupos");
        }

        $stmt->bind_param('i', $groupId);
        $stmt->execute();

        $result = $stmt->get_result();

        $groups = array();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $groups[] = array(
                    "success" => true,
                    "student_id" => $row['student_id'],
                    "id_group" => $row['group_id'],
                    "student_name" => $row['student_name'],
                    "student_group_id" => $row['student_group_id']
                );
            }
        } else {
            $groups[] = array("success" => false, "message" => "No se encontraron alumnos en el grupo");
        }

        $stmt->close();
        $this->connection->close();

        return $groups;
    }

    public function getStudentsNames()
    {
        $VerifySession = auth::check();
        if (!$VerifySession['success']) {
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }

        $sql = "SELECT id, nombre FROM students WHERE id_group IS NULL";
        $result = $this->connection->query($sql);

        if (!$result) {
            $this->connection->close();
            return array("success" => false, "message" => "Error al obtener los grupos");
        }

        $students = array();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $students[] = array(
                    "success" => true,
                    "id" => $row['id'],
                    "name" => $row['nombre']
                );
            }
        } else {
            $students = array("success" => false, "message" => "No se encontraron grupos");
        }

        $result->free();
        $this->connection->close();

        return $students;
    }

    public function getGroupData($groupId)
    {
        $VerifySession = auth::check();
        if (!$VerifySession['success']) {
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }

        $sql = "SELECT carreers.nombre as nombre_carrera, carreers.id as id_carreer, carreers.subarea as subarea_carrera, groups.* FROM groups INNER JOIN carreers ON groups.id_carreer = carreers.id WHERE groups.id = ?";
        $stmt = $this->connection->prepare($sql);

        if (!$stmt) {
            $this->connection->close();
            return array("success" => false, "message" => "Error al obtener los grupos");
        }

        $stmt->bind_param('i', $groupId);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $stmt->close();
            $this->connection->close();
            return array("success" => false, "message" => "No se encontró el grupo");
        }

        $group = array();
        while ($row = $result->fetch_assoc()) {
            $group = array(
                "success" => true,
                "id" => $row['id'],
                "id_carreer" => $row['id_carreer'],
                "subarea_carrera" => $row['subarea_carrera'],
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

    public function getGroupsJson()
    {
        $VerifySession = auth::check();
        if (!$VerifySession['success']) {
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }

        $sql = "SELECT id, nombre, area, subarea FROM carreers";
        $stmt = $this->connection->prepare($sql);

        if (!$stmt) {
            $this->connection->close();
            return array("success" => false, "message" => "Error al obtener los grupos");
        }

        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $stmt->close();
            $this->connection->close();
            return array("success" => false, "message" => "No se encontró el grupo");
        }

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

        $stmt->close();
        $this->connection->close();

        return $structuredData;
    }

    public function addGroup($groupDataArray)
    {
        $VerifySession = auth::check();
        if (!$VerifySession['success']) {
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }

        $sql = "INSERT INTO groups (id_carreer, clave, nombre, fecha_inicio, fecha_termino, descripcion) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->connection->prepare($sql);

        if (!$stmt) {
            $this->connection->close();
            return array("success" => false, "message" => "Error al agregar el grupo");
        }

        $stmt->bind_param('isssss', $groupDataArray['carreerNameGroup'], $groupDataArray['keyGroup'], $groupDataArray['nameGroup'], $groupDataArray['startDate'], $groupDataArray['endDate'], $groupDataArray['descriptionGroup']);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $stmt->close();
            $this->connection->close();
            return array("success" => true, "message" => "Grupo agregado correctamente");
        }

        $stmt->close();
        $this->connection->close();
        return array("success" => false, "message" => "Error al agregar el grupo");
    }

    public function updateGroup($groupDataEditArray)
    {
        $VerifySession = auth::check();
        if (!$VerifySession['success']) {
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }

        $sql = "UPDATE groups SET id_carreer = ?, clave = ?, nombre = ?, fecha_inicio = ?, fecha_termino = ?, descripcion = ? WHERE id = ?";
        $stmt = $this->connection->prepare($sql);

        if (!$stmt) {
            $this->connection->close();
            return array("success" => false, "message" => "Error al actualizar el grupo");
        }

        $stmt->bind_param('isssssi', $groupDataEditArray['idCarreerHidden'], $groupDataEditArray['keyGroupEdit'], $groupDataEditArray['nameGroupEdit'], $groupDataEditArray['startDateEdit'], $groupDataEditArray['endDateEdit'], $groupDataEditArray['descriptionGroupEdit'], $groupDataEditArray['idGroupDB']);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $stmt->close();
            $this->connection->close();
            return array("success" => true, "message" => "Grupo actualizado correctamente");
        }

        $stmt->close();
        $this->connection->close();
        return array("success" => false, "message" => "Error al actualizar el grupo");
    }

    public function deleteGroup($groupId)
    {
        $VerifySession = auth::check();
        if (!$VerifySession['success']) {
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }

        $sql = "DELETE FROM groups WHERE id = ?";
        $stmt = $this->connection->prepare($sql);

        if (!$stmt) {
            $this->connection->close();
            return array("success" => false, "message" => "Error al eliminar el grupo");
        }

        $stmt->bind_param('i', $groupId);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $stmt->close();
            $this->connection->close();
            return array("success" => true, "message" => "Grupo eliminado correctamente");
        }

        $stmt->close();
        $this->connection->close();
        return array("success" => false, "message" => "Error al eliminar el grupo");
    }

    public function addStudentGroup($groupId, $studentIds)
    {
        $VerifySession = auth::check();

        if (!$VerifySession['success']) {
            return ["success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado"];
        }

        if (!is_array($studentIds) || empty($studentIds)) {
            return ["success" => false, "message" => "No se proporcionaron estudiantes válidos"];
        }

        $ids = array_values(array_unique(array_map('intval', $studentIds)));
        $ids = array_filter($ids, fn($id) => $id > 0);

        if (empty($ids)) {
            return ["success" => false, "message" => "No se proporcionaron estudiantes válidos"];
        }

        try {
            $this->connection->begin_transaction();

            /**
             * Determinar si el grupo es Curso o Diplomado
             */
            $sqlType = "
            SELECT c.subarea 
            FROM groups g
            JOIN carreers c ON g.id_carreer = c.id
            WHERE g.id = ?
        ";
            $stmtType = $this->connection->prepare($sqlType);
            $stmtType->bind_param('i', $groupId);
            $stmtType->execute();
            $resultType = $stmtType->get_result();
            $rowType = $resultType->fetch_assoc();
            $stmtType->close();

            $isCourseOrDiploma = false;
            if ($rowType) {
                $subarea = strtolower($rowType['subarea']);
                $isCourseOrDiploma = in_array($subarea, ['curso', 'diplomados']);
            }

            $idsStr = implode(',', $ids);
            $alreadyAssigned = [];
            $availableStudents = [];

            if ($isCourseOrDiploma) {
                /**
                 * Curso/Diplomado: solo verificar que no estén ya en ESTE grupo
                 */
                $sqlAssigned = "
                SELECT student_id AS id
                FROM student_groups
                WHERE student_id IN ($idsStr)
                AND group_id = ?
            ";
                $stmtAssigned = $this->connection->prepare($sqlAssigned);
                $stmtAssigned->bind_param('i', $groupId);
                $stmtAssigned->execute();
                $resultAssigned = $stmtAssigned->get_result();
                $stmtAssigned->close();

                while ($row = $resultAssigned->fetch_assoc()) {
                    $alreadyAssigned[] = (int) $row['id'];
                }

                $availableStudents = array_values(array_diff($ids, $alreadyAssigned));

            } else {
                /**
                 * Carrera: solo estudiantes sin grupo asignado
                 */
                $sqlAssigned = "
                SELECT id
                FROM students
                WHERE id IN ($idsStr)
                AND id_group IS NOT NULL
            ";
                $resultAssigned = $this->connection->query($sqlAssigned);

                if ($resultAssigned === false) {
                    throw new Exception("Error verificando estudiantes asignados");
                }

                while ($row = $resultAssigned->fetch_assoc()) {
                    $alreadyAssigned[] = (int) $row['id'];
                }

                $availableStudents = array_values(array_diff($ids, $alreadyAssigned));
            }

            if (empty($availableStudents)) {
                $this->connection->rollback();
                return [
                    "success" => false,
                    "message" => $isCourseOrDiploma
                        ? "Todos los estudiantes seleccionados ya están registrados en este curso/diplomado"
                        : "Todos los estudiantes seleccionados ya pertenecen a un grupo"
                ];
            }

            $availableIdsStr = implode(',', $availableStudents);

            /**
             * Actualizar id_group en students solo si es carrera
             */
            if (!$isCourseOrDiploma) {
                $sqlUpdate = "UPDATE students SET id_group = ? WHERE id IN ($availableIdsStr)";
                $stmtUpdate = $this->connection->prepare($sqlUpdate);

                if (!$stmtUpdate) {
                    throw new Exception("Error preparando actualización de grupo");
                }

                $stmtUpdate->bind_param("i", $groupId);

                if (!$stmtUpdate->execute()) {
                    throw new Exception("Error asignando grupo a estudiantes");
                }

                $stmtUpdate->close();
            }

            /**
             * Registrar en student_groups
             * is_primary = groupId si es carrera, 0 si es curso/diplomado
             */
            $isPrimary = $isCourseOrDiploma ? 0 : $groupId;
            $values = [];

            foreach ($availableStudents as $studentId) {
                $values[] = "(" . intval($studentId) . ", " . intval($groupId) . ", " . intval($isPrimary) . ")";
            }

            if (!empty($values)) {
                $sqlInsert = "
                INSERT INTO student_groups (student_id, group_id, is_primary)
                VALUES " . implode(',', $values);

                if (!$this->connection->query($sqlInsert)) {
                    throw new Exception("Error registrando estudiantes en student_groups");
                }
            }

            $this->connection->commit();

            $updatedRows = count($availableStudents);
            $message = "{$updatedRows} estudiante(s) agregado(s) correctamente";

            if (!empty($alreadyAssigned)) {
                $message .= ". Se omitieron " . count($alreadyAssigned) . " porque ya estaban registrados.";
            }

            return ["success" => true, "message" => $message];

        } catch (Exception $e) {
            $this->connection->rollback();
            return ["success" => false, "message" => "Error al agregar estudiantes al grupo: " . $e->getMessage()];
        } finally {
            if ($this->connection) {
                $this->connection->close();
            }
        }
    }


    public function addStudentGroupold($groupId, $studentId)
    {
        $VerifySession = auth::check();
        if (!$VerifySession['success']) {
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }

        if (!is_array($studentId) || empty($studentId)) {
            $this->connection->close();
            return array("success" => false, "message" => "No se proporcionaron estudiantes válidos");
        }

        $ids = array_map('intval', $studentId);
        $ids_str = implode(',', $ids);

        $checkSql = "SELECT DISTINCT student_id FROM student_groups WHERE student_id IN ($ids_str) AND is_primary = TRUE";
        $result = $this->connection->query($checkSql);

        $studentsWithPrimary = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $studentsWithPrimary[] = (int) $row['student_id'];
            }
        }
        // 2️⃣ Separar los alumnos en dos grupos
        $primaryStudents = array_diff($ids, $studentsWithPrimary);  // sin grupo principal
        $secondaryStudents = $studentsWithPrimary;

        $inserted = 0;

        // 3️⃣ Insertar los alumnos sin grupo principal como is_primary = TRUE
        if (!empty($primaryStudents)) {
            $primaryIdsStr = implode(',', $primaryStudents);
            $sqlPrimary = "INSERT INTO student_groups (student_id, group_id, is_primary)
                SELECT id AS student_id, ? AS group_id, TRUE AS is_primary
                FROM students
                WHERE id IN ($primaryIdsStr)
                ON DUPLICATE KEY UPDATE is_primary = VALUES(is_primary)";
            $stmt1 = $this->connection->prepare($sqlPrimary);
            if ($stmt1) {
                $stmt1->bind_param('i', $groupId);
                $stmt1->execute();
                $inserted += $stmt1->affected_rows;
                $stmt1->close();
            }
        }

        // 4️⃣ Insertar los alumnos que ya tenían grupo principal como is_primary = FALSE
        if (!empty($secondaryStudents)) {
            $secondaryIdsStr = implode(',', $secondaryStudents);
            $sqlSecondary = "INSERT INTO student_groups (student_id, group_id, is_primary)
                SELECT id AS student_id, ? AS group_id, FALSE AS is_primary
                FROM students
                WHERE id IN ($secondaryIdsStr)
                ON DUPLICATE KEY UPDATE is_primary = VALUES(is_primary)";
            $stmt2 = $this->connection->prepare($sqlSecondary);
            if ($stmt2) {
                $stmt2->bind_param('i', $groupId);
                $stmt2->execute();
                $inserted += $stmt2->affected_rows;
                $stmt2->close();
            }
        }

        $this->connection->close();

        if ($inserted > 0) {
            return ["success" => true, "message" => "Estudiantes agregados correctamente (se asignaron primarios/secundarios según correspondía)"];
        }
        return ["success" => false, "message" => "No se pudieron agregar los estudiantes al grupo"];
    }

    public function deleteStudentGroup($groupId, $studentId, $password)
    {
        $VerifySession = auth::check();
        if (!$VerifySession['success']) {
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }

        $userId = $_SESSION['userId'];

        $isValidPassword = $this->loginControl->verifyUserPassword($userId, $password);

        if (!$isValidPassword) {
            return [
                "success" => false,
                "message" => "Contraseña incorrecta"
            ];
        }

        try {
            $this->connection->begin_transaction();

            // 1. Nullear id_group en students
            $sqlUpdate = "UPDATE students SET id_group = NULL WHERE id = ? AND id_group = ?";
            $stmtUpdate = $this->connection->prepare($sqlUpdate);

            if (!$stmtUpdate) {
                throw new Exception("Error preparando actualización del grupo");
            }

            $stmtUpdate->bind_param('ii', $studentId, $groupId);
            $stmtUpdate->execute();
            $stmtUpdate->close();

            // 2. Eliminar de student_groups
            $sqlDelete = "DELETE FROM student_groups WHERE student_id = ? AND group_id = ?";
            $stmtDelete = $this->connection->prepare($sqlDelete);

            if (!$stmtDelete) {
                throw new Exception("Error preparando eliminación del grupo");
            }

            $stmtDelete->bind_param('ii', $studentId, $groupId);
            $stmtDelete->execute();

            if ($stmtDelete->affected_rows === 0) {
                throw new Exception("No se encontró el registro a eliminar");
            }

            $stmtDelete->close();
            $this->connection->commit();

            return array("success" => true, "message" => "Estudiante eliminado del grupo correctamente");

        } catch (Exception $e) {
            $this->connection->rollback();
            return array("success" => false, "message" => "Error al eliminar el estudiante del grupo: " . $e->getMessage());
        } finally {
            $this->connection->close();
        }
    }

    public function getDuplicateStudents()
    {
        $VerifySession = auth::check();
        if (!$VerifySession['success']) {
            return ["success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado"];
        }

        $sql = "
        SELECT 
            s.id,
            s.nombre,
            COUNT(sg.group_id) AS total_grupos
        FROM students s
        JOIN student_groups sg ON s.id = sg.student_id
        JOIN groups g ON sg.group_id = g.id
        JOIN carreers c ON g.id_carreer = c.id
        WHERE LOWER(c.subarea) NOT IN ('curso', 'diplomados')
        GROUP BY s.id, s.nombre
        HAVING COUNT(sg.group_id) > 1
        ORDER BY total_grupos DESC
    ";

        $result = $this->connection->query($sql);

        if (!$result) {
            return ["success" => false, "message" => $this->connection->error];
        }

        $students = [];
        while ($row = $result->fetch_assoc()) {
            $students[] = [
                "id" => $row['id'],
                "nombre" => $row['nombre'],
                "total_grupos" => $row['total_grupos']
            ];
        }

        $result->free();

        return [
            "success" => true,
            "results" => $students
        ];
    }

    public function getStudentDuplicateGroups($studentId)
    {
        $VerifySession = auth::check();
        if (!$VerifySession['success']) {
            return ["success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado"];
        }

        $sql = "
        SELECT 
            sg.id AS sg_id,
            g.id AS group_id,
            g.clave,
            g.nombre AS group_nombre,
            c.nombre AS carreer_nombre,
            c.subarea,
            sg.assigned_at
        FROM student_groups sg
        JOIN groups g ON sg.group_id = g.id
        JOIN carreers c ON g.id_carreer = c.id
        WHERE sg.student_id = ?
        AND LOWER(c.subarea) NOT IN ('curso', 'diplomados')
        ORDER BY sg.assigned_at ASC
    ";

        $stmt = $this->connection->prepare($sql);
        if (!$stmt) {
            return ["success" => false, "message" => "Error preparando consulta"];
        }

        $stmt->bind_param('i', $studentId);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        $groups = [];
        while ($row = $result->fetch_assoc()) {
            $groups[] = [
                "sg_id" => $row['sg_id'],
                "group_id" => $row['group_id'],
                "clave" => $row['clave'],
                "group_nombre" => $row['group_nombre'],
                "carreer_nombre" => $row['carreer_nombre'],
                "subarea" => $row['subarea'],
                "assigned_at" => $row['assigned_at']
            ];
        }

        return ["success" => true, "results" => $groups];
    }

    public function resolveDuplicate($studentId, $correctGroupId)
    {
        $VerifySession = auth::check();
        if (!$VerifySession['success']) {
            return ["success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado"];
        }

        $studentId = intval($studentId);
        $correctGroupId = intval($correctGroupId);

        try {
            $this->connection->begin_transaction();

            // 1. Obtener todos los grupos de carrera del alumno excepto el correcto
            $sqlGet = "
            SELECT sg.group_id
            FROM student_groups sg
            JOIN groups g ON sg.group_id = g.id
            JOIN carreers c ON g.id_carreer = c.id
            WHERE sg.student_id = ?
            AND LOWER(c.subarea) NOT IN ('curso', 'diplomados')
            AND sg.group_id != ?
        ";
            $stmtGet = $this->connection->prepare($sqlGet);
            $stmtGet->bind_param('ii', $studentId, $correctGroupId);
            $stmtGet->execute();
            $resultGet = $stmtGet->get_result();
            $stmtGet->close();

            $groupsToRemove = [];
            while ($row = $resultGet->fetch_assoc()) {
                $groupsToRemove[] = intval($row['group_id']);
            }

            if (empty($groupsToRemove)) {
                $this->connection->rollback();
                return ["success" => false, "message" => "No se encontraron grupos duplicados a eliminar"];
            }

            $idsStr = implode(',', $groupsToRemove);

            // 2. Eliminar de student_groups los grupos incorrectos
            $sqlDelete = "
            DELETE FROM student_groups
            WHERE student_id = ?
            AND group_id IN ($idsStr)
        ";
            $stmtDelete = $this->connection->prepare($sqlDelete);
            $stmtDelete->bind_param('i', $studentId);
            $stmtDelete->execute();
            $stmtDelete->close();

            // 3. Actualizar id_group en students con el grupo correcto
            $sqlUpdate = "UPDATE students SET id_group = ? WHERE id = ?";
            $stmtUpdate = $this->connection->prepare($sqlUpdate);
            $stmtUpdate->bind_param('ii', $correctGroupId, $studentId);
            $stmtUpdate->execute();
            $stmtUpdate->close();

            $this->connection->commit();

            return [
                "success" => true,
                "message" => "Duplicados resueltos correctamente. Se eliminaron " . count($groupsToRemove) . " grupo(s) incorrectos."
            ];

        } catch (Exception $e) {
            $this->connection->rollback();
            return ["success" => false, "message" => "Error al resolver duplicados: " . $e->getMessage()];
        } finally {
            $this->connection->close();
        }
    }

    public function getGroupCareer($studentId)
    {
        $VerifySession = auth::check();
        if (!$VerifySession['success']) {
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }

        $sql = "SELECT carreers.id as career_id, carreers.nombre as career_name 
                FROM students 
                INNER JOIN groups ON students.id_group = groups.id 
                INNER JOIN carreers ON groups.id_carreer = carreers.id 
                WHERE students.id = ?";
        $stmt = $this->connection->prepare($sql);

        if (!$stmt) {
            $this->connection->close();
            return array("success" => false, "message" => "Error al obtener la carrera del estudiante");
        }

        $stmt->bind_param('i', $studentId);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $stmt->close();
            $this->connection->close();
            return array("success" => false, "message" => "No se encontró la carrera para el estudiante");
        }

        $career = array();
        while ($row = $result->fetch_assoc()) {
            $career = array(
                "success" => true,
                "careerId" => $row['career_id'],
                "careerName" => $row['career_name']
            );
        }

        $stmt->close();
        $this->connection->close();

        return $career;
    }
}
