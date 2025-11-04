<?php
require_once __DIR__ . '/../controller/createProfileController.php';
require_once __DIR__ . '/../../shared/entity/UserProfiles.php';
require_once __DIR__ . '/../../shared/boundary/FormValidator.php';

use CSRPlatform\Admin\Controller\createProfileController;
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

$profiles = new UserProfiles();
$validator = new FormValidator();
$createController = new createProfileController($profiles, $validator);

$formValues = [
    'role' => '',
    'description' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payload = [
        'role' => (string) ($_POST['role'] ?? ''),
        'description' => (string) ($_POST['description'] ?? ''),
        'status' => 'active',
    ];

    $formValues = array_merge($formValues, [
        'role' => $payload['role'],
        'description' => $payload['description'],
    ]);

    if ($createController->createUserProfile($payload['role'], $payload['description'], $payload['status'])) {
        $_SESSION['flash_success'] = 'Profile created successfully.';
        header('Location: /index.php?page=admin-profiles');
        exit();
    }

    $errors = $createController->errors();
    $_SESSION['flash_error'] = implode(' ', array_map(static fn($messages) => implode(' ', (array) $messages), $errors));
}

$pageTitle = 'Create profile';
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
            <h1>Create Profile</h1>
            <p class="muted">Define a new role and outline its capabilities.</p>
        </div>
        <div class="card-actions">
            <a class="btn-secondary" href="/index.php?page=admin-profiles">Back to profiles</a>
        </div>
    </div>
    <form method="POST" action="/index.php?page=admin-profile-create" class="form-grid">
        <label>Identifier
            <input type="text" name="role" placeholder="e.g. volunteer" value="<?= htmlspecialchars($formValues['role'], ENT_QUOTES) ?>" required>
        </label>
        <label>Description
            <textarea name="description" rows="4" required placeholder="Describe the permissions granted to this profile."><?= htmlspecialchars($formValues['description'], ENT_QUOTES) ?></textarea>
        </label>
        <button type="submit" class="btn-primary">Create profile</button>
    </form>
</section>
<?php include __DIR__ . '/../../shared/boundary/footer.php'; ?>
