<section class="card">
    <h1><?= htmlspecialchars((string) ($course['title'] ?? 'Course'), ENT_QUOTES, 'UTF-8') ?>: Quizzes</h1>
    <p>Practice assessments for this course. Your score is calculated on the server, because browsers are lovely places to lie.</p>

    <?php if (empty($quizzes)): ?>
        <p>No published quizzes are available yet.</p>
    <?php else: ?>
        <div class="course-grid">
            <?php foreach ($quizzes as $quiz): ?>
                <article class="course-card">
                    <h3><?= htmlspecialchars((string) $quiz['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                    <p><?= htmlspecialchars((string) ($quiz['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                    <p><strong>Pass mark:</strong> <?= (float) $quiz['pass_percentage'] ?>%</p>
                    <a class="btn" href="/quizzes/<?= (int) $quiz['id'] ?>">Open quiz</a>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
