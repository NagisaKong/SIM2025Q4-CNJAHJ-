<?php
require_once __DIR__ . '/../controller/updateProfileController.php';
require_once __DIR__ . '/../../shared/entity/UserProfiles.php';
require_once __DIR__ . '/../../shared/utils/Validation.php';

use CSRPlatform\Admin\Controller\updateProfileController;
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

$profileId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($profileId <= 0) {
    $_SESSION['flash_error'] = 'Unable to find the requested profile.';
    header('Location: /index.php?page=admin-profiles');
    exit();
}

$profiles = new UserProfiles();
$profile = $profiles->findById($profileId);
if ($profile === null) {
    $_SESSION['flash_error'] = 'Profile not found.';
    header('Location: /index.php?page=admin-profiles');
    exit();
}

$controller = new updateProfileController($profiles, new Validation());
$formValues = [
    'role' => (string) ($profile['role'] ?? ''),
    'description' => (string) ($profile['description'] ?? ''),
    'status' => (string) ($profile['status'] ?? 'active'),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payload = [
        'role' => $_POST['role'] ?? '',
        'description' => $_POST['description'] ?? '',
        'status' => $_POST['status'] ?? '',
    ];
    $formValues = [
        'role' => (string) $payload['role'],
        'description' => (string) $payload['description'],
        'status' => (string) $payload['status'],
    ];

    if ($controller->update($profileId, $payload)) {
        $_SESSION['flash_success'] = 'Profile updated successfully.';
        header('Location: /index.php?page=admin-profile-view&id=' . $profileId);
        exit();
    }

    $errorMessages = array_map(static fn($messages) => implode(' ', (array) $messages), $controller->errors());
    $_SESSION['flash_error'] = implode(' ', $errorMessages) ?: 'Unable to update profile.';
}

$pageTitle = 'Edit profile';
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
            <h1>Edit profile</h1>
            <p class="muted">Adjust permissions and status for this role.</p>
        </div>
        <div class="card-actions">
            <a class="btn-secondary" href="/index.php?page=admin-profile-view&amp;id=<?= (int) $profile['id'] ?>">Cancel</a>
        </div>
    </div>
    <form method="POST" action="/index.php?page=admin-profile-edit&amp;id=<?= (int) $profile['id'] ?>" class="form-grid">
        <label>Identifier
            <input type="text" name="role" value="<?= htmlspecialchars($formValues['role'], ENT_QUOTES) ?>" required>
        </label>
        <label>Profile permissions
            <textarea name="description" rows="5" required><?= htmlspecialchars($formValues['description'], ENT_QUOTES) ?></textarea>
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
