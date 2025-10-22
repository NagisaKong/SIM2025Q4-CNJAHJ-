<?php
declare(strict_types=1);

require_once __DIR__ . '/../../shared/bootstrap.php';
require_once __DIR__ . '/../controller/viewCSRHistoryController.php';
require_once __DIR__ . '/../controller/searchCSRHistoryController.php';
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
$controller = new ViewCSRHistoryController();
$searchController = new SearchCSRHistoryController();
$history = $csrId ? $searchController->search($csrId, $_GET) : [];
?>
<section class="card">
    <h1>Service History</h1>
    <form class="form-inline" method="GET" action="/public/index.php">
        <input type="hidden" name="route" value="csr/history">
        <input type="search" name="q" placeholder="Search history" value="<?= htmlspecialchars((string) ($_GET['q'] ?? ''), ENT_QUOTES) ?>">
        <button type="submit" class="btn-secondary">Filter</button>
    </form>
    <table class="table">
        <thead>
            <tr>
                <th>Request</th>
                <th>Status</th>
                <th>Matched at</th>
                <th>Completed</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($history as $item): ?>
                <tr>
                    <td>#<?= (int) ($item['id'] ?? 0) ?> - <?= htmlspecialchars($item['title'] ?? '', ENT_QUOTES) ?></td>
                    <td><span class="tag tag-<?= htmlspecialchars(strtolower((string) ($item['match_status'] ?? 'open')), ENT_QUOTES) ?>"><?= htmlspecialchars($item['match_status'] ?? 'pending', ENT_QUOTES) ?></span></td>
                    <td><?= htmlspecialchars($item['matched_at'] ?? '-', ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($item['completed_at'] ?? '-', ENT_QUOTES) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php include __DIR__ . '/../../shared/boundary/footer.php'; ?>
