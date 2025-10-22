<?php
require_once __DIR__ . '/../controller/viewShortlistedRequestController.php';
require_once __DIR__ . '/../controller/searchShortlistController.php';

use CSRPlatform\CSRRepresentative\Controller\searchShortlistController;
use CSRPlatform\CSRRepresentative\Controller\viewShortlistedRequestController;
use CSRPlatform\Shared\Entity\Shortlist;
use CSRPlatform\Shared\Entity\Request;

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
$viewController = new viewShortlistedRequestController($shortlistEntity);
$searchController = new searchShortlistController($shortlistEntity);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_id'])) {
    $requestId = (int) $_POST['remove_id'];
    if ($requestId > 0) {
        $shortlistEntity->removeFromShortlist((int) $currentUser['id'], $requestId);
        $_SESSION['flash_info'] = 'Request removed from shortlist.';
    }
    header('Location: /index.php?page=csr-shortlist');
    exit();
}

$searchQuery = $_GET['q'] ?? '';
$requests = $searchQuery === ''
    ? $viewController->list((int) $currentUser['id'])
    : $searchController->search((int) $currentUser['id'], $searchQuery);

$pageTitle = 'My Shortlist';
$navLinks = [
    ['href' => '/index.php?page=csr-requests', 'label' => 'Opportunities'],
    ['href' => '/index.php?page=csr-shortlist', 'label' => 'Shortlist'],
    ['href' => '/index.php?page=csr-history', 'label' => 'History'],
];
include __DIR__ . '/../../shared/boundary/header.php';
?>
<section class="card">
    <h1>Shortlisted Requests</h1>
    <form method="GET" action="/index.php" class="form-inline">
        <input type="hidden" name="page" value="csr-shortlist">
        <input type="search" name="q" placeholder="Search shortlist" value="<?= htmlspecialchars((string) $searchQuery, ENT_QUOTES) ?>">
        <button type="submit" class="btn-secondary">Search</button>
        <?php if ($searchQuery !== ''): ?>
            <a href="/index.php?page=csr-shortlist" class="btn-secondary">Clear</a>
        <?php endif; ?>
    </form>
    <table class="table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Category</th>
                <th>Location</th>
                <th>Shortlisted on</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($requests as $request): ?>
                <tr>
                    <td><?= htmlspecialchars((string) $request['title'], ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars((string) ($request['category_name'] ?? 'N/A'), ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars((string) $request['location'], ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars((string) $request['shortlisted_at'], ENT_QUOTES) ?></td>
                    <td>
                        <form method="POST" action="/index.php?page=csr-shortlist" class="inline-form">
                            <input type="hidden" name="remove_id" value="<?= (int) $request['id'] ?>">
                            <button type="submit" class="link-button">Remove</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if ($requests === []): ?>
                <tr>
                    <td colspan="5" style="text-align: center;">No shortlisted requests.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</section>
<?php include __DIR__ . '/../../shared/boundary/footer.php'; ?>
