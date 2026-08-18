<?php
namespace Vendor\Schoolarsystem\Models;

use Vendor\Schoolarsystem\DBConnection;
use Vendor\Schoolarsystem\Core\DatabaseHelper;

class TeachersModel
{
    private $connection;

    public function __construct(DBConnection $dbConnection)
    {
        $this->connection = $dbConnection->getConnection();
    }

    public function getTeacherById(int $teacherId): array
    {
        return DatabaseHelper::selectOne(
            $this->connection,
            "
                SELECT
                    id,
                    nombre AS name,
                    genero AS gender,
                    nacimiento AS birthdate,
                    estado_civil AS civil_status,
                    telefono AS phone,
                    email
                FROM teachers
                WHERE id = ?;
            ",
            "i",
            [$teacherId]
        );
    }

    public function getAllTeachers(): array
    {
        return DatabaseHelper::selectAll(
            $this->connection,
            "
                SELECT
                    id,
                    nombre AS name,
                    telefono AS phone,
                    email
                FROM teachers;
            "
        );
    }

    public function addTeacher(array $teacherDataArray): array
    {
        return DatabaseHelper::insert(
            $this->connection,
            "
                INSERT INTO teachers (
                    nombre,
                    genero,
                    nacimiento,
                    estado_civil,
                    telefono,
                    email
                ) VALUES (?, ?, ?, ?, ?, ?);
            ",
            "ssssss",
            [
                $teacherDataArray['teacherName'],
                $teacherDataArray['teacherGender'],
                $teacherDataArray['teacherBirthday'],
                $teacherDataArray['teacherState'],
                $teacherDataArray['teacherPhone'],
                $teacherDataArray['teacherEmail']
            ]
        );
    }

    public function updateTeacher(array $teacherUpdateData): array
    {
        $id = $teacherUpdateData['idTeacherEdit'];

        try {
            $currentData = DatabaseHelper::selectOne(
                $this->connection,
                "
                    SELECT
                        nombre,
                        genero,
                        nacimiento,
                        estado_civil,
                        telefono,
                        email
                    FROM teachers
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
                "nombre" => $teacherUpdateData['teacherNameEdit'],
                "genero" => $teacherUpdateData['teacherGenderEdit'],
                "nacimiento" => $teacherUpdateData['teacherBirthdayEdit'],
                "estado_civil" => $teacherUpdateData['teacherStateEdit'],
                "telefono" => $teacherUpdateData['teacherPhoneEdit'],
                "email" => $teacherUpdateData['teacherEmailEdit']
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
                    UPDATE teachers
                    SET
                        nombre = ?,
                        genero = ?,
                        nacimiento = ?,
                        estado_civil = ?,
                        telefono = ?,
                        email = ?
                    WHERE id = ?;
                ",
                "ssssssi",
                [
                    $newData["nombre"],
                    $newData["genero"],
                    $newData["nacimiento"],
                    $newData["estado_civil"],
                    $newData["telefono"],
                    $newData["email"],
                    $id
                ]
            );

        } catch (\Exception $e) {
            return [
                "success" => false,
                "message" => "Error inesperado al actualizar los datos del profesor.",
                "data" => null
            ];
        }
    }

    public function deleteTeacherById(int $teacherId): array
    {
        return DatabaseHelper::delete(
            $this->connection,
            "
                DELETE
                FROM teachers
                WHERE id = ?;
            ",
            "i",
            [$teacherId]
        );
    }

    public function getAllTeachersUsers(): array
    {
        return DatabaseHelper::selectAll(
            $this->connection,
            "
                SELECT
                    teachers.id,
                    teachers.nombre AS name,
                    login_teachers.user,
                    login_teachers.status
                FROM teachers
                LEFT JOIN login_teachers ON teachers.id = login_teachers.id_teacher;
            "
        );
    }

    public function addTeacherUser(array $teacherUserDataArray): array
    {
        return DatabaseHelper::insert(
            $this->connection,
            "
                INSERT INTO login_teachers (
                    id_teacher,
                    user,
                    password,
                    status
                ) VALUES (?, ?, ?, ?);
            ",
            "isss",
            [
                $teacherUserDataArray['teacherUserId'],
                $teacherUserDataArray['teacherUserAdd'],
                $teacherUserDataArray['teacherUserPass'],
                "Activo"
            ]
        );
    }

    public function updateTeacherUserData(array $teacherUserUpdateDataArray): array
    {
        $id = $teacherUserUpdateDataArray['teacherUserIdEdit'];

        try {
            $currentData = DatabaseHelper::selectOne(
                $this->connection,
                "
                    SELECT
                       user,
                       password
                    FROM login_teachers
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
                "user" => $teacherUserUpdateDataArray['teacherUserAddEdit'],
                "password" => $teacherUserUpdateDataArray['teacherUserPassEdit'],
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
                    UPDATE login_teachers
                    SET
                        user = ?,
                        password = ?
                    WHERE id_teacher = ?;
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
                "message" => "Error inesperado al actualizar los datos de usuario del profesor.",
                "data" => null
            ];
        }
    }

    public function verifyTeacherByUser(string $teacherUser): array
    {
        $result = DatabaseHelper::selectOne(
            $this->connection,
            "
                SELECT user
                FROM login_teachers
                WHERE user = ?;
            ",
            "s",
            [$teacherUser]
        );

        if ($result['success']) {
            return [
                "success" => true,
                "user" => false,
                "message" => "El usuario ya existe"
            ];
        } else {
            return [
                "success" => true,
                "user" => true,
                "message" => "Usuario disponible"
            ];
        }
    }

    public function desactivateTeacherUser(int $teacherUserId): array
    {
        $status = 'Inactivo';

        $result = DatabaseHelper::update(
            $this->connection,
            "
                UPDATE login_teachers
                SET
                    status = ?
                WHERE id_teacher = ?;
            ",
            "si",
            [
                $status,
                $teacherUserId
            ]
        );

        if ($result['success']) {
            return [
                "success" => true,
                "user" => false,
                "message" => "Usuario desactivado correctamente."
            ];
        } else {
            return [
                "success" => true,
                "user" => true,
                "message" => "Error al desactivar el usuario, por favor intente de nuevo o más tarde."
            ];
        }
    }

    public function reactivateTeacherUser(int $teacherUserId): array
    {
        $status = 'Activo';

        $result = DatabaseHelper::update(
            $this->connection,
            "
                UPDATE login_teachers
                SET
                    status = ?
                WHERE id_teacher = ?;
            ",
            "si",
            [
                $status,
                $teacherUserId
            ]
        );

        if ($result['success']) {
            return [
                "success" => true,
                "user" => false,
                "message" => "Usuario reactivado correctamente."
            ];
        } else {
            return [
                "success" => true,
                "user" => true,
                "message" => "Error al reactivar el usuario, por favor intente de nuevo o más tarde."
            ];
        }
    }
}
