<?php
namespace Vendor\Schoolarsystem\Helpers;

/**
 * Genera una contraseña aleatoria.
 *
 * @param int $length Longitud deseada para la contraseña. Por defecto es 12 caracteres.
 * @return string La contraseña generada.
 */
class RandomPasswords {
    public static function generateRandomPassword($length = 12) {
        // Conjunto de caracteres a utilizar en la contraseña
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomPassword = '';

        // Se generan los caracteres aleatorios uno a uno
        for ($i = 0; $i < $length; $i++) {
            $index = random_int(0, $charactersLength - 1);
            $randomPassword .= $characters[$index];
        }

        return $randomPassword;
    }
}
    

?>