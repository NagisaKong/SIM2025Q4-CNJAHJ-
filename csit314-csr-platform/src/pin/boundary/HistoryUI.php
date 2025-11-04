<?php
require_once __DIR__ . '/../controller/viewPINHistoryController.php';
require_once __DIR__ . '/../controller/searchPINHistoryController.php';
require_once __DIR__ . '/../../shared/entity/serviceCategories.php';

use CSRPlatform\PIN\Controller\searchPINHistoryController;
use CSRPlatform\PIN\Controller\viewPINHistoryController;
use CSRPlatform\Shared\Entity\Request;
use CSRPlatform\Shared\Entity\ServiceCategories;

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
if (!$currentUser || strtolower((string) ($currentUser['role'] ?? '')) !== 'pin') {
    header('Location: /index.php?page=login');
    exit();
}

$requestEntity = new Request();
$viewController = new viewPINHistoryController($requestEntity);
$searchController = new searchPINHistoryController($requestEntity);
$categoriesEntity = new ServiceCategories();

$searchQuery = $_GET['q'] ?? '';
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';
$serviceFilter = isset($_GET['service']) && $_GET['service'] !== '' ? (int) $_GET['service'] : null;

if ($searchQuery !== '' || $startDate !== '' || $endDate !== '' || $serviceFilter !== null) {
    $history = $searchController->searchPINHistory(
        (int) $currentUser['id'],
        $searchQuery,
        $startDate !== '' ? $startDate . ' 00:00:00' : null,
        $endDate !== '' ? $endDate . ' 23:59:59' : null,
        $serviceFilter
    );
} else {
    $history = $viewController->viewPINHistory((int) $currentUser['id']);
}

$categories = $categoriesEntity->listCategories('active');

$pageTitle = 'Request History';
$navLinks = [
    ['href' => '/index.php?page=dashboard', 'label' => 'Dashboard'],
    ['href' => '/index.php?page=pin-requests', 'label' => 'My Requests'],
    ['href' => '/index.php?page=pin-history', 'label' => 'History'],
];
include __DIR__ . '/../../shared/boundary/header.php';
?>
<section class="card">
    <div class="card-header">
        <div>
            <h1>Request history</h1>
            <p>Search through your completed and in-progress requests.</p>
        </div>
    </div>
    <form method="GET" action="/index.php" class="form-grid">
        <input type="hidden" name="page" value="pin-history">
        <label>Keyword
            <input type="search" name="q" value="<?= htmlspecialchars((string) $searchQuery, ENT_QUOTES) ?>" placeholder="Search title, category, or status">
        </label>
        <label>Service category
            <select name="service">
                <option value="">All services</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= (int) $category['id'] ?>" <?= $serviceFilter === (int) $category['id'] ? 'selected' : '' ?>><?= htmlspecialchars((string) $category['name'], ENT_QUOTES) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Start date
            <input type="date" name="start_date" value="<?= htmlspecialchars((string) $startDate, ENT_QUOTES) ?>">
        </label>
        <label>End date
            <input type="date" name="end_date" value="<?= htmlspecialchars((string) $endDate, ENT_QUOTES) ?>">
        </label>
        <div class="form-inline">
            <button type="submit" class="btn-primary">Search history</button>
            <?php if ($searchQuery !== '' || $startDate !== '' || $endDate !== '' || $serviceFilter !== null): ?>
                <a href="/index.php?page=pin-history" class="btn-secondary">Reset</a>
            <?php endif; ?>
        </div>
    </form>
    <table class="table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Category</th>
                <th>Status</th>
                <th>Last updated</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($history as $row): ?>
                <tr>
                    <td><?= htmlspecialchars((string) $row['title'], ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars((string) ($row['category_name'] ?? ''), ENT_QUOTES) ?></td>
                    <td><span class="tag tag-<?= htmlspecialchars((string) $row['status'], ENT_QUOTES) ?>"><?= htmlspecialchars((string) $row['status'], ENT_QUOTES) ?></span></td>
                    <td><?= htmlspecialchars((string) $row['updated_at'], ENT_QUOTES) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if ($history === []): ?>
                <tr>
                    <td colspan="4" style="text-align: center;">No history records found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</section>
<?php include __DIR__ . '/../../shared/boundary/footer.php'; ?>
