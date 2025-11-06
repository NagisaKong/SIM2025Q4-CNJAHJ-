<?php
require_once __DIR__ . '/../controller/searchPostedRequestsController.php';
require_once __DIR__ . '/../../shared/entity/serviceCategories.php';

use CSRPlatform\PIN\Controller\searchPostedRequestsController;
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
$searchController = new searchPostedRequestsController($requestEntity);
$categoriesEntity = new ServiceCategories();

$searchQuery = $_GET['q'] ?? '';
$statusFilter = $_GET['status'] ?? 'all';
$serviceFilter = isset($_GET['service']) && $_GET['service'] !== '' ? (int) $_GET['service'] : null;

$requests = $searchController->searchPostedRequests(
    (int) $currentUser['id'],
    $searchQuery,
    $statusFilter,
    $serviceFilter
);

$categories = $categoriesEntity->listCategories('active');

$pageTitle = 'My Requests';
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
            <h1>My Requests</h1>
            <p>Review and manage the requests you have posted.</p>
        </div>
        <a href="/index.php?page=pin-request-create" class="btn-primary">New request</a>
    </div>
    <form method="GET" action="/index.php" class="form-inline">
        <input type="hidden" name="page" value="pin-requests">
        <label>Keyword
            <input type="search" name="q" placeholder="Search by keyword" value="<?= htmlspecialchars((string) $searchQuery, ENT_QUOTES) ?>">
        </label>
        <label>Status
            <select name="status">
                <?php $statuses = ['all' => 'All statuses', 'open' => 'Open', 'in_progress' => 'In progress', 'completed' => 'Completed', 'cancelled' => 'Cancelled', 'suspended' => 'Suspended']; ?>
                <?php foreach ($statuses as $value => $label): ?>
                    <option value="<?= htmlspecialchars($value, ENT_QUOTES) ?>" <?= $statusFilter === $value ? 'selected' : '' ?>><?= htmlspecialchars($label, ENT_QUOTES) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Service
            <select name="service">
                <option value="">All services</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= (int) $category['id'] ?>" <?= $serviceFilter === (int) $category['id'] ? 'selected' : '' ?>><?= htmlspecialchars((string) $category['name'], ENT_QUOTES) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <button type="submit" class="btn-secondary btn-filter">Filter</button>
        <?php if ($searchQuery !== '' || $statusFilter !== 'all' || $serviceFilter !== null): ?>
            <a href="/index.php?page=pin-requests" class="btn-secondary">Reset</a>
        <?php endif; ?>
    </form>
    <table class="table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Service</th>
                <th>Status</th>
                <th>Updated</th>
                <th>Views</th>
                <th>Shortlisted</th>
                <th class="actions">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($requests as $row): ?>
                <tr>
                    <td><?= htmlspecialchars((string) $row['title'], ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars((string) ($row['category_name'] ?? ''), ENT_QUOTES) ?></td>
                    <td><span class="tag tag-<?= htmlspecialchars((string) $row['status'], ENT_QUOTES) ?>"><?= htmlspecialchars((string) $row['status'], ENT_QUOTES) ?></span></td>
                    <td><?= htmlspecialchars((string) $row['updated_at'], ENT_QUOTES) ?></td>
                    <td><?= (int) $row['views_count'] ?></td>
                    <td><?= (int) $row['shortlist_count'] ?></td>
                    <td class="actions">
                        <a class="btn-secondary" href="/index.php?page=pin-request-view&amp;id=<?= (int) $row['id'] ?>">View</a>
                        <a class="btn-secondary" href="/index.php?page=pin-request-edit&amp;id=<?= (int) $row['id'] ?>">Edit</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if ($requests === []): ?>
                <tr>
                    <td colspan="7" style="text-align: center;">No requests found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</section>
<?php include __DIR__ . '/../../shared/boundary/footer.php'; ?>
