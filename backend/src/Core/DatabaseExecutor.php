<?php

namespace Vendor\Schoolarsystem\Core;
use mysqli;

class DatabaseExecutor
{
    public static function execute(
        mysqli $connection,
        string $sql,
        string $types = "",
        array $params = []
    ): array {
        $stmt = null;

        try {

            $stmt = $connection->prepare($sql);

            if (!$stmt) {
                return [
                    "success" => false,
                    "message" => "Error al preparar la consulta."
                ];
            }


            if ($params && !$stmt->bind_param($types, ...$params)) {
                return [
                    "success" => false,
                    "message" => "Error al asociar parámetros."
                ];
            }


            if (!$stmt->execute()) {
                return [
                    "success" => false,
                    "message" => "Error al ejecutar la consulta."
                ];
            }


            return [
                "success" => true,
                "stmt" => $stmt,
                "result" => $stmt->get_result(),
                "affectedRows" => $stmt->affected_rows,
                "insertId" => $stmt->insert_id
            ];


        } catch (\Exception $e) {

            return [
                "success" => false,
                "message" => "Error inesperado en la base de datos. $e"
            ];

        }
    }
}