<?php ob_start(); ?>
<section class="card">
    <h1><?= htmlspecialchars($requestItem->title, ENT_QUOTES) ?></h1>
    <p><?= nl2br(htmlspecialchars($requestItem->description, ENT_QUOTES)) ?></p>
    <dl class="definition-list">
        <dt>Status</dt><dd><?= htmlspecialchars($requestItem->status, ENT_QUOTES) ?></dd>
        <dt>Requested Date</dt><dd><?= htmlspecialchars($requestItem->requested_date, ENT_QUOTES) ?></dd>
        <dt>Location</dt><dd><?= htmlspecialchars($requestItem->location, ENT_QUOTES) ?></dd>
        <dt>Views</dt><dd><?= $requestItem->views_count ?></dd>
        <dt>Shortlisted</dt><dd><?= $requestItem->shortlist_count ?></dd>
    </dl>
    <a href="/pin/requests" class="btn-secondary">Back</a>
</section>
<?php $content = ob_get_clean(); include dirname(__DIR__, 3) . '/Shared/Boundary/layouts/app.php'; ?>
