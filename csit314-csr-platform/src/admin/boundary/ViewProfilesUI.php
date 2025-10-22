<?php
require_once __DIR__ . '/../controller/viewProfilesController.php';
require_once __DIR__ . '/../controller/createProfileController.php';
require_once __DIR__ . '/../controller/suspendProfileController.php';

use CSRPlatform\Admin\Controller\createProfileController;
use CSRPlatform\Admin\Controller\suspendProfileController;
use CSRPlatform\Admin\Controller\viewProfilesController;
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

$profilesEntity = new UserProfiles();
$validator = new Validation();
$viewController = new viewProfilesController($profilesEntity);
$createController = new createProfileController($profilesEntity, $validator);
$suspendController = new suspendProfileController($profilesEntity);

$showCreateForm = isset($_GET['create']);
$formValues = [
    'role' => '',
    'description' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formType = $_POST['form_type'] ?? '';
    if ($formType === 'create') {
        if ($createController->create($_POST)) {
            $_SESSION['flash_success'] = 'Profile created successfully.';
            header('Location: /index.php?page=admin-profiles');
            exit();
        }
        $_SESSION['flash_error'] = implode(' ', array_map(static fn($errors) => implode(' ', (array) $errors), $createController->errors()));
        $showCreateForm = true;
        $formValues = array_merge($formValues, [
            'role' => (string) ($_POST['role'] ?? ''),
            'description' => (string) ($_POST['description'] ?? ''),
        ]);
    } elseif ($formType === 'suspend') {
        $profileId = (int) ($_POST['profile_id'] ?? 0);
        if ($profileId > 0 && $suspendController->suspend($profileId)) {
            $_SESSION['flash_warning'] = 'Profile suspended.';
        }
        header('Location: /index.php?page=admin-profiles');
        exit();
    }
}

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
            <a class="btn-primary" href="/index.php?page=admin-profiles&amp;create=1#profile-form">New Profile</a>
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
                    <td class="actions">
                        <?php if (($profile['status'] ?? '') !== 'suspended'): ?>
                        <form method="POST" action="/index.php?page=admin-profiles" class="inline-form">
                            <input type="hidden" name="form_type" value="suspend">
                            <input type="hidden" name="profile_id" value="<?= (int) $profile['id'] ?>">
                            <button type="submit" class="link-button">Suspend</button>
                        </form>
                        <?php else: ?>
                            <em>Suspended</em>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php if ($showCreateForm): ?>
<section class="card" id="profile-form">
    <div class="card-heading">
        <div>
            <h2>Create New Profile</h2>
            <p class="muted">Assign a role identifier and outline its capabilities.</p>
        </div>
        <div class="card-actions">
            <a class="btn-secondary" href="/index.php?page=admin-profiles">Cancel</a>
        </div>
    </div>
    <form method="POST" action="/index.php?page=admin-profiles" class="form-grid">
        <input type="hidden" name="form_type" value="create">
        <label>Role identifier
            <input type="text" name="role" placeholder="e.g. volunteer" value="<?= htmlspecialchars($formValues['role'], ENT_QUOTES) ?>" required>
        </label>
        <label>Description
            <textarea name="description" rows="3" required placeholder="Describe this profile's permissions"><?= htmlspecialchars($formValues['description'], ENT_QUOTES) ?></textarea>
        </label>
        <button type="submit" class="btn-primary">Create Profile</button>
    </form>
</section>
<?php endif; ?>
<?php include __DIR__ . '/../../shared/boundary/footer.php'; ?>
