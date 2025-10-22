<?php ob_start(); ?>
<section class="card">
    <h1>My Shortlist</h1>
    <table class="table">
        <thead>
            <tr>
                <th>Request</th>
                <th>Saved at</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($shortlist as $item): ?>
                <tr>
                    <td><a href="/csr/requests/<?= $item->request_id ?>">Request #<?= $item->request_id ?></a></td>
                    <td><?= htmlspecialchars($item->created_at, ENT_QUOTES) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php $content = ob_get_clean(); include dirname(__DIR__, 3) . '/Shared/Boundary/layouts/app.php'; ?>
