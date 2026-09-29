<?php
use App\Support\Csrf;
$courseId = (int) ($course['id'] ?? 0);
$courseTitle = trim((string) ($course['title'] ?? 'Untitled course'));
?>
<section class="admin-form-page">
    <div class="admin-form-heading">
        <div><span class="eyebrow">Assessment authoring</span><h1>Create exam</h1><p>Configure the assessment before adding its questions.</p></div>
        <a class="btn secondary" href="<?= htmlspecialchars(base_url('admin/courses/' . $courseId . '/edit'), ENT_QUOTES, 'UTF-8') ?>">← Course</a>
    </div>
    <div class="admin-form-layout">
        <form class="admin-editor-card" method="post" action="<?= htmlspecialchars(base_url('admin/exams/store'), ENT_QUOTES, 'UTF-8') ?>">
            <?= Csrf::input() ?>
            <input type="hidden" name="course_id" value="<?= $courseId ?>">
            <div class="admin-editor-section">
                <span class="eyebrow">01 · Exam details</span>
                <div class="admin-context-chip">Course · <?= htmlspecialchars($courseTitle, ENT_QUOTES, 'UTF-8') ?></div>
                <div class="form-group"><label for="exam-title">Title</label><input id="exam-title" name="title" required maxlength="255"></div>
                <div class="form-group"><label for="exam-description">Description</label><textarea id="exam-description" name="description" rows="5"></textarea></div>
            </div>
            <div class="admin-editor-section">
                <span class="eyebrow">02 · Assessment rules</span>
                <div class="admin-form-grid">
                    <div class="form-group"><label for="time-limit">Time limit</label><input id="time-limit" type="number" name="time_limit" min="1" value="60" required><small>Minutes available for each attempt.</small></div>
                    <div class="form-group"><label for="passing-score">Pass percentage</label><input id="passing-score" type="number" name="passing_score" min="0" max="100" step="0.1" value="70" required><small>Minimum percentage required to pass.</small></div>
                    <div class="form-group"><label for="attempts-allowed">Attempts allowed</label><input id="attempts-allowed" type="number" name="attempts_allowed" min="1" value="1" required><small>Maximum attempts per student.</small></div>
                </div>
            </div>
            <div class="admin-editor-actions"><button class="btn" type="submit">Create exam</button><a class="btn secondary" href="<?= htmlspecialchars(base_url('admin/courses/' . $courseId . '/edit'), ENT_QUOTES, 'UTF-8') ?>">Cancel</a></div>
        </form>
        <aside class="admin-editor-sidebar">
            <div class="admin-editor-note"><span class="eyebrow">Workflow</span><h2>Build, review, publish</h2><p>Create the exam, add questions, review the answer key, then publish it when the assessment is ready.</p></div>
            <div class="admin-editor-note"><span class="eyebrow">Security</span><h2>Answers stay server-side</h2><p>Correct answers are authoring data. Student exam responses and scoring remain handled by the server.</p></div>
        </aside>
    </div>
</section>