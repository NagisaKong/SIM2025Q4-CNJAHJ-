<?php

namespace App\Auth\Controller;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Core\View;
use App\Entity\UserAccount;

class LoginController extends Controller
{
    private array $roleOptions = [];
    private Validator $validator;
    protected Csrf $csrf;
    private UserAccount $userAccount;

    public function __construct(
        Request $request,
        View $view,
        Response $response,
        Session $session,
        Auth $auth,
        Validator $validator,
        Csrf $csrf,
        UserAccount $userAccount
    ) {
        parent::__construct($request, $view, $response, $session, $auth);
        $this->validator = $validator;
        $this->csrf = $csrf;
        $this->userAccount = $userAccount;
    }

    public function show(): Response
    {
        return $this->render('Auth/Boundary/login.php', [
            'title' => 'Sign in',
            'csrfToken' => $this->csrf->token(),
            'roleOptions' => $this->getRoleOptions(),
            'selectedRole' => $this->session->getFlash('login_role', ''),
            'emailValue' => $this->session->getFlash('login_email', ''),
        ]);
    }

    public function submit(): Response
    {
        $data = $this->request->post();
        $role = $data['role'] ?? '';
        $email = strtolower(trim((string) ($data['email'] ?? '')));
        $password = $data['password'] ?? '';

        $rememberState = function () use ($role, $email): void {
            $this->session->flash('login_role', $role);
            $this->session->flash('login_email', $email);
        };

        $data['email'] = $email;

        if (!$this->validator->validate($data, [
            'email' => 'required|email',
            'password' => 'required|min:6',
        ])) {
            $rememberState();
            $this->session->flash('error', 'Please provide a valid email and password.');
            return $this->redirect('/');
        }

        if (!$this->userAccount->validatePassword($password)) {
            $rememberState();
            $this->session->flash('error', 'Password must be 8-20 characters long and include letters and numbers.');
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

        if ($this->validateLogin($email, $password, $role)) {
            $user = $this->userAccount->authenticatedUser();
            if ($user !== null) {
                $this->auth->loginUser($user);
            }
            $this->session->flash('success', 'Welcome back!');
            return $this->redirect('/dashboard');
        }

        $rememberState();
        $this->session->flash('error', 'Invalid credentials or suspended account.');
        return $this->redirect('/');
    }

    protected function validateLogin(string $email, string $password, string $role): bool
    {
        return $this->userAccount->validateUser($email, $password, $role);
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
