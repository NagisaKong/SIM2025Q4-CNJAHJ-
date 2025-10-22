<?php
require_once __DIR__ . '/../controller/viewProfilesController.php';

use CSRPlatform\Admin\Controller\viewProfilesController;
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

$profilesEntity = new UserProfiles();
$viewController = new viewProfilesController($profilesEntity);

$searchQuery = $_GET['q'] ?? null;
$profiles = $viewController->list(null, $searchQuery);

$pageTitle = 'Manage profiles';
$baseUrl = '/index.php?page=admin-dashboard';
$navLinks = [
    ['href' => '/index.php?page=admin-dashboard', 'label' => 'Dashboard'],
    ['href' => '/index.php?page=admin-accounts', 'label' => 'Users'],
    ['href' => '/index.php?page=admin-profiles', 'label' => 'Profiles'],
    ['href' => '/index.php?page=pm-categories', 'label' => 'Categories'],
];
include __DIR__ . '/../../shared/boundary/header.php';
?>
<section class="card">
    <div class="card-heading">
        <div>
            <h1>User Profiles</h1>
            <p class="muted">Define and monitor permission sets for every role.</p>
        </div>
        <div class="card-actions">
            <a class="btn-primary" href="/index.php?page=admin-profile-create">New Profile</a>
        </div>
    </div>
    <form method="GET" action="/index.php" class="form-inline">
        <input type="hidden" name="page" value="admin-profiles">
        <input
            id="profile-search"
            type="search"
            name="q"
            placeholder="Search profiles"
            aria-label="Search profiles"
            value="<?= htmlspecialchars((string) $searchQuery, ENT_QUOTES) ?>"
        >
        <button type="submit" class="btn-primary">Search</button>
        <?php if (!empty($searchQuery)): ?>
            <a href="/index.php?page=admin-profiles" class="btn-secondary">Clear</a>
        <?php endif; ?>
    </form>
    <table class="table">
        <thead>
            <tr>
                <th>Profile</th>
                <th>Description</th>
                <th class="status-col">Status</th>
                <th class="actions">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($profiles === []): ?>
                <tr>
                    <td colspan="4" style="text-align: center;">No profiles found.</td>
                </tr>
            <?php endif; ?>
            <?php foreach ($profiles as $profile): ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars(ucwords(str_replace('_', ' ', (string) $profile['role'])), ENT_QUOTES) ?></strong>
                        <div><small>Identifier: <?= htmlspecialchars((string) $profile['role'], ENT_QUOTES) ?></small></div>
                    </td>
                    <td><?= htmlspecialchars((string) $profile['description'], ENT_QUOTES) ?></td>
                    <td class="status-col"><span class="tag tag-<?= htmlspecialchars((string) $profile['status'], ENT_QUOTES) ?>"><?= htmlspecialchars((string) $profile['status'], ENT_QUOTES) ?></span></td>
                    <td class="actions"><span class="muted">No actions</span></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php include __DIR__ . '/../../shared/boundary/footer.php'; ?>
