<?php
require_once __DIR__ . '/../controller/generateDailyReportController.php';

use CSRPlatform\PM\Controller\generateDailyReportController;

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function logout(): void
{
    $_SESSION = [];
    session_destroy();
    header('Location: /index.php?page=login');
    exit();
}

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    logout();
}

$currentUser = $_SESSION['user'] ?? null;
if (!$currentUser) {
    header('Location: /index.php?page=login');
    exit();
}

$controller = new generateDailyReportController();
$report = $controller->generate();

$pageTitle = 'Daily report';
$navLinks = [
    ['href' => '/index.php?page=dashboard', 'label' => 'Dashboard'],
    ['href' => '/index.php?page=pm-categories', 'label' => 'Categories'],
    ['href' => '/index.php?page=pm-report-daily', 'label' => 'Daily report'],
    ['href' => '/index.php?page=pm-report-weekly', 'label' => 'Weekly report'],
];
include __DIR__ . '/../../shared/boundary/header.php';
?>
<section class="card">
    <div class="card-header">
        <h1>Daily activity report</h1>
        <p>Summary of requests created today.</p>
    </div>
    <h2>Status summary</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Status</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($report['summary'] as $row): ?>
                <tr>
                    <td><?= htmlspecialchars((string) $row['status'], ENT_QUOTES) ?></td>
                    <td><?= (int) $row['total'] ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (($report['summary'] ?? []) === []): ?>
                <tr><td colspan="2" style="text-align:center;">No requests created today.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    <h2>Categories</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Category</th>
                <th>Total requests</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($report['categories'] as $row): ?>
                <tr>
                    <td><?= htmlspecialchars((string) $row['name'], ENT_QUOTES) ?></td>
                    <td><?= (int) $row['total'] ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (($report['categories'] ?? []) === []): ?>
                <tr><td colspan="2" style="text-align:center;">No category data.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</section>
<?php include __DIR__ . '/../../shared/boundary/footer.php'; ?>
