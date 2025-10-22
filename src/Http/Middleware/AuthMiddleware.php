<?php

namespace App\Http\Middleware;

use App\Core\Auth;
use App\Core\Middleware;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class AuthMiddleware extends Middleware
{
    public function __construct(private Auth $auth, private Session $session)
    {
    }

    public function handle(Request $request, callable $next): Response
    {
        if (!$this->auth->check()) {
            $this->session->flash('error', 'Please log in to continue.');
            return (new Response())->setStatus(302)->header('Location', '/');
        }

        return $next($request);
    }
}
