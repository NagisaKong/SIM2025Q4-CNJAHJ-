<section class="card">
    <div class="card-header">
        <h1>My Requests</h1>
        <a class="btn-primary" href="/pin/requests/create">New Request</a>
    </div>
    <table class="table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Status</th>
                <th>Views</th>
                <th>Shortlisted</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($requests as $requestItem): ?>
                <tr>
                    <td><?= htmlspecialchars($requestItem->title, ENT_QUOTES) ?></td>
                    <td><span class="tag tag-<?= $requestItem->status ?>"><?= htmlspecialchars($requestItem->status, ENT_QUOTES) ?></span></td>
                    <td><?= $requestItem->views_count ?></td>
                    <td><?= $requestItem->shortlist_count ?></td>
                    <td class="actions">
                        <a href="/pin/requests/<?= $requestItem->id ?>">View</a>
                        <a href="/pin/requests/<?= $requestItem->id ?>/edit">Edit</a>
                        <form method="POST" action="/pin/requests/<?= $requestItem->id ?>/delete">
                            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES) ?>">
                            <button type="submit" class="link">Cancel</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
