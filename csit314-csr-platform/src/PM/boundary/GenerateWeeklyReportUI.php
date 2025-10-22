<?php
declare(strict_types=1);

require_once __DIR__ . '/../../shared/bootstrap.php';
require_once __DIR__ . '/../controller/generateWeeklyReportController.php';
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

$controller = new GenerateWeeklyReportController();
$data = $controller->generate($_POST ?: $_GET);
?>
<section class="card">
    <h1>Weekly Request Report</h1>
    <form class="form-inline" method="GET" action="/public/index.php">
        <input type="hidden" name="route" value="pm/weekly-report">
        <input type="date" name="week_start" value="<?= htmlspecialchars($data['week_start'] ?? date('Y-m-d'), ENT_QUOTES) ?>">
        <input type="date" name="week_end" value="<?= htmlspecialchars($data['week_end'] ?? date('Y-m-d'), ENT_QUOTES) ?>">
        <button type="submit" class="btn-secondary">Refresh</button>
    </form>
    <table class="table">
        <thead>
            <tr>
                <th>Week Start</th>
                <th>Week End</th>
                <th>Open</th>
                <th>Matched</th>
                <th>Completed</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><?= htmlspecialchars($data['week_start'] ?? '', ENT_QUOTES) ?></td>
                <td><?= htmlspecialchars($data['week_end'] ?? '', ENT_QUOTES) ?></td>
                <td><?= (int) ($data['open_requests'] ?? 0) ?></td>
                <td><?= (int) ($data['matched_requests'] ?? 0) ?></td>
                <td><?= (int) ($data['completed_requests'] ?? 0) ?></td>
            </tr>
        </tbody>
    </table>
</section>
<?php include __DIR__ . '/../../shared/boundary/footer.php'; ?>
