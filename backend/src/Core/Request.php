<?php

namespace Vendor\Schoolarsystem\Core;

class Request
{
    private string $method;

    private string $uri;

    private array $query;

    private array $data;

    private array $headers;

    private array $cookies;

    private array $files;

    public function __construct()
    {
        $this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        $this->uri = parse_url(
            $_SERVER['REQUEST_URI'],
            PHP_URL_PATH
        );

        $this->query = $_GET;

        $this->data = $this->parseBody();

        $this->headers = function_exists('getallheaders')
            ? getallheaders()
            : [];

        $this->cookies = $_COOKIE;

        $this->files = $_FILES;
    }

    private function parseBody(): array
    {
        $content = file_get_contents("php://input");

        $json = json_decode($content, true);

        if (is_array($json)) {
            return $json;
        }

        return $_POST;
    }

    public function method(): string
    {
        return $this->method;
    }

    public function uri(): string
    {
        return $this->uri;
    }

    public function query(): array
    {
        return $this->query;
    }

    public function data(): array
    {
        return $this->data;
    }

    public function headers(): array
    {
        return $this->headers;
    }

    public function cookies(): array
    {
        return $this->cookies;
    }

    public function files(): array
    {
        return $this->files;
    }

    /**
     * Busca un dato tanto en GET como POST/JSON
     */
    public function input(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $this->data)) {
            return $this->data[$key];
        }

        if (array_key_exists($key, $this->query)) {
            return $this->query[$key];
        }

        return $default;
    }

    /**
     * Devuelve todos los parámetros mezclados.
     */
    public function all(): array
    {
        return array_merge(
            $this->query,
            $this->data
        );
    }

    public function has(string $key): bool
    {
        return $this->input($key) !== null;
    }

    public function only(array $keys): array
    {
        $data = [];

        foreach ($keys as $key) {
            if ($this->has($key)) {
                $data[$key] = $this->input($key);
            }
        }

        return $data;
    }

    public function except(array $keys): array
    {
        return array_diff_key(
            $this->all(),
            array_flip($keys)
        );
    }

}