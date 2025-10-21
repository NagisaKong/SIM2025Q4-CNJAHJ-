<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Response;
use App\Core\Validator;
use App\Core\Csrf;
use App\Services\AccountService;
use App\Repositories\ProfileRepository;
use DomainException;

class UserController extends Controller
{
    public function __construct(
        \App\Core\Request $request,
        \App\Core\View $view,
        \App\Core\Response $response,
        \App\Core\Session $session,
        \App\Core\Auth $auth,
        private AccountService $accounts,
        private ProfileRepository $profiles,
        private Validator $validator,
        protected Csrf $csrf
    ) {
        parent::__construct($request, $view, $response, $session, $auth);
    }

    public function index(): Response
    {
        $page = (int) ($this->request->query()['page'] ?? 1);
        [$users, $total] = $this->accounts->listUsers($page, 20, ['q' => $this->request->query()['q'] ?? null]);
        return $this->render('admin/users/index.php', [
            'title' => 'User Accounts',
            'users' => $users,
            'total' => $total,
            'page' => $page,
            'csrfToken' => $this->csrf->token(),
        ]);
    }

    public function create(): Response
    {
        return $this->render('admin/users/form.php', [
            'title' => 'Create User',
            'profiles' => $this->profiles->paginate(1, 100)[0],
            'csrfToken' => $this->csrf->token(),
        ]);
    }

    public function store(): Response
    {
        $data = $this->request->post();
        if (!$this->csrf->validate($data['_token'] ?? null)) {
            $this->session->flash('error', 'Invalid security token.');
            return $this->redirect('/admin/users');
        }

        if (!$this->validator->validate($data, [
            'name' => 'required|min:3',
            'email' => 'required|email',
            'password' => 'required|min:6',
            'profile_id' => 'required',
        ])) {
            $this->session->flash('error', 'Please fill in all required fields before submitting.');
            return $this->redirect('/admin/users/create');
        }

        try {
            $this->accounts->createUser($data);
        } catch (DomainException $exception) {
            $this->session->flash('warning', $exception->getMessage());
            return $this->redirect('/admin/users/create');
        }

        $this->session->flash('success', 'User created successfully.');
        return $this->redirect('/admin/users');
    }

    public function show(int $id): Response
    {
        $user = $this->accounts->findUser($id);
        return $this->render('admin/users/show.php', [
            'title' => 'User Detail',
            'user' => $user,
        ]);
    }

    public function edit(int $id): Response
    {
        $user = $this->accounts->findUser($id);
        return $this->render('admin/users/form.php', [
            'title' => 'Edit User',
            'user' => $user,
            'profiles' => $this->profiles->paginate(1, 100)[0],
            'csrfToken' => $this->csrf->token(),
        ]);
    }

    public function update(int $id): Response
    {
        $data = $this->request->post();
        if (!$this->csrf->validate($data['_token'] ?? null)) {
            $this->session->flash('error', 'Invalid security token.');
            return $this->redirect("/admin/users/{$id}/edit");
        }

        unset($data['_token']);
        $allowedKeys = ['name', 'email', 'password', 'profile_id', 'status'];
        $payload = array_intersect_key($data, array_flip($allowedKeys));

        if (array_key_exists('password', $payload) && trim((string) $payload['password']) === '') {
            unset($payload['password']);
        }

        foreach ($payload as $key => $value) {
            if (is_string($value)) {
                $payload[$key] = trim($value);
            }

            if ($payload[$key] === '' || $payload[$key] === null) {
                unset($payload[$key]);
            }
        }

        if ($payload === []) {
            $this->session->flash('error', 'No valid data was provided for update.');
            return $this->redirect("/admin/users/{$id}/edit");
        }

        $this->accounts->updateUser($id, $payload);
        $this->session->flash('success', 'User updated.');
        return $this->redirect('/admin/users');
    }

    public function suspend(int $id): Response
    {
        $this->accounts->updateUser($id, ['status' => 'suspended']);
        $this->session->flash('success', 'User suspended.');
        return $this->redirect('/admin/users');
    }
}
