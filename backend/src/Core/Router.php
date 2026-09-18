<?php

namespace Vendor\Schoolarsystem\Core;

use ReflectionMethod;

class Router
{
    private array $routes = [];

    public function get(
        string $uri,
        callable|array $action,
        array $middleware = []
    ): void {

        $this->routes['GET'][$uri] = [
            'action' => $action,
            'middleware' => $middleware
        ];

    }

    public function post(
        string $uri,
        callable|array $action,
        array $middleware = []
    ): void {

        $this->routes['POST'][$uri] = [
            'action' => $action,
            'middleware' => $middleware
        ];

    }

    public function dispatch(string $httpMethod, string $uri): void
    {
        if (!isset($this->routes[$httpMethod][$uri])) {

            ResponseEmitter::emit(
                Response::text("404 - Route Not Found", 404)
            );

            return;
        }

        $route = $this->routes[$httpMethod][$uri];

        $request = new Request();

        foreach ($route['middleware'] as $middleware) {

            $instance = new $middleware();

            $response = $instance->handle($request);

            if ($response instanceof Response) {

                ResponseEmitter::emit($response);

                return;

            }

        }

        $action = $route['action'];

        if (is_callable($action)) {

            $result = $action();

        } else {

            [$controller, $controllerMethod] = $action;

            $instance = new $controller();

            $reflection = new ReflectionMethod(
                $instance,
                $controllerMethod
            );

            $arguments = [];

            $parameters = $reflection->getParameters();

            if (
                count($parameters) === 1 &&
                $parameters[0]->getType()?->getName() === 'array'
            ) {

                $arguments[] = $request->data();

            } else {

                foreach ($parameters as $parameter) {

                    $arguments[] = $request->input(
                        $parameter->getName()
                    );

                }

            }

            $result = $reflection->invokeArgs(
                $instance,
                $arguments
            );

        }

        if (!$result instanceof Response) {

            if (is_array($result) || is_object($result)) {

                $result = Response::json($result);

            } else {

                $result = Response::text(
                    (string) $result
                );

            }

        }

        ResponseEmitter::emit($result);
    }
}