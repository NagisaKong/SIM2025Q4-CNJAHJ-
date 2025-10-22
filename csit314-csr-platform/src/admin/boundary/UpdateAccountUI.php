<?php
declare(strict_types=1);

require_once __DIR__ . '/../../shared/bootstrap.php';
require_once __DIR__ . '/../controller/updateAccountController.php';
require_once __DIR__ . '/../controller/viewProfilesController.php';
include __DIR__ . '/../../shared/boundary/header.php';

function logout(): void
{
    session_destroy();
    header('Location: /public/index.php?route=login');
    exit();
}

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    logout();
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$controller = new UpdateAccountController();
$user = $id ? $controller->find($id) : [];
$profilesController = new ViewProfilesController();
$profiles = $profilesController->list();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $id) {
    if ($controller->update($id, $_POST)) {
        header('Location: /public/index.php?route=admin/view-accounts');
        exit();
    }
}
?>
<section class="card">
    <h1>Edit User</h1>
    <?php if (!empty($_SESSION['registration_message'])): ?>
        <div class="alert alert-info"><?= htmlspecialchars($_SESSION['registration_message'], ENT_QUOTES) ?></div>
        <?php unset($_SESSION['registration_message']); ?>
    <?php endif; ?>
    <form method="POST" action="/public/index.php?route=admin/update-account&id=<?= (int) $id ?>" class="form">
        <label>Name
            <input type="text" value="<?= htmlspecialchars($user['name'] ?? '', ENT_QUOTES) ?>" disabled>
        </label>
        <label>Email
            <input type="email" name="email" value="<?= htmlspecialchars((string) ($user['email'] ?? ''), ENT_QUOTES) ?>" required>
        </label>
        <label>Role
            <select name="role" required>
                <?php foreach ($profiles as $profile): ?>
                    <option value="<?= htmlspecialchars($profile['role'] ?? '', ENT_QUOTES) ?>" <?= (($user['role'] ?? '') === ($profile['role'] ?? '')) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($profile['role'] ?? '', ENT_QUOTES) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Status
            <select name="status">
                <option value="active" <?= (($user['status'] ?? '') === 'active') ? 'selected' : '' ?>>Active</option>
                <option value="suspended" <?= (($user['status'] ?? '') === 'suspended') ? 'selected' : '' ?>>Suspended</option>
            </select>
        </label>
        <button type="submit" class="btn-primary">Save</button>
        <a href="/public/index.php?route=admin/view-accounts" class="btn-secondary">Cancel</a>
    </form>
</section>
<?php include __DIR__ . '/../../shared/boundary/footer.php'; ?>
