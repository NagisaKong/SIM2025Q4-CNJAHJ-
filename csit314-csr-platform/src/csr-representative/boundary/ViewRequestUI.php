<?php
require_once __DIR__ . '/../controller/viewRequestController.php';

use CSRPlatform\CSRRepresentative\Controller\viewRequestController;
use CSRPlatform\Shared\Entity\Request;

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

$requestId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($requestId <= 0) {
    $_SESSION['flash_error'] = 'Request not found.';
    header('Location: /index.php?page=csr-requests');
    exit();
}

$controller = new viewRequestController(new Request());
$requestItem = $controller->viewRequest($requestId);
if ($requestItem === null) {
    $_SESSION['flash_error'] = 'Request not found.';
    header('Location: /index.php?page=csr-requests');
    exit();
}

$title = $requestItem['title'] ?? 'Volunteer request';
$category = $requestItem['category_name'] ?? 'Uncategorised';
$location = $requestItem['location'] ?? 'Not specified';
$status = $requestItem['status'] ?? 'unknown';
$requestedDate = $requestItem['requested_date'] ?? ($requestItem['requestedDate'] ?? '');
$createdAt = $requestItem['created_at'] ?? '';
$updatedAt = $requestItem['updated_at'] ?? '';
$additionalDetails = $requestItem['additional_details'] ?? ($requestItem['description'] ?? '');
$viewsCount = (int) ($requestItem['views_count'] ?? 0);
$shortlistCount = (int) ($requestItem['shortlist_count'] ?? 0);

$formatTimestamp = static function (?string $value): string {
    if ($value === null) {
        return '';
    }

    $trimmed = trim($value);
    if ($trimmed === '') {
        return '';
    }

    try {
        $dateTime = new DateTime($trimmed);
        $format = strpos($trimmed, ':') !== false ? 'Y-m-d H:i:s' : 'Y-m-d';
        return $dateTime->format($format);
    } catch (Exception $exception) {
        return $trimmed;
    }
};

$requestedDate = $formatTimestamp($requestedDate);
$createdAt = $formatTimestamp($createdAt);
$updatedAt = $formatTimestamp($updatedAt);

$pageTitle = 'Volunteer Request';
$navLinks = [
    ['href' => '/index.php?page=dashboard', 'label' => 'Dashboard'],
    ['href' => '/index.php?page=csr-requests', 'label' => 'Opportunities'],
    ['href' => '/index.php?page=csr-shortlist', 'label' => 'Shortlist'],
    ['href' => '/index.php?page=csr-history', 'label' => 'History'],
];
include __DIR__ . '/../../shared/boundary/header.php';
?>
<section class="card">
    <div class="card-heading">
        <h1><?= htmlspecialchars((string) $title, ENT_QUOTES) ?></h1>
        <a class="btn-secondary" href="/index.php?page=csr-requests">Back to opportunities</a>
    </div>
    <dl class="definition-list">
        <dt>Category</dt>
        <dd><?= htmlspecialchars((string) $category, ENT_QUOTES) ?></dd>
        <dt>Location</dt>
        <dd><?= htmlspecialchars((string) $location, ENT_QUOTES) ?></dd>
        <dt>Status</dt>
        <dd><?= htmlspecialchars((string) $status, ENT_QUOTES) ?></dd>
        <dt>Requested on</dt>
        <dd><?= htmlspecialchars((string) $requestedDate, ENT_QUOTES) ?></dd>
        <dt>Created at</dt>
        <dd><?= htmlspecialchars((string) $createdAt, ENT_QUOTES) ?></dd>
        <dt>Last updated</dt>
        <dd><?= htmlspecialchars((string) $updatedAt, ENT_QUOTES) ?></dd>
        <dt>Views</dt>
        <dd><?= $viewsCount ?></dd>
        <dt>Shortlisted</dt>
        <dd><?= $shortlistCount ?></dd>
    </dl>
    <section class="card" style="margin-top: 1.5rem;">
        <h2>Additional details</h2>
        <p><?= nl2br(htmlspecialchars((string) $additionalDetails, ENT_QUOTES)) ?></p>
    </section>
</section>
<?php include __DIR__ . '/../../shared/boundary/footer.php'; ?>
