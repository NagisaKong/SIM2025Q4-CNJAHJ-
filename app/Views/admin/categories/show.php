<?php ob_start(); ?>
<section class="card">
    <h1><?= htmlspecialchars($category->name, ENT_QUOTES) ?></h1>
    <dl class="definition-list">
        <dt>Status</dt><dd><?= htmlspecialchars($category->status, ENT_QUOTES) ?></dd>
        <dt>Created</dt><dd><?= htmlspecialchars($category->created_at, ENT_QUOTES) ?></dd>
    </dl>
    <a href="/admin/categories" class="btn-secondary">Back</a>
</section>
<?php $content = ob_get_clean(); include __DIR__.'/../../layouts/app.php'; ?>
