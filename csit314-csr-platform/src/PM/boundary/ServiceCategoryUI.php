<?php
require_once __DIR__ . '/../controller/viewServiceCategoryController.php';
require_once __DIR__ . '/../controller/updateServiceCategoryController.php';

use CSRPlatform\PM\Controller\updateServiceCategoryController;
use CSRPlatform\PM\Controller\viewServiceCategoryController;
use CSRPlatform\Shared\Entity\ServiceCategories;
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
if (!$currentUser) {
    header('Location: /index.php?page=login');
    exit();
}

$categoriesEntity = new ServiceCategories();
$viewController = new viewServiceCategoryController($categoriesEntity);
$updateController = new updateServiceCategoryController($categoriesEntity, new Validation());

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['form_type'] ?? '') === 'create') {
        $name = trim($_POST['name'] ?? '');
        if ($name !== '') {
            $categoriesEntity->createCategory($name);
            $_SESSION['flash_success'] = 'Category created successfully.';
        } else {
            $_SESSION['flash_error'] = 'Category name is required.';
        }
        header('Location: /index.php?page=pm-categories');
        exit();
    }

    $categoryId = (int) ($_POST['category_id'] ?? 0);
    $payload = [
        'status' => $_POST['status'] ?? null,
    ];
    if (!empty($_POST['name'])) {
        $payload['name'] = $_POST['name'];
    }
    if ($categoryId > 0 && $updateController->update($categoryId, $payload)) {
        $_SESSION['flash_success'] = 'Category updated successfully.';
    } else {
        $_SESSION['flash_error'] = 'Unable to update category.';
    }
    header('Location: /index.php?page=pm-categories');
    exit();
}

$searchQuery = $_GET['q'] ?? '';
$categories = $viewController->list('all', $searchQuery);

$pageTitle = 'Service categories';
$navLinks = [
    ['href' => '/index.php?page=pm-categories', 'label' => 'Categories'],
    ['href' => '/index.php?page=pm-report-daily', 'label' => 'Daily report'],
    ['href' => '/index.php?page=pm-report-weekly', 'label' => 'Weekly report'],
];
include __DIR__ . '/../../shared/boundary/header.php';
?>
<section class="card">
    <div class="card-header">
        <h1>Service categories</h1>
        <p>Manage the categories that classify requests.</p>
    </div>
    <form method="GET" action="/index.php" class="form-inline">
        <input type="hidden" name="page" value="pm-categories">
        <input type="search" name="q" placeholder="Search categories" value="<?= htmlspecialchars((string) $searchQuery, ENT_QUOTES) ?>">
        <button type="submit" class="btn-secondary">Search</button>
        <?php if ($searchQuery !== ''): ?>
            <a href="/index.php?page=pm-categories" class="btn-secondary">Clear</a>
        <?php endif; ?>
    </form>
    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $category): ?>
                <tr>
                    <td><?= htmlspecialchars((string) $category['name'], ENT_QUOTES) ?></td>
                    <td><span class="tag tag-<?= htmlspecialchars((string) $category['status'], ENT_QUOTES) ?>"><?= htmlspecialchars((string) $category['status'], ENT_QUOTES) ?></span></td>
                    <td>
                        <form method="POST" action="/index.php?page=pm-categories" class="form-inline">
                            <input type="hidden" name="category_id" value="<?= (int) $category['id'] ?>">
                            <input type="text" name="name" value="<?= htmlspecialchars((string) $category['name'], ENT_QUOTES) ?>" placeholder="Rename category">
                            <select name="status">
                                <option value="active" <?= $category['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= $category['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                            <button type="submit" class="btn-primary">Update</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if ($categories === []): ?>
                <tr>
                    <td colspan="3" style="text-align: center;">No categories available.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</section>
<section class="card">
    <div class="card-header">
        <h2>Create new category</h2>
    </div>
    <form method="POST" action="/index.php?page=pm-categories" class="form-inline">
        <input type="hidden" name="form_type" value="create">
        <input type="text" name="name" placeholder="Category name" required>
        <button type="submit" class="btn-primary">Add category</button>
    </form>
</section>
<?php include __DIR__ . '/../../shared/boundary/footer.php'; ?>
