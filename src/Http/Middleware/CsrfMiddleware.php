<?php

namespace App\Http\Middleware;

use App\Core\Csrf;
use App\Core\Middleware;
use App\Core\Request;
use App\Core\Response;

class CsrfMiddleware extends Middleware
{
    public function __construct(private Csrf $csrf)
    {
    }

    public function handle(Request $request, callable $next): Response
    {
        if ($request->isPost()) {
            $token = $request->post()['_token'] ?? null;
            if (!$this->csrf->validate($token)) {
                return (new Response())->setStatus(419)->setContent('Invalid CSRF token.');
            }
        }

        return $next($request);
    }
}
