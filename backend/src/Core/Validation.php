<?php

namespace Vendor\Schoolarsystem\Core;

class Validation
{
    public static function id($id): ?array
    {
        if ($id === null) {
            return [
                "success" => false,
                "message" => "ID no especificado."
            ];
        }

        if (!is_numeric($id)) {
            return [
                "success" => false,
                "message" => "El ID debe ser numérico."
            ];
        }

        if ((int) $id <= 0) {
            return [
                "success" => false,
                "message" => "ID inválido."
            ];
        }

        return null;
    }

    public static function password($password): ?array
    {
        if ($password === null || $password === "") {
            return [
                "success" => false,
                "message" => "Contraseña no especificada."
            ];
        }

        if (!is_string($password)) {
            return [
                "success" => false,
                "message" => "La contraseña debe ser una cadena de texto."
            ];
        }

        return null;
    }

    public static function string($string): ?array
    {
        if ($string === null || $string === "") {
            return [
                "success" => false,
                "message" => "Texto no especificado."
            ];
        }

        if (!is_string($string)) {
            return [
                "success" => false,
                "message" => "El texto debe ser una cadena."
            ];
        }

        return null;
    }

    public static function requiredArray(array $data): ?array
    {
        if ($data === []) {

            return [
                "success" => false,
                "message" => "No se recibieron datos."
            ];

        }

        return null;
    }

}
