<?php
require_once __DIR__ . '/../controller/viewAccountsController.php';
require_once __DIR__ . '/../../shared/entity/UserAccount.php';
require_once __DIR__ . '/../../shared/entity/UserProfiles.php';

use CSRPlatform\Admin\Controller\viewAccountsController;
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

$accounts = new UserAccount();
$profiles = new UserProfiles();
$viewController = new viewAccountsController($accounts);
$availableProfiles = $profiles->listProfiles('active');
if ($availableProfiles === []) {
    $availableProfiles = $profiles->listProfiles('all');
}
if ($availableProfiles === []) {
    $availableProfiles = [
        ['role' => 'admin'],
        ['role' => 'csr'],
        ['role' => 'pin'],
        ['role' => 'pm'],
    ];
}

$filterRole = $_GET['role'] ?? null;
$normalizedRole = $filterRole !== null ? strtolower((string) $filterRole) : 'all';
$searchQuery = $_GET['q'] ?? null;
$users = $viewController->viewUserAccountList($filterRole, $searchQuery);

$pageTitle = 'Manage users';
$navLinks = [
    ['href' => '/index.php?page=dashboard', 'label' => 'Dashboard'],
    ['href' => '/index.php?page=admin-accounts', 'label' => 'Users'],
    ['href' => '/index.php?page=admin-profiles', 'label' => 'Profiles'],
    ['href' => '/index.php?page=pm-categories', 'label' => 'Categories'],
];
include __DIR__ . '/../../shared/boundary/header.php';
?>
<section class="card">
    <div class="card-heading">
        <div>
            <h1>User Accounts</h1>
            <p class="muted">Review and maintain every user account across the platform.</p>
        </div>
        <div class="card-actions">
            <a class="btn-primary" href="/index.php?page=admin-account-create">New User</a>
        </div>
    </div>
    <form class="form-inline" method="GET" action="/index.php">
        <input type="hidden" name="page" value="admin-accounts">
        <input type="text" name="q" placeholder="Search email or name" value="<?= htmlspecialchars((string) $searchQuery, ENT_QUOTES) ?>">
        <select name="role">
            <option value="all" <?= $normalizedRole === 'all' ? 'selected' : '' ?>>All roles</option>
            <?php foreach ($availableProfiles as $profileOption): ?>
                <?php $profileRole = (string) ($profileOption['role'] ?? ''); ?>
                <option value="<?= htmlspecialchars($profileRole, ENT_QUOTES) ?>" <?= $normalizedRole === strtolower($profileRole) ? 'selected' : '' ?>>
                    <?= htmlspecialchars(ucwords(str_replace('_', ' ', $profileRole)), ENT_QUOTES) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn-secondary">Search</button>
    </form>
    <table class="table">
        <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th class="status-col">Status</th>
            <th>Role</th>
            <th class="actions">Action</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?= htmlspecialchars((string) $user['name'], ENT_QUOTES) ?></td>
                <td><?= htmlspecialchars((string) $user['email'], ENT_QUOTES) ?></td>
                <td class="status-col"><span class="tag tag-<?= htmlspecialchars((string) $user['status'], ENT_QUOTES) ?>"><?= htmlspecialchars((string) $user['status'], ENT_QUOTES) ?></span></td>
                <td><?= htmlspecialchars(ucwords(str_replace('_', ' ', (string) ($user['role'] ?? 'N/A'))), ENT_QUOTES) ?></td>
                <td class="actions">
                    <a class="link-button" href="/index.php?page=admin-account-edit&amp;id=<?= (int) $user['id'] ?>">Edit</a>
                    <a class="link-button" href="/index.php?page=admin-account-view&amp;id=<?= (int) $user['id'] ?>">View</a>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if ($users === []): ?>
            <tr>
                <td colspan="5" style="text-align: center;">No accounts found.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</section>
<?php include __DIR__ . '/../../shared/boundary/footer.php'; ?>
