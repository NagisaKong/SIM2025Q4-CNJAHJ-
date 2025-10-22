<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$currentUser = $_SESSION['user'] ?? null;
$pageTitle = $pageTitle ?? 'CSR Platform';
$navLinks = $navLinks ?? [];
$baseUrl = '/';
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
<?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="alert alert-success">
        <?= htmlspecialchars($_SESSION['flash_success'], ENT_QUOTES) ?>
    </div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>
<?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-error">
        <?= htmlspecialchars($_SESSION['flash_error'], ENT_QUOTES) ?>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>
<?php if (!empty($_SESSION['flash_warning'])): ?>
    <div class="alert alert-warning">
        <?= htmlspecialchars($_SESSION['flash_warning'], ENT_QUOTES) ?>
    </div>
    <?php unset($_SESSION['flash_warning']); ?>
<?php endif; ?>
<?php if (!empty($_SESSION['flash_info'])): ?>
    <div class="alert alert-info">
        <?= htmlspecialchars($_SESSION['flash_info'], ENT_QUOTES) ?>
    </div>
    <?php unset($_SESSION['flash_info']); ?>
<?php endif; ?>
