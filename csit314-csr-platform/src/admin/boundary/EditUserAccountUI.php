<?php
require_once __DIR__ . '/../controller/updateAccountController.php';
require_once __DIR__ . '/../controller/viewAccountsController.php';
require_once __DIR__ . '/../../shared/entity/UserAccount.php';
require_once __DIR__ . '/../../shared/entity/UserProfiles.php';
require_once __DIR__ . '/../../shared/utils/Validation.php';

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

$userId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($userId <= 0) {
    $_SESSION['flash_error'] = 'Unable to find the requested account.';
    header('Location: /index.php?page=admin-accounts');
    exit();
}

$accounts = new UserAccount();
$profiles = new UserProfiles();
$viewController = new viewAccountsController($accounts);
$updateController = new updateAccountController($accounts, new Validation());
$availableProfiles = $profiles->listProfiles('active');
if ($availableProfiles === []) {
    $availableProfiles = $profiles->listProfiles('all');
}

$account = $viewController->viewUserAccount($userId);
if ($account === null) {
    $_SESSION['flash_error'] = 'Account not found.';
    header('Location: /index.php?page=admin-accounts');
    exit();
}

$formValues = [
    'name' => (string) ($account['name'] ?? ''),
    'email' => (string) ($account['email'] ?? ''),
    'role' => (string) ($account['role'] ?? ''),
    'status' => (string) ($account['status'] ?? 'active'),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payload = [
        'name' => $_POST['name'] ?? '',
        'email' => $_POST['email'] ?? '',
        'password' => $_POST['password'] ?? '',
        'role' => $_POST['role'] ?? '',
        'status' => $_POST['status'] ?? '',
    ];
    $formValues = array_merge($formValues, [
        'name' => (string) $payload['name'],
        'email' => (string) $payload['email'],
        'role' => (string) $payload['role'],
        'status' => (string) $payload['status'],
    ]);

    if ($updateController->updateUserAccount(
        $userId,
        (string) $payload['name'],
        (string) $payload['email'],
        (string) $payload['password'],
        (string) $payload['status'],
        (string) $payload['role']
    )) {
        $_SESSION['flash_success'] = 'Account updated successfully.';
        header('Location: /index.php?page=admin-account-view&id=' . $userId);
        exit();
    }

    $errorMessages = array_map(static fn($messages) => implode(' ', (array) $messages), $updateController->errors());
    $_SESSION['flash_error'] = implode(' ', $errorMessages) ?: 'Unable to update account.';
}

$pageTitle = 'Edit account';
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
            <h1>Edit account</h1>
            <p class="muted">Update details for <?= htmlspecialchars((string) ($account['name'] ?? ''), ENT_QUOTES) ?>.</p>
        </div>
        <div class="card-actions">
            <a class="btn-secondary" href="/index.php?page=admin-account-view&amp;id=<?= (int) $account['id'] ?>">Cancel</a>
        </div>
    </div>
    <form method="POST" action="/index.php?page=admin-account-edit&amp;id=<?= (int) $account['id'] ?>" class="form-grid">
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
                <?php foreach ($availableProfiles as $profile): ?>
                    <?php $role = (string) ($profile['role'] ?? ''); ?>
                    <option value="<?= htmlspecialchars($role, ENT_QUOTES) ?>" <?= strtolower($role) === strtolower($formValues['role']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars(ucwords(str_replace('_', ' ', $role)), ENT_QUOTES) ?>
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
        <button type="submit" class="btn-primary">Save changes</button>
    </form>
</section>
<?php include __DIR__ . '/../../shared/boundary/footer.php'; ?>
