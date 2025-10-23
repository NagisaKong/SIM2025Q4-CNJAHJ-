<?php
require_once __DIR__ . '/../../shared/entity/UserProfiles.php';

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

$profileId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($profileId <= 0) {
    $_SESSION['flash_error'] = 'Unable to find the requested profile.';
    header('Location: /index.php?page=admin-profiles');
    exit();
}

$profilesEntity = new UserProfiles();
$profile = $profilesEntity->findById($profileId);

if ($profile === null) {
    $_SESSION['flash_error'] = 'Profile not found.';
    header('Location: /index.php?page=admin-profiles');
    exit();
}

$pageTitle = 'View profile';
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
            <h1>Profile details</h1>
            <p class="muted">Permissions and metadata for <?= htmlspecialchars(ucwords(str_replace('_', ' ', (string) $profile['role'])), ENT_QUOTES) ?>.</p>
        </div>
        <div class="card-actions">
            <a class="btn-secondary" href="/index.php?page=admin-profiles">Back to profiles</a>
            <a class="btn-primary" href="/index.php?page=admin-profile-edit&amp;id=<?= (int) $profile['id'] ?>">Edit profile</a>
        </div>
    </div>
    <dl class="definition-list">
        <dt>Identifier</dt>
        <dd><?= htmlspecialchars((string) $profile['role'], ENT_QUOTES) ?></dd>
        <dt>Profile permissions</dt>
        <dd><?= nl2br(htmlspecialchars((string) $profile['description'], ENT_QUOTES)) ?></dd>
        <dt>Status</dt>
        <dd><span class="tag tag-<?= htmlspecialchars((string) $profile['status'], ENT_QUOTES) ?>"><?= htmlspecialchars((string) $profile['status'], ENT_QUOTES) ?></span></dd>
        <dt>Created at</dt>
        <dd><?= htmlspecialchars((string) ($profile['created_at'] ?? ''), ENT_QUOTES) ?></dd>
        <dt>Updated at</dt>
        <dd><?= htmlspecialchars((string) ($profile['updated_at'] ?? ''), ENT_QUOTES) ?></dd>
    </dl>
</section>
<?php include __DIR__ . '/../../shared/boundary/footer.php'; ?>
