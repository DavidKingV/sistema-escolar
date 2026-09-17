<?php

namespace Vendor\Schoolarsystem\Core;

use RuntimeException;

class View
{
    public static function modal(string $modalName, array $data = []): string
    {
        if ($modalName !== basename($modalName)) {
            throw new RuntimeException('El nombre del modal no es válido.');
        }

        $data['__modalView'] = true;

        return self::render(
            dirname(__DIR__) . '/Views/modals/' . $modalName,
            $data
        );
    }

    public static function render(string $viewPath, array $data = []): string
    {
        if (!is_file($viewPath)) {
            throw new RuntimeException("La vista solicitada no existe: {$viewPath}");
        }

        extract($data, EXTR_SKIP);

        ob_start();

        try {
            require $viewPath;
            return (string) ob_get_clean();
        } catch (\Throwable $error) {
            ob_end_clean();
            throw $error;
        }
    }
}
