<?php
require_once __DIR__ . '/../controller/viewCSRHistoryController.php';
require_once __DIR__ . '/../controller/searchCSRHistoryController.php';

use CSRPlatform\CSRRepresentative\Controller\searchCSRHistoryController;
use CSRPlatform\CSRRepresentative\Controller\viewCSRHistoryController;
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

$shortlistEntity = new Shortlist();
$viewController = new viewCSRHistoryController($shortlistEntity);
$searchController = new searchCSRHistoryController($shortlistEntity);

$searchQuery = $_GET['q'] ?? '';
$history = $searchQuery === ''
    ? $viewController->history((int) $currentUser['id'])
    : $searchController->search((int) $currentUser['id'], $searchQuery);

$pageTitle = 'CSR History';
$navLinks = [
    ['href' => '/index.php?page=csr-requests', 'label' => 'Opportunities'],
    ['href' => '/index.php?page=csr-shortlist', 'label' => 'Shortlist'],
    ['href' => '/index.php?page=csr-history', 'label' => 'History'],
];
include __DIR__ . '/../../shared/boundary/header.php';
?>
<section class="card">
    <h1>Volunteer history</h1>
    <form method="GET" action="/index.php" class="form-inline">
        <input type="hidden" name="page" value="csr-history">
        <input type="search" name="q" placeholder="Search history" value="<?= htmlspecialchars((string) $searchQuery, ENT_QUOTES) ?>">
        <button type="submit" class="btn-secondary">Search</button>
        <?php if ($searchQuery !== ''): ?>
            <a href="/index.php?page=csr-history" class="btn-secondary">Clear</a>
        <?php endif; ?>
    </form>
    <table class="table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Status</th>
                <th>Shortlisted on</th>
                <th>Updated at</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($history as $row): ?>
                <tr>
                    <td><?= htmlspecialchars((string) $row['title'], ENT_QUOTES) ?></td>
                    <td><span class="tag tag-<?= htmlspecialchars((string) ($row['status'] ?? 'unknown'), ENT_QUOTES) ?>"><?= htmlspecialchars((string) ($row['status'] ?? 'unknown'), ENT_QUOTES) ?></span></td>
                    <td><?= htmlspecialchars((string) $row['created_at'], ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars((string) ($row['updated_at'] ?? ''), ENT_QUOTES) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if ($history === []): ?>
                <tr>
                    <td colspan="4" style="text-align: center;">No history available.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</section>
<?php include __DIR__ . '/../../shared/boundary/footer.php'; ?>
