<?php
$courseId = (int) ($course['id'] ?? 0);
$courseTitle = trim((string) ($course['title'] ?? 'Course'));
$courseDescription = trim((string) ($course['description'] ?? ''));
$percent = max(0, min(100, (float) ($progress['percent'] ?? 0)));
$completedLessons = (int) ($progress['completed_lessons'] ?? 0);
$totalLessons = (int) ($progress['total_lessons'] ?? 0);
?>
<section class="learning-overview-page">
    <div class="learning-overview-breadcrumb">
        <a href="<?= htmlspecialchars(base_url('courses'), ENT_QUOTES, 'UTF-8') ?>">Catalogue</a>
        <span aria-hidden="true">/</span>
        <a href="<?= htmlspecialchars(base_url('courses/' . $courseId), ENT_QUOTES, 'UTF-8') ?>">Course</a>
        <span aria-hidden="true">/</span>
        <strong>Learning</strong>
    </div>

    <header class="learning-overview-header">
        <div>
            <span class="eyebrow">Course learning</span>
            <h1><?= htmlspecialchars($courseTitle, ENT_QUOTES, 'UTF-8') ?></h1>
            <?php if ($courseDescription !== ''): ?>
                <p><?= htmlspecialchars($courseDescription, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
        </div>
        <a class="btn secondary" href="<?= htmlspecialchars(base_url('courses/' . $courseId), ENT_QUOTES, 'UTF-8') ?>">Course details</a>
    </header>

    <section class="learning-progress-card" aria-label="Course progress">
        <div>
            <span class="eyebrow">Your progress</span>
            <strong><?= number_format($percent, 0) ?>%</strong>
            <span><?= $completedLessons ?> of <?= $totalLessons ?> <?= $totalLessons === 1 ? 'lesson' : 'lessons' ?> completed</span>
        </div>
        <div class="learning-progress-track">
            <span style="width: <?= number_format($percent, 2, '.', '') ?>%;"></span>
        </div>
    </section>

    <?php if (empty($modules)): ?>
        <section class="learning-empty">
            <span class="eyebrow">Course content</span>
            <h2>No modules are available yet</h2>
            <p>This course has been created, but learning content has not been added yet.</p>
            <a class="btn secondary" href="<?= htmlspecialchars(base_url('courses/' . $courseId), ENT_QUOTES, 'UTF-8') ?>">Back to course</a>
        </section>
    <?php else: ?>
        <div class="learning-module-list">
            <?php foreach ($modules as $moduleIndex => $entry): ?>
                <?php
                    $module = $entry['module'] ?? [];
                    $lessons = $entry['lessons'] ?? [];
                ?>
                <section class="learning-module">
                    <div class="learning-module-heading">
                        <div>
                            <span class="learning-module-number">Module <?= $moduleIndex + 1 ?></span>
                            <h2><?= htmlspecialchars((string) ($module['title'] ?? 'Untitled module'), ENT_QUOTES, 'UTF-8') ?></h2>
                            <?php if (trim((string) ($module['description'] ?? '')) !== ''): ?>
                                <p><?= htmlspecialchars((string) $module['description'], ENT_QUOTES, 'UTF-8') ?></p>
                            <?php endif; ?>
                        </div>
                        <span class="learning-module-count"><?= count($lessons) ?> <?= count($lessons) === 1 ? 'lesson' : 'lessons' ?></span>
                    </div>

                    <?php if (empty($lessons)): ?>
                        <div class="learning-no-lessons">No lessons in this module yet.</div>
                    <?php else: ?>
                        <div class="learning-lesson-list">
                            <?php foreach ($lessons as $lessonIndex => $lesson): ?>
                                <?php
                                    $lessonId = (int) ($lesson['id'] ?? 0);
                                    $lessonTitle = trim((string) ($lesson['title'] ?? 'Lesson'));
                                ?>
                                <a class="learning-lesson-row" href="<?= htmlspecialchars(base_url('lessons/' . $lessonId), ENT_QUOTES, 'UTF-8') ?>">
                                    <span class="learning-lesson-index"><?= str_pad((string) ($lessonIndex + 1), 2, '0', STR_PAD_LEFT) ?></span>
                                    <span class="learning-lesson-copy">
                                        <strong><?= htmlspecialchars($lessonTitle, ENT_QUOTES, 'UTF-8') ?></strong>
                                        <small>Open lesson</small>
                                    </span>
                                    <span class="learning-lesson-arrow" aria-hidden="true">→</span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
