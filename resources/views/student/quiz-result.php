<?php
use App\Support\Csrf;

$attemptId = (int) ($attempt['id'] ?? 0);
$status = ucfirst((string) ($attempt['status'] ?? ''));
$score = (int) ($attempt['score'] ?? 0);
$percentage = (float) ($attempt['percentage'] ?? 0);
$passed = !empty($attempt['passed']);
$quizId = (int) ($attempt['quiz_id'] ?? 0);
$attemptNumber = (int) ($attempt['attempt_number'] ?? 0);
?>

<section class="quiz-result-page">
    <nav class="quiz-result-breadcrumb" aria-label="Breadcrumb">
        <a href="<?= htmlspecialchars(base_url('dashboard'), ENT_QUOTES, 'UTF-8') ?>">Dashboard</a>
        <span aria-hidden="true">/</span>
        <strong>Quiz Result</strong>
    </nav>

    <div class="quiz-result-card">
        <div class="quiz-result-icon <?= $passed ? 'is-passed' : 'is-not-passed' ?>" aria-hidden="true">
            <?php if ($passed): ?>
                <svg viewBox="0 0 24 24">
                    <path d="m5 12 4 4L19 6"></path>
                </svg>
            <?php else: ?>
                <svg viewBox="0 0 24 24">
                    <path d="M6 6l12 12M18 6 6 18"></path>
                </svg>
            <?php endif; ?>
        </div>

        <span class="quiz-result-eyebrow">Practice result</span>
        <h1><?= $passed ? 'Quiz passed' : 'Quiz not passed' ?></h1>
        <p class="quiz-result-summary">
            Your submission has been scored by FastPhunzira's assessment engine.
        </p>

        <div class="quiz-result-score" aria-label="Quiz percentage">
            <strong><?= htmlspecialchars(rtrim(rtrim(number_format($percentage, 1, '.', ''), '0'), '.') , ENT_QUOTES, 'UTF-8') ?>%</strong>
            <span>Final percentage</span>
        </div>

        <div class="quiz-result-stats">
            <div>
                <span>Score</span>
                <strong><?= $score ?></strong>
            </div>
            <div>
                <span>Status</span>
                <strong><?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
            <?php if ($attemptNumber > 0): ?>
                <div>
                    <span>Attempt</span>
                    <strong>#<?= $attemptNumber ?></strong>
                </div>
            <?php endif; ?>
        </div>

        <div class="quiz-result-message <?= $passed ? 'is-passed' : 'is-not-passed' ?>">
            <div class="quiz-result-message-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="9"></circle>
                    <path d="M12 10v6M12 7h.01"></path>
                </svg>
            </div>
            <div>
                <strong><?= $passed ? 'Assessment requirement met' : 'Assessment requirement not met' ?></strong>
                <p>
                    <?= $passed
                        ? 'This attempt meets the quiz pass requirement.'
                        : 'This attempt does not meet the quiz pass requirement.' ?>
                </p>
            </div>
        </div>

        <div class="quiz-result-actions">
            <a class="btn" href="<?= htmlspecialchars(base_url('dashboard'), ENT_QUOTES, 'UTF-8') ?>">
                Back to dashboard
            </a>
            <?php if ($quizId > 0): ?>
                <a class="btn btn-secondary" href="<?= htmlspecialchars(base_url('quizzes/' . $quizId), ENT_QUOTES, 'UTF-8') ?>">
                    Return to quiz
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>
