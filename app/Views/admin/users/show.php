<?php ob_start(); ?>
<section class="card">
    <h1><?= htmlspecialchars($user->name, ENT_QUOTES) ?></h1>
    <dl class="definition-list">
        <dt>Email</dt><dd><?= htmlspecialchars($user->email, ENT_QUOTES) ?></dd>
        <dt>Status</dt><dd><?= htmlspecialchars($user->status, ENT_QUOTES) ?></dd>
        <dt>Role</dt><dd><?= htmlspecialchars($user->profile?->role ?? 'N/A', ENT_QUOTES) ?></dd>
        <dt>Joined</dt><dd><?= htmlspecialchars($user->created_at, ENT_QUOTES) ?></dd>
    </dl>
    <a href="/admin/users" class="btn-secondary">Back</a>
</section>
<?php $content = ob_get_clean(); include __DIR__.'/../../layouts/app.php'; ?>
