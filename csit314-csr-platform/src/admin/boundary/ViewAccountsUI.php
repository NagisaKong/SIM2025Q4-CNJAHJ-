<?php
require_once __DIR__ . '/../controller/viewAccountsController.php';
require_once __DIR__ . '/../controller/updateAccountController.php';

use CSRPlatform\Admin\Controller\updateAccountController;
use CSRPlatform\Admin\Controller\viewAccountsController;
use CSRPlatform\Shared\Entity\UserAccount;
use CSRPlatform\Shared\Entity\UserProfiles;
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
if (!$currentUser || ($currentUser['role'] ?? '') !== 'admin') {
    header('Location: /index.php?page=login');
    exit();
}

$accounts = new UserAccount();
$profiles = new UserProfiles();
$validator = new Validation();
$viewController = new viewAccountsController($accounts);
$updateController = new updateAccountController($accounts, $validator);
$availableProfiles = $profiles->listProfiles('active');
if ($availableProfiles === []) {
    $availableProfiles = [
        ['role' => 'admin'],
        ['role' => 'csr'],
        ['role' => 'pin'],
        ['role' => 'pm'],
    ];
}
$defaultRole = $availableProfiles[0]['role'] ?? 'admin';

$editingId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
$editingUser = $editingId > 0 ? $viewController->find($editingId) : null;
$formValues = [
    'name' => '',
    'email' => '',
    'role' => (string) $defaultRole,
    'status' => 'active',
];

if ($editingUser) {
    $formValues = array_merge($formValues, [
        'name' => (string) ($editingUser['name'] ?? ''),
        'email' => (string) ($editingUser['email'] ?? ''),
        'role' => (string) ($editingUser['role'] ?? 'admin'),
        'status' => (string) ($editingUser['status'] ?? 'active'),
    ]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formType = $_POST['form_type'] ?? '';
    if ($formType === 'update') {
        $userId = (int) ($_POST['user_id'] ?? 0);
        if ($userId > 0 && $updateController->update($userId, $_POST)) {
            $_SESSION['flash_success'] = 'Account updated successfully.';
            header('Location: /index.php?page=admin-accounts');
            exit();
        }
        $_SESSION['flash_error'] = implode(' ', array_map(static fn($errors) => implode(' ', (array) $errors), $updateController->errors()));
        $editingId = $userId;
        $editingUser = $viewController->find($userId);
        $formValues = array_merge($formValues, [
            'name' => (string) ($_POST['name'] ?? ($editingUser['name'] ?? '')),
            'email' => (string) ($_POST['email'] ?? ($editingUser['email'] ?? '')),
            'role' => (string) ($_POST['role'] ?? ($editingUser['role'] ?? 'admin')),
            'status' => (string) ($_POST['status'] ?? ($editingUser['status'] ?? 'active')),
        ]);
    }
}

$filterRole = $_GET['role'] ?? null;
$normalizedRole = $filterRole !== null ? strtolower((string) $filterRole) : 'all';
$searchQuery = $_GET['q'] ?? null;
$users = $viewController->list($filterRole, $searchQuery);

$pageTitle = 'Manage users';
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
            <th class="actions">Actions</th>
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
                    <a class="link-button" href="/index.php?page=admin-accounts&amp;edit=<?= (int) $user['id'] ?>#account-form">Edit</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php if ($editingUser): ?>
<section class="card" id="account-form">
    <div class="card-heading">
        <div>
            <h2>Edit Account</h2>
            <p class="muted">Update the selected account details.</p>
        </div>
        <div class="card-actions">
            <a class="btn-secondary" href="/index.php?page=admin-accounts">Cancel</a>
        </div>
    </div>
    <form method="POST" action="/index.php?page=admin-accounts" class="form-grid">
        <input type="hidden" name="form_type" value="update">
        <input type="hidden" name="user_id" value="<?= (int) $editingUser['id'] ?>">
        <label>Name
            <input type="text" name="name" value="<?= htmlspecialchars($formValues['name'], ENT_QUOTES) ?>" required>
        </label>
        <label>Email
            <input type="email" name="email" value="<?= htmlspecialchars($formValues['email'], ENT_QUOTES) ?>" required>
        </label>
        <label>Password
            <input type="password" name="password" placeholder="Leave blank to keep current password">
        </label>
        <label>Role
            <select name="role" required>
                <?php foreach ($availableProfiles as $profileOption): ?>
                    <?php $profileRole = (string) ($profileOption['role'] ?? ''); ?>
                    <option value="<?= htmlspecialchars($profileRole, ENT_QUOTES) ?>" <?= strtolower($formValues['role']) === strtolower($profileRole) ? 'selected' : '' ?>>
                        <?= htmlspecialchars(ucwords(str_replace('_', ' ', $profileRole)), ENT_QUOTES) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Status
            <select name="status">
                <option value="active" <?= $formValues['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="suspended" <?= $formValues['status'] === 'suspended' ? 'selected' : '' ?>>Suspended</option>
            </select>
        </label>
        <button type="submit" class="btn-primary">Update Account</button>
    </form>
</section>
<?php endif; ?>
<?php include __DIR__ . '/../../shared/boundary/footer.php'; ?>
