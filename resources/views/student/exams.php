<?php
$courseId = (int) ($course['id'] ?? 0);
$courseTitle = trim((string) ($course['title'] ?? 'Course'));
?>
<section class="exams-page">
    <div class="exams-breadcrumb">
        <a href="<?= htmlspecialchars(base_url('courses/' . $courseId . '/learn'), ENT_QUOTES, 'UTF-8') ?>">Learning</a>
        <span>/</span>
        <strong>Exams</strong>
    </div>

    <div class="exams-heading">
        <div>
            <span class="eyebrow">Assessment</span>
            <h1><?= htmlspecialchars($courseTitle, ENT_QUOTES, 'UTF-8') ?> exams</h1>
            <p>Test what you have learned and track your assessment progress.</p>
        </div>
        <div class="exams-count">
            <strong><?= count($exams) ?></strong>
            <span><?= count($exams) === 1 ? 'published exam' : 'published exams' ?></span>
        </div>
    </div>

    <?php if (empty($exams)): ?>
        <section class="exams-empty">
            <span class="eyebrow">Assessment</span>
            <h2>No exams available yet</h2>
            <p>Your course does not have a published exam at the moment. Keep working through the lessons and practice activities.</p>
            <a class="btn secondary" href="<?= htmlspecialchars(base_url('courses/' . $courseId . '/learn'), ENT_QUOTES, 'UTF-8') ?>">Back to learning</a>
        </section>
    <?php else: ?>
        <div class="exam-list">
            <?php foreach ($exams as $exam): ?>
                <?php
                    $examId = (int) ($exam['id'] ?? 0);
                    $title = trim((string) ($exam['title'] ?? 'Untitled exam'));
                    $description = trim((string) ($exam['description'] ?? ''));
                ?>
                <article class="exam-card">
                    <div class="exam-card-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M7 3h10v18H7zM9.5 7h5M9.5 11h5M9.5 15h3"/></svg>
                    </div>
                    <div class="exam-card-main">
                        <span class="eyebrow">Published exam</span>
                        <h2><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h2>
                        <?php if ($description !== ''): ?>
                            <p><?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endif; ?>
                        <div class="exam-meta">
                            <span><strong><?= (int) $exam['time_limit'] ?></strong> min</span>
                            <span><strong><?= number_format((float) $exam['passing_score'], 0) ?>%</strong> pass mark</span>
                            <span><strong><?= (int) $exam['attempts_allowed'] ?></strong> <?= (int) $exam['attempts_allowed'] === 1 ? 'attempt' : 'attempts' ?></span>
                        </div>
                    </div>
                    <div class="exam-card-action">
                        <a class="btn" href="<?= htmlspecialchars(base_url('exams/' . $examId), ENT_QUOTES, 'UTF-8') ?>">
                            Open exam
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h13m-5-5 5 5-5 5"/></svg>
                        </a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>