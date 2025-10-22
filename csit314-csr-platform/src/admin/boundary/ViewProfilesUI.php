<?php
declare(strict_types=1);

require_once __DIR__ . '/../../shared/bootstrap.php';
require_once __DIR__ . '/../controller/viewProfilesController.php';
require_once __DIR__ . '/../controller/suspendProfileController.php';
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

$controller = new ViewProfilesController();
$profiles = $controller->list();

if (isset($_POST['suspend_id'])) {
    $suspendController = new SuspendProfileController();
    $suspendController->suspend((int) $_POST['suspend_id']);
    header('Location: /public/index.php?route=admin/view-profiles');
    exit();
}
?>
<section class="card">
    <div class="card-header">
        <h1>User Profiles</h1>
        <a class="btn-primary" href="/public/index.php?route=admin/create-profile">New Profile</a>
    </div>
    <?php if (!empty($_SESSION['profile_message'])): ?>
        <div class="alert alert-info"><?= htmlspecialchars($_SESSION['profile_message'], ENT_QUOTES) ?></div>
        <?php unset($_SESSION['profile_message']); ?>
    <?php endif; ?>
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
            <?php if (empty($profiles)): ?>
                <tr>
                    <td colspan="4" style="text-align: center;">No profiles found.</td>
                </tr>
            <?php endif; ?>
            <?php foreach ($profiles as $profile): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($profile['role'] ?? '', ENT_QUOTES) ?></strong></td>
                    <td><?= htmlspecialchars($profile['description'] ?? '', ENT_QUOTES) ?></td>
                    <td class="status-col"><span class="tag tag-<?= htmlspecialchars($profile['status'] ?? '', ENT_QUOTES) ?>"><?= htmlspecialchars($profile['status'] ?? '', ENT_QUOTES) ?></span></td>
                    <td class="actions">
                        <form method="POST" action="/public/index.php?route=admin/view-profiles" style="display:inline;">
                            <input type="hidden" name="suspend_id" value="<?= (int) ($profile['id'] ?? 0) ?>">
                            <button type="submit" class="btn-link">Suspend</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php include __DIR__ . '/../../shared/boundary/footer.php'; ?>
