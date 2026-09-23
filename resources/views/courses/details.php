<section class="card">
    <h1><?= htmlspecialchars((string) ($course['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h1>
    <span class="status-badge"><?= htmlspecialchars((string) ($course['status'] ?? 'draft'), ENT_QUOTES, 'UTF-8') ?></span>
    <p><?= htmlspecialchars((string) ($course['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
    <p><a class="btn" href="/courses">Back to catalogue</a></p>
</section>
