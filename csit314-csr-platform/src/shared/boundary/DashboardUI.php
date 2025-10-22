<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
include __DIR__ . '/header.php';

function logout(): void
{
    session_destroy();
    header('Location: /public/index.php?route=login');
    exit();
}

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    logout();
}

$user = $_SESSION['user'] ?? null;
$role = $user['role'] ?? '';
?>
<section class="card">
    <h1>Welcome, <?= htmlspecialchars($user['name'] ?? 'Guest', ENT_QUOTES) ?></h1>
    <p>Your role: <?= htmlspecialchars($role, ENT_QUOTES) ?></p>
    <div class="grid">
        <?php if ($role === 'user_admin'): ?>
            <a class="shortcut" href="/public/index.php?route=admin/view-accounts">Manage Users</a>
            <a class="shortcut" href="/public/index.php?route=admin/view-profiles">Manage Profiles</a>
            <a class="shortcut" href="/public/index.php?route=pm/categories">Service Categories</a>
        <?php elseif ($role === 'csr_rep'): ?>
            <a class="shortcut" href="/public/index.php?route=csr/search-requests">Browse Requests</a>
            <a class="shortcut" href="/public/index.php?route=csr/shortlist">My Shortlist</a>
            <a class="shortcut" href="/public/index.php?route=csr/history">Service History</a>
        <?php elseif ($role === 'pin'): ?>
            <a class="shortcut" href="/public/index.php?route=pin/my-requests">My Requests</a>
            <a class="shortcut" href="/public/index.php?route=pin/create-request">Create Request</a>
            <a class="shortcut" href="/public/index.php?route=pin/history">Completed Matches</a>
        <?php elseif ($role === 'platform_manager'): ?>
            <a class="shortcut" href="/public/index.php?route=pm/categories">Service Categories</a>
            <a class="shortcut" href="/public/index.php?route=pm/daily-report">Daily Reports</a>
            <a class="shortcut" href="/public/index.php?route=pm/weekly-report">Weekly Reports</a>
        <?php else: ?>
            <p>Please contact an administrator for assistance.</p>
        <?php endif; ?>
    </div>
</section>
<?php include __DIR__ . '/footer.php'; ?>
