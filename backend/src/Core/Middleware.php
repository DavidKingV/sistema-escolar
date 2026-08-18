<?php

namespace Vendor\Schoolarsystem\Core;

interface Middleware
{
    public function handle(Request $request): ?Response;
}