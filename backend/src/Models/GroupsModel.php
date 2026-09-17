<?php
namespace Vendor\Schoolarsystem\Models;

use Vendor\Schoolarsystem\DBConnection;
use Vendor\Schoolarsystem\Core\DatabaseHelper;
use Vendor\Schoolarsystem\Core\DatabaseExecutor;

class GroupsModel
{
    private $connection;

    public function __construct(DBConnection $dbConnection)
    {
        $this->connection = $dbConnection->getConnection();
    }

    public function getGroupById(int $groupId): array
    {
        $result = DatabaseHelper::selectOne(
            $this->connection,
            "
                SELECT
                    carreers.id AS id_carreer,
                    carreers.nombre AS carreer_name,
                    groups.id,
                    groups.clave,
                    groups.nombre AS name,
                    groups.fecha_inicio AS startDate,
                    groups.fecha_termino AS endDate,
                    groups.descripcion AS description
                FROM groups
                INNER JOIN carreers
                    ON groups.id_carreer = carreers.id
                WHERE groups.id = ?;
            ",
            "i",
            [$groupId]
        );

        if (!$result["success"] && ($result["message"] ?? '') === 'No se encontró el registro solicitado.') {
            $result["code"] = "GROUP_NOT_FOUND";
            $result["message"] = "No se encontró el grupo solicitado.";
        }

        return $result;
    }

    public function getAllGroups(): array
    {

        return DatabaseHelper::selectAll(
            $this->connection,
            "
                SELECT
                    carreers.nombre AS nombre_carrera,
                    groups.id,
                    groups.clave,
                    groups.nombre AS name,
                    COUNT(student_groups.student_id) AS members
                FROM groups
                INNER JOIN carreers
                    ON groups.id_carreer = carreers.id
                LEFT JOIN student_groups
                    ON student_groups.group_id = groups.id
                GROUP BY groups.id;
            "
        );
    }

    public function addGroup(array $groupDataArray): array
    {
        $career = DatabaseHelper::selectOne(
            $this->connection,
            "SELECT id FROM carreers WHERE id = ?;",
            "i",
            [$groupDataArray["carreerNameGroup"]]
        );

        if (!$career["success"]) {
            return [
                "success" => false,
                "code" => "CAREER_NOT_FOUND",
                "message" => "No se encontró la carrera seleccionada."
            ];
        }

        return DatabaseHelper::insert(
            $this->connection,
            "
                INSERT INTO groups (
                    id_carreer,
                    clave,
                    nombre,
                    fecha_inicio,
                    fecha_termino,
                    descripcion
                ) VALUES (?, ?, ?, ?, ?, ?);
            ",
            "isssss",
            [
                $groupDataArray["carreerNameGroup"],
                $groupDataArray["keyGroup"],
                $groupDataArray["nameGroup"],
                $groupDataArray["startDate"],
                $groupDataArray["endDate"],
                $groupDataArray["descriptionGroup"]
            ]
        );
    }

    public function updateGroup(array $groupUpdateDataArray): array
    {
        $id = $groupUpdateDataArray['idGroupDB'];

        try {
            $currentData = DatabaseHelper::selectOne(
                $this->connection,
                "
                    SELECT
                        id_carreer,
                        clave,
                        nombre,
                        fecha_inicio,
                        fecha_termino,
                        descripcion
                    FROM groups
                    WHERE id = ?;
                ",
                "i",
                [$id]
            );

            if (!$currentData["success"]) {
                return [
                    "success" => false,
                    "code" => "GROUP_NOT_FOUND",
                    "message" => "No se encontró el grupo solicitado.",
                    "data" => null
                ];
            }

            $career = DatabaseHelper::selectOne(
                $this->connection,
                "SELECT id FROM carreers WHERE id = ?;",
                "i",
                [$groupUpdateDataArray['carreerNameGroupEdit']]
            );

            if (!$career["success"]) {
                return [
                    "success" => false,
                    "code" => "CAREER_NOT_FOUND",
                    "message" => "No se encontró la carrera seleccionada.",
                    "data" => null
                ];
            }

            $newData = [
                "id_carreer" => $groupUpdateDataArray['carreerNameGroupEdit'],
                "clave" => $groupUpdateDataArray['keyGroupEdit'],
                "nombre" => $groupUpdateDataArray['nameGroupEdit'],
                "fecha_inicio" => $groupUpdateDataArray['startDateEdit'],
                "fecha_termino" => $groupUpdateDataArray['endDateEdit'],
                "descripcion" => $groupUpdateDataArray['descriptionGroupEdit'],
            ];

            if ($currentData["data"] == $newData) {
                return [
                    "success" => false,
                    "code" => "NO_CHANGES",
                    "message" => "No se detectaron cambios para guardar.",
                    "data" => null
                ];
            }

            return DatabaseHelper::update(
                $this->connection,
                "
                    UPDATE groups
                    SET
                        id_carreer = ?,
                        clave = ?,
                        nombre = ?,
                        fecha_inicio = ?,
                        fecha_termino = ?,
                        descripcion = ?
                    WHERE id = ?;
                ",
                "isssssi",
                [
                    $newData["id_carreer"],
                    $newData["clave"],
                    $newData["nombre"],
                    $newData["fecha_inicio"],
                    $newData["fecha_termino"],
                    $newData["descripcion"],
                    $id
                ]
            );

        } catch (\Exception $e) {
            return [
                "success" => false,
                "message" => "Error inesperado al actualizar los datos del grupo.",
                "data" => null
            ];
        }
    }

    public function deleteGroupById(int $groupId): array
    {
        $group = DatabaseHelper::selectOne(
            $this->connection,
            "SELECT id FROM groups WHERE id = ?;",
            "i",
            [$groupId]
        );

        if (!$group["success"]) {
            return [
                "success" => false,
                "code" => "GROUP_NOT_FOUND",
                "message" => "No se encontró el grupo solicitado."
            ];
        }

        $members = DatabaseHelper::selectValue(
            $this->connection,
            "
                SELECT COUNT(*) AS total
                FROM students s
                LEFT JOIN student_groups sg
                    ON sg.student_id = s.id AND sg.group_id = ?
                WHERE s.id_group = ? OR sg.group_id IS NOT NULL;
            ",
            "total",
            "ii",
            [$groupId, $groupId]
        );

        if ($members > 0) {
            return [
                "success" => false,
                "code" => "GROUP_HAS_STUDENTS",
                "message" => "No se puede eliminar un grupo que todavía tiene alumnos asignados."
            ];
        }

        return DatabaseHelper::delete(
            $this->connection,
            "
                DELETE
                FROM groups
                WHERE id = ?;
            ",
            "i",
            [$groupId]
        );
    }

    public function getStudentsByGroupId(int $groupId): array
    {
        $group = DatabaseHelper::selectOne(
            $this->connection,
            "SELECT id FROM groups WHERE id = ?;",
            "i",
            [$groupId]
        );

        if (!$group["success"]) {
            return [
                "success" => false,
                "code" => "GROUP_NOT_FOUND",
                "message" => "No se encontró el grupo solicitado."
            ];
        }

        return DatabaseHelper::selectAll(
            $this->connection,
            "
                SELECT
                    s.id AS student_id,
                    s.nombre AS student_name,
                    sg.group_id AS student_group_id,
                    g.id AS group_id,
                    g.nombre AS group_name,
                    sg.is_primary
                FROM students s
                INNER JOIN student_groups sg ON s.id = sg.student_id
                INNER JOIN groups g ON sg.group_id = g.id
                WHERE sg.group_id = ?;
            ",
            "i",
            [$groupId]
        );
    }

    public function addStudentToGroup(int $groupId, array $studentIds): array
    {
        $ids = array_values(array_unique($studentIds));

        try {
            $this->connection->begin_transaction();

            $group = DatabaseHelper::selectOne(
                $this->connection,
                "
                    SELECT c.subarea
                    FROM groups g
                    INNER JOIN carreers c ON g.id_carreer = c.id
                    WHERE g.id = ?;
                ",
                "i",
                [$groupId]
            );

            if (!$group["success"]) {
                $this->connection->rollback();

                return [
                    "success" => false,
                    "code" => "GROUP_NOT_FOUND",
                    "message" => "No se encontró el grupo solicitado.",
                    "data" => null
                ];
            }

            $isCourseOrDiploma = in_array(
                strtolower($group["data"]["subarea"]),
                ["cursos", "diplomados"],
                true
            );

            $placeholders = implode(",", array_fill(0, count($ids), "?"));
            $types = str_repeat("i", count($ids));

            $existingStudents = DatabaseHelper::selectAll(
                $this->connection,
                "SELECT id FROM students WHERE id IN ($placeholders);",
                $types,
                $ids
            );

            if (
                !$existingStudents["success"]
                || count($existingStudents["data"]) !== count($ids)
            ) {
                $this->connection->rollback();

                return [
                    "success" => false,
                    "code" => "STUDENT_NOT_FOUND",
                    "message" => "Uno o más alumnos seleccionados no existen.",
                    "data" => null
                ];
            }

            if ($isCourseOrDiploma) {
                $sql = "
                    SELECT student_id AS id
                    FROM student_groups
                    WHERE student_id IN ($placeholders)
                        AND group_id = ?
                ";

                $alreadyAssigned = DatabaseHelper::selectAll(
                    $this->connection,
                    $sql,
                    $types . "i",
                    [...$ids, $groupId]
                );
            } else {
                $sql = "
                    SELECT s.id
                    FROM students s
                    WHERE s.id IN ($placeholders)
                        AND (
                            s.id_group IS NOT NULL
                            OR EXISTS (
                                SELECT 1
                                FROM student_groups sg
                                INNER JOIN groups g ON g.id = sg.group_id
                                INNER JOIN carreers c ON c.id = g.id_carreer
                                WHERE sg.student_id = s.id
                                    AND LOWER(c.subarea) NOT IN ('cursos', 'diplomados')
                            )
                        )
                ";

                $alreadyAssigned = DatabaseHelper::selectAll(
                    $this->connection,
                    $sql,
                    $types,
                    $ids
                );
            }

            if (!$alreadyAssigned["success"]) {
                $this->connection->rollback();

                return [
                    "success" => false,
                    "message" => $alreadyAssigned["message"],
                    "data" => null
                ];
            }

            $assignedIds = array_map(
                'intval',
                array_column($alreadyAssigned["data"], "id")
            );

            $availableStudents = array_values(
                array_diff($ids, $assignedIds)
            );

            if (empty($availableStudents)) {
                $this->connection->rollback();

                return [
                    "success" => false,
                    "code" => "STUDENT_ALREADY_ASSIGNED",
                    "message" => $isCourseOrDiploma
                        ? "Todos los alumnos seleccionados ya están registrados en este curso/diplomado."
                        : "Todos los alumnos seleccionados ya pertenecen a un grupo.",
                    "data" => null
                ];
            }

            if (!$isCourseOrDiploma) {
                $placeholders = implode(
                    ",",
                    array_fill(0, count($availableStudents), "?")
                );

                $types = "i" . str_repeat("i", count($availableStudents));

                $update = DatabaseHelper::update(
                    $this->connection,
                    "
                        UPDATE students
                        SET id_group = ?
                        WHERE id IN ($placeholders)
                    ",
                    $types,
                    [$groupId, ...$availableStudents]
                );

                if (!$update["success"]) {
                    $this->connection->rollback();

                    return [
                        "success" => false,
                        "message" => $update["message"],
                        "data" => null
                    ];
                }
            }

            $isPrimary = $isCourseOrDiploma ? 0 : 1;

            $valuePlaceholders = [];
            $insertParams = [];
            $insertTypes = "";

            foreach ($availableStudents as $studentId) {
                $valuePlaceholders[] = "(?, ?, ?)";
                $insertParams[] = $studentId;
                $insertParams[] = $groupId;
                $insertParams[] = $isPrimary;
                $insertTypes .= "iii";
            }

            $insert = DatabaseHelper::insert(
                $this->connection,
                "
                INSERT INTO student_groups
                    (student_id, group_id, is_primary)
                VALUES " . implode(",", $valuePlaceholders),
                $insertTypes,
                $insertParams
            );

            if (!$insert["success"]) {
                $this->connection->rollback();

                return [
                    "success" => false,
                    "message" => "No fue posible registrar los alumnos en el grupo.",
                    "data" => null
                ];
            }

            $this->connection->commit();

            $message = count($availableStudents)
                . " alumno(s) agregado(s) correctamente.";

            if (!empty($assignedIds)) {
                $message .= " Se omitieron "
                    . count($assignedIds)
                    . " porque ya estaban registrados.";
            }

            return [
                "success" => true,
                "message" => $message,
                "data" => null
            ];

        } catch (\Exception $e) {
            $this->connection->rollback();

            return [
                "success" => false,
                "message" => "Error inesperado al agregar los alumnos al grupo.",
                "data" => null
            ];
        }
    }

    public function removeStudentFromGroup(int $groupId, int $studentId): array
    {
        try {
            $this->connection->begin_transaction();

            $membership = DatabaseHelper::selectOne(
                $this->connection,
                "
                    SELECT sg.id, sg.is_primary, s.id_group
                    FROM student_groups sg
                    INNER JOIN students s ON s.id = sg.student_id
                    WHERE sg.student_id = ? AND sg.group_id = ?;
                ",
                "ii",
                [$studentId, $groupId]
            );

            if (!$membership["success"]) {
                $this->connection->rollback();

                return [
                    "success" => false,
                    "code" => "MEMBERSHIP_NOT_FOUND",
                    "message" => "El alumno no pertenece al grupo indicado.",
                    "data" => null
                ];
            }

            $delete = DatabaseHelper::delete(
                $this->connection,
                "
                    DELETE FROM student_groups
                    WHERE student_id = ?
                        AND group_id = ?;
                ",
                "ii",
                [$studentId, $groupId]
            );

            if (!$delete["success"]) {
                $this->connection->rollback();

                return [
                    "success" => false,
                    "message" => $delete["message"],
                    "data" => null
                ];
            }

            if ((int) $membership['data']['id_group'] === $groupId) {
                $update = DatabaseHelper::update(
                    $this->connection,
                    "UPDATE students SET id_group = NULL WHERE id = ? AND id_group = ?;",
                    "ii",
                    [$studentId, $groupId]
                );

                if (!$update["success"]) {
                    $this->connection->rollback();

                    return [
                        "success" => false,
                        "message" => $update["message"],
                        "data" => null
                    ];
                }
            }

            $this->connection->commit();

            return [
                "success" => true,
                "message" => "Alumno eliminado del grupo correctamente.",
                "data" => null
            ];

        } catch (\Exception $e) {
            $this->connection->rollback();

            return [
                "success" => false,
                "message" => "Error inesperado al eliminar el alumno del grupo.",
                "data" => null
            ];
        }
    }

    public function getCarreersForGroupCreation(): array
    {
        $result = DatabaseHelper::selectAll(
            $this->connection,
            "
                SELECT
                    id,
                    nombre,
                    area,
                    subarea
                FROM carreers;
            "
        );

        $structuredData = [];

        if (!$result["success"]) {
            return $result;
        }

        foreach ($result["data"] as $row) {
            $structuredData[$row["area"]][$row["subarea"]][] = [
                "id" => $row["id"],
                "nombre" => $row["nombre"]
            ];
        }

        return [
            "success" => true,
            "message" => "Carreras obtenidas correctamente.",
            "data" => $structuredData
        ];
    }

    public function getDuplicateStudents(): array
    {
        return DatabaseHelper::selectAll(
            $this->connection,
            "
                SELECT
                    s.id,
                    s.nombre,
                    COUNT(sg.group_id) AS total_grupos
                FROM students s
                JOIN student_groups sg ON s.id = sg.student_id
                JOIN groups g ON sg.group_id = g.id
                JOIN carreers c ON g.id_carreer = c.id
                WHERE LOWER(c.subarea) NOT IN ('cursos', 'diplomados')
                GROUP BY s.id, s.nombre
                HAVING COUNT(sg.group_id) > 1
                ORDER BY total_grupos DESC;
            ",
        );
    }

    public function getStudentDuplicateGroups(int $studentId): array
    {
        $student = DatabaseHelper::selectOne(
            $this->connection,
            "SELECT id FROM students WHERE id = ?;",
            "i",
            [$studentId]
        );

        if (!$student["success"]) {
            return [
                "success" => false,
                "code" => "STUDENT_NOT_FOUND",
                "message" => "No se encontró el alumno solicitado."
            ];
        }

        return DatabaseHelper::selectAll(
            $this->connection,
            "
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
                    AND LOWER(c.subarea) NOT IN ('cursos', 'diplomados')
                ORDER BY sg.assigned_at ASC;
            ",
            "i",
            [$studentId]
        );
    }

    public function resolveDuplicate(int $studentId, int $correctGroupId): array
    {
        try {
            $this->connection->begin_transaction();

            $correctGroup = DatabaseHelper::selectOne(
                $this->connection,
                "
                    SELECT sg.id
                    FROM student_groups sg
                    INNER JOIN groups g ON sg.group_id = g.id
                    INNER JOIN carreers c ON g.id_carreer = c.id
                    WHERE sg.student_id = ?
                        AND sg.group_id = ?
                        AND LOWER(c.subarea) NOT IN ('cursos', 'diplomados');
                ",
                "ii",
                [$studentId, $correctGroupId]
            );

            if (!$correctGroup["success"]) {
                $this->connection->rollback();

                return [
                    "success" => false,
                    "code" => "INVALID_CORRECT_GROUP",
                    "message" => "El grupo seleccionado no pertenece al alumno o no es un grupo de carrera.",
                    "data" => null
                ];
            }

            // Obtener grupos de carrera duplicados
            $groups = DatabaseHelper::selectAll(
                $this->connection,
                "
                    SELECT sg.group_id
                    FROM student_groups sg
                    INNER JOIN groups g ON sg.group_id = g.id
                    INNER JOIN carreers c ON g.id_carreer = c.id
                    WHERE sg.student_id = ?
                        AND LOWER(c.subarea) NOT IN ('cursos', 'diplomados')
                        AND sg.group_id != ?;
                ",
                "ii",
                [$studentId, $correctGroupId]
            );

            if (!$groups["success"]) {
                $this->connection->rollback();

                return [
                    "success" => false,
                    "message" => $groups["message"],
                    "data" => null
                ];
            }

            if (empty($groups["data"])) {
                $this->connection->rollback();

                return [
                    "success" => false,
                    "code" => "DUPLICATE_NOT_FOUND",
                    "message" => "No se encontraron grupos duplicados a eliminar.",
                    "data" => null
                ];
            }

            $groupIds = array_column($groups["data"], "group_id");

            // Los IDs provienen directamente de la base de datos.
            $placeholders = implode(',', array_fill(0, count($groupIds), '?'));

            // Eliminar grupos duplicados
            $delete = DatabaseHelper::delete(
                $this->connection,
                "
                    DELETE FROM student_groups
                    WHERE student_id = ?
                        AND group_id IN ($placeholders);
                ",
                str_repeat("i", count($groupIds) + 1),
                [$studentId, ...$groupIds]
            );

            if (!$delete["success"]) {
                $this->connection->rollback();

                return [
                    "success" => false,
                    "message" => $delete["message"],
                    "data" => null
                ];
            }

            $primary = DatabaseExecutor::execute(
                $this->connection,
                "
                    UPDATE student_groups
                    SET is_primary = 1
                    WHERE student_id = ? AND group_id = ?;
                ",
                "ii",
                [$studentId, $correctGroupId]
            );

            if (!$primary["success"]) {
                $this->connection->rollback();
                return $primary;
            }

            // Asignar el grupo correcto al alumno
            $update = DatabaseExecutor::execute(
                $this->connection,
                "
                    UPDATE students
                    SET id_group = ?
                    WHERE id = ?;
                ",
                "ii",
                [$correctGroupId, $studentId]
            );

            if (!$update["success"]) {
                $this->connection->rollback();

                return [
                    "success" => false,
                    "message" => $update["message"],
                    "data" => null
                ];
            }

            $this->connection->commit();

            return [
                "success" => true,
                "message" => "Duplicados resueltos correctamente. Se eliminaron "
                    . count($groupIds)
                    . " grupo(s) incorrectos.",
                "data" => null
            ];

        } catch (\Exception $e) {
            $this->connection->rollback();

            return [
                "success" => false,
                "message" => "Error inesperado al resolver los duplicados.",
                "data" => null
            ];
        }
    }

    // *****************************************************************************************
    // API Methods
    // *****************************************************************************************

    public function getNoGroupStudentsList(
        string $search = '',
        int $page = 1,
        int $limit = 30,
        int $groupId = 0
    ): array {
        $groupType = DatabaseHelper::selectOne(
            $this->connection,
            "
                SELECT c.subarea
                FROM groups g
                INNER JOIN carreers c ON g.id_carreer = c.id
                WHERE g.id = ?;
            ",
            "i",
            [$groupId]
        );

        if (!$groupType["success"]) {
            return [
                "success" => false,
                "code" => "GROUP_NOT_FOUND",
                "message" => "No se encontró el grupo solicitado."
            ];
        }

        $isCourseOrDiploma = in_array(
            strtolower($groupType["data"]["subarea"]),
            ["cursos", "diplomados"],
            true
        );

        if ($isCourseOrDiploma) {
            $sql = "
                SELECT s.id, s.nombre
                FROM students s
                WHERE s.id NOT IN (
                    SELECT student_id
                    FROM student_groups
                    WHERE group_id = ?
                )
            ";

            $types = "i";
            $params = [$groupId];
        } else {
            $sql = "
                SELECT id, nombre
                FROM students
                WHERE id_group IS NULL
            ";

            $types = "";
            $params = [];
        }

        if ($search !== '') {
            $sql .= " AND nombre LIKE ?";
            $types .= "s";
            $params[] = "%{$search}%";
        }

        $sql .= " ORDER BY nombre ASC LIMIT ? OFFSET ?;";

        $params[] = $limit;
        $params[] = ($page - 1) * $limit;
        $types .= "ii";

        $students = DatabaseHelper::selectAll(
            $this->connection,
            $sql,
            $types,
            $params
        );

        if (!$students["success"]) {
            return $students;
        }

        if ($isCourseOrDiploma) {
            $countSql = "
                SELECT COUNT(*) AS total
                FROM students s
                WHERE s.id NOT IN (
                    SELECT student_id FROM student_groups WHERE group_id = ?
                )
            ";
            $countTypes = "i";
            $countParams = [$groupId];
        } else {
            $countSql = "SELECT COUNT(*) AS total FROM students WHERE id_group IS NULL";
            $countTypes = "";
            $countParams = [];
        }

        if ($search !== '') {
            $countSql .= " AND nombre LIKE ?";
            $countTypes .= "s";
            $countParams[] = "%{$search}%";
        }

        $total = DatabaseHelper::selectValue(
            $this->connection,
            $countSql,
            "total",
            $countTypes,
            $countParams
        );

        return [
            "success" => true,
            "message" => "Alumnos disponibles obtenidos correctamente.",
            "data" => [
                "students" => $students["data"],
                "total" => $total
            ]
        ];
    }

    public function getGroupCareer(int $studentId): array
    {
        $result = DatabaseHelper::selectOne(
            $this->connection,
            "
                SELECT
                    carreers.id AS careerId,
                    carreers.nombre AS careerName
                FROM students
                INNER JOIN groups ON students.id_group = groups.id
                INNER JOIN carreers ON groups.id_carreer = carreers.id
                WHERE students.id = ?;
            ",
            "i",
            [$studentId]
        );

        if (!$result["success"] && ($result["message"] ?? '') === 'No se encontró el registro solicitado.') {
            $result["code"] = "STUDENT_NOT_FOUND";
            $result["message"] = "El alumno no existe o no tiene un grupo de carrera asignado.";
        }

        return $result;
    }

    public function getGroupSchedules(int $groupId): array
    {
        $group = DatabaseHelper::selectOne(
            $this->connection,
            "SELECT id FROM groups WHERE id = ?;",
            "i",
            [$groupId]
        );

        if (!$group["success"]) {
            return [
                "success" => false,
                "code" => "GROUP_NOT_FOUND",
                "message" => "No se encontró el grupo solicitado."
            ];
        }

        return DatabaseHelper::selectAll(
            $this->connection,
            "
                SELECT
                    id,
                    title,
                    date,
                    start,
                    end,
                    description
                FROM schedules
                WHERE id_group = ?;
            ",
            "i",
            [$groupId]
        );
    }

    public function addSchedule(array $data): array
    {
        $group = DatabaseHelper::selectOne(
            $this->connection,
            "SELECT id FROM groups WHERE id = ?;",
            "i",
            [$data['groupId']]
        );

        if (!$group["success"]) {
            return [
                "success" => false,
                "code" => "GROUP_NOT_FOUND",
                "message" => "No se encontró el grupo solicitado."
            ];
        }

        return DatabaseHelper::insert(
            $this->connection,
            "
                INSERT INTO schedules (
                    id_group,
                    title,
                    date,
                    start,
                    end,
                    description
                ) VALUES (?, ?, ?, ?, ?, ?);
            ",
            "isssss",
            [
                $data['groupId'],
                $data['title'],
                $data['date'],
                $data['inputStart'],
                $data['inputEnd'],
                $data['description']
            ]
        );
    }

}
