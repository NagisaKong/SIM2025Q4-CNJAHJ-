<?php
declare(strict_types=1);

require_once __DIR__ . '/../../shared/bootstrap.php';
require_once __DIR__ . '/../controller/viewRequestController.php';
require_once __DIR__ . '/../controller/viewRequestShortlistCountController.php';
include __DIR__ . '/../../shared/boundary/header.php';

function logout(): void
{
    session_destroy();
    header('Location: /public/index.php?route=login');
    exit();
}

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    logout();
}

$requestId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$controller = new ViewRequestController();
$request = $requestId ? $controller->find($requestId) : null;
$shortlistController = new ViewRequestShortlistCountController();
$shortlistCount = $requestId ? $shortlistController->count($requestId) : 0;
?>
<section class="card">
    <?php if ($request): ?>
        <h1><?= htmlspecialchars($request['title'] ?? '', ENT_QUOTES) ?></h1>
        <p><?= nl2br(htmlspecialchars($request['description'] ?? '', ENT_QUOTES)) ?></p>
        <dl class="definition-list">
            <dt>Status</dt><dd><?= htmlspecialchars($request['status'] ?? '', ENT_QUOTES) ?></dd>
            <dt>Requested Date</dt><dd><?= htmlspecialchars($request['requested_date'] ?? '', ENT_QUOTES) ?></dd>
            <dt>Location</dt><dd><?= htmlspecialchars($request['location'] ?? '', ENT_QUOTES) ?></dd>
            <dt>Views</dt><dd><?= (int) ($request['views_count'] ?? 0) ?></dd>
            <dt>Shortlisted</dt><dd><?= (int) $shortlistCount ?></dd>
        </dl>
        <a href="/public/index.php?route=pin/my-requests" class="btn-secondary">Back</a>
    <?php else: ?>
        <p>Request not found.</p>
    <?php endif; ?>
</section>
<?php include __DIR__ . '/../../shared/boundary/footer.php'; ?>
