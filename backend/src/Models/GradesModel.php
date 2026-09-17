<?php
namespace Vendor\Schoolarsystem\Models;

use Vendor\Schoolarsystem\Core\DatabaseHelper;
use Vendor\Schoolarsystem\DBConnection;

class GradesModel
{
    private $connection;

    public function __construct(DBConnection $dbConnection)
    {
        $this->connection = $dbConnection->getConnection();
    }

    public function getMakeOverGrades(int $makeOverId): array
    {
        $response = DatabaseHelper::selectAll(
            $this->connection,
            "
                SELECT
                    mog.*,
                    s.nombre AS subject_nombre,
                    sc.nombre AS subject_child_nombre
                FROM makeOverGrades mog
                INNER JOIN subjects s ON mog.subjectId = s.id
                LEFT JOIN subject_child sc ON mog.subjectChildId = sc.id
                WHERE mog.id = ?;
            ",
            "i",
            [$makeOverId]
        );

        if (!$response["success"]) {
            return $response;
        }

        if ($response["data"] === []) {
            return [
                "success" => false,
                "code" => "MAKEOVER_NOT_FOUND",
                "message" => "No se encontraron calificaciones."
            ];
        }

        return [
            "success" => true,
            "message" => $response["message"],
            "grades" => $response["data"]
        ];
    }

    public function getGradeTarget(
        int $studentId,
        int $subjectId,
        ?int $subjectChildId,
        int $gradeId
    ): array {
        return $this->findGradeTarget(
            $studentId,
            $subjectId,
            $subjectChildId,
            $gradeId
        );
    }

    public function addMakeOverGrade(array $data): array
    {
        $studentId = $data["studentId"];
        $subjectId = $data["subjectId"];
        $subjectChildId = $data["subjectChildId"];
        $gradeId = $data["gradeId"];
        $continuousGrade = $data["continuousGrade"];
        $examGrade = (float) $data["examGrade"];
        $finalGrade = (float) $data["finalGrade"];

        try {
            $this->connection->begin_transaction();

            $gradeTarget = $this->findGradeTarget(
                $studentId,
                $subjectId,
                $subjectChildId,
                $gradeId,
                true
            );

            if (!$gradeTarget["success"]) {
                $this->connection->rollback();
                return $gradeTarget;
            }

            $makeOver = DatabaseHelper::insert(
                $this->connection,
                "
                    INSERT INTO makeOverGrades (
                    studentId,
                    subjectId,
                    subjectChildId,
                        continuosGrade,
                        examGrade,
                        finalGrade
                    ) VALUES (?, ?, ?, ?, ?, ?);
                ",
                "iiiddd",
                [
                    $studentId,
                    $subjectId,
                    $subjectChildId,
                    $continuousGrade,
                    $examGrade,
                    $finalGrade
                ]
            );

            if (!$makeOver["success"]) {
                $this->connection->rollback();
                return $makeOver;
            }

            $makeOverId = (int) $makeOver["insertedId"];

            if ($subjectChildId !== null) {
                $link = DatabaseHelper::update(
                    $this->connection,
                    "
                        UPDATE student_grades_child
                        SET makeOverId = ?
                        WHERE id = ?
                            AND makeOverId IS NULL;
                    ",
                    "ii",
                    [$makeOverId, $gradeId]
                );
            } else {
                $link = DatabaseHelper::update(
                    $this->connection,
                    "
                        UPDATE student_grades
                        SET makeOver = ?
                        WHERE id = ?
                            AND makeOver IS NULL;
                    ",
                    "ii",
                    [$makeOverId, $gradeId]
                );
            }

            if (!$link["success"]) {
                $this->connection->rollback();
                return $link;
            }

            $this->connection->commit();

            return [
                "success" => true,
                "message" => "Se ha insertado la calificación correctamente.",
                "insertedId" => $makeOverId
            ];
        } catch (\Throwable $e) {
            $this->connection->rollback();

            return [
                "success" => false,
                "message" => "Error inesperado al insertar la calificación."
            ];
        }
    }

    private function findGradeTarget(
        int $studentId,
        int $subjectId,
        ?int $subjectChildId,
        int $gradeId,
        bool $lockForUpdate = false
    ): array {
        if ($subjectChildId !== null) {
            $response = DatabaseHelper::selectOne(
                $this->connection,
                "
                    SELECT
                        sgc.id,
                        sgc.final_grade AS finalGrade,
                        sgc.makeOverId,
                        s.nombre AS subjectName,
                        sc.nombre AS subjectChildName
                    FROM student_grades_child sgc
                    INNER JOIN subjects s ON s.id = sgc.id_subject
                    INNER JOIN subject_child sc ON sc.id = sgc.id_subject_child
                    WHERE sgc.id = ?
                        AND sgc.id_student = ?
                        AND sgc.id_subject = ?
                        AND sgc.id_subject_child = ?
                    " . ($lockForUpdate ? "FOR UPDATE" : "") . ";
                ",
                "iiii",
                [$gradeId, $studentId, $subjectId, $subjectChildId]
            );
        } else {
            $response = DatabaseHelper::selectOne(
                $this->connection,
                "
                    SELECT
                        sg.id,
                        sg.final_grade AS finalGrade,
                        sg.makeOver AS makeOverId,
                        s.nombre AS subjectName,
                        NULL AS subjectChildName
                    FROM student_grades sg
                    INNER JOIN subjects s ON s.id = sg.id_subject
                    WHERE sg.id = ?
                        AND sg.id_student = ?
                        AND sg.id_subject = ?
                    " . ($lockForUpdate ? "FOR UPDATE" : "") . ";
                ",
                "iii",
                [$gradeId, $studentId, $subjectId]
            );
        }

        if (!$response["success"]) {
            if (($response["message"] ?? "") === "No se encontró el registro solicitado.") {
                $response["code"] = "GRADE_RECORD_NOT_FOUND";
                $response["message"] = "No se encontró la calificación original indicada.";
            }

            return $response;
        }

        if ($response["data"]["makeOverId"] !== null) {
            return [
                "success" => false,
                "code" => "MAKEOVER_ALREADY_EXISTS",
                "message" => "La calificación ya cuenta con un recursamiento registrado."
            ];
        }

        $originalFinalGrade = (float) $response["data"]["finalGrade"];

        if ($originalFinalGrade <= 0 || $originalFinalGrade >= 6) {
            return [
                "success" => false,
                "code" => "GRADE_NOT_ELIGIBLE",
                "message" => "La calificación original no es elegible para recursamiento."
            ];
        }

        return $response;
    }
}
