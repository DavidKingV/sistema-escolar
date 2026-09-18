<?php
namespace Vendor\Schoolarsystem\Models;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Vendor\Schoolarsystem\DBConnection;
use Vendor\Schoolarsystem\MicrosoftActions;
use Vendor\Schoolarsystem\Core\DatabaseHelper;

class StudentsModel
{
    private $connection;

    public function __construct(DBConnection $dbConnection)
    {
        $this->connection = $dbConnection->getConnection();
    }

    public function getStudentById(int $studentId): array
    {
        return DatabaseHelper::selectOne(
            $this->connection,
            "
                SELECT
                    id,
                    no_control,
                    noControlSEP AS noControlSep,
                    nombre AS name,
                    genero AS gender,
                    nacimiento AS birthdate,
                    estado_civil AS civil_status,
                    nacionalidad AS nationality,
                    curp,
                    telefono AS phone,
                    email
                FROM students
                WHERE id = ?;
            ",
            "i",
            [$studentId]
        );
    }

    public function getAllStudents(): array
    {
        return DatabaseHelper::selectAll(
            $this->connection,
            "
                SELECT
                    s.*,
                    g.nombre AS nombre_grupo,
                    g.clave AS clave_grupo
                FROM students s
                LEFT JOIN student_groups sg
                    ON s.id = sg.student_id
                    AND sg.is_primary = TRUE
                LEFT JOIN groups g
                    ON sg.group_id = g.id;
            "
        );
    }

    public function addStudent(array $studentDataArray): array
    {
        $hasMicrosoftUser =
            !empty($studentDataArray["microsoftId"]) &&
            !empty($studentDataArray["microsoftEmail"]);

        try {
            if ($hasMicrosoftUser) {
                $this->connection->begin_transaction();
            }

            $studentResponse = DatabaseHelper::insert(
                $this->connection,
                "
                    INSERT INTO students (
                        no_control,
                        noControlSep,
                        nombre,
                        genero,
                        nacimiento,
                        estado_civil,
                        nacionalidad,
                        curp,
                        telefono,
                        email
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?);
                ",
                "ssssssssss",
                [
                    $studentDataArray["controlNumber"],
                    $studentDataArray["controlSepNumber"],
                    $studentDataArray["studentName"],
                    $studentDataArray["studentGender"],
                    $studentDataArray["studentBirthday"],
                    $studentDataArray["studentState"],
                    $studentDataArray["studentNation"],
                    $studentDataArray["studentCurp"],
                    $studentDataArray["studentPhone"],
                    $studentDataArray["studentEmail"]
                ]
            );

            if (!$studentResponse["success"]) {
                if ($hasMicrosoftUser) {
                    $this->connection->rollback();
                }

                return $studentResponse;
            }

            if ($hasMicrosoftUser) {
                $microsoftResponse = DatabaseHelper::insert(
                    $this->connection,
                    "
                        INSERT INTO microsoft_students (
                            id,
                            student_id,
                            displayName,
                            mail
                        ) VALUES (?, ?, ?, ?);
                    ",
                    "siss",
                    [
                        $studentDataArray["microsoftId"],
                        $studentResponse["insertedId"],
                        $studentDataArray["studentName"],
                        $studentDataArray["microsoftEmail"]
                    ]
                );

                if (!$microsoftResponse["success"]) {
                    $this->connection->rollback();
                    return $microsoftResponse;
                }

                $this->connection->commit();
            }

            return $studentResponse;
        } catch (\Throwable $e) {
            if ($hasMicrosoftUser) {
                $this->connection->rollback();
            }

            return [
                "success" => false,
                "message" => "Error inesperado al registrar al estudiante."
            ];
        }
    }

    public function updateStudent(array $studentDataArray): array
    {
        $id = $studentDataArray["idStudentDB"];

        try {
            $currentData = DatabaseHelper::selectOne(
                $this->connection,
                "
                    SELECT
                        no_control,
                        noControlSEP,
                        nombre,
                        genero,
                        nacimiento,
                        estado_civil,
                        nacionalidad,
                        curp,
                        telefono,
                        email
                    FROM students
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
                "no_control" => $studentDataArray["controlNumber"],
                "noControlSEP" => $studentDataArray["controlSepNumber"],
                "nombre" => $studentDataArray["studentName"],
                "genero" => $studentDataArray["studentGender"],
                "nacimiento" => $studentDataArray["studentBirthday"],
                "estado_civil" => $studentDataArray["studentState"],
                "nacionalidad" => $studentDataArray["studentNation"],
                "curp" => $studentDataArray["studentCurp"],
                "telefono" => $studentDataArray["studentPhone"],
                "email" => $studentDataArray["studentEmail"]
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
                    UPDATE students
                    SET
                        no_control = ?,
                        noControlSEP = ?,
                        nombre = ?,
                        genero = ?,
                        nacimiento = ?,
                        estado_civil = ?,
                        nacionalidad = ?,
                        curp = ?,
                        telefono = ?,
                        email = ?
                    WHERE id = ?;
                ",
                "ssssssssssi",
                [
                    $newData["no_control"],
                    $newData["noControlSEP"],
                    $newData["nombre"],
                    $newData["genero"],
                    $newData["nacimiento"],
                    $newData["estado_civil"],
                    $newData["nacionalidad"],
                    $newData["curp"],
                    $newData["telefono"],
                    $newData["email"],
                    $id
                ]
            );

        } catch (\Exception $e) {
            return [
                "success" => false,
                "message" => "Error inesperado al actualizar los datos del estudiante.",
                "data" => null
            ];
        }
    }

    public function deleteStudentById(int $studentId): array
    {
        return DatabaseHelper::delete(
            $this->connection,
            "
                DELETE
                FROM students
                WHERE id = ?;
            ",
            "i",
            [$studentId]
        );
    }

    public function getAllStudentsUsers(): array
    {
        return DatabaseHelper::selectAll(
            $this->connection,
            "
                SELECT
                    students.id,
                    students.no_control,
                    students.nombre AS name,
                    login_students.user,
                    login_students.status
                FROM students
                LEFT JOIN login_students
                    ON students.id = login_students.student_id
                WHERE students.id NOT IN (
                    SELECT student_id
                    FROM microsoft_students
                );
            "
        );
    }

    public function addStudentUser(array $studentDataArray): array
    {
        $status = 'Activo';

        return DatabaseHelper::insert(
            $this->connection,
            "
                INSERT INTO login_students (
                    student_id,
                    user,
                    password,
                    status
                ) VALUES (?, ?, ?, ?);
            ",
            "isss",
            [
                $studentDataArray['studentUserId'],
                $studentDataArray['studentUserAdd'],
                $studentDataArray['studentUserPass'],
                $status
            ]
        );
    }

    public function updateStudentUser(array $studentEditDataArray): array
    {
        $id = $studentEditDataArray['studentUserIdEdit'];

        try {
            $currentData = DatabaseHelper::selectOne(
                $this->connection,
                "
                    SELECT
                        user,
                        password
                    FROM login_students
                    WHERE student_id = ?;
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
                "user" => $studentEditDataArray['studentUserAddEdit'],
                "password" => $studentEditDataArray['studentUserPassEdit']
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
                    UPDATE login_students
                    SET
                        user = ?,
                        password = ?
                    WHERE student_id = ?;
                ",
                "ssi",
                [
                    $newData["user"],
                    $newData["password"],
                    $id
                ]
            );

        } catch (\Exception $e) {
            return [
                "success" => false,
                "message" => "Error inesperado al actualizar los datos del usuario.",
                "data" => null
            ];
        }
    }

    public function desactivateStudentUser(int $studentId): array
    {
        return DatabaseHelper::update(
            $this->connection,
            "
                UPDATE login_students
                SET
                    status = ?
                WHERE student_id = ?;
            ",
            "si",
            [
                'Inactivo',
                $studentId
            ]
        );
    }

    public function reactivateStudentUser(int $studentId): array
    {
        return DatabaseHelper::update(
            $this->connection,
            "
                UPDATE login_students
                SET
                    status = ?
                WHERE student_id = ?;
            ",
            "si",
            [
                'Activo',
                $studentId
            ]
        );
    }

    public function getMicrosoftStudentsUsers(): array
    {
        return DatabaseHelper::selectAll(
            $this->connection,
            "
                SELECT
                    id,
                    student_id,
                    displayName AS name,
                    mail AS email
                FROM microsoft_students;
            "
        );
    }

    public function findMicrosoftUser(
        string $studentName,
        ?string $accessToken
    ): array {
        if (!$accessToken) {
            return [
                "success" => false,
                "message" => "Debes iniciar sesión en Microsoft para poder enlazar un usuario a una cuenta."
            ];
        }

        $searchUser = new MicrosoftActions($this->connection);

        $search = $searchUser->getStudentByName(
            $accessToken,
            $studentName
        );

        if ($search['success']) {
            return [
                "success" => true,
                "message" => "Usuario encontrado",
                "data" => $search
            ];
        }

        return [
            "success" => false,
            "message" => $search['error']
        ];
    }

    public function assignMicrosoftUserToStudent(
        array $dataStudentUserArray
    ): array {
        $studentId = (int) $dataStudentUserArray["studentId"];

        try {
            $this->connection->begin_transaction();

            $insert = DatabaseHelper::insert(
                $this->connection,
                "
                    INSERT INTO microsoft_students (
                        id,
                        student_id,
                        displayName,
                        mail
                    ) VALUES (?, ?, ?, ?);
                ",
                "siss",
                [
                    $dataStudentUserArray["microsoftUserId"],
                    $studentId,
                    $dataStudentUserArray["microsoftDisplayName"],
                    $dataStudentUserArray["microsoftEmail"]
                ]
            );

            if (!$insert["success"]) {
                $this->connection->rollback();
                return $insert;
            }

            $localUser = DatabaseHelper::selectOne(
                $this->connection,
                "
                    SELECT student_id
                    FROM login_students
                    WHERE student_id = ?;
                ",
                "i",
                [$studentId]
            );

            if ($localUser["success"]) {
                $delete = DatabaseHelper::delete(
                    $this->connection,
                    "
                        DELETE
                        FROM login_students
                        WHERE student_id = ?;
                    ",
                    "i",
                    [$studentId]
                );

                if (!$delete["success"]) {
                    $this->connection->rollback();
                    return $delete;
                }
            }

            $this->connection->commit();

            return [
                "success" => true,
                "message" => "Usuario de Microsoft asignado correctamente."
            ];
        } catch (\Throwable $e) {
            $this->connection->rollback();

            return [
                "success" => false,
                "message" => "Error inesperado al asignar el usuario de Microsoft."
            ];
        }
    }

    public function verifyStudentToken(int $studentId, string $token): array
    {
        try {
            $decodedToken = JWT::decode(
                urldecode($token),
                new Key($_ENV['KEY'], 'HS256')
            );

            if ((int) ($decodedToken->studentId ?? 0) !== $studentId) {
                return [
                    "success" => true,
                    "valid" => false,
                    "message" => "Token inválido."
                ];
            }

            $student = DatabaseHelper::selectOne(
                $this->connection,
                "
                    SELECT id
                    FROM students
                    WHERE id = ?;
                ",
                "i",
                [$studentId]
            );

            return [
                "success" => true,
                "valid" => $student["success"],
                "message" => $student["success"]
                    ? "Token válido."
                    : "Token inválido.",
                "token" => $student["success"] ? $decodedToken : null
            ];
        } catch (\Throwable $e) {
            return [
                "success" => true,
                "valid" => false,
                "message" => "Token inválido."
            ];
        }
    }

    public function getStudentName(int $studentId): array
    {
        $student = DatabaseHelper::selectOne(
            $this->connection,
            "
                SELECT nombre
                FROM students
                WHERE id = ?;
            ",
            "i",
            [$studentId]
        );

        if (!$student["success"]) {
            return $student;
        }

        return [
            "success" => true,
            "studentName" => $student["data"]["nombre"],
            "message" => $student["message"]
        ];
    }

    public function verifyStudentGroup(int $studentId): array
    {
        $group = DatabaseHelper::selectOne(
            $this->connection,
            "
                SELECT g.id_carreer
                FROM students s
                INNER JOIN groups g ON s.id_group = g.id
                WHERE s.id = ?;
            ",
            "i",
            [$studentId]
        );

        return [
            "success" => true,
            "group" => $group["success"],
            "id_carrer" => $group["data"]["id_carreer"] ?? null,
            "message" => $group["success"]
                ? "Alumno con grupo asignado."
                : "Alumno sin grupo asignado."
        ];
    }

    public function getSubjectNames(int $careerId): array
    {
        return DatabaseHelper::selectAll(
            $this->connection,
            "
                SELECT
                    cs.id_carreer AS id_career,
                    cs.id_subject,
                    cs.id_child_subject,
                    s.nombre AS name_subject
                FROM carreers_subjects cs
                INNER JOIN subjects s ON cs.id_subject = s.id
                WHERE cs.id_carreer = ?;
            ",
            "i",
            [$careerId]
        );
    }

    public function getStudentGrades(int $studentId): array
    {
        return DatabaseHelper::selectAll(
            $this->connection,
            "
                SELECT
                    sg.id AS grade_id,
                    s.id AS student_id,
                    s.nombre AS student_name,
                    sub.id AS subject_id,
                    sub.nombre AS subject_name,
                    NULL AS subject_child_id,
                    NULL AS subject_child_name,
                    sg.continuos_grade AS continuous_grade,
                    sg.exam_grade AS exam_grade,
                    sg.final_grade AS final_grade,
                    sg.updated_at AS update_at,
                    sg.makeOver AS makeOverId,
                    NULL AS makeOverIdChild
                FROM student_grades sg
                INNER JOIN students s ON sg.id_student = s.id
                INNER JOIN subjects sub ON sg.id_subject = sub.id
                WHERE sg.id_student = ?

                UNION

                SELECT
                    sgc.id AS grade_id,
                    s.id AS student_id,
                    s.nombre AS student_name,
                    sub.id AS subject_id,
                    sub.nombre AS subject_name,
                    sc.id AS subject_child_id,
                    sc.nombre AS subject_child_name,
                    sgc.continuos_grade AS continuous_grade,
                    sgc.exam_grade AS exam_grade,
                    sgc.final_grade AS final_grade,
                    sgc.updated_at AS update_at,
                    NULL AS makeOverId,
                    sgc.makeOverId AS makeOverIdChild
                FROM student_grades_child sgc
                INNER JOIN students s ON sgc.id_student = s.id
                INNER JOIN subjects sub ON sgc.id_subject = sub.id
                INNER JOIN subject_child sc
                    ON sgc.id_subject_child = sc.id
                WHERE sgc.id_student = ?;
            ",
            "ii",
            [$studentId, $studentId]
        );
    }

    public function getChildSubjectNames(int $subjectId): array
    {
        return DatabaseHelper::selectAll(
            $this->connection,
            "
                SELECT
                    id AS id_child_subject,
                    id_subject,
                    nombre AS name_child_subject
                FROM subject_child
                WHERE id_subject = ?;
            ",
            "i",
            [$subjectId]
        );
    }

    public function addStudentGrade(array $gradeDataArray): array
    {
        try {
            $this->connection->begin_transaction();

            $studentId = (int) $gradeDataArray['studentId'];
            $subjectId = (int) $gradeDataArray['subject'];
            $childSubjectId = (int) ($gradeDataArray['subjectChild'] ?? 0);
            $continuousGrade = (float) $gradeDataArray['gradeCont'];
            $examGrade = (float) $gradeDataArray['gradetest'];
            $finalGrade = (float) $gradeDataArray['gradefinal'];

            if ($childSubjectId > 0) {
                $childGrade = DatabaseHelper::insert(
                    $this->connection,
                    "
                        INSERT INTO student_grades_child (
                            id_student,
                            id_subject,
                            id_subject_child,
                            continuos_grade,
                            exam_grade,
                            final_grade
                        ) VALUES (?, ?, ?, ?, ?, ?);
                    ",
                    "iiiddd",
                    [
                        $studentId,
                        $subjectId,
                        $childSubjectId,
                        $continuousGrade,
                        $examGrade,
                        $finalGrade
                    ]
                );

                if (!$childGrade["success"]) {
                    $this->connection->rollback();
                    return $childGrade;
                }

                $averageData = DatabaseHelper::selectOne(
                    $this->connection,
                    "
                        SELECT AVG(final_grade) AS average
                        FROM student_grades_child
                        WHERE id_student = ? AND id_subject = ?;
                    ",
                    "ii",
                    [$studentId, $subjectId]
                );

                if (!$averageData["success"]) {
                    $this->connection->rollback();
                    return $averageData;
                }

                $average = (float) $averageData["data"]["average"];

                $mainGrade = DatabaseHelper::insert(
                    $this->connection,
                    "
                        INSERT INTO student_grades (
                            id_student,
                            id_subject,
                            continuos_grade,
                            exam_grade,
                            final_grade
                        ) VALUES (?, ?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE
                            continuos_grade = VALUES(continuos_grade),
                            exam_grade = VALUES(exam_grade),
                            final_grade = VALUES(final_grade);
                    ",
                    "iiddd",
                    [$studentId, $subjectId, $average, $average, $average]
                );

                if (!$mainGrade["success"]) {
                    $this->connection->rollback();
                    return $mainGrade;
                }
            } else {
                $mainGrade = DatabaseHelper::insert(
                    $this->connection,
                    "
                        INSERT INTO student_grades (
                            id_student,
                            id_subject,
                            continuos_grade,
                            exam_grade,
                            final_grade
                        ) VALUES (?, ?, ?, ?, ?);
                    ",
                    "iiddd",
                    [
                        $studentId,
                        $subjectId,
                        $continuousGrade,
                        $examGrade,
                        $finalGrade
                    ]
                );

                if (!$mainGrade["success"]) {
                    $this->connection->rollback();
                    return $mainGrade;
                }
            }

            $this->connection->commit();

            return [
                "success" => true,
                "message" => "Calificación registrada correctamente."
            ];
        } catch (\Throwable $e) {
            $this->connection->rollback();

            return [
                "success" => false,
                "message" => "Error inesperado al registrar la calificación."
            ];
        }
    }

    public function getGroupNames(): array
    {
        return DatabaseHelper::selectAll(
            $this->connection,
            "
                SELECT
                    id,
                    nombre AS name,
                    id_carreer AS id_career
                FROM groups
                ORDER BY nombre ASC;
            "
        );
    }

    public function addStudentToGroup(array $studentGroupDataArray): array
    {
        return DatabaseHelper::update(
            $this->connection,
            "UPDATE students SET id_group = ? WHERE id = ?;",
            "ii",
            [
                $studentGroupDataArray['studentIdGroup'],
                $studentGroupDataArray['studentId']
            ]
        );
    }

    public function verifyStudentUser(string $studentUser): array
    {
        $user = DatabaseHelper::selectOne(
            $this->connection,
            "
                SELECT student_id
                FROM login_students
                WHERE user = ?;
            ",
            "s",
            [$studentUser]
        );

        return [
            "success" => true,
            "user" => !$user["success"],
            "message" => $user["success"]
                ? "El usuario ya existe."
                : "Usuario disponible."
        ];
    }

    public function getStudentsNames(): array
    {
        return DatabaseHelper::selectAll(
            $this->connection,
            "
                SELECT id, nombre AS name
                FROM students
                ORDER BY nombre ASC;
            "
        );
    }

    // *****************************************************************************************
    // API Methods
    // *****************************************************************************************

    public function updateStatus(array $statusData): array
    {
        return DatabaseHelper::update(
            $this->connection,
            "
                UPDATE students
                SET academical_status = ?
                WHERE id = ?;
            ",
            "ii",
            [
                $statusData['studentStatus'],
                $statusData['studentId']
            ]
        );
    }

    public function getStudentsListSelect(
        string $search = '',
        int $page = 1,
        int $limit = 30
    ): array {
        $offset = (max(1, $page) - 1) * $limit;

        if ($search !== '') {
            return DatabaseHelper::selectAll(
                $this->connection,
                "
                    SELECT id, nombre
                    FROM students
                    WHERE nombre LIKE ?
                    ORDER BY nombre ASC
                    LIMIT ? OFFSET ?;
                ",
                "sii",
                ["%$search%", $limit, $offset]
            );
        }

        return DatabaseHelper::selectAll(
            $this->connection,
            "
                SELECT id, nombre
                FROM students
                ORDER BY nombre ASC
                LIMIT ? OFFSET ?;
            ",
            "ii",
            [$limit, $offset]
        );
    }

    public function getStudentsCount(string $search = ''): int
    {
        if ($search !== '') {
            return DatabaseHelper::selectValue(
                $this->connection,
                "
                    SELECT COUNT(*) AS total
                    FROM students
                    WHERE nombre LIKE ?;
                ",
                "total",
                "s",
                ["%$search%"]
            );
        }

        return DatabaseHelper::selectValue(
            $this->connection,
            "
                SELECT COUNT(*) AS total
                FROM students;
            ",
            "total"
        );
    }

    public function getStudentsForCron(): array
    {
        $response = DatabaseHelper::selectAll(
            $this->connection,
            "
                SELECT id, nombre, email
                FROM students;
            "
        );

        if (!$response["success"]) {
            return [];
        }

        return array_map(
            static fn($student) => [
                "success" => true,
                "studentId" => $student["id"],
                "name" => $student["nombre"],
                "email" => $student["email"]
            ],
            $response["data"]
        );
    }
}
