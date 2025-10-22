<?php

use App\Auth\Controller\LoginController;
use App\Auth\Controller\LogoutController;
use App\Common\Controller\DashboardController;
use App\Admin\Controller\UserController as AdminUserController;
use App\Admin\Controller\ProfileController as AdminProfileController;
use App\Admin\Controller\CategoryController;
use App\CSR\Controller\OpportunityController;
use App\CSR\Controller\HistoryController as CSRHistoryController;
use App\PIN\Controller\RequestController;
use App\PIN\Controller\HistoryController as PINHistoryController;
use App\Manager\Controller\ReportController;

return function (\App\Core\Router $router) {
    $router->get('/', [LoginController::class, 'show'])->name('login');
    $router->post('/login', [LoginController::class, 'submit']);
    $router->post('/logout', [LogoutController::class, 'logout'])->middleware(['auth', 'csrf']);

    $router->get('/dashboard', [DashboardController::class, 'index'])->middleware('auth');

    $router->group('/admin', function (\App\Core\Router $router) {
        $router->get('/users', [AdminUserController::class, 'index']);
        $router->get('/users/create', [AdminUserController::class, 'create']);
        $router->post('/users', [AdminUserController::class, 'store']);
        $router->get('/users/{id}', [AdminUserController::class, 'show']);
        $router->get('/users/{id}/edit', [AdminUserController::class, 'edit']);
        $router->post('/users/{id}', [AdminUserController::class, 'update']);
        $router->post('/users/{id}/suspend', [AdminUserController::class, 'suspend']);

        $router->get('/profiles', [AdminProfileController::class, 'index']);
        $router->get('/profiles/create', [AdminProfileController::class, 'create']);
        $router->post('/profiles', [AdminProfileController::class, 'store']);
        $router->get('/profiles/{id}', [AdminProfileController::class, 'show']);
        $router->get('/profiles/{id}/edit', [AdminProfileController::class, 'edit']);
        $router->post('/profiles/{id}', [AdminProfileController::class, 'update']);
        $router->post('/profiles/{id}/suspend', [AdminProfileController::class, 'suspend']);

        $router->resource('categories', CategoryController::class);
    }, ['auth', 'role:user_admin']);

    $router->group('/csr', function (\App\Core\Router $router) {
        $router->get('/requests', [OpportunityController::class, 'index']);
        $router->get('/requests/{id}', [OpportunityController::class, 'show']);
        $router->post('/requests/{id}/shortlist', [OpportunityController::class, 'shortlist']);
        $router->get('/shortlist', [OpportunityController::class, 'shortlistIndex']);
        $router->get('/history', [CSRHistoryController::class, 'index']);
    }, ['auth', 'role:csr_rep']);

    $router->group('/pin', function (\App\Core\Router $router) {
        $router->get('/requests', [RequestController::class, 'index']);
        $router->get('/requests/create', [RequestController::class, 'create']);
        $router->post('/requests', [RequestController::class, 'store']);
        $router->get('/requests/{id}', [RequestController::class, 'show']);
        $router->get('/requests/{id}/edit', [RequestController::class, 'edit']);
        $router->post('/requests/{id}', [RequestController::class, 'update']);
        $router->post('/requests/{id}/delete', [RequestController::class, 'destroy']);
        $router->get('/history', [PINHistoryController::class, 'index']);
    }, ['auth', 'role:pin']);

    $router->group('/reports', function (\App\Core\Router $router) {
        $router->get('/', [ReportController::class, 'index']);
        $router->post('/export', [ReportController::class, 'export']);
    }, ['auth', 'role:platform_manager']);
};
