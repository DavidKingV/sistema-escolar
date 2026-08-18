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
                "message" => "No se encontraron calificaciones."
            ];
        }

        return [
            "success" => true,
            "message" => $response["message"],
            "grades" => $response["data"]
        ];
    }

    public function addMakeOverGrade(array $data): array
    {
        $studentId = (int) $data["studentId"];
        $subjectId = (int) $data["subjectId"];
        $subjectChildId = (int) ($data["subjectChildId"] ?? 0);
        $gradeId = (int) $data["gradeId"];
        $continuousGrade = (float) (
            $data["continuousGrade"]
            ?? $data["continuosGrade"]
            ?? 0
        );
        $examGrade = (float) $data["examGrade"];
        $finalGrade = (float) $data["finalGrade"];

        try {
            $this->connection->begin_transaction();

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
                    $subjectChildId > 0 ? $subjectChildId : null,
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

            if ($subjectChildId > 0) {
                $link = DatabaseHelper::update(
                    $this->connection,
                    "
                        UPDATE student_grades_child
                        SET makeOverId = ?
                        WHERE id = ?;
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
                        WHERE id = ?;
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
                "message" => "Se ha insertado la calificación correctamente."
            ];
        } catch (\Throwable $e) {
            $this->connection->rollback();

            return [
                "success" => false,
                "message" => "Error inesperado al insertar la calificación."
            ];
        }
    }
}
