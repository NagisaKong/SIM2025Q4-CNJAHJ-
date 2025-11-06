<?php
require_once __DIR__ . '/../controller/viewRequestController.php';
require_once __DIR__ . '/../controller/viewRequestShortlistCountController.php';
require_once __DIR__ . '/../controller/viewRequestViewCountController.php';
require_once __DIR__ . '/../../shared/entity/Request.php';

use CSRPlatform\PIN\Controller\viewRequestController;
use CSRPlatform\PIN\Controller\viewRequestShortlistCountController;
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
    $_SESSION['flash_error'] = 'The request you are looking for was not found.';
    header('Location: /index.php?page=pin-requests');
    exit();
}

$requestEntity = new Request();
$viewController = new viewRequestController($requestEntity);
$shortlistController = new viewRequestShortlistCountController($requestEntity);
$viewCountController = new viewRequestViewCountController($requestEntity);

$requestDetails = $viewController->viewPostedRequest((int) $currentUser['id'], $requestId, false);
if (!$requestDetails) {
    $_SESSION['flash_error'] = 'The request you are looking for was not found.';
    header('Location: /index.php?page=pin-requests');
    exit();
}

$shortlistCount = $shortlistController->viewRequestShortlistCount($requestId);
$viewCount = $viewCountController->viewRequestViewCount($requestId);

$pageTitle = 'View request';
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
            <h1><?= htmlspecialchars((string) $requestDetails['title'], ENT_QUOTES) ?></h1>
            <p><?= htmlspecialchars((string) ($requestDetails['category_name'] ?? ''), ENT_QUOTES) ?> ·
                <?= htmlspecialchars((string) $requestDetails['status'], ENT_QUOTES) ?></p>
        </div>
        <div class="card-actions">
            <a href="/index.php?page=pin-requests" class="btn-secondary">Back to My Requests</a>
            <a href="/index.php?page=pin-request-edit&amp;id=<?= $requestId ?>" class="btn-secondary">Edit</a>
        </div>
    </div>
    <div class="grid">
        <div>
            <h2>Details</h2>
            <p><strong>Status:</strong> <?= htmlspecialchars((string) $requestDetails['status'], ENT_QUOTES) ?></p>
            <p><strong>Created:</strong> <?= htmlspecialchars((string) $requestDetails['created_at'], ENT_QUOTES) ?></p>
            <p><strong>Last updated:</strong> <?= htmlspecialchars((string) $requestDetails['updated_at'], ENT_QUOTES) ?></p>
            <?php if (!empty($requestDetails['requested_date'])): ?>
                <p><strong>Requested date:</strong> <?= htmlspecialchars((string) $requestDetails['requested_date'], ENT_QUOTES) ?></p>
            <?php endif; ?>
        </div>
        <div>
            <h2>Additional details</h2>
            <p><?= nl2br(htmlspecialchars((string) ($requestDetails['additional_details'] ?? $requestDetails['description']), ENT_QUOTES)) ?></p>
        </div>
    </div>
    <div class="metric-grid">
        <div class="metric-card">
            <span class="metric-label">Shortlist count</span>
            <span class="metric-value"><?= $shortlistCount ?></span>
        </div>
        <div class="metric-card">
            <span class="metric-label">View count</span>
            <span class="metric-value"><?= $viewCount ?></span>
        </div>
    </div>
</section>
<?php include __DIR__ . '/../../shared/boundary/footer.php'; ?>
