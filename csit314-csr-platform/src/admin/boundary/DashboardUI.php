<?php
require_once __DIR__ . '/../controller/viewAccountsController.php';
require_once __DIR__ . '/../controller/viewProfilesController.php';

use CSRPlatform\Admin\Controller\viewAccountsController;
use CSRPlatform\Admin\Controller\viewProfilesController;
use CSRPlatform\Shared\Entity\UserAccount;
use CSRPlatform\Shared\Entity\UserProfiles;

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
if (!$currentUser || ($currentUser['role'] ?? '') !== 'admin') {
    header('Location: /index.php?page=login');
    exit();
}

$accountController = new viewAccountsController(new UserAccount());
$profileController = new viewProfilesController(new UserProfiles());

$totalAccounts = count($accountController->list('all', null));
$totalProfiles = count($profileController->list(null, null));

$displayName = $currentUser['name'] ?? 'Administrator';
$roleName = $currentUser['role'] ?? 'admin';

$pageTitle = 'Admin dashboard';
$baseUrl = '/index.php?page=admin-dashboard';
$navLinks = [
    ['href' => '/index.php?page=admin-dashboard', 'label' => 'Dashboard'],
    ['href' => '/index.php?page=admin-accounts', 'label' => 'Users'],
    ['href' => '/index.php?page=admin-profiles', 'label' => 'Profiles'],
    ['href' => '/index.php?page=pm-categories', 'label' => 'Categories'],
];
include __DIR__ . '/../../shared/boundary/header.php';
?>
<section class="card dashboard-card">
    <div class="card-heading">
        <div>
            <p class="muted">Welcome back!</p>
            <h1>Welcome, <?= htmlspecialchars((string) $displayName, ENT_QUOTES) ?></h1>
            <p class="muted">Your role: <?= htmlspecialchars((string) $roleName, ENT_QUOTES) ?></p>
        </div>
    </div>
    <div class="dashboard-actions">
        <a class="dashboard-tile" href="/index.php?page=admin-accounts">
            <span class="tile-title">Manage Users</span>
            <span class="tile-meta"><?= $totalAccounts ?> total</span>
        </a>
        <a class="dashboard-tile" href="/index.php?page=admin-profiles">
            <span class="tile-title">Manage Profiles</span>
            <span class="tile-meta"><?= $totalProfiles ?> profiles</span>
        </a>
        <a class="dashboard-tile" href="/index.php?page=pm-categories">
            <span class="tile-title">Service Categories</span>
            <span class="tile-meta">Keep offerings curated</span>
        </a>
    </div>
</section>
<?php include __DIR__ . '/../../shared/boundary/footer.php'; ?>
