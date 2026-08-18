<?php

namespace Vendor\Schoolarsystem\Core\Middleware;

use Vendor\Schoolarsystem\auth;
use Vendor\Schoolarsystem\Core\Middleware;
use Vendor\Schoolarsystem\Core\Request;
use Vendor\Schoolarsystem\Core\Response;

class AuthMiddleware implements Middleware
{
    public function handle(Request $request): ?Response
    {
        $verify = auth::check();

        if (!$verify['success']) {

            return Response::json([
                "success" => false,
                "message" => "No se ha iniciado sesión o la sesión ha expirado."
            ], 401);

        }

        return null;
    }
}