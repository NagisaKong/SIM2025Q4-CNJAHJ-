<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Response;
use App\Core\Validator;
use App\Core\Csrf;
use App\Repositories\ProfileRepository;

class ProfileController extends Controller
{
    public function __construct(
        \App\Core\Request $request,
        \App\Core\View $view,
        \App\Core\Response $response,
        \App\Core\Session $session,
        \App\Core\Auth $auth,
        private ProfileRepository $profiles,
        private Validator $validator,
        protected Csrf $csrf
    ) {
        parent::__construct($request, $view, $response, $session, $auth);
    }

    public function index(): Response
    {
        $page = (int) ($this->request->query()['page'] ?? 1);
        [$profiles, $total] = $this->profiles->paginate($page, 20, [
            'role' => $this->request->query()['role'] ?? null,
            'status' => $this->request->query()['status'] ?? null,
        ]);
        return $this->render('admin/profiles/index.php', [
            'title' => 'User Profiles',
            'profiles' => $profiles,
            'total' => $total,
            'page' => $page,
            'csrfToken' => $this->csrf->token(),
        ]);
    }

    public function create(): Response
    {
        return $this->render('admin/profiles/form.php', [
            'title' => 'Create Profile',
            'csrfToken' => $this->csrf->token(),
        ]);
    }

    public function store(): Response
    {
        $data = $this->request->post();
        if (!$this->csrf->validate($data['_token'] ?? null)) {
            $this->session->flash('error', 'Invalid security token.');
            return $this->redirect('/admin/profiles');
        }

        if (!$this->validator->validate($data, [
            'role' => 'required',
            'description' => 'required|min:3',
        ])) {
            $this->session->flash('error', 'Please fill in all required fields before submitting.');
            return $this->redirect('/admin/profiles/create');
        }

        $data['role'] = trim((string) $data['role']);
        if ($this->profiles->findByRole($data['role']) !== null) {
            $this->session->flash('warning', 'A profile for this role already exists.');
            return $this->redirect('/admin/profiles/create');
        }

        $this->profiles->create($data);
        $this->session->flash('success', 'Profile created.');
        return $this->redirect('/admin/profiles');
    }

    public function show(int $id): Response
    {
        $profile = $this->profiles->find($id);
        return $this->render('admin/profiles/show.php', [
            'title' => 'Profile Detail',
            'profile' => $profile,
        ]);
    }

    public function edit(int $id): Response
    {
        $profile = $this->profiles->find($id);
        return $this->render('admin/profiles/form.php', [
            'title' => 'Edit Profile',
            'profile' => $profile,
            'csrfToken' => $this->csrf->token(),
        ]);
    }

    public function update(int $id): Response
    {
        $data = $this->request->post();
        if (!$this->csrf->validate($data['_token'] ?? null)) {
            $this->session->flash('error', 'Invalid security token.');
            return $this->redirect("/admin/profiles/{$id}/edit");
        }

        $this->profiles->update($id, $data);
        $this->session->flash('success', 'Profile updated.');
        return $this->redirect('/admin/profiles');
    }

    public function suspend(int $id): Response
    {
        $this->profiles->update($id, ['status' => 'suspended']);
        $this->session->flash('success', 'Profile suspended.');
        return $this->redirect('/admin/profiles');
    }
}
