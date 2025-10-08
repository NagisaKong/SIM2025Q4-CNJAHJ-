<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;

class AuthController extends Controller
{
    public function __construct(
        Request $request,
        \App\Core\View $view,
        Response $response,
        Session $session,
        Auth $auth,
        private Validator $validator,
        private Csrf $csrf
    ) {
        parent::__construct($request, $view, $response, $session, $auth);
    }

    public function showLoginForm(): Response
    {
        return $this->render('auth/login.php', [
            'title' => 'Sign in',
            'csrfToken' => $this->csrf->token(),
        ]);
    }

    public function login(): Response
    {
        $data = $this->request->post();
        if (!$this->validator->validate($data, [
            'email' => 'required|email',
            'password' => 'required|min:6',
        ])) {
            $this->session->flash('error', 'Please provide a valid email and password.');
            return $this->redirect('/');
        }

        if (!$this->csrf->validate($data['_token'] ?? null)) {
            $this->session->flash('error', 'Invalid security token.');
            return $this->redirect('/');
        }

        if ($this->auth->attempt($data['email'], $data['password'])) {
            $this->session->flash('success', 'Welcome back!');
            return $this->redirect('/dashboard');
        }

        $this->session->flash('error', 'Invalid credentials or suspended account.');
        return $this->redirect('/');
    }

    public function logout(): Response
    {
        $this->auth->logout();
        $this->session->flash('success', 'You have been signed out.');
        return $this->redirect('/');
    }
}
