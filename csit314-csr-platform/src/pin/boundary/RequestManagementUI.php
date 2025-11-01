<?php
require_once __DIR__ . '/../controller/createRequestController.php';
require_once __DIR__ . '/../controller/searchPostedRequestsController.php';
require_once __DIR__ . '/../controller/viewRequestController.php';
require_once __DIR__ . '/../controller/viewRequestShortlistCountController.php';

use CSRPlatform\PIN\Controller\createRequestController;
use CSRPlatform\PIN\Controller\searchPostedRequestsController;
use CSRPlatform\PIN\Controller\viewRequestController;
use CSRPlatform\PIN\Controller\viewRequestShortlistCountController;
use CSRPlatform\Shared\Entity\Request;
use CSRPlatform\Shared\Entity\ServiceCategories;
use CSRPlatform\Shared\Utils\Validation;

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

$requestEntity = new Request();
$categoriesEntity = new ServiceCategories();
$validator = new Validation();
$createController = new createRequestController($requestEntity, $validator);
$searchController = new searchPostedRequestsController($requestEntity);
$viewController = new viewRequestController($requestEntity);
$shortlistSummaryController = new viewRequestShortlistCountController($requestEntity);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_type'] ?? '') === 'create') {
    if ($createController->create((int) $currentUser['id'], $_POST)) {
        $_SESSION['flash_success'] = 'Request created successfully.';
        header('Location: /index.php?page=pin-requests');
        exit();
    }
    $_SESSION['flash_error'] = implode(' ', array_map(static fn($errors) => implode(' ', (array) $errors), $createController->errors()));
}

$searchQuery = $_GET['q'] ?? '';
$requests = $searchController->search((int) $currentUser['id'], $searchQuery);
$selectedRequestId = isset($_GET['view']) ? (int) $_GET['view'] : 0;
$selectedRequest = $selectedRequestId > 0 ? $viewController->view($selectedRequestId, false) : null;
$categories = $categoriesEntity->listCategories('active');
$shortlistSummary = $shortlistSummaryController->list((int) $currentUser['id']);

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
        <h1>My Requests</h1>
        <p>Track and manage support requests you've posted.</p>
    </div>
    <form method="GET" action="/index.php" class="form-inline">
        <input type="hidden" name="page" value="pin-requests">
        <input type="search" name="q" placeholder="Search requests" value="<?= htmlspecialchars((string) $searchQuery, ENT_QUOTES) ?>">
        <button type="submit" class="btn-secondary">Search</button>
        <?php if ($searchQuery !== ''): ?>
            <a href="/index.php?page=pin-requests" class="btn-secondary">Clear</a>
        <?php endif; ?>
    </form>
    <table class="table">
        <thead>
            <tr>
                <th>Title</th>
                <th class="status-col">Status</th>
                <th>Views</th>
                <th>Shortlisted</th>
                <th class="actions">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($requests as $requestItem): ?>
                <tr>
                    <td><?= htmlspecialchars((string) $requestItem['title'], ENT_QUOTES) ?></td>
                    <td class="status-col"><span class="tag tag-<?= htmlspecialchars((string) $requestItem['status'], ENT_QUOTES) ?>"><?= htmlspecialchars((string) $requestItem['status'], ENT_QUOTES) ?></span></td>
                    <td><?= (int) $requestItem['views_count'] ?></td>
                    <td><?= (int) $requestItem['shortlist_count'] ?></td>
                    <td class="actions">
                        <a href="/index.php?page=pin-requests&amp;view=<?= (int) $requestItem['id'] ?>">View</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if ($requests === []): ?>
                <tr>
                    <td colspan="5" style="text-align: center;">No requests yet.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</section>
<section class="card">
    <div class="card-header">
        <h2><?= $selectedRequest ? 'Request details' : 'Create a new request' ?></h2>
    </div>
    <?php if ($selectedRequest): ?>
        <div class="request-detail">
            <h3><?= htmlspecialchars((string) $selectedRequest['title'], ENT_QUOTES) ?></h3>
            <p><strong>Category:</strong> <?= htmlspecialchars((string) ($selectedRequest['category_name'] ?? ''), ENT_QUOTES) ?></p>
            <p><strong>Status:</strong> <?= htmlspecialchars((string) $selectedRequest['status'], ENT_QUOTES) ?></p>
            <p><strong>Description:</strong></p>
            <p><?= nl2br(htmlspecialchars((string) $selectedRequest['description'], ENT_QUOTES)) ?></p>
        </div>
    <?php else: ?>
        <form method="POST" action="/index.php?page=pin-requests" class="form-grid">
            <input type="hidden" name="form_type" value="create">
            <label>Title
                <input type="text" name="title" required>
            </label>
            <label>Category
                <select name="category_id" required>
                    <option value="">Select category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= (int) $category['id'] ?>"><?= htmlspecialchars((string) $category['name'], ENT_QUOTES) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Location
                <input type="text" name="location" required>
            </label>
            <label>Requested date
                <input type="date" name="requested_date" required>
            </label>
            <label>Description
                <textarea name="description" rows="4" required></textarea>
            </label>
            <button type="submit" class="btn-primary">Submit request</button>
        </form>
    <?php endif; ?>
</section>
<section class="card">
    <div class="card-header">
        <h2>Shortlist overview</h2>
        <p>Monitor volunteer interest in your requests.</p>
    </div>
    <table class="table">
        <thead>
            <tr>
                <th>Request</th>
                <th>Shortlist count</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($shortlistSummary as $summary): ?>
                <tr>
                    <td><?= htmlspecialchars((string) $summary['title'], ENT_QUOTES) ?></td>
                    <td><?= (int) $summary['shortlist_count'] ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if ($shortlistSummary === []): ?>
                <tr>
                    <td colspan="2" style="text-align: center;">No shortlist activity yet.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</section>
<?php include __DIR__ . '/../../shared/boundary/footer.php'; ?>
