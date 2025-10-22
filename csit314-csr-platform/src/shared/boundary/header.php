<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$currentUser = $_SESSION['user'] ?? null;
$pageTitle = $pageTitle ?? 'CSR Platform';
$navLinks = $navLinks ?? [];
$baseUrl = '/';
$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashWarning = $_SESSION['flash_warning'] ?? null;
$flashInfo = $_SESSION['flash_info'] ?? null;
$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_warning'], $_SESSION['flash_info'], $_SESSION['flash_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES) ?></title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
<?php if ($flashError): ?>
<div class="flash-popup-overlay" data-flash-overlay role="alertdialog" aria-modal="true">
    <div class="flash-popup-card flash-error" role="document">
        <span class="flash-popup-title">We could not process your request</span>
        <p><?= htmlspecialchars($flashError, ENT_QUOTES) ?></p>
        <button type="button" class="btn-primary" data-flash-dismiss>Close</button>
    </div>
</div>
<?php endif; ?>
<header class="top-nav">
    <div class="container">
        <div class="brand"><a href="<?= $baseUrl ?>">CSR Match Platform</a></div>
        <nav>
            <?php if ($currentUser): ?>
                <?php foreach ($navLinks as $link): ?>
                    <a href="<?= htmlspecialchars($link['href'], ENT_QUOTES) ?>"><?= htmlspecialchars($link['label'], ENT_QUOTES) ?></a>
                <?php endforeach; ?>
                <a href="?action=logout" class="logout-link">Logout</a>
            <?php else: ?>
                <a href="/">Sign in</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="container">
<?php if ($flashSuccess): ?>
    <div class="alert alert-success">
        <?= htmlspecialchars($flashSuccess, ENT_QUOTES) ?>
    </div>
<?php endif; ?>
<?php if ($flashWarning): ?>
    <div class="alert alert-warning">
        <?= htmlspecialchars($flashWarning, ENT_QUOTES) ?>
    </div>
<?php endif; ?>
<?php if ($flashInfo): ?>
    <div class="alert alert-info">
        <?= htmlspecialchars($flashInfo, ENT_QUOTES) ?>
    </div>
<?php endif; ?>
