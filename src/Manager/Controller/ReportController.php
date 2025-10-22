<?php

namespace App\Manager\Controller;

use App\Core\Controller;
use App\Core\Response;
use App\Core\Csrf;
use App\Services\ReportingService;

class ReportController extends Controller
{
    public function __construct(
        \App\Core\Request $request,
        \App\Core\View $view,
        \App\Core\Response $response,
        \App\Core\Session $session,
        \App\Core\Auth $auth,
        private ReportingService $reports,
        protected Csrf $csrf
    ) {
        parent::__construct($request, $view, $response, $session, $auth);
    }

    public function index(): Response
    {
        $period = $this->request->query()['period'] ?? 'daily';
        $data = $this->reports->aggregate($period);
        return $this->render('Manager/Boundary/index.php', [
            'title' => 'Platform Reports',
            'period' => $period,
            'rows' => $data,
            'csrfToken' => $this->csrf->token(),
        ]);
    }

    public function export(): Response
    {
        $data = $this->request->post();
        if (!$this->csrf->validate($data['_token'] ?? null)) {
            $this->session->flash('error', 'Invalid security token.');
            return $this->redirect('/reports');
        }

        $period = $data['period'] ?? 'daily';
        $rows = $this->reports->aggregate($period);
        $csv = $this->toCsv($rows);
        return (new Response())
            ->setStatus(200)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="report.csv"')
            ->setContent($csv);
    }

    private function toCsv(array $rows): string
    {
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, ['Period', 'Matches Created', 'Matches Completed']);
        foreach ($rows as $row) {
            fputcsv($handle, [$row['period'], $row['matches_created'], $row['matches_completed']]);
        }
        rewind($handle);
        $csv = stream_get_contents($handle) ?: '';
        fclose($handle);
        return $csv;
    }
}
