<?php

use App\Shared\Boundary\Format\DateFormatter;

ob_start();
?>
<section class="card">
    <h1>Completed Matches</h1>
    <form class="form-inline" method="GET">
        <select name="status">
            <option value="">All statuses</option>
            <option value="completed" <?= (($_GET['status'] ?? '') === 'completed') ? 'selected' : '' ?>>Completed</option>
            <option value="in_progress" <?= (($_GET['status'] ?? '') === 'in_progress') ? 'selected' : '' ?>>In progress</option>
        </select>
        <input type="date" name="from" lang="en" placeholder="YYYY-MM-DD" value="<?= htmlspecialchars($filters['from'] ?? '', ENT_QUOTES) ?>">
        <input type="date" name="to" lang="en" placeholder="YYYY-MM-DD" value="<?= htmlspecialchars($filters['to'] ?? '', ENT_QUOTES) ?>">
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
                    <?php $matchedAt = DateFormatter::dateTime($item->matched_at); ?>
                    <td><?= htmlspecialchars($matchedAt ?? $item->matched_at, ENT_QUOTES) ?></td>
                    <?php $completedAt = DateFormatter::dateTime($item->completed_at); ?>
                    <td><?= htmlspecialchars($completedAt ?? '-', ENT_QUOTES) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php $content = ob_get_clean(); include dirname(__DIR__, 3) . '/Shared/Boundary/layouts/app.php'; ?>
