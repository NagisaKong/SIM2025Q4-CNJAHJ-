<?php
require_once __DIR__ . '/../controller/createAccountController.php';
require_once __DIR__ . '/../../shared/entity/UserProfiles.php';
require_once __DIR__ . '/../../shared/boundary/FormValidator.php';

use CSRPlatform\Admin\Controller\createAccountController;
use CSRPlatform\Shared\Entity\UserAccount;
use CSRPlatform\Shared\Entity\UserProfiles;
use CSRPlatform\Shared\Boundary\FormValidator;

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
    header('Location: /index.php?page=login');
    exit();
}

$accounts = new UserAccount();
$profiles = new UserProfiles();
$validator = new FormValidator();
$createController = new createAccountController($accounts, $profiles, $validator);

$availableProfiles = $profiles->listProfiles('active');
if ($availableProfiles === []) {
    $availableProfiles = [
        ['role' => 'admin'],
        ['role' => 'csr'],
        ['role' => 'pin'],
        ['role' => 'pm'],
    ];
}

$formValues = [
    'name' => '',
    'email' => '',
    'role' => $availableProfiles[0]['role'] ?? 'admin',
    'status' => 'active',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payload = [
        'name' => (string) ($_POST['name'] ?? ''),
        'email' => (string) ($_POST['email'] ?? ''),
        'role' => (string) ($_POST['role'] ?? ($formValues['role'] ?? 'admin')),
        'status' => (string) ($_POST['status'] ?? 'active'),
        'password' => (string) ($_POST['password'] ?? ''),
    ];

    $formValues = array_merge($formValues, [
        'name' => $payload['name'],
        'email' => $payload['email'],
        'role' => $payload['role'],
        'status' => $payload['status'],
    ]);

    if ($createController->createUserAccount(
        $payload['role'],
        $payload['name'],
        $payload['email'],
        $payload['password'],
        $payload['status']
    )) {
        $_SESSION['flash_success'] = 'Account created successfully.';
        header('Location: /index.php?page=admin-accounts');
        exit();
    }

    $errors = $createController->errors();
    $_SESSION['flash_error'] = implode(' ', array_map(static fn($messages) => implode(' ', (array) $messages), $errors));
}

$pageTitle = 'Create user account';
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
            <h1>Create User</h1>
            <p class="muted">Complete the details below to add a new platform account.</p>
        </div>
        <div class="card-actions">
            <a class="btn-secondary" href="/index.php?page=admin-accounts">Back to list</a>
        </div>
    </div>
    <form method="POST" action="/index.php?page=admin-account-create" class="form-grid">
        <label>Name
            <input type="text" name="name" value="<?= htmlspecialchars($formValues['name'], ENT_QUOTES) ?>" required>
        </label>
        <label>Email
            <input type="email" name="email" value="<?= htmlspecialchars($formValues['email'], ENT_QUOTES) ?>" required>
        </label>
        <label>Password
            <input type="password" name="password" required placeholder="Must include letters and numbers">
        </label>
        <label>Profile
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
        <button type="submit" class="btn-primary">Create account</button>
    </form>
</section>
<?php include __DIR__ . '/../../shared/boundary/footer.php'; ?>
