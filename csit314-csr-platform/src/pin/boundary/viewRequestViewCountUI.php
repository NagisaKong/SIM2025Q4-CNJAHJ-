<?php
require_once __DIR__ . '/../controller/viewRequestViewCountController.php';
require_once __DIR__ . '/../controller/viewRequestController.php';

use CSRPlatform\PIN\Controller\viewRequestController;
use CSRPlatform\PIN\Controller\viewRequestViewCountController;
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
if (!$currentUser || strtolower((string) ($currentUser['role'] ?? '')) !== 'pin') {
    header('Location: /index.php?page=login');
    exit();
}

$requestId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($requestId <= 0) {
    $_SESSION['flash_error'] = 'The requested record could not be found.';
    header('Location: /index.php?page=pin-requests');
    exit();
}

$requestEntity = new Request();
$viewController = new viewRequestController($requestEntity);
$viewCountController = new viewRequestViewCountController($requestEntity);

$requestDetails = $viewController->viewPostedRequest((int) $currentUser['id'], $requestId, false);
if (!$requestDetails) {
    $_SESSION['flash_error'] = 'The requested record could not be found.';
    header('Location: /index.php?page=pin-requests');
    exit();
}

$viewCount = $viewCountController->viewRequestViewCount($requestId);

$pageTitle = 'View count';
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
            <h1>View count</h1>
            <p><?= htmlspecialchars((string) $requestDetails['title'], ENT_QUOTES) ?></p>
        </div>
        <a href="/index.php?page=pin-requests&amp;view=<?= $requestId ?>" class="btn-secondary">Back to request</a>
    </div>
    <p><strong>Total views recorded</strong></p>
    <p><span class="tag tag-open"><?= $viewCount ?></span></p>
</section>
<?php include __DIR__ . '/../../shared/boundary/footer.php'; ?>
