<section class="practice-page">
    <div class="practice-breadcrumb">
        <a href="<?= htmlspecialchars(base_url('courses'), ENT_QUOTES, 'UTF-8') ?>">Catalogue</a>
        <span aria-hidden="true">/</span>
        <a href="<?= htmlspecialchars(base_url('courses/' . (int) ($course['id'] ?? 0)), ENT_QUOTES, 'UTF-8') ?>">Course</a>
        <span aria-hidden="true">/</span>
        <strong>Practice</strong>
    </div>

    <header class="practice-header">
        <div>
            <span class="eyebrow">Practice</span>
            <h1><?= htmlspecialchars((string) ($course['title'] ?? 'Course'), ENT_QUOTES, 'UTF-8') ?></h1>
            <p>Use quizzes to reinforce what you have learned before moving into formal assessment.</p>
        </div>
        <a class="btn secondary" href="<?= htmlspecialchars(base_url('courses/' . (int) ($course['id'] ?? 0) . '/learn'), ENT_QUOTES, 'UTF-8') ?>">Back to learning</a>
    </header>

    <?php if (empty($quizzes)): ?>
        <section class="practice-empty">
            <div class="practice-empty-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="M5 5h14v14H5zM8 9h8M8 12h6M8 15h4"/></svg>
            </div>
            <span class="eyebrow">Practice content</span>
            <h2>No published quizzes yet</h2>
            <p>There are no quizzes available for this course at the moment. The content team has apparently been persuaded that buttons can wait.</p>
            <a class="btn secondary" href="<?= htmlspecialchars(base_url('courses/' . (int) ($course['id'] ?? 0)), ENT_QUOTES, 'UTF-8') ?>">View course</a>
        </section>
    <?php else: ?>
        <div class="practice-summary">
            <div>
                <strong><?= count($quizzes) ?></strong>
                <span><?= count($quizzes) === 1 ? 'quiz available' : 'quizzes available' ?></span>
            </div>
            <div class="practice-summary-note">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v18M5 8h10a4 4 0 0 1 0 8H7a2 2 0 0 1 0-4h8"/></svg>
                <span>Scores are calculated on the server.</span>
            </div>
        </div>

        <div class="practice-grid">
            <?php foreach ($quizzes as $index => $quiz): ?>
                <?php
                    $quizId = (int) ($quiz['id'] ?? 0);
                    $quizTitle = trim((string) ($quiz['title'] ?? 'Untitled quiz'));
                    $quizDescription = trim((string) ($quiz['description'] ?? ''));
                    $passPercentage = (float) ($quiz['pass_percentage'] ?? 0);
                ?>
                <article class="practice-card">
                    <div class="practice-card-top">
                        <span class="practice-number">Quiz <?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></span>
                        <span class="practice-status">Published</span>
                    </div>

                    <div class="practice-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M6 4h12v16H6zM9 8h6M9 12h6M9 16h3"/></svg>
                    </div>

                    <h2><?= htmlspecialchars($quizTitle, ENT_QUOTES, 'UTF-8') ?></h2>
                    <p class="practice-description">
                        <?= htmlspecialchars($quizDescription !== '' ? $quizDescription : 'Practice your understanding of this course.', ENT_QUOTES, 'UTF-8') ?>
                    </p>

                    <div class="practice-meta">
                        <span>Pass mark</span>
                        <strong><?= number_format($passPercentage, 0) ?>%</strong>
                    </div>

                    <a class="btn practice-action" href="<?= htmlspecialchars(base_url('quizzes/' . $quizId), ENT_QUOTES, 'UTF-8') ?>">
                        Open quiz
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h13m-5-5 5 5-5 5"/></svg>
                    </a>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
