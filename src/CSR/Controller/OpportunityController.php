<?php

namespace App\CSR\Controller;

use App\Core\Controller;
use App\Core\Response;
use App\Core\Csrf;
use App\Repositories\RequestRepository;
use App\Repositories\CategoryRepository;
use App\Services\AccountService;

class OpportunityController extends Controller
{
    public function __construct(
        \App\Core\Request $request,
        \App\Core\View $view,
        \App\Core\Response $response,
        \App\Core\Session $session,
        \App\Core\Auth $auth,
        private RequestRepository $requests,
        private CategoryRepository $categories,
        private AccountService $accounts,
        protected Csrf $csrf
    ) {
        parent::__construct($request, $view, $response, $session, $auth);
    }

    public function index(): Response
    {
        $filters = [
            'category_id' => $this->request->query()['category_id'] ?? null,
            'q' => $this->request->query()['q'] ?? null,
            'from' => $this->request->query()['from'] ?? null,
            'to' => $this->request->query()['to'] ?? null,
        ];
        $page = (int) ($this->request->query()['page'] ?? 1);
        [$items, $total] = $this->requests->searchForCsr($filters, $page, 20);
        return $this->render('CSR/Boundary/requests/index.php', [
            'title' => 'Volunteer Opportunities',
            'requests' => $items,
            'total' => $total,
            'page' => $page,
            'filters' => $filters,
            'categories' => $this->categories->allActive(),
            'csrfToken' => $this->csrf->token(),
        ]);
    }

    public function show(int $id): Response
    {
        $request = $this->requests->find($id);
        $this->requests->incrementViews($id);
        return $this->render('CSR/Boundary/requests/show.php', [
            'title' => 'Request Detail',
            'requestItem' => $request,
            'csrfToken' => $this->csrf->token(),
        ]);
    }

    public function shortlist(int $id): Response
    {
        if (!$this->csrf->validate($this->request->post()['_token'] ?? null)) {
            $this->session->flash('error', 'Invalid security token.');
            return $this->redirect("/csr/requests/{$id}");
        }

        $csr = $this->auth->user();
        $this->accounts->addShortlist($csr->id, $id);
        $this->session->flash('success', 'Request saved to shortlist.');
        return $this->redirect('/csr/shortlist');
    }

    public function shortlistIndex(): Response
    {
        $csr = $this->auth->user();
        $shortlist = $this->accounts->listShortlist($csr->id);
        return $this->render('CSR/Boundary/shortlist/index.php', [
            'title' => 'My Shortlist',
            'shortlist' => $shortlist,
        ]);
    }
}
