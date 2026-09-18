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

        if (is_string($id)) {
            $id = trim($id);
        }

        if ($id === '') {
            return [
                "success" => false,
                "message" => "ID no especificado."
            ];
        }

        if (!is_int($id) && !is_string($id)) {
            return [
                "success" => false,
                "message" => "El ID debe ser un número entero."
            ];
        }

        if (
            filter_var(
                $id,
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 1]]
            ) === false
        ) {
            if (preg_match('/^-?\d+$/D', (string) $id) === 1 && (float) $id <= 0) {
                return [
                    "success" => false,
                    "message" => "ID inválido."
                ];
            }

            return [
                "success" => false,
                "message" => "El ID debe ser un número entero positivo."
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
