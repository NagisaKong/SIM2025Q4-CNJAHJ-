<?php
declare(strict_types=1);

require_once __DIR__ . '/../../shared/bootstrap.php';
require_once __DIR__ . '/../controller/updateServiceCategoryController.php';
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
$controller = new UpdateServiceCategoryController();
$category = $id ? $controller->find($id) : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $id) {
    if ($controller->update($id, $_POST)) {
        header('Location: /public/index.php?route=pm/categories');
        exit();
    }
}
?>
<section class="card">
    <h1>Edit Category</h1>
    <?php if (!empty($_SESSION['category_message'])): ?>
        <div class="alert alert-info"><?= htmlspecialchars($_SESSION['category_message'], ENT_QUOTES) ?></div>
        <?php unset($_SESSION['category_message']); ?>
    <?php endif; ?>
    <form method="POST" action="/public/index.php?route=pm/update-category&id=<?= (int) $id ?>" class="form">
        <label>Name
            <input type="text" name="name" value="<?= htmlspecialchars($category['name'] ?? '', ENT_QUOTES) ?>" required>
        </label>
        <label>Status
            <select name="status">
                <option value="active" <?= (($category['status'] ?? '') === 'active') ? 'selected' : '' ?>>Active</option>
                <option value="suspended" <?= (($category['status'] ?? '') === 'suspended') ? 'selected' : '' ?>>Suspended</option>
            </select>
        </label>
        <button type="submit" class="btn-primary">Save</button>
        <a href="/public/index.php?route=pm/categories" class="btn-secondary">Cancel</a>
    </form>
</section>
<?php include __DIR__ . '/../../shared/boundary/footer.php'; ?>
