<?php
use App\Core\Auth;
use App\Support\Csrf;
?>
<section class="card">
    <h1>My Courses</h1>
    <p>Track your learning path and continue where you left off.</p>

    <?php if (empty($courses)): ?>
        <p>You are not enrolled in any courses yet.</p>
        <p><a class="btn" href="<?= htmlspecialchars(base_url('courses'), ENT_QUOTES, 'UTF-8') ?>">Browse courses</a></p>
    <?php else: ?>
        <div class="course-grid">
            <?php foreach ($courses as $entry): ?>
                <?php $course = $entry['course']; ?>
                <?php $progress = $entry['progress']; ?>
                <article class="course-card">
                    <h3><?= htmlspecialchars((string) ($course['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
                    <p><?= htmlspecialchars((string) ($course['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                    <p><strong>Progress:</strong> <?= (float) ($progress['percent'] ?? 0.0) ?>%</p>
                    <div style="margin-top: 12px;">
                        <a class="btn" href="<?= htmlspecialchars(base_url('courses/' . (int) ($course['id'] ?? 0) . '/learn'), ENT_QUOTES, 'UTF-8') ?>">Continue learning</a>
                        <a class="btn" href="<?= htmlspecialchars(base_url('courses/' . (int) ($course['id'] ?? 0) . '/quizzes'), ENT_QUOTES, 'UTF-8') ?>">Practice quizzes</a>
                        <a class="btn" href="<?= htmlspecialchars(base_url('courses/' . (int) ($course['id'] ?? 0) . '/exams'), ENT_QUOTES, 'UTF-8') ?>">Final exams</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
