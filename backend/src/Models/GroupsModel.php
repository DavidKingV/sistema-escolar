<?php
namespace Vendor\Schoolarsystem\Models;

use Vendor\Schoolarsystem\DBConnection;
use Vendor\Schoolarsystem\Core\DatabaseHelper;

class GroupsModel
{
    private $connection;

    public function __construct(DBConnection $dbConnection)
    {
        $this->connection = $dbConnection->getConnection();
    }

    public function getGroupById(int $groupId): array
    {
        return DatabaseHelper::selectOne(
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
                    "message" => $currentData["message"],
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
        if (empty($studentIds)) {
            return [
                "success" => false,
                "message" => "No se proporcionaron alumnos válidos.",
                "data" => null
            ];
        }

        $ids = array_values(
            array_filter(
                array_unique(array_map('intval', $studentIds)),
                fn($id) => $id > 0
            )
        );

        if (empty($ids)) {
            return [
                "success" => false,
                "message" => "No se proporcionaron alumnos válidos.",
                "data" => null
            ];
        }

        try {
            $this->connection->begin_transaction();

            /*
             * Determinar tipo de grupo
             */
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
                    "message" => $group["message"],
                    "data" => null
                ];
            }

            $isCourseOrDiploma = in_array(
                strtolower($group["data"]["subarea"]),
                ["cursos", "diplomados"],
                true
            );

            /*
             * Obtener alumnos que ya están asignados
             */
            $placeholders = implode(",", array_fill(0, count($ids), "?"));
            $types = str_repeat("i", count($ids));

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
                    SELECT id
                    FROM students
                    WHERE id IN ($placeholders)
                        AND id_group IS NOT NULL
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
                    "message" => $isCourseOrDiploma
                        ? "Todos los alumnos seleccionados ya están registrados en este curso/diplomado."
                        : "Todos los alumnos seleccionados ya pertenecen a un grupo.",
                    "data" => null
                ];
            }

            /*
             * Asignar grupo de carrera
             */
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

            /*
             * Registrar alumnos en student_groups
             */
            $isPrimary = $isCourseOrDiploma ? 0 : $groupId;

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
            if ($this->connection->errno === 0 || $this->connection->ping()) {
                $this->connection->rollback();
            }

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

            $update = DatabaseHelper::update(
                $this->connection,
                "
                    UPDATE students
                    SET id_group = NULL
                    WHERE id = ?
                        AND id_group = ?;
                ",
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

            // Asignar el grupo correcto al alumno
            $update = DatabaseHelper::update(
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
        $isCourseOrDiploma = false;

        if ($groupId > 0) {
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

            if ($groupType["success"]) {
                $isCourseOrDiploma = in_array(
                    strtolower($groupType["data"]["subarea"]),
                    ["cursos", "diplomados"],
                    true
                );
            }
        }

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

        return $students["success"]
            ? $students["data"]
            : [];
    }

    public function getGroupsCount(string $search = ''): int
    {
        $sql = "
            SELECT COUNT(*) AS total
            FROM students
            WHERE id_group IS NULL
        ";

        $params = [];
        $types = "";

        if ($search !== '') {
            $sql .= " AND nombre LIKE ?";
            $types = "s";
            $params[] = "%{$search}%";
        }

        return DatabaseHelper::selectValue(
            $this->connection,
            $sql,
            "total",
            $types,
            $params
        );
    }

    public function getGroupCareer(int $studentId): array
    {
        return DatabaseHelper::selectOne(
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
    }

    public function getGroupSchedules(int $groupId): array
    {
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
