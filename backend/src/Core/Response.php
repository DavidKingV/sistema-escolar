<?php

namespace Vendor\Schoolarsystem\Core;

class Response
{
    private mixed $data = null;

    private int $statusCode = 200;

    private array $headers = [];

    public function __construct(
        mixed $data = null,
        int $statusCode = 200
    ) {
        $this->data = $data;
        $this->statusCode = $statusCode;
    }

    public static function json(
        mixed $data,
        int $statusCode = 200
    ): self {

        $response = new self($data, $statusCode);

        $response->header(
            "Content-Type",
            "application/json"
        );

        return $response;
    }

    public static function text(
        string $text,
        int $statusCode = 200
    ): self {

        $response = new self($text, $statusCode);

        $response->header(
            "Content-Type",
            "text/plain"
        );

        return $response;
    }

    public static function html(
        string $html,
        int $statusCode = 200
    ): self {

        $response = new self($html, $statusCode);

        $response->header(
            "Content-Type",
            "text/html"
        );

        return $response;
    }

    public function header(
        string $name,
        string $value
    ): self {

        $this->headers[$name] = $value;

        return $this;
    }

    public function status(
        int $statusCode
    ): self {

        $this->statusCode = $statusCode;

        return $this;
    }

    public function data(): mixed
    {
        return $this->data;
    }

    public function headers(): array
    {
        return $this->headers;
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }

}