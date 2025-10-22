<?php

namespace App\Core;

use PDO;

class Application
{
    public function __construct(private Container $container)
    {
    }

    public function bootstrap(): void
    {
        $appConfig = require __DIR__ . '/../../config/app.php';
        $dbConfig = require __DIR__ . '/../../config/database.php';

        $this->container->set(Container::class, $this->container);
        $this->container->set(Request::class, fn() => Request::capture());
        $this->container->set(Response::class, fn() => new Response());
        $this->container->set(Session::class, fn() => new Session($appConfig['session_name']));
        $this->container->set(View::class, fn() => new View(dirname(__DIR__)));
        $this->container->set(PDO::class, function () use ($dbConfig) {
            return new PDO($dbConfig['dsn'], $dbConfig['username'], $dbConfig['password'], $dbConfig['options']);
        });
        $this->container->set(Auth::class, fn(Container $c) => new Auth($c->get(Session::class), $c->get(\App\Repositories\UserRepository::class)));
        $this->container->set(Csrf::class, fn(Container $c) => new Csrf($c->get(Session::class), $appConfig['csrf_secret']));
        $this->container->set(\App\Core\Validator::class, fn() => new \App\Core\Validator());
        $this->container->set(Router::class, fn(Container $c) => new Router($c));

        $this->container->set(\App\Repositories\UserRepository::class, fn(Container $c) => new \App\Repositories\UserRepository($c->get(PDO::class)));
        $this->container->set(\App\Repositories\ProfileRepository::class, fn(Container $c) => new \App\Repositories\ProfileRepository($c->get(PDO::class)));
        $this->container->set(\App\Repositories\CategoryRepository::class, fn(Container $c) => new \App\Repositories\CategoryRepository($c->get(PDO::class)));
        $this->container->set(\App\Repositories\RequestRepository::class, fn(Container $c) => new \App\Repositories\RequestRepository($c->get(PDO::class)));
        $this->container->set(\App\Repositories\ShortlistRepository::class, fn(Container $c) => new \App\Repositories\ShortlistRepository($c->get(PDO::class)));
        $this->container->set(\App\Repositories\MatchRepository::class, fn(Container $c) => new \App\Repositories\MatchRepository($c->get(PDO::class)));

        $this->container->set(\App\Entity\UserAccount::class, fn(Container $c) => new \App\Entity\UserAccount(
            $c->get(\App\Repositories\UserRepository::class)
        ));

        $this->container->set(\App\Services\AccountService::class, fn(Container $c) => new \App\Services\AccountService(
            $c->get(\App\Repositories\UserRepository::class),
            $c->get(\App\Repositories\CategoryRepository::class),
            $c->get(\App\Repositories\RequestRepository::class),
            $c->get(\App\Repositories\ShortlistRepository::class),
            $c->get(\App\Repositories\MatchRepository::class)
        ));
        $this->container->set(\App\Services\ReportingService::class, fn(Container $c) => new \App\Services\ReportingService(
            $c->get(\App\Repositories\MatchRepository::class)
        ));
    }

    public function handle(): void
    {
        $router = $this->container->get(Router::class);
        $routes = require __DIR__ . '/../../config/routes.php';
        $routes($router);

        $request = $this->container->get(Request::class);
        $response = $router->dispatch($request);
        $response->send();
    }
}
