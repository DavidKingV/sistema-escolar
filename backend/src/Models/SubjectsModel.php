<?php
namespace Vendor\Schoolarsystem\Models;

use Vendor\Schoolarsystem\DBConnection;
use Vendor\Schoolarsystem\Core\DatabaseHelper;

class SubjectsModel
{
    private $connection;

    public function __construct(DBConnection $dbConnection)
    {
        $this->connection = $dbConnection->getConnection();
    }

    public function getSubjectById(int $subjectId): array
    {
        return DatabaseHelper::selectOne(
            $this->connection,
            "
                SELECT
                    id,
                    clave,
                    nombre AS name,
                    descripcion AS description
                FROM subjects
                WHERE id = ?;
            ",
            "i",
            [$subjectId]
        );
    }

    public function getAllSubjects(): array
    {
        return DatabaseHelper::selectAll(
            $this->connection,
            "
                SELECT DISTINCT
                    subjects.nombre AS name,
                    subjects.descripcion AS description,
                    subjects.id,
                    carreers_subjects.id_carreer AS id_carrer,
                    carreers.nombre AS career,
                    subject_child.nombre AS child,
                    subject_child.id AS id_child
                FROM subjects
                LEFT JOIN carreers_subjects ON subjects.id = carreers_subjects.id_subject
                LEFT JOIN carreers ON carreers_subjects.id_carreer = carreers.id
                LEFT JOIN subject_child ON subjects.id = subject_child.id_subject;
            "
        );
    }

    public function addSubject(array $subjectDataArray): array
    {
        return DatabaseHelper::insert(
            $this->connection,
            "
                INSERT INTO subjects (
                    clave,
                    nombre,
                    descripcion
                ) VALUES (?, ?, ?);
            ",
            "sss",
            [
                $subjectDataArray["subjectKey"],
                $subjectDataArray["subjectName"],
                $subjectDataArray["subjectDes"],
            ]
        );
    }

    public function updateSubject(array $subjectUpdateDataArray): array
    {
        $id = $subjectUpdateDataArray['idSubjectDB'];

        try {
            $currentData = DatabaseHelper::selectOne(
                $this->connection,
                "
                    SELECT
                       clave,
                       nombre,
                       descripcion
                    FROM subjects
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
                "clave" => $subjectUpdateDataArray['subjectKeyEdit'],
                "nombre" => $subjectUpdateDataArray['subjectNameEdit'],
                "descripcion" => $subjectUpdateDataArray['descriptionSubjectEdit'],
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
                    UPDATE subjects
                    SET
                        clave = ?,
                        nombre = ?,
                        descripcion = ?
                    WHERE id = ?;
                ",
                "sssi",
                [
                    $newData["clave"],
                    $newData["nombre"],
                    $newData["descripcion"],
                    $id
                ]
            );

        } catch (\Exception $e) {
            return [
                "success" => false,
                "message" => "Error inesperado al actualizar los datos de la materia.",
                "data" => null
            ];
        }
    }

    public function deleteSubjectById(int $subjectId): array
    {
        return DatabaseHelper::delete(
            $this->connection,
            "
                DELETE
                FROM subjects
                WHERE id = ?;
            ",
            "i",
            [$subjectId]
        );
    }

    public function getChildSubjectFindById(int $subjectChildId, int $subjectFatherId): array
    {
        return DatabaseHelper::selectOne(
            $this->connection,
            "
                SELECT
                    id,
                    id_subject,
                    nombre AS name,
                    descripcion AS description
                FROM subject_child
                WHERE id = ?
                    AND id_subject = ?;
            ",
            "ii",
            [$subjectChildId, $subjectFatherId]
        );
    }

    public function addSubjectChild(array $subjectChildDataArray): array
    {
        try {
            $this->connection->begin_transaction();

            $insert = DatabaseHelper::insert(
                $this->connection,
                "
                    INSERT INTO subject_child (
                        id_subject,
                        clave,
                        nombre,
                        descripcion
                    ) VALUES (?, ?, ?, ?);
                ",
                "isss",
                [
                    $subjectChildDataArray["idMainSubject"],
                    $subjectChildDataArray["subjectChildKey"],
                    $subjectChildDataArray["subjectChildName"],
                    $subjectChildDataArray["descriptionChildSubject"]
                ]
            );

            if (!$insert["success"]) {
                $this->connection->rollback();

                return [
                    "success" => false,
                    "message" => $insert["message"],
                    "data" => null
                ];
            }

            $subjectChildId = $insert["insertedId"];

            if ($subjectChildId <= 0) {
                $this->connection->rollback();

                return [
                    "success" => false,
                    "message" => "No fue posible crear la submateria.",
                    "data" => null
                ];
            }

            $update = DatabaseHelper::update(
                $this->connection,
                "
                    UPDATE carreers_subjects
                    SET
                        id_child_subject = ?
                    WHERE id_subject = ?
                        AND id_carreer = ?;
                ",
                "iii",
                [
                    $subjectChildId,
                    $subjectChildDataArray["idMainSubject"],
                    $subjectChildDataArray["carrerId"]
                ]
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
                "message" => "Materia y submateria agregadas correctamente.",
                "data" => [
                    "idSubjectChild" => $subjectChildId
                ]
            ];

        } catch (\Exception $e) {
            $this->connection->rollback();

            return [
                "success" => false,
                "message" => "Error inesperado al agregar la submateria.",
                "data" => null
            ];
        }
    }

    // CHECK
    public function updateSubjectChild(array $subjectChildUpdateDataArray): array
    {
        if ($subjectChildUpdateDataArray['subjectChildKey'] ?? null) {
            return DatabaseHelper::update(
                $this->connection,
                "
                    UPDATE subject_child
                    SET
                        clave = ?,
                        nombre = ?,
                        descripcion = ?
                    WHERE id = ?
                        AND id_subject = ?;
                ",
                "sssii",
                [
                    $subjectChildUpdateDataArray['subjectChildKey'],
                    $subjectChildUpdateDataArray['subjectChildNameInfo'],
                    $subjectChildUpdateDataArray['descriptionChildSubjectInfo'],
                    $subjectChildUpdateDataArray['idMainSubjectInfo'],
                    $subjectChildUpdateDataArray['idChildSubjectInfo']
                ]
            );
        } else {
            return DatabaseHelper::update(
                $this->connection,
                "
                    UPDATE subject_child
                    SET
                        nombre = ?,
                        descripcion = ?
                    WHERE id = ?
                        AND id_subject = ?;
                ",
                "ssii",
                [
                    $subjectChildUpdateDataArray['subjectChildNameInfo'],
                    $subjectChildUpdateDataArray['descriptionChildSubjectInfo'],
                    $subjectChildUpdateDataArray['idMainSubjectInfo'],
                    $subjectChildUpdateDataArray['idChildSubjectInfo']
                ]
            );
        }
    }

    public function deleteSubjectChildById(int $subjectChildId): array
    {
        return DatabaseHelper::delete(
            $this->connection,
            "
                DELETE
                FROM subject_child
                WHERE id = ?;
            ",
            "i",
            [$subjectChildId]
        );
    }

    // *****************************************************************************************
    // API Methods
    // *****************************************************************************************

    public function getSubjectsListSelect(
        string $search,
        int $page,
        int $limit,
        int $careerId
    ): array {
        $offset = (max(1, $page) - 1) * $limit;

        if ($search !== '') {
            return DatabaseHelper::selectAll(
                $this->connection,
                "
                    SELECT s.id, s.clave, s.nombre
                    FROM subjects s
                    LEFT JOIN carreers_subjects cs
                        ON s.id = cs.id_subject
                        AND cs.id_carreer = ?
                    WHERE cs.id_subject IS NULL
                        AND s.nombre LIKE ?
                    ORDER BY s.nombre ASC
                    LIMIT ? OFFSET ?;
                ",
                "isii",
                [$careerId, "%$search%", $limit, $offset]
            );
        }

        return DatabaseHelper::selectAll(
            $this->connection,
            "
                SELECT s.id, s.clave, s.nombre
                FROM subjects s
                LEFT JOIN carreers_subjects cs
                    ON s.id = cs.id_subject
                    AND cs.id_carreer = ?
                WHERE cs.id_subject IS NULL
                ORDER BY s.nombre ASC
                LIMIT ? OFFSET ?;
            ",
            "iii",
            [$careerId, $limit, $offset]
        );
    }

    public function getSubjectsCount(string $search, int $careerId): int
    {
        if ($search !== '') {
            return DatabaseHelper::selectValue(
                $this->connection,
                "
                    SELECT COUNT(*) AS total
                    FROM subjects s
                    LEFT JOIN carreers_subjects cs
                        ON s.id = cs.id_subject
                        AND cs.id_carreer = ?
                    WHERE cs.id_subject IS NULL
                        AND s.nombre LIKE ?;
                ",
                'total',
                'is',
                [$careerId, "%$search%"]
            );
        }

        return DatabaseHelper::selectValue(
            $this->connection,
            "
                SELECT COUNT(*) AS total
                FROM subjects s
                LEFT JOIN carreers_subjects cs
                    ON s.id = cs.id_subject
                    AND cs.id_carreer = ?
                WHERE cs.id_subject IS NULL;
            ",
            'total',
            'i',
            [$careerId]
        );
    }

    public function getChildSubject(int $subjectId): array
    {
        return DatabaseHelper::selectAll(
            $this->connection,
            "
                SELECT id, clave, nombre
                FROM subject_child
                WHERE id_subject = ?;
            ",
            'i',
            [$subjectId]
        );
    }

    public function subjectsListTable(int $careerId): array
    {
        return DatabaseHelper::selectAll(
            $this->connection,
            "
                SELECT
                    s.id,
                    s.clave AS claveSubject,
                    COALESCE(sc.clave, 'Sin materias hijas')
                        AS claveSubjectChild,
                    s.nombre,
                    COALESCE(sc.nombre, 'Sin materias hijas')
                        AS subject_child_nombre
                FROM subjects s
                INNER JOIN carreers_subjects cs
                    ON s.id = cs.id_subject
                    AND cs.id_carreer = ?
                LEFT JOIN subject_child sc
                    ON cs.id_child_subject = sc.id;
            ",
            'i',
            [$careerId]
        );
    }

    public function addSubjectCareer(array $subjectData): array
    {
        $subject = (int) $subjectData['subjectName'];
        $childSubject = !empty($subjectData['childSubjectName'])
            ? (int) $subjectData['childSubjectName']
            : null;
        $careerId = (int) $subjectData['careerId'];

        return DatabaseHelper::insert(
            $this->connection,
            "
                INSERT INTO carreers_subjects (
                    id_subject,
                    id_child_subject,
                    id_carreer
                ) VALUES (?, ?, ?);
            ",
            'iii',
            [$subject, $childSubject, $careerId]
        );
    }
}
