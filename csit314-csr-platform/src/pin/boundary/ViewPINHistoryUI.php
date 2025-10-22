<?php
declare(strict_types=1);

require_once __DIR__ . '/../../shared/bootstrap.php';
require_once __DIR__ . '/../controller/viewPINHistoryController.php';
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
$controller = new ViewPINHistoryController();
$history = $pinId ? $controller->list($pinId) : [];
?>
<section class="card">
    <h1>Completed Matches</h1>
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
                    <td><span class="tag tag-<?= htmlspecialchars($item['match_status'] ?? '', ENT_QUOTES) ?>"><?= htmlspecialchars($item['match_status'] ?? '', ENT_QUOTES) ?></span></td>
                    <td><?= htmlspecialchars($item['matched_at'] ?? '-', ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($item['completed_at'] ?? '-', ENT_QUOTES) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php include __DIR__ . '/../../shared/boundary/footer.php'; ?>
