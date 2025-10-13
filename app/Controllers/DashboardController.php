<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $user = $this->auth->user();
        $csrf = $this->session->get('_csrf_token') ?? '';
        return $this->render('layouts/dashboard.php', [
            'title' => 'Dashboard',
            'user' => $user,
            'csrfToken' => $csrf,
        ]);
    }
}
