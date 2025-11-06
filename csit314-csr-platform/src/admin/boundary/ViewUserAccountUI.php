<?php
require_once __DIR__ . '/../controller/viewAccountsController.php';
require_once __DIR__ . '/../../shared/entity/UserAccount.php';

use CSRPlatform\Admin\Controller\viewAccountsController;
use CSRPlatform\Shared\Entity\UserAccount;

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

$userId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($userId <= 0) {
    $_SESSION['flash_error'] = 'Unable to find the requested account.';
    header('Location: /index.php?page=admin-accounts');
    exit();
}

$controller = new viewAccountsController(new UserAccount());
$account = $controller->viewUserAccount($userId);
if ($account === null) {
    $_SESSION['flash_error'] = 'Account not found.';
    header('Location: /index.php?page=admin-accounts');
    exit();
}

$pageTitle = 'View account';
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
            <h1>Account details</h1>
            <p class="muted">Full view of <?= htmlspecialchars((string) ($account['name'] ?? 'the selected user'), ENT_QUOTES) ?>.</p>
        </div>
        <div class="card-actions">
            <a class="btn-secondary" href="/index.php?page=admin-accounts">Back to list</a>
            <a class="btn-primary" href="/index.php?page=admin-account-edit&amp;id=<?= (int) $account['id'] ?>">Edit account</a>
        </div>
    </div>
    <dl class="definition-list">
        <dt>Name</dt>
        <dd><?= htmlspecialchars((string) $account['name'], ENT_QUOTES) ?></dd>
        <dt>Email</dt>
        <dd><?= htmlspecialchars((string) $account['email'], ENT_QUOTES) ?></dd>
        <dt>Role</dt>
        <dd><?= htmlspecialchars(ucwords(str_replace('_', ' ', (string) ($account['role'] ?? ''))), ENT_QUOTES) ?></dd>
        <dt>Status</dt>
        <dd><span class="tag tag-<?= htmlspecialchars((string) $account['status'], ENT_QUOTES) ?>"><?= htmlspecialchars((string) $account['status'], ENT_QUOTES) ?></span></dd>
        <dt>Created at</dt>
        <dd><?= htmlspecialchars((string) ($account['created_at'] ?? ''), ENT_QUOTES) ?></dd>
        <dt>Updated at</dt>
        <dd><?= htmlspecialchars((string) ($account['updated_at'] ?? ''), ENT_QUOTES) ?></dd>
    </dl>
</section>
<?php include __DIR__ . '/../../shared/boundary/footer.php'; ?>
