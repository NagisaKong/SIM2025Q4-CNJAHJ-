<?php
declare(strict_types=1);

require_once __DIR__ . '/../../shared/bootstrap.php';
require_once __DIR__ . '/../controller/createRequestController.php';
require_once __DIR__ . '/../controller/searchPostedRequestsController.php';
require_once __DIR__ . '/../controller/viewRequestShortlistCountController.php';
require_once __DIR__ . '/../../PM/controller/viewServiceCategoryController.php';
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

$categoryController = new ViewServiceCategoryController();
$categories = $categoryController->list();
$pinId = (int) ($_SESSION['user']['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pinId) {
    $controller = new CreateRequestController();
    $requestId = $controller->create($pinId, $_POST);
    if ($requestId) {
        header('Location: /public/index.php?route=pin/my-requests');
        exit();
    }
}
?>
<section class="card">
    <h1>Create Request</h1>
    <?php if (!empty($_SESSION['pin_message'])): ?>
        <div class="alert alert-error"><?= htmlspecialchars($_SESSION['pin_message'], ENT_QUOTES) ?></div>
        <?php unset($_SESSION['pin_message']); ?>
    <?php endif; ?>
    <form method="POST" action="/public/index.php?route=pin/create-request" class="form">
        <label>Title
            <input type="text" name="title" value="<?= htmlspecialchars((string) ($_POST['title'] ?? ''), ENT_QUOTES) ?>" required>
        </label>
        <label>Description
            <textarea name="description" required><?= htmlspecialchars((string) ($_POST['description'] ?? ''), ENT_QUOTES) ?></textarea>
        </label>
        <label>Location
            <input type="text" name="location" value="<?= htmlspecialchars((string) ($_POST['location'] ?? ''), ENT_QUOTES) ?>" required>
        </label>
        <label>Requested Date
            <input type="date" name="requested_date" value="<?= htmlspecialchars((string) ($_POST['requested_date'] ?? date('Y-m-d')), ENT_QUOTES) ?>" required>
        </label>
        <label>Category
            <select name="category_id" required>
                <option value="">Select category</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= (int) $category['id'] ?>" <?= ((string) ($_POST['category_id'] ?? '') === (string) $category['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($category['name'] ?? '', ENT_QUOTES) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <button type="submit" class="btn-primary">Save</button>
        <a href="/public/index.php?route=pin/my-requests" class="btn-secondary">Cancel</a>
    </form>
</section>
<?php include __DIR__ . '/../../shared/boundary/footer.php'; ?>
