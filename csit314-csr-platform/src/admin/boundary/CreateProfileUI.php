<?php
declare(strict_types=1);

require_once __DIR__ . '/../../shared/bootstrap.php';
require_once __DIR__ . '/../controller/createProfileController.php';
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new CreateProfileController();
    if ($controller->create($_POST)) {
        header('Location: /public/index.php?route=admin/view-profiles');
        exit();
    }
}
?>
<section class="card">
    <h1>Create Profile</h1>
    <?php if (!empty($_SESSION['profile_message'])): ?>
        <div class="alert alert-error"><?= htmlspecialchars($_SESSION['profile_message'], ENT_QUOTES) ?></div>
        <?php unset($_SESSION['profile_message']); ?>
    <?php endif; ?>
    <form method="POST" action="/public/index.php?route=admin/create-profile" class="form">
        <label>Role
            <input type="text" name="role" value="<?= htmlspecialchars((string) ($_POST['role'] ?? ''), ENT_QUOTES) ?>" required>
        </label>
        <label>Description
            <textarea name="description" required><?= htmlspecialchars((string) ($_POST['description'] ?? ''), ENT_QUOTES) ?></textarea>
        </label>
        <button type="submit" class="btn-primary">Save</button>
        <a href="/public/index.php?route=admin/view-profiles" class="btn-secondary">Cancel</a>
    </form>
</section>
<?php include __DIR__ . '/../../shared/boundary/footer.php'; ?>
