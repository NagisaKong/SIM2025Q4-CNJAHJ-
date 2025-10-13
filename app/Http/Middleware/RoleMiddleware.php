<?php

namespace App\Http\Middleware;

use App\Core\Auth;
use App\Core\Middleware;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class RoleMiddleware extends Middleware
{
    private string $role;

    public function __construct(private Auth $auth, private Session $session)
    {
    }

    public function withArgument(string $role): self
    {
        $clone = clone $this;
        $clone->role = $role;
        return $clone;
    }

    public function handle(Request $request, callable $next): Response
    {
        if (!$this->auth->check() || !$this->auth->hasRole($this->role)) {
            $this->session->flash('error', 'You do not have permission to access this area.');
            return (new Response())->setStatus(302)->header('Location', '/dashboard');
        }

        return $next($request);
    }
}
