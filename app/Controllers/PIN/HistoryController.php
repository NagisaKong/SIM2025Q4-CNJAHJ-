<?php

namespace App\Controllers\PIN;

use App\Core\Controller;
use App\Core\Response;
use App\Services\AccountService;

class HistoryController extends Controller
{
    public function __construct(
        \App\Core\Request $request,
        \App\Core\View $view,
        \App\Core\Response $response,
        \App\Core\Session $session,
        \App\Core\Auth $auth,
        private AccountService $accounts
    ) {
        parent::__construct($request, $view, $response, $session, $auth);
    }

    public function index(): Response
    {
        $filters = [
            'status' => $this->request->query()['status'] ?? null,
            'from' => $this->request->query()['from'] ?? null,
            'to' => $this->request->query()['to'] ?? null,
        ];
        $pin = $this->auth->user();
        $history = $this->accounts->listMatchesForPin($pin->id, $filters);
        return $this->render('pin/history/index.php', [
            'title' => 'Completed Matches',
            'history' => $history,
            'filters' => $filters,
        ]);
    }
}
