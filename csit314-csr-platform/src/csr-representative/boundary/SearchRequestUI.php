<?php
declare(strict_types=1);

require_once __DIR__ . '/../../shared/bootstrap.php';
require_once __DIR__ . '/../controller/searchRequestController.php';
require_once __DIR__ . '/../controller/saveRequestController.php';
require_once __DIR__ . '/../controller/viewShortlistedRequestController.php';
require_once __DIR__ . '/../controller/viewCSRHistoryController.php';
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

$searchController = new SearchRequestController();
$requests = $searchController->search($_GET);

if (isset($_POST['shortlist_request'])) {
    $csrId = (int) ($_SESSION['user']['id'] ?? 0);
    if ($csrId) {
        $saveController = new SaveRequestController();
        $saveController->addToShortlist($csrId, (int) $_POST['shortlist_request']);
    }
    header('Location: /public/index.php?route=csr/search-requests');
    exit();
}
?>
<section class="card">
    <h1>Volunteer Opportunities</h1>
    <?php if (!empty($_SESSION['csr_message'])): ?>
        <div class="alert alert-info"><?= htmlspecialchars($_SESSION['csr_message'], ENT_QUOTES) ?></div>
        <?php unset($_SESSION['csr_message']); ?>
    <?php endif; ?>
    <form class="form-inline" method="GET" action="/public/index.php">
        <input type="hidden" name="route" value="csr/search-requests">
        <input type="text" name="q" placeholder="Keyword" value="<?= htmlspecialchars((string) ($_GET['q'] ?? ''), ENT_QUOTES) ?>">
        <select name="category">
            <option value="">All categories</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= (int) $category['id'] ?>" <?= ((string) ($_GET['category'] ?? '') === (string) $category['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($category['name'] ?? '', ENT_QUOTES) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn-secondary">Filter</button>
    </form>
    <table class="table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Location</th>
                <th>Date</th>
                <th>Views</th>
                <th>Shortlisted</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($requests as $requestItem): ?>
                <tr>
                    <td><?= htmlspecialchars($requestItem['title'] ?? '', ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($requestItem['location'] ?? '', ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($requestItem['requested_date'] ?? '', ENT_QUOTES) ?></td>
                    <td><?= (int) ($requestItem['views_count'] ?? 0) ?></td>
                    <td><?= (int) ($requestItem['shortlist_count'] ?? 0) ?></td>
                    <td>
                        <form method="POST" action="/public/index.php?route=csr/search-requests">
                            <input type="hidden" name="shortlist_request" value="<?= (int) ($requestItem['id'] ?? 0) ?>">
                            <button type="submit" class="btn-link">Shortlist</button>
                            <a href="/public/index.php?route=csr/view-request&id=<?= (int) ($requestItem['id'] ?? 0) ?>" class="btn-link">View</a>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php include __DIR__ . '/../../shared/boundary/footer.php'; ?>
