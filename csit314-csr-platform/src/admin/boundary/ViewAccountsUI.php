<?php
require_once __DIR__ . '/../controller/viewAccountsController.php';
require_once __DIR__ . '/../controller/createAccountController.php';
require_once __DIR__ . '/../controller/updateAccountController.php';

use CSRPlatform\Admin\Controller\createAccountController;
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

$accounts = new UserAccount();
$profiles = new UserProfiles();
$validator = new Validation();
$viewController = new viewAccountsController($accounts);
$createController = new createAccountController($accounts, $profiles, $validator);
$updateController = new updateAccountController($accounts, $validator);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formType = $_POST['form_type'] ?? '';
    if ($formType === 'create') {
        if ($createController->create($_POST)) {
            $_SESSION['flash_success'] = 'Account created successfully.';
            header('Location: /index.php?page=admin-accounts');
            exit();
        }
        $_SESSION['flash_error'] = implode(' ', array_map(static fn($errors) => implode(' ', (array) $errors), $createController->errors()));
    } elseif ($formType === 'update') {
        $userId = (int) ($_POST['user_id'] ?? 0);
        if ($userId > 0 && $updateController->update($userId, $_POST)) {
            $_SESSION['flash_success'] = 'Account updated successfully.';
            header('Location: /index.php?page=admin-accounts');
            exit();
        }
        $_SESSION['flash_error'] = implode(' ', array_map(static fn($errors) => implode(' ', (array) $errors), $updateController->errors()));
    } elseif ($formType === 'suspend') {
        $userId = (int) ($_POST['user_id'] ?? 0);
        if ($userId > 0 && $viewController->suspend($userId)) {
            $_SESSION['flash_warning'] = 'Account suspended.';
        }
        header('Location: /index.php?page=admin-accounts');
        exit();
    }
}

$filterRole = $_GET['role'] ?? null;
$searchQuery = $_GET['q'] ?? null;
$users = $viewController->list($filterRole, $searchQuery);
$editingId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
$editingUser = $editingId > 0 ? $viewController->find($editingId) : null;

$pageTitle = 'User Accounts';
$navLinks = [
    ['href' => '/index.php?page=admin-accounts', 'label' => 'Accounts'],
    ['href' => '/index.php?page=admin-profiles', 'label' => 'Profiles'],
];
include __DIR__ . '/../../shared/boundary/header.php';
?>
<section class="card">
    <div class="card-header">
        <h1>User Accounts</h1>
        <p>Manage system user accounts and their access.</p>
    </div>
    <form class="form-inline" method="GET" action="/index.php">
        <input type="hidden" name="page" value="admin-accounts">
        <input type="text" name="q" placeholder="Search email or name" value="<?= htmlspecialchars((string) $searchQuery, ENT_QUOTES) ?>">
        <select name="role">
            <option value="all">All roles</option>
            <option value="admin" <?= $filterRole === 'admin' ? 'selected' : '' ?>>Administrator</option>
            <option value="csr" <?= $filterRole === 'csr' ? 'selected' : '' ?>>CSR Representative</option>
            <option value="pin" <?= $filterRole === 'pin' ? 'selected' : '' ?>>Person in Need</option>
            <option value="pm" <?= $filterRole === 'pm' ? 'selected' : '' ?>>Project Manager</option>
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
                <td><?= htmlspecialchars((string) ($user['role'] ?? 'N/A'), ENT_QUOTES) ?></td>
                <td class="actions">
                    <a href="/index.php?page=admin-accounts&amp;edit=<?= (int) $user['id'] ?>">Edit</a>
                    <?php if (($user['status'] ?? '') !== 'suspended'): ?>
                    <form method="POST" action="/index.php?page=admin-accounts" class="inline-form">
                        <input type="hidden" name="form_type" value="suspend">
                        <input type="hidden" name="user_id" value="<?= (int) $user['id'] ?>">
                        <button type="submit" class="link-button">Suspend</button>
                    </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
<section class="card">
    <div class="card-header">
        <h2><?= $editingUser ? 'Edit account' : 'Create new account' ?></h2>
    </div>
    <form method="POST" action="/index.php?page=admin-accounts" class="form-grid">
        <input type="hidden" name="form_type" value="<?= $editingUser ? 'update' : 'create' ?>">
        <?php if ($editingUser): ?>
            <input type="hidden" name="user_id" value="<?= (int) $editingUser['id'] ?>">
        <?php endif; ?>
        <label>Name
            <input type="text" name="name" value="<?= htmlspecialchars((string) ($editingUser['name'] ?? ''), ENT_QUOTES) ?>" required>
        </label>
        <label>Email
            <input type="email" name="email" value="<?= htmlspecialchars((string) ($editingUser['email'] ?? ''), ENT_QUOTES) ?>" required>
        </label>
        <label>Password
            <input type="password" name="password" <?= $editingUser ? '' : 'required' ?> placeholder="<?= $editingUser ? 'Leave blank to keep current password' : '' ?>">
        </label>
        <label>Role
            <select name="role" required>
                <option value="admin" <?= ($editingUser['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Administrator</option>
                <option value="csr" <?= ($editingUser['role'] ?? '') === 'csr' ? 'selected' : '' ?>>CSR Representative</option>
                <option value="pin" <?= ($editingUser['role'] ?? '') === 'pin' ? 'selected' : '' ?>>Person in Need</option>
                <option value="pm" <?= ($editingUser['role'] ?? '') === 'pm' ? 'selected' : '' ?>>Project Manager</option>
            </select>
        </label>
        <label>Status
            <select name="status">
                <option value="active" <?= ($editingUser['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="suspended" <?= ($editingUser['status'] ?? '') === 'suspended' ? 'selected' : '' ?>>Suspended</option>
            </select>
        </label>
        <button type="submit" class="btn-primary"><?= $editingUser ? 'Update account' : 'Create account' ?></button>
    </form>
</section>
<?php include __DIR__ . '/../../shared/boundary/footer.php'; ?>
