<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'CSR Platform', ENT_QUOTES) ?></title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
<header class="top-nav">
    <div class="container">
        <div class="brand"><a href="/dashboard">CSR Match Platform</a></div>
        <nav>
            <?php if (!empty($authUser)): ?>
                <a href="/dashboard">Dashboard</a>
                <?php if ($authUser->profile?->role === 'user_admin'): ?>
                    <a href="/admin/users">Users</a>
                    <a href="/admin/profiles">Profiles</a>
                    <a href="/admin/categories">Categories</a>
                <?php elseif ($authUser->profile?->role === 'csr_rep'): ?>
                    <a href="/csr/requests">Opportunities</a>
                    <a href="/csr/shortlist">Shortlist</a>
                    <a href="/csr/history">History</a>
                <?php elseif ($authUser->profile?->role === 'pin'): ?>
                    <a href="/pin/requests">My Requests</a>
                    <a href="/pin/history">History</a>
                <?php elseif ($authUser->profile?->role === 'platform_manager'): ?>
                    <a href="/admin/categories">Categories</a>
                    <a href="/reports">Reports</a>
                <?php endif; ?>
                <form method="POST" action="/logout" class="logout-form">
                    <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES) ?>">
                    <button type="submit">Logout</button>
                </form>
            <?php else: ?>
                <a href="/">Sign in</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="container">
    <?php if (!empty($flash_success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($flash_success, ENT_QUOTES) ?></div>
    <?php endif; ?>
    <?= $content ?? '' ?>
</main>

<?php
$popupMessages = [];
if (!empty($flash_warning)) {
    $popupMessages[] = ['level' => 'warning', 'message' => $flash_warning];
}
if (!empty($flash_error)) {
    $popupMessages[] = ['level' => 'error', 'message' => $flash_error];
}
?>

<?php foreach ($popupMessages as $index => $popup): ?>
    <div class="flash-popup-overlay is-visible" data-popup="<?= $index ?>">
        <div class="flash-popup-card flash-popup-<?= htmlspecialchars($popup['level'], ENT_QUOTES) ?>">
            <strong class="flash-popup-title">
                <?= $popup['level'] === 'error' ? 'Action required' : 'Please check' ?>
            </strong>
            <p><?= htmlspecialchars($popup['message'], ENT_QUOTES) ?></p>
            <button type="button" class="flash-popup-close" data-popup-close="<?= $index ?>">Close</button>
        </div>
    </div>
<?php endforeach; ?>

<?php if ($popupMessages !== []): ?>
    <script>
        (function () {
            const closeButtons = document.querySelectorAll('[data-popup-close]');
            closeButtons.forEach((button) => {
                button.addEventListener('click', function () {
                    const key = this.getAttribute('data-popup-close');
                    const overlay = document.querySelector('[data-popup="' + key + '"]');
                    if (overlay) {
                        overlay.classList.add('is-dismissing');
                        window.setTimeout(() => overlay.remove(), 150);
                    }
                });
            });
        })();
    </script>
<?php endif; ?>
<footer class="footer">
    <div class="container">&copy; <?= date('Y') ?> CSR Match Platform</div>
</footer>
</body>
</html>
