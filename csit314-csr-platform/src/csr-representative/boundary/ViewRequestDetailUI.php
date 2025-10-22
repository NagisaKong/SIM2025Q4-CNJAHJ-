<?php
declare(strict_types=1);

require_once __DIR__ . '/../../shared/bootstrap.php';
require_once __DIR__ . '/../controller/saveRequestController.php';
require_once __DIR__ . '/../../pin/controller/viewRequestController.php';
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $requestId) {
    $csrId = (int) ($_SESSION['user']['id'] ?? 0);
    if ($csrId) {
        $saveController = new SaveRequestController();
        $saveController->addToShortlist($csrId, $requestId);
    }
    header('Location: /public/index.php?route=csr/view-request&id=' . $requestId);
    exit();
}
?>
<section class="card">
    <?php if ($request): ?>
        <h1><?= htmlspecialchars($request['title'] ?? '', ENT_QUOTES) ?></h1>
        <p><?= nl2br(htmlspecialchars($request['description'] ?? '', ENT_QUOTES)) ?></p>
        <p><strong>Location:</strong> <?= htmlspecialchars($request['location'] ?? '', ENT_QUOTES) ?></p>
        <p><strong>Date:</strong> <?= htmlspecialchars($request['requested_date'] ?? '', ENT_QUOTES) ?></p>
        <p><strong>Views:</strong> <?= (int) ($request['views_count'] ?? 0) ?> | <strong>Shortlists:</strong> <?= (int) ($request['shortlist_count'] ?? 0) ?></p>
        <form method="POST" action="/public/index.php?route=csr/view-request&id=<?= (int) $requestId ?>">
            <button type="submit" class="btn-primary">Add to shortlist</button>
        </form>
        <a href="/public/index.php?route=csr/search-requests" class="btn-secondary">Back</a>
    <?php else: ?>
        <p>Request not found.</p>
    <?php endif; ?>
</section>
<?php include __DIR__ . '/../../shared/boundary/footer.php'; ?>
