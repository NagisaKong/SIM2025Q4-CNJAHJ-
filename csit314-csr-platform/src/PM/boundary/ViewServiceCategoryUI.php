<?php
declare(strict_types=1);

require_once __DIR__ . '/../../shared/bootstrap.php';
require_once __DIR__ . '/../controller/viewServiceCategoryController.php';
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

$controller = new ViewServiceCategoryController();
$categories = $controller->list();
?>
<section class="card">
    <div class="card-header">
        <h1>Service Categories</h1>
    </div>
    <?php if (!empty($_SESSION['category_message'])): ?>
        <div class="alert alert-info"><?= htmlspecialchars($_SESSION['category_message'], ENT_QUOTES) ?></div>
        <?php unset($_SESSION['category_message']); ?>
    <?php endif; ?>
    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th class="status-col">Status</th>
                <th class="actions">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $category): ?>
                <tr>
                    <td><?= htmlspecialchars($category['name'] ?? '', ENT_QUOTES) ?></td>
                    <td class="status-col"><span class="tag tag-<?= htmlspecialchars($category['status'] ?? '', ENT_QUOTES) ?>"><?= htmlspecialchars($category['status'] ?? '', ENT_QUOTES) ?></span></td>
                    <td class="actions">
                        <a href="/public/index.php?route=pm/update-category&id=<?= (int) ($category['id'] ?? 0) ?>">Edit</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php include __DIR__ . '/../../shared/boundary/footer.php'; ?>
