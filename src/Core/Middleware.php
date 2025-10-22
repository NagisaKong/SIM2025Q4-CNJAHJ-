<?php

namespace App\Core;

abstract class Middleware
{
    abstract public function handle(Request $request, callable $next): Response;
}
