<?php ob_start(); ?>
<section class="card">
    <h1>Sign in</h1>
    <form method="POST" action="/login" class="form">
        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES) ?>">
        <label>Email
            <input type="email" name="email" required>
        </label>
        <label>Password
            <input type="password" name="password" required>
        </label>
        <button type="submit" class="btn-primary">Sign in</button>
    </form>
</section>
<?php $content = ob_get_clean(); include __DIR__.'/../layouts/app.php'; ?>
