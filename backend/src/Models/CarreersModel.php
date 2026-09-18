<?php
namespace Vendor\Schoolarsystem\Models;

use Vendor\Schoolarsystem\DBConnection;
use Vendor\Schoolarsystem\Core\DatabaseHelper;

class CarreersModel
{
    private $connection;

    public function __construct(DBConnection $dbConnection)
    {
        $this->connection = $dbConnection->getConnection();
    }

    public function getCarreerById(int $carreerId): array
    {
        return DatabaseHelper::selectOne(
            $this->connection,
            "
                SELECT
                    id,
                    nombre AS name,
                    area,
                    subarea,
                    descripcion AS description
                FROM carreers
                WHERE id = ?;
            ",
            "i",
            [$carreerId]
        );
    }

    public function getAllCarreers(): array
    {
        return DatabaseHelper::selectAll(
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
    }

    public function addCarreer(array $carreerDataArray): array
    {
        return DatabaseHelper::insert(
            $this->connection,
            "
                INSERT INTO carreers (
                    nombre,
                    area,
                    subarea,
                    descripcion
                ) VALUES (?, ?, ?, ?);
            ",
            "ssss",
            [
                $carreerDataArray["careerName"],
                $carreerDataArray["careerArea"],
                $carreerDataArray["careerSubarea"],
                $carreerDataArray["careerDes"]
            ]
        );
    }

    public function updateCarreer(array $carreerUpdateDataArray): array
    {
        $id = $carreerUpdateDataArray['idCarreerDB'];

        try {
            $currentData = DatabaseHelper::selectOne(
                $this->connection,
                "
                    SELECT
                        nombre,
                        area,
                        subarea,
                        descripcion
                    FROM carreers
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
                "nombre" => $carreerUpdateDataArray['careerNameEdit'],
                "area" => $carreerUpdateDataArray['carreerAreaEdit'],
                "subarea" => $carreerUpdateDataArray['careerSubareaEdit'],
                "descripcion" => $carreerUpdateDataArray['careerComentsEdit'],
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
                    UPDATE carreers
                    SET
                        nombre = ?,
                        area = ?,
                        subarea = ?,
                        descripcion = ?
                    WHERE id = ?;
                ",
                "ssssi",
                [
                    $newData["nombre"],
                    $newData["area"],
                    $newData["subarea"],
                    $newData["descripcion"],
                    $id
                ]
            );

        } catch (\Exception $e) {
            return [
                "success" => false,
                "message" => "Error inesperado al actualizar los datos de la carrera.",
                "data" => null
            ];
        }
    }

    public function deleteCarreerById(int $carreerId): array
    {
        return DatabaseHelper::delete(
            $this->connection,
            "
                DELETE
                FROM carreers
                WHERE id = ?;
            ",
            "i",
            [$carreerId]
        );
    }
}
