<?php

namespace App\Admin\Controller;

use App\Core\Controller;
use App\Core\Response;
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
        private CreateProfileController $createProfileController,
        private ViewProfilesController $viewProfilesController,
        protected Csrf $csrf
    ) {
        parent::__construct($request, $view, $response, $session, $auth);
    }

    public function index(): Response
    {
        $queryParams = $this->request->query();
        $page = (int) ($queryParams['page'] ?? 1);
        $search = isset($queryParams['q']) ? trim((string) $queryParams['q']) : null;
        $filters = [
            'role' => isset($queryParams['role']) ? trim((string) $queryParams['role']) : null,
            'status' => isset($queryParams['status']) ? trim((string) $queryParams['status']) : null,
            'q' => $search === '' ? null : $search,
        ];
        [$profiles, $total] = $this->viewProfilesController->viewProfiles($filters, $page, 20);
        $profileDetails = $this->viewProfilesController->describeCollection($profiles);
        return $this->render('Admin/Boundary/profiles/index.php', [
            'title' => 'User Profiles',
            'profiles' => $profiles,
            'profileDetails' => $profileDetails,
            'total' => $total,
            'page' => $page,
            'filters' => $filters,
            'csrfToken' => $this->csrf->token(),
        ]);
    }

    public function create(): Response
    {
        return $this->render('Admin/Boundary/profiles/form.php', [
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

        $role = (string) ($data['role'] ?? '');
        $description = (string) ($data['description'] ?? '');
        $status = $data['status'] ?? 'active';

        if ($this->createProfileController->createProfile($role, $description, $status)) {
            $this->session->flash('success', 'Profile created.');
            return $this->redirect('/admin/profiles');
        }

        $message = $this->createProfileController->errorMessage() ?? 'Unable to create profile.';
        $flashType = $this->createProfileController->errorType();
        $this->session->flash($flashType, $message);
        return $this->redirect('/admin/profiles/create');
    }

    public function show(int $id): Response
    {
        $profileDetail = $this->viewProfilesController->detail($id);
        if ($profileDetail === null) {
            $this->session->flash('error', 'Profile not found.');
            return $this->redirect('/admin/profiles');
        }
        return $this->render('Admin/Boundary/profiles/show.php', [
            'title' => 'Profile Detail',
            'profile' => $profileDetail['profile'],
            'profileDetail' => $profileDetail,
        ]);
    }

    public function edit(int $id): Response
    {
        $profile = $this->profiles->find($id);
        return $this->render('Admin/Boundary/profiles/form.php', [
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

        unset($data['_token']);
        $allowedKeys = ['role', 'description', 'status'];
        $payload = array_intersect_key($data, array_flip($allowedKeys));

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
            return $this->redirect("/admin/profiles/{$id}/edit");
        }

        $this->profiles->update($id, $payload);
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
