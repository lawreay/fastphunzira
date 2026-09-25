<section class="card">
    <h1><?= htmlspecialchars((string) $course['title'], ENT_QUOTES, 'UTF-8') ?> Exams</h1>

    <?php if (!$exams): ?>
        <p>No published exams are available for this course.</p>
    <?php else: ?>
        <?php foreach ($exams as $exam): ?>
            <article style="padding:16px 0;border-bottom:1px solid #ddd;">
                <h2><?= htmlspecialchars((string) $exam['title'], ENT_QUOTES, 'UTF-8') ?></h2>
                <p><?= htmlspecialchars((string) ($exam['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                <p>
                    Time limit: <?= (int) $exam['time_limit'] ?> minutes ·
                    Pass mark: <?= (float) $exam['passing_score'] ?>% ·
                    Attempts: <?= (int) $exam['attempts_allowed'] ?>
                </p>
                <a class="btn" href="<?= htmlspecialchars(base_url('exams/' . (int) $exam['id']), ENT_QUOTES, 'UTF-8') ?>">Open Exam</a>
            </article>
        <?php endforeach; ?>
    <?php endif; ?>
</section>
