<?php
declare(strict_types=1);

require_once __DIR__ . '/../../shared/bootstrap.php';
require_once __DIR__ . '/../controller/generateDailyReportController.php';
include __DIR__ . '/../../shared/boundary/header.php';

function logout(): void
{
    session_destroy();
    header('Location: /public/index.php?route=login');
    exit();
}

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    logout();
}

$controller = new GenerateDailyReportController();
$data = $controller->generate($_POST ?: $_GET);
?>
<section class="card">
    <h1>Daily Request Report</h1>
    <form class="form-inline" method="GET" action="/public/index.php">
        <input type="hidden" name="route" value="pm/daily-report">
        <input type="date" name="date" value="<?= htmlspecialchars($data['date'] ?? date('Y-m-d'), ENT_QUOTES) ?>">
        <button type="submit" class="btn-secondary">Refresh</button>
    </form>
    <table class="table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Total Requests</th>
                <th>Open</th>
                <th>Matched</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><?= htmlspecialchars($data['date'] ?? '', ENT_QUOTES) ?></td>
                <td><?= (int) ($data['total_requests'] ?? 0) ?></td>
                <td><?= (int) ($data['open_requests'] ?? 0) ?></td>
                <td><?= (int) ($data['matched_requests'] ?? 0) ?></td>
            </tr>
        </tbody>
    </table>
</section>
<?php include __DIR__ . '/../../shared/boundary/footer.php'; ?>
