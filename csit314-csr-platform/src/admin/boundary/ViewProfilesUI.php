<?php
require_once __DIR__ . '/../controller/viewProfilesController.php';
require_once __DIR__ . '/../../shared/entity/UserProfiles.php';

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
$roleKey = strtolower((string) ($currentUser['role'] ?? ''));
if ($roleKey !== 'admin') {
    if (!isset($_SESSION['flash_error'])) {
        $_SESSION['flash_error'] = 'You need an administrator session to manage profiles.';
    }

    if ($currentUser) {
        header('Location: /index.php?page=dashboard');
    } else {
        header('Location: /index.php?page=login');
    }
    exit();
}

$profilesEntity = new UserProfiles();
$viewController = new viewProfilesController($profilesEntity);

$searchQuery = $_GET['q'] ?? null;
$profiles = $viewController->viewUserProfileList(null, $searchQuery);

$pageTitle = 'Manage profiles';
$navLinks = [
    ['href' => '/index.php?page=dashboard', 'label' => 'Dashboard'],
    ['href' => '/index.php?page=admin-accounts', 'label' => 'Users'],
    ['href' => '/index.php?page=admin-profiles', 'label' => 'Profiles'],
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
                <th>Permissions</th>
                <th class="status-col">Status</th>
                <th class="actions">Action</th>
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
                    </td>
                    <td><?= htmlspecialchars((string) $profile['description'], ENT_QUOTES) ?></td>
                    <td class="status-col"><span class="tag tag-<?= htmlspecialchars((string) $profile['status'], ENT_QUOTES) ?>"><?= htmlspecialchars((string) $profile['status'], ENT_QUOTES) ?></span></td>
                    <td class="actions">
                        <a class="link-button" href="/index.php?page=admin-profile-edit&amp;id=<?= (int) $profile['id'] ?>">Edit</a>
                        <a class="link-button" href="/index.php?page=admin-profile-view&amp;id=<?= (int) $profile['id'] ?>">View</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php include __DIR__ . '/../../shared/boundary/footer.php'; ?>
