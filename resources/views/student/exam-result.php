<?php
$passed = !empty($attempt['passed']);
$percentage = (float) ($attempt['percentage'] ?? 0);
$score = (float) ($attempt['score'] ?? 0);
$status = ucfirst((string) ($attempt['status'] ?? 'completed'));
?>
<section class="exam-result-page">
    <div class="exam-result-breadcrumb">
        <a href="<?= htmlspecialchars(base_url('dashboard'), ENT_QUOTES, 'UTF-8') ?>">Dashboard</a>
        <span>/</span>
        <strong>Exam result</strong>
    </div>

    <section class="exam-result-card <?= $passed ? 'is-passed' : 'is-not-passed' ?>">
        <div class="exam-result-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="<?= $passed ? 'm5 12 4 4L19 6' : 'm7 7 10 10M17 7 7 17' ?>"/></svg>
        </div>
        <span class="eyebrow">Assessment complete</span>
        <h1><?= $passed ? 'Exam passed' : 'Exam not passed' ?></h1>
        <p>Your attempt has been finalized and the result below was calculated by the server.</p>

        <div class="exam-result-score">
            <strong><?= number_format($percentage, 1) ?>%</strong>
            <span>Final percentage</span>
        </div>

        <div class="exam-result-stats">
            <div><span>Status</span><strong><?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?></strong></div>
            <div><span>Score</span><strong><?= number_format($score, 2) ?></strong></div>
            <div><span>Result</span><strong><?= $passed ? 'Passed' : 'Not passed' ?></strong></div>
        </div>

        <div class="exam-result-message">
            <strong><?= $passed ? 'Keep going.' : 'Keep learning.' ?></strong>
            <span><?= $passed ? 'You have completed this assessment successfully.' : 'Review the course material and practice activities before your next eligible attempt.' ?></span>
        </div>

        <div class="exam-result-actions">
            <a class="btn" href="<?= htmlspecialchars(base_url('dashboard'), ENT_QUOTES, 'UTF-8') ?>">Back to dashboard</a>
        </div>
    </section>
</section>