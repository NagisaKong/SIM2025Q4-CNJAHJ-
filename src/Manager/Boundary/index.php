<?php ob_start(); ?>
<section class="card">
    <h1>Platform Reports</h1>
    <form class="form-inline" method="GET">
        <select name="period">
            <option value="daily" <?= $period === 'daily' ? 'selected' : '' ?>>Daily</option>
            <option value="weekly" <?= $period === 'weekly' ? 'selected' : '' ?>>Weekly</option>
            <option value="monthly" <?= $period === 'monthly' ? 'selected' : '' ?>>Monthly</option>
        </select>
        <button type="submit" class="btn-secondary">Refresh</button>
    </form>
    <form method="POST" action="/reports/export" class="form-inline">
        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES) ?>">
        <input type="hidden" name="period" value="<?= htmlspecialchars($period, ENT_QUOTES) ?>">
        <button type="submit" class="btn-primary">Export CSV</button>
    </form>
    <table class="table">
        <thead>
            <tr>
                <th>Period</th>
                <th>Matches Created</th>
                <th>Matches Completed</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['period'], ENT_QUOTES) ?></td>
                    <td><?= $row['matches_created'] ?></td>
                    <td><?= $row['matches_completed'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php $content = ob_get_clean(); include dirname(__DIR__, 2) . '/Shared/Boundary/layouts/app.php'; ?>
