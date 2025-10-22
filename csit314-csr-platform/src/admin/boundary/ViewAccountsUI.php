<?php
declare(strict_types=1);

require_once __DIR__ . '/../../shared/bootstrap.php';
require_once __DIR__ . '/../controller/viewAccountsController.php';
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

$controller = new ViewAccountsController();
$search = $_GET['q'] ?? null;
$role = $_GET['role'] ?? null;
$users = $controller->list($search ? (string) $search : null, $role ? (string) $role : null);
?>
<section class="card">
    <div class="card-header">
        <h1>User Accounts</h1>
        <a class="btn-primary" href="/public/index.php?route=admin/create-account">New User</a>
    </div>
    <?php if (!empty($_SESSION['registration_message'])): ?>
        <div class="alert alert-info"><?= htmlspecialchars($_SESSION['registration_message'], ENT_QUOTES) ?></div>
        <?php unset($_SESSION['registration_message']); ?>
    <?php endif; ?>
    <form class="form-inline" method="GET" action="/public/index.php">
        <input type="hidden" name="route" value="admin/view-accounts">
        <input type="text" name="q" placeholder="Search email or name" value="<?= htmlspecialchars((string) ($search ?? ''), ENT_QUOTES) ?>">
        <select name="role">
            <option value="All">All Roles</option>
            <option value="user_admin" <?= ($role ?? '') === 'user_admin' ? 'selected' : '' ?>>User Admin</option>
            <option value="csr_rep" <?= ($role ?? '') === 'csr_rep' ? 'selected' : '' ?>>CSR Rep</option>
            <option value="pin" <?= ($role ?? '') === 'pin' ? 'selected' : '' ?>>Person In Need</option>
            <option value="platform_manager" <?= ($role ?? '') === 'platform_manager' ? 'selected' : '' ?>>Platform Manager</option>
        </select>
        <button type="submit" class="btn-secondary">Search</button>
    </form>
    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th class="status-col">Status</th>
                <th>Role</th>
                <th class="actions">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['name'] ?? '', ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($user['email'] ?? '', ENT_QUOTES) ?></td>
                    <td class="status-col"><span class="tag tag-<?= htmlspecialchars($user['status'] ?? '', ENT_QUOTES) ?>"><?= htmlspecialchars($user['status'] ?? '', ENT_QUOTES) ?></span></td>
                    <td><?= htmlspecialchars($user['role'] ?? '', ENT_QUOTES) ?></td>
                    <td class="actions">
                        <a href="/public/index.php?route=admin/update-account&id=<?= (int) ($user['id'] ?? 0) ?>">Edit</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php include __DIR__ . '/../../shared/boundary/footer.php'; ?>
