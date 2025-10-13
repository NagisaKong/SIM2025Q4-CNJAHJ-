<section class="card">
    <h1><?= htmlspecialchars($requestItem->title, ENT_QUOTES) ?></h1>
    <p><?= nl2br(htmlspecialchars($requestItem->description, ENT_QUOTES)) ?></p>
    <p><strong>Location:</strong> <?= htmlspecialchars($requestItem->location, ENT_QUOTES) ?></p>
    <p><strong>Date:</strong> <?= htmlspecialchars($requestItem->requested_date, ENT_QUOTES) ?></p>
    <p><strong>Views:</strong> <?= $requestItem->views_count ?> | <strong>Shortlists:</strong> <?= $requestItem->shortlist_count ?></p>
    <form method="POST" action="/csr/requests/<?= $requestItem->id ?>/shortlist">
        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES) ?>">
        <button type="submit" class="btn-primary">Add to shortlist</button>
    </form>
    <a href="/csr/requests" class="btn-secondary">Back</a>
</section>
