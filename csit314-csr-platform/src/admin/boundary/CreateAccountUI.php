<?php
declare(strict_types=1);

require_once __DIR__ . '/../../shared/bootstrap.php';
require_once __DIR__ . '/../controller/createAccountController.php';
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

$profilesController = new ViewProfilesController();
$profiles = $profilesController->list();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new CreateAccountController();
    if ($controller->create($_POST)) {
        header('Location: /public/index.php?route=admin/view-accounts');
        exit();
    }
}
?>
<section class="card">
    <h1>Create User</h1>
    <?php if (!empty($_SESSION['registration_message'])): ?>
        <div class="alert alert-error"><?= htmlspecialchars($_SESSION['registration_message'], ENT_QUOTES) ?></div>
        <?php unset($_SESSION['registration_message']); ?>
    <?php endif; ?>
    <form method="POST" action="/public/index.php?route=admin/create-account" class="form">
        <label>Name
            <input type="text" name="name" value="<?= htmlspecialchars((string) ($_POST['name'] ?? ''), ENT_QUOTES) ?>" required>
        </label>
        <label>Email
            <input type="email" name="email" value="<?= htmlspecialchars((string) ($_POST['email'] ?? ''), ENT_QUOTES) ?>" required>
        </label>
        <label>Password
            <input type="password" name="password" required>
        </label>
        <label>Role
            <select name="role" required>
                <?php foreach ($profiles as $profile): ?>
                    <option value="<?= htmlspecialchars($profile['role'] ?? '', ENT_QUOTES) ?>" <?= (($_POST['role'] ?? '') === ($profile['role'] ?? '')) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($profile['role'] ?? '', ENT_QUOTES) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <button type="submit" class="btn-primary">Save</button>
        <a href="/public/index.php?route=admin/view-accounts" class="btn-secondary">Cancel</a>
    </form>
</section>
<?php include __DIR__ . '/../../shared/boundary/footer.php'; ?>
