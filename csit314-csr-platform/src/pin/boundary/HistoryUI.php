<?php
require_once __DIR__ . '/../controller/viewPINHistoryController.php';

use CSRPlatform\PIN\Controller\viewPINHistoryController;
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

$viewController = new viewPINHistoryController(new Request());
$history = $viewController->history((int) $currentUser['id']);

$pageTitle = 'Request History';
$navLinks = [
    ['href' => '/index.php?page=pin-requests', 'label' => 'My Requests'],
    ['href' => '/index.php?page=pin-history', 'label' => 'History'],
];
include __DIR__ . '/../../shared/boundary/header.php';
?>
<section class="card">
    <h1>Request history</h1>
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
                    <td colspan="4" style="text-align: center;">No history records yet.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</section>
<?php include __DIR__ . '/../../shared/boundary/footer.php'; ?>
