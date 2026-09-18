<?php

namespace Vendor\Schoolarsystem\Core;

class ResponseEmitter
{

    public static function emit(Response $response): void
    {

        http_response_code(
            $response->statusCode()
        );

        foreach ($response->headers() as $name => $value) {

            header("$name: $value");

        }

        $data = $response->data();

        if (is_array($data) || is_object($data)) {

            echo json_encode($data);

            return;

        }

        echo $data;

    }

}