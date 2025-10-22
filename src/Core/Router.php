<?php

namespace App\Core;

use Closure;
use RuntimeException;

class Router
{
    private array $routes = [];
    private array $groupStack = [];
    private array $namedRoutes = [];
    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function get(string $uri, array $action): Route
    {
        return $this->addRoute('GET', $uri, $action);
    }

    public function post(string $uri, array $action): Route
    {
        return $this->addRoute('POST', $uri, $action);
    }

    public function group(string $prefix, Closure $callback, array $middleware = []): RouteGroup
    {
        $group = new RouteGroup($prefix);
        if ($middleware) {
            $group->middleware($middleware);
        }
        $this->groupStack[] = $group;
        $callback($this);
        array_pop($this->groupStack);
        return $group;
    }

    public function resource(string $name, string $controller): void
    {
        $this->get("/{$name}", [$controller, 'index']);
        $this->get("/{$name}/create", [$controller, 'create']);
        $this->post("/{$name}", [$controller, 'store']);
        $this->get("/{$name}/{id}", [$controller, 'show']);
        $this->get("/{$name}/{id}/edit", [$controller, 'edit']);
        $this->post("/{$name}/{id}", [$controller, 'update']);
        $this->post("/{$name}/{id}/delete", [$controller, 'destroy']);
    }

    public function dispatch(Request $request): Response
    {
        $method = $request->method();
        $path = $request->path();

        foreach ($this->routes[$method] ?? [] as $route) {
            $params = [];
            if ($route->matches($path, $params)) {
                return $this->runRoute($route, $request, $params);
            }
        }

        $response = new Response();
        return $response->setStatus(404)->setContent('Not Found');
    }

    public function nameRoute(string $name, Route $route): void
    {
        $this->namedRoutes[$name] = $route;
    }

    private function addRoute(string $method, string $uri, array $action): Route
    {
        $prefix = $this->currentGroupPrefix();
        $uri = $this->normalizeUri($prefix . $uri);
        $route = new Route($method, $uri, $action);

        if ($group = $this->currentGroup()) {
            $route->middleware($group->getMiddleware());
        }

        $this->routes[$method][] = $route;
        return $route;
    }

    private function normalizeUri(string $uri): string
    {
        $uri = '/' . trim($uri, '/');
        return $uri === '/' ? $uri : rtrim($uri, '/');
    }

    private function currentGroupPrefix(): string
    {
        if (!$this->groupStack) {
            return '';
        }

        return implode('', array_map(fn(RouteGroup $group) => $group->getPrefix(), $this->groupStack));
    }

    private function currentGroup(): ?RouteGroup
    {
        return $this->groupStack ? end($this->groupStack) : null;
    }

    private function runRoute(Route $route, Request $request, array $params): Response
    {
        $handler = function (Request $request) use ($route, $params) {
            [$controllerClass, $method] = $route->getAction();
            $controller = $this->container->get($controllerClass);
            return $controller->$method(...$params);
        };

        $middlewareStack = [];
        foreach ($route->getMiddleware() as $middleware) {
            $middlewareStack[] = $this->resolveMiddleware($middleware);
        }

        $pipeline = array_reduce(
            array_reverse($middlewareStack),
            fn(callable $next, Middleware $middleware) => fn(Request $req) => $middleware->handle($req, $next),
            $handler
        );

        $response = $pipeline($request);
        if (!$response instanceof Response) {
            throw new RuntimeException('Route handler must return a Response instance.');
        }

        return $response;
    }

    private function resolveMiddleware(string|callable $middleware): Middleware
    {
        if (is_string($middleware)) {
            if (str_contains($middleware, ':')) {
                [$name, $argument] = explode(':', $middleware, 2);
                $instance = $this->container->get($this->middlewareAlias($name));
                if (method_exists($instance, 'withArgument')) {
                    $instance = $instance->withArgument($argument);
                } elseif (property_exists($instance, 'argument')) {
                    $instance->argument = $argument;
                }
                return $instance;
            }

            return $this->container->get($this->middlewareAlias($middleware));
        }

        if (is_callable($middleware)) {
            return new class($middleware) extends Middleware {
                public function __construct(private $callable) {}
                public function handle(Request $request, callable $next): Response
                {
                    return ($this->callable)($request, $next);
                }
            };
        }

        throw new RuntimeException('Invalid middleware.');
    }

    private function middlewareAlias(string $alias): string
    {
        return match ($alias) {
            'auth' => \App\Http\Middleware\AuthMiddleware::class,
            'csrf' => \App\Http\Middleware\CsrfMiddleware::class,
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            default => $alias,
        };
    }
}

class Route
{
    private array $middleware = [];

    public function __construct(private string $method, private string $uri, private array $action)
    {
    }

    public function matches(string $path, ?array &$params = []): bool
    {
        $pattern = preg_replace('#\{[^/]+\}#', '([^/]+)', $this->uri);
        $pattern = '#^' . $pattern . '$#';
        if (preg_match($pattern, $path, $matches)) {
            array_shift($matches);
            $params = $matches;
            return true;
        }
        return false;
    }

    public function getAction(): array
    {
        return $this->action;
    }

    public function middleware(array|string $middleware): self
    {
        $middleware = is_array($middleware) ? $middleware : [$middleware];
        $this->middleware = array_merge($this->middleware, $middleware);
        return $this;
    }

    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    public function name(string $name): self
    {
        return $this;
    }
}

class RouteGroup
{
    private array $middleware = [];

    public function __construct(private string $prefix)
    {
    }

    public function middleware(array|string $middleware): self
    {
        $this->middleware = array_merge(
            $this->middleware,
            is_array($middleware) ? $middleware : [$middleware]
        );
        return $this;
    }

    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }
}
