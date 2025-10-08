<?php

namespace App\Controllers\PIN;

use App\Core\Controller;
use App\Core\Response;
use App\Core\Validator;
use App\Core\Csrf;
use App\Repositories\RequestRepository;
use App\Repositories\CategoryRepository;

class RequestController extends Controller
{
    public function __construct(
        \App\Core\Request $request,
        \App\Core\View $view,
        \App\Core\Response $response,
        \App\Core\Session $session,
        \App\Core\Auth $auth,
        private RequestRepository $requests,
        private CategoryRepository $categories,
        private Validator $validator,
        protected Csrf $csrf
    ) {
        parent::__construct($request, $view, $response, $session, $auth);
    }

    public function index(): Response
    {
        $pin = $this->auth->user();
        $filters = [
            'status' => $this->request->query()['status'] ?? null,
            'q' => $this->request->query()['q'] ?? null,
        ];
        $page = (int) ($this->request->query()['page'] ?? 1);
        [$requests, $total] = $this->requests->paginateForPin($pin->id, $page, 20, $filters);
        return $this->render('pin/requests/index.php', [
            'title' => 'My Requests',
            'requests' => $requests,
            'total' => $total,
            'page' => $page,
            'categories' => $this->categories->allActive(),
            'csrfToken' => $this->csrf->token(),
        ]);
    }

    public function create(): Response
    {
        return $this->render('pin/requests/form.php', [
            'title' => 'Create Request',
            'categories' => $this->categories->allActive(),
            'csrfToken' => $this->csrf->token(),
        ]);
    }

    public function store(): Response
    {
        $data = $this->request->post();
        if (!$this->csrf->validate($data['_token'] ?? null)) {
            $this->session->flash('error', 'Invalid security token.');
            return $this->redirect('/pin/requests');
        }

        if (!$this->validator->validate($data, [
            'title' => 'required|min:3',
            'description' => 'required|min:10',
            'location' => 'required',
            'requested_date' => 'required',
            'category_id' => 'required',
        ])) {
            $this->session->flash('error', 'Please correct the highlighted fields.');
            return $this->redirect('/pin/requests/create');
        }

        $data['pin_id'] = $this->auth->user()->id;
        $this->requests->create($data);
        $this->session->flash('success', 'Request submitted.');
        return $this->redirect('/pin/requests');
    }

    public function show(int $id): Response
    {
        $request = $this->requests->find($id);
        return $this->render('pin/requests/show.php', [
            'title' => 'Request Detail',
            'requestItem' => $request,
        ]);
    }

    public function edit(int $id): Response
    {
        $requestItem = $this->requests->find($id);
        return $this->render('pin/requests/form.php', [
            'title' => 'Edit Request',
            'requestItem' => $requestItem,
            'categories' => $this->categories->allActive(),
            'csrfToken' => $this->csrf->token(),
        ]);
    }

    public function update(int $id): Response
    {
        $data = $this->request->post();
        if (!$this->csrf->validate($data['_token'] ?? null)) {
            $this->session->flash('error', 'Invalid security token.');
            return $this->redirect("/pin/requests/{$id}/edit");
        }

        $this->requests->update($id, $data);
        $this->session->flash('success', 'Request updated.');
        return $this->redirect('/pin/requests');
    }

    public function destroy(int $id): Response
    {
        if (!$this->csrf->validate($this->request->post()['_token'] ?? null)) {
            $this->session->flash('error', 'Invalid security token.');
            return $this->redirect('/pin/requests');
        }

        $this->requests->update($id, ['status' => 'cancelled']);
        $this->session->flash('success', 'Request cancelled.');
        return $this->redirect('/pin/requests');
    }
}
