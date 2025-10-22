<?php
declare(strict_types=1);

require_once __DIR__ . '/../../shared/bootstrap.php';
require_once __DIR__ . '/../controller/viewShortlistedRequestController.php';
require_once __DIR__ . '/../controller/searchShortlistController.php';
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

$csrId = (int) ($_SESSION['user']['id'] ?? 0);
$controller = new ViewShortlistedRequestController();
$searchController = new SearchShortlistController();
$shortlist = $csrId ? $searchController->search($csrId, $_GET) : [];
?>
<section class="card">
    <h1>My Shortlist</h1>
    <form class="form-inline" method="GET" action="/public/index.php">
        <input type="hidden" name="route" value="csr/shortlist">
        <input type="search" name="q" placeholder="Search shortlist" value="<?= htmlspecialchars((string) ($_GET['q'] ?? ''), ENT_QUOTES) ?>">
        <button type="submit" class="btn-secondary">Search</button>
    </form>
    <table class="table">
        <thead>
            <tr>
                <th>Request</th>
                <th>Category</th>
                <th>Status</th>
                <th>Saved at</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($shortlist as $item): ?>
                <tr>
                    <td><a href="/public/index.php?route=csr/view-request&id=<?= (int) ($item['request_id'] ?? 0) ?>"><?= htmlspecialchars($item['title'] ?? ('Request #' . ($item['request_id'] ?? '')), ENT_QUOTES) ?></a></td>
                    <td><?= htmlspecialchars($item['category_name'] ?? '', ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($item['status'] ?? '', ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($item['created_at'] ?? '', ENT_QUOTES) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php include __DIR__ . '/../../shared/boundary/footer.php'; ?>
