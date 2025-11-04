<?php
require_once __DIR__ . '/../controller/searchRequestController.php';
require_once __DIR__ . '/../controller/saveRequestController.php';

use CSRPlatform\CSRRepresentative\Controller\saveRequestController;
use CSRPlatform\CSRRepresentative\Controller\searchRequestController;
use CSRPlatform\Shared\Entity\Request;
use CSRPlatform\Shared\Entity\ServiceCategories;
use CSRPlatform\Shared\Entity\Shortlist;

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

$categoriesEntity = new ServiceCategories();
$searchController = new searchRequestController(new Request());
$saveController = new saveRequestController(new Shortlist(), new Request());

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['shortlist_id'])) {
    $requestId = (int) $_POST['shortlist_id'];
    if ($requestId > 0) {
        if ($saveController->saveRequest((int) $currentUser['id'], $requestId)) {
            $_SESSION['flash_success'] = 'Request added to your shortlist.';
        } else {
            $_SESSION['flash_warning'] = 'Request already shortlisted.';
        }
    }
    header('Location: /index.php?page=csr-requests');
    exit();
}

$searchQuery = $_GET['q'] ?? null;
$categoryId = isset($_GET['category_id']) && $_GET['category_id'] !== '' ? (int) $_GET['category_id'] : null;
$status = $_GET['status'] ?? null;
$requests = $searchController->searchRequests((string) ($searchQuery ?? ''), $status, $categoryId);
$categories = $categoriesEntity->listCategories('active');

$pageTitle = 'Volunteer Opportunities';
$navLinks = [
    ['href' => '/index.php?page=dashboard', 'label' => 'Dashboard'],
    ['href' => '/index.php?page=csr-requests', 'label' => 'Opportunities'],
    ['href' => '/index.php?page=csr-shortlist', 'label' => 'Shortlist'],
    ['href' => '/index.php?page=csr-history', 'label' => 'History'],
];
include __DIR__ . '/../../shared/boundary/header.php';
?>
<section class="card">
    <h1>Volunteer Opportunities</h1>
    <form class="form-inline" method="GET" action="/index.php">
        <input type="hidden" name="page" value="csr-requests">
        <input type="text" name="q" placeholder="Keyword" value="<?= htmlspecialchars((string) $searchQuery, ENT_QUOTES) ?>">
        <select name="category_id">
            <option value="">All categories</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= (int) $category['id'] ?>" <?= $categoryId === (int) $category['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars((string) $category['name'], ENT_QUOTES) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <select name="status">
            <option value="all">All statuses</option>
            <option value="open" <?= $status === 'open' ? 'selected' : '' ?>>Open</option>
            <option value="matched" <?= $status === 'matched' ? 'selected' : '' ?>>Matched</option>
            <option value="closed" <?= $status === 'closed' ? 'selected' : '' ?>>Closed</option>
        </select>
        <button type="submit" class="btn-secondary">Filter</button>
    </form>
    <table class="table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Location</th>
                <th>Date</th>
                <th>Views</th>
                <th>Shortlisted</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($requests as $requestItem): ?>
                <tr>
                    <td><?= htmlspecialchars((string) $requestItem['title'], ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars((string) $requestItem['location'], ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars((string) $requestItem['requested_date'], ENT_QUOTES) ?></td>
                    <td><?= (int) $requestItem['views_count'] ?></td>
                    <td><?= (int) $requestItem['shortlist_count'] ?></td>
                    <td>
                        <form method="POST" action="/index.php?page=csr-requests" class="inline-form">
                            <input type="hidden" name="shortlist_id" value="<?= (int) $requestItem['id'] ?>">
                            <button type="submit" class="btn-primary">Shortlist</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if ($requests === []): ?>
                <tr>
                    <td colspan="6" style="text-align: center;">No requests found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</section>
<?php include __DIR__ . '/../../shared/boundary/footer.php'; ?>
