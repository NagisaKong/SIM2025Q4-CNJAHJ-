<?php
require_once __DIR__ . '/../controller/generateMonthlyReportController.php';

use CSRPlatform\PM\Controller\generateMonthlyReportController;
use CSRPlatform\Shared\Entity\Report;

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

$roleKey = strtolower((string) ($currentUser['role'] ?? ''));
if ($roleKey !== 'pm') {
    header('Location: /index.php?page=dashboard');
    exit();
}

$controller = new generateMonthlyReportController(new Report());
$report = $controller->generateMonthlyReport();

$pageTitle = 'Monthly report';
$navLinks = [
    ['href' => '/index.php?page=dashboard', 'label' => 'Dashboard'],
    ['href' => '/index.php?page=pm-categories', 'label' => 'Categories'],
    ['href' => '/index.php?page=pm-report-daily', 'label' => 'Daily report'],
    ['href' => '/index.php?page=pm-report-weekly', 'label' => 'Weekly report'],
    ['href' => '/index.php?page=pm-report-monthly', 'label' => 'Monthly report'],
];
include __DIR__ . '/../../shared/boundary/header.php';
?>
<section class="card">
    <div class="card-heading">
        <div>
            <h1>Monthly activity report</h1>
            <p class="muted"><?= htmlspecialchars(($report['range']['start'] ?? '') . ' to ' . ($report['range']['end'] ?? ''), ENT_QUOTES) ?></p>
        </div>
        <div class="card-actions">
            <a class="btn-secondary" href="/index.php?page=dashboard">Back to dashboard</a>
        </div>
    </div>
    <div class="metrics-grid">
        <div class="metric-card">
            <span class="metric-label">Requests created</span>
            <span class="metric-value"><?= (int) ($report['totals']['requests'] ?? 0) ?></span>
        </div>
        <div class="metric-card">
            <span class="metric-label">Completed</span>
            <span class="metric-value"><?= (int) ($report['totals']['completed'] ?? 0) ?></span>
        </div>
    </div>
    <h2>Requests by category</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Category</th>
                <th>Total requests</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($report['byCategory'] as $row): ?>
                <tr>
                    <td><?= htmlspecialchars((string) $row['name'], ENT_QUOTES) ?></td>
                    <td><?= (int) $row['total'] ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (($report['byCategory'] ?? []) === []): ?>
                <tr><td colspan="2" style="text-align:center;">No category data.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</section>
<?php include __DIR__ . '/../../shared/boundary/footer.php'; ?>
