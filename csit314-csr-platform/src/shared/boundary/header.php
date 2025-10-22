<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$user = $_SESSION['user'] ?? null;
$userRole = $_SESSION['user_role'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CSR Match Platform</title>
    <link rel="stylesheet" href="/public/css/style.css">
</head>
<body>
<header class="site-header">
    <div class="container">
        <div class="branding">CSR Match Platform</div>
        <nav>
            <ul>
                <?php if ($user): ?>
                    <li><strong><?= htmlspecialchars($user['name'] ?? 'User', ENT_QUOTES) ?></strong> (<?= htmlspecialchars($userRole ?? 'guest', ENT_QUOTES) ?>)</li>
                    <li><a href="?action=logout" class="logout-link">Logout</a></li>
                <?php else: ?>
                    <li><a href="/public/index.php?route=login">Login</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>
<main class="container">
<?php if (isset($_SESSION['flash_message'])): ?>
    <div class="alert alert-info"><?= htmlspecialchars($_SESSION['flash_message'], ENT_QUOTES) ?></div>
    <?php unset($_SESSION['flash_message']); ?>
<?php endif; ?>
