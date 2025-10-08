<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Response;
use App\Core\Validator;
use App\Core\Csrf;
use App\Repositories\CategoryRepository;

class CategoryController extends Controller
{
    public function __construct(
        \App\Core\Request $request,
        \App\Core\View $view,
        \App\Core\Response $response,
        \App\Core\Session $session,
        \App\Core\Auth $auth,
        private CategoryRepository $categories,
        private Validator $validator,
        private Csrf $csrf
    ) {
        parent::__construct($request, $view, $response, $session, $auth);
    }

    public function index(): Response
    {
        $page = (int) ($this->request->query()['page'] ?? 1);
        [$categories, $total] = $this->categories->paginate($page, 20, [
            'status' => $this->request->query()['status'] ?? null,
            'q' => $this->request->query()['q'] ?? null,
        ]);
        return $this->render('admin/categories/index.php', [
            'title' => 'Service Categories',
            'categories' => $categories,
            'total' => $total,
            'page' => $page,
            'csrfToken' => $this->csrf->token(),
        ]);
    }

    public function create(): Response
    {
        return $this->render('admin/categories/form.php', [
            'title' => 'Create Category',
            'csrfToken' => $this->csrf->token(),
        ]);
    }

    public function store(): Response
    {
        $data = $this->request->post();
        if (!$this->csrf->validate($data['_token'] ?? null)) {
            $this->session->flash('error', 'Invalid security token.');
            return $this->redirect('/admin/categories');
        }

        if (!$this->validator->validate($data, [
            'name' => 'required|min:3',
        ])) {
            $this->session->flash('error', 'Please provide a category name.');
            return $this->redirect('/admin/categories/create');
        }

        $this->categories->create($data);
        $this->session->flash('success', 'Category created.');
        return $this->redirect('/admin/categories');
    }

    public function show(int $id): Response
    {
        $category = $this->categories->find($id);
        return $this->render('admin/categories/show.php', [
            'title' => 'Category Detail',
            'category' => $category,
        ]);
    }

    public function edit(int $id): Response
    {
        $category = $this->categories->find($id);
        return $this->render('admin/categories/form.php', [
            'title' => 'Edit Category',
            'category' => $category,
            'csrfToken' => $this->csrf->token(),
        ]);
    }

    public function update(int $id): Response
    {
        $data = $this->request->post();
        if (!$this->csrf->validate($data['_token'] ?? null)) {
            $this->session->flash('error', 'Invalid security token.');
            return $this->redirect("/admin/categories/{$id}/edit");
        }

        $this->categories->update($id, $data);
        $this->session->flash('success', 'Category updated.');
        return $this->redirect('/admin/categories');
    }

    public function destroy(int $id): Response
    {
        $this->categories->update($id, ['status' => 'suspended']);
        $this->session->flash('success', 'Category suspended.');
        return $this->redirect('/admin/categories');
    }
}
