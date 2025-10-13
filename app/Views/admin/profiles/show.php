<?php ob_start(); ?>
<section class="card">
    <h1><?= htmlspecialchars($profile->role, ENT_QUOTES) ?></h1>
    <dl class="definition-list">
        <dt>Description</dt><dd><?= htmlspecialchars($profile->description, ENT_QUOTES) ?></dd>
        <dt>Status</dt><dd><?= htmlspecialchars($profile->status, ENT_QUOTES) ?></dd>
        <dt>Created</dt><dd><?= htmlspecialchars($profile->created_at, ENT_QUOTES) ?></dd>
    </dl>
    <a href="/admin/profiles" class="btn-secondary">Back</a>
</section>
<?php $content = ob_get_clean(); include __DIR__.'/../../layouts/app.php'; ?>
