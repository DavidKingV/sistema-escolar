<?php

namespace Vendor\Schoolarsystem\Core;

class DatabaseHelper
{

    public static function selectValue(
        $connection,
        $sql,
        $column,
        $types = "",
        $params = []
    ): int {
        $query = DatabaseExecutor::execute(
            $connection,
            $sql,
            $types,
            $params
        );

        if (!$query["success"] || !$query["result"]) {
            return 0;
        }

        $data = $query["result"]->fetch_assoc();

        return (int) ($data[$column] ?? 0);
    }

    public static function selectOne(
        $connection,
        $sql,
        $types,
        $params
    ) {
        $query = DatabaseExecutor::execute(
            $connection,
            $sql,
            $types,
            $params
        );

        if (!$query["success"])
            return $query;

        $data = $query["result"]->fetch_assoc();

        if (!$data) {
            return [
                "success" => false,
                "message" => "No se encontró el registro solicitado."
            ];
        }

        return [
            "success" => true,
            "message" => "Registro encontrado.",
            "data" => $data
        ];
    }

    public static function selectAll(
        $connection,
        $sql,
        $types = "",
        $params = []
    ) {
        $query = DatabaseExecutor::execute(
            $connection,
            $sql,
            $types,
            $params
        );

        if (!$query["success"])
            return $query;

        $data = $query["result"]->fetch_all(MYSQLI_ASSOC);

        return [
            "success" => true,
            "message" => "Información obtenida correctamente.",
            "data" => $data
        ];
    }

    public static function insert(
        $connection,
        $sql,
        $types,
        $params
    ) {
        $query = DatabaseExecutor::execute(
            $connection,
            $sql,
            $types,
            $params
        );

        if (!$query["success"])
            return $query;

        if ($query["stmt"]->affected_rows === 0) {
            return [
                "success" => false,
                "message" => "No fue posible insertar el registro."
            ];
        }

        return [
            "success" => true,
            "message" => "Registro insertado correctamente.",
            "insertedId" => $query["stmt"]->insert_id
        ];
    }

    public static function update(
        $connection,
        $sql,
        $types,
        $params
    ) {
        $query = DatabaseExecutor::execute(
            $connection,
            $sql,
            $types,
            $params
        );

        if (!$query["success"])
            return $query;

        if ($query["stmt"]->affected_rows === 0) {
            return [
                "success" => false,
                "message" => "No fue posible actualizar el registro."
            ];
        }

        return [
            "success" => true,
            "message" => "Registro actualizado correctamente."
        ];
    }

    public static function delete(
        $connection,
        $sql,
        $types,
        $params
    ) {
        $query = DatabaseExecutor::execute(
            $connection,
            $sql,
            $types,
            $params
        );

        if (!$query["success"])
            return $query;

        if ($query["stmt"]->affected_rows === 0) {
            return [
                "success" => false,
                "message" => "No se encontró el registro a eliminar."
            ];
        }

        return [
            "success" => true,
            "message" => "Registro eliminado correctamente."
        ];
    }
}