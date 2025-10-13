<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;

class LogoutController extends Controller
{
    protected Csrf $csrf;

    public function __construct(
        Request $request,
        View $view,
        Response $response,
        Session $session,
        Auth $auth,
        Csrf $csrf
    ) {
        parent::__construct($request, $view, $response, $session, $auth);
        $this->csrf = $csrf;
    }

    public function handle(): Response
    {
        $data = $this->request->post();
        if (!$this->csrf->validate($data['_token'] ?? null)) {
            $this->session->flash('error', 'Security token mismatch. Please try again.');
            return $this->redirect('/dashboard');
        }

        $this->auth->logout();
        $this->session->flash('success', 'You have been signed out.');
        return $this->redirect('/');
    }
}
