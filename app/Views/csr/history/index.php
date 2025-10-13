<?php ob_start(); ?>
<section class="card">
    <h1>Service History</h1>
    <form class="form-inline" method="GET">
        <select name="status">
            <option value="">All statuses</option>
            <option value="completed" <?= (($_GET['status'] ?? '') === 'completed') ? 'selected' : '' ?>>Completed</option>
            <option value="in_progress" <?= (($_GET['status'] ?? '') === 'in_progress') ? 'selected' : '' ?>>In progress</option>
        </select>
        <input type="date" name="from" value="<?= htmlspecialchars($filters['from'] ?? '', ENT_QUOTES) ?>">
        <input type="date" name="to" value="<?= htmlspecialchars($filters['to'] ?? '', ENT_QUOTES) ?>">
        <button type="submit" class="btn-secondary">Filter</button>
    </form>
    <table class="table">
        <thead>
            <tr>
                <th>Request</th>
                <th>Status</th>
                <th>Matched at</th>
                <th>Completed</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($history as $item): ?>
                <tr>
                    <td>#<?= $item->request_id ?></td>
                    <td><span class="tag tag-<?= $item->status ?>"><?= htmlspecialchars($item->status, ENT_QUOTES) ?></span></td>
                    <td><?= htmlspecialchars($item->matched_at, ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($item->completed_at ?? '-', ENT_QUOTES) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php $content = ob_get_clean(); include __DIR__.'/../../layouts/app.php'; ?>
