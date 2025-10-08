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
    private array $roleOptions = [];

    public function __construct(
        Request $request,
        \App\Core\View $view,
        Response $response,
        Session $session,
        Auth $auth,
        private Validator $validator,
        protected Csrf $csrf
    ) {
        parent::__construct($request, $view, $response, $session, $auth);
    }

    public function showLoginForm(): Response
    {
        return $this->render('auth/login.php', [
            'title' => 'Sign in',
            'csrfToken' => $this->csrf->token(),
            'roleOptions' => $this->getRoleOptions(),
            'selectedRole' => $this->session->getFlash('login_role', ''),
            'emailValue' => $this->session->getFlash('login_email', ''),
        ]);
    }

    public function login(): Response
    {
        $data = $this->request->post();
        $role = $data['role'] ?? '';
        $email = $data['email'] ?? '';
        $rememberState = function () use ($role, $email): void {
            $this->session->flash('login_role', $role);
            $this->session->flash('login_email', $email);
        };

        if (!$this->validator->validate($data, [
            'email' => 'required|email',
            'password' => 'required|min:6',
        ])) {
            $rememberState();
            $this->session->flash('error', 'Please provide a valid email and password.');
            return $this->redirect('/');
        }

        if ($role === '' || !array_key_exists($role, $this->getRoleOptions())) {
            $rememberState();
            $this->session->flash('error', 'Please choose a valid account type.');
            return $this->redirect('/');
        }

        if (!$this->csrf->validate($data['_token'] ?? null)) {
            $rememberState();
            $this->session->flash('error', 'Invalid security token.');
            return $this->redirect('/');
        }

        if ($this->auth->attempt($email, $data['password'], $role)) {
            $this->session->flash('success', 'Welcome back!');
            return $this->redirect('/dashboard');
        }

        $rememberState();
        $this->session->flash('error', 'Invalid credentials or suspended account.');
        return $this->redirect('/');
    }

    public function logout(): Response
    {
        $this->auth->logout();
        $this->session->flash('success', 'You have been signed out.');
        return $this->redirect('/');
    }

    /**
     * @return array<string, string>
     */
    private function getRoleOptions(): array
    {
        if ($this->roleOptions === []) {
            $configPath = dirname(__DIR__, 2) . '/config/roles.php';
            $roles = require $configPath;
            foreach ($roles as $key => $meta) {
                $this->roleOptions[$key] = $meta['name'] ?? ucfirst(str_replace('_', ' ', (string) $key));
            }
        }

        return $this->roleOptions;
    }
}
