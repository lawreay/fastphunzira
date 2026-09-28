<?php
?>
<section class="my-courses-page">
    <div class="my-courses-heading">
        <div>
            <span class="eyebrow">Student area</span>
            <h1>My courses</h1>
            <p>Continue your enrolled courses and keep track of your learning progress.</p>
        </div>
        <a class="btn secondary" href="<?= htmlspecialchars(base_url('courses'), ENT_QUOTES, 'UTF-8') ?>">
            Browse catalogue
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h13m-5-5 5 5-5 5"/></svg>
        </a>
    </div>

    <?php if (empty($courses)): ?>
        <section class="my-courses-empty">
            <div class="my-courses-empty-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="M5 4h12a2 2 0 0 1 2 2v14H7a2 2 0 0 0-2 2V4Zm0 16a2 2 0 0 1 2-2h12M8 8h7M8 12h5"/></svg>
            </div>
            <span class="eyebrow">Your learning starts here</span>
            <h2>No enrolled courses yet</h2>
            <p>Browse the catalogue, choose a course, and enroll to build your learning path.</p>
            <a class="btn" href="<?= htmlspecialchars(base_url('courses'), ENT_QUOTES, 'UTF-8') ?>">Explore courses</a>
        </section>
    <?php else: ?>
        <div class="my-courses-summary">
            <strong><?= count($courses) ?></strong>
            <span><?= count($courses) === 1 ? 'enrolled course' : 'enrolled courses' ?></span>
        </div>

        <div class="my-courses-grid">
            <?php foreach ($courses as $entry): ?>
                <?php
                    $course = $entry['course'] ?? [];
                    $progress = $entry['progress'] ?? [];
                    $courseId = (int) ($course['id'] ?? 0);
                    $title = trim((string) ($course['title'] ?? 'Untitled course'));
                    $description = trim((string) ($course['description'] ?? ''));
                    $percent = max(0, min(100, (float) ($progress['percent'] ?? 0)));
                    $completed = (int) ($progress['completed_lessons'] ?? 0);
                    $total = (int) ($progress['total_lessons'] ?? 0);
                    $isPremium = strtolower((string) ($course['access_tier'] ?? 'regular')) === 'premium';
                ?>
                <article class="my-course-card">
                    <div class="my-course-card-top">
                        <span class="course-type <?= $isPremium ? 'premium' : 'standard' ?>">
                            <?= $isPremium ? 'Premium' : 'Standard' ?>
                        </span>
                        <span class="my-course-percent"><?= number_format($percent, 0) ?>%</span>
                    </div>

                    <h2><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h2>
                    <p class="my-course-description">
                        <?= htmlspecialchars($description !== '' ? $description : 'Continue working through this course.', ENT_QUOTES, 'UTF-8') ?>
                    </p>

                    <div class="my-course-progress">
                        <div class="my-course-progress-meta">
                            <span>Learning progress</span>
                            <strong><?= number_format($percent, 0) ?>%</strong>
                        </div>
                        <div class="progress-track" role="progressbar" aria-valuenow="<?= number_format($percent, 0, '.', '') ?>" aria-valuemin="0" aria-valuemax="100" aria-label="Course progress">
                            <span style="width: <?= number_format($percent, 2, '.', '') ?>%;"></span>
                        </div>
                        <span class="my-course-lessons">
                            <?= $completed ?> of <?= $total ?> <?= $total === 1 ? 'lesson' : 'lessons' ?> completed
                        </span>
                    </div>

                    <div class="my-course-actions">
                        <a class="btn" href="<?= htmlspecialchars(base_url('courses/' . $courseId . '/learn'), ENT_QUOTES, 'UTF-8') ?>">
                            Continue learning
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h13m-5-5 5 5-5 5"/></svg>
                        </a>
                        <a class="btn secondary" href="<?= htmlspecialchars(base_url('courses/' . $courseId . '/quizzes'), ENT_QUOTES, 'UTF-8') ?>">Practice</a>
                        <a class="my-course-text-link" href="<?= htmlspecialchars(base_url('courses/' . $courseId . '/exams'), ENT_QUOTES, 'UTF-8') ?>">View exams</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
