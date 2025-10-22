<?php
declare(strict_types=1);

require_once __DIR__ . '/../../shared/bootstrap.php';
require_once __DIR__ . '/../controller/searchPostedRequestsController.php';
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

$pinId = (int) ($_SESSION['user']['id'] ?? 0);
$controller = new SearchPostedRequestsController();
$requests = $pinId ? $controller->search($pinId, $_GET) : [];
?>
<section class="card">
    <div class="card-header">
        <h1>My Requests</h1>
        <a class="btn-primary" href="/public/index.php?route=pin/create-request">New Request</a>
    </div>
    <?php if (!empty($_SESSION['pin_message'])): ?>
        <div class="alert alert-info"><?= htmlspecialchars($_SESSION['pin_message'], ENT_QUOTES) ?></div>
        <?php unset($_SESSION['pin_message']); ?>
    <?php endif; ?>
    <form class="form-inline" method="GET" action="/public/index.php">
        <input type="hidden" name="route" value="pin/my-requests">
        <input type="search" name="q" placeholder="Search requests" value="<?= htmlspecialchars((string) ($_GET['q'] ?? ''), ENT_QUOTES) ?>">
        <button type="submit" class="btn-secondary">Search</button>
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
                    <td><?= htmlspecialchars($requestItem['title'] ?? '', ENT_QUOTES) ?></td>
                    <td class="status-col"><span class="tag tag-<?= htmlspecialchars($requestItem['status'] ?? '', ENT_QUOTES) ?>"><?= htmlspecialchars($requestItem['status'] ?? '', ENT_QUOTES) ?></span></td>
                    <td><?= (int) ($requestItem['views_count'] ?? 0) ?></td>
                    <td><?= (int) ($requestItem['shortlist_count'] ?? 0) ?></td>
                    <td class="actions">
                        <a href="/public/index.php?route=pin/view-request&id=<?= (int) ($requestItem['id'] ?? 0) ?>">View</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php include __DIR__ . '/../../shared/boundary/footer.php'; ?>
