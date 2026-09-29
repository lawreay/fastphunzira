<?php
use AppSupportCsrf;

$examId = (int) ($exam['id'] ?? 0);
$title = trim((string) ($exam['title'] ?? 'Exam'));
$description = trim((string) ($exam['description'] ?? ''));
$questionCount = count($exam['questions'] ?? []);
$timeLimit = (int) ($exam['time_limit'] ?? 0);
$passingScore = (float) ($exam['passing_score'] ?? 0);
?>
<section class="exam-page">
    <div class="exam-breadcrumb">
        <a href="<?= htmlspecialchars(base_url('courses/' . (int) ($exam['course_id'] ?? 0) . '/exams'), ENT_QUOTES, 'UTF-8') ?>">Course exams</a>
        <span>/</span>
        <strong><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></strong>
    </div>

    <?php if (!$attempt): ?>
        <section class="exam-start-card">
            <span class="eyebrow">Ready when you are</span>
            <h1><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h1>
            <?php if ($description !== ''): ?><p class="exam-description"><?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>

            <div class="exam-start-meta">
                <div><strong><?= $questionCount ?></strong><span><?= $questionCount === 1 ? 'Question' : 'Questions' ?></span></div>
                <div><strong><?= $timeLimit ?></strong><span>Minutes</span></div>
                <div><strong><?= number_format($passingScore, 0) ?>%</strong><span>Pass mark</span></div>
                <div><strong><?= (int) ($exam['attempts_allowed'] ?? 1) ?></strong><span><?= (int) ($exam['attempts_allowed'] ?? 1) === 1 ? 'Attempt' : 'Attempts' ?></span></div>
            </div>

            <div class="exam-start-note">
                <strong>Before you start</strong>
                <span>The exam timer is enforced by the server. Submit your answers before the time limit expires.</span>
            </div>

            <form method="post" action="<?= htmlspecialchars(base_url('exams/' . $examId . '/start'), ENT_QUOTES, 'UTF-8') ?>">
                <?= Csrf::input() ?>
                <button class="btn btn-block" type="submit">Start exam</button>
            </form>
        </section>
    <?php else: ?>
        <div class="exam-attempt-topbar">
            <div>
                <span class="eyebrow">Exam in progress</span>
                <h1><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h1>
            </div>
            <div class="exam-timer-card">
                <span>Server expiry</span>
                <strong><?= htmlspecialchars((string) $attempt['expires_at'], ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
        </div>

        <form method="post" action="<?= htmlspecialchars(base_url('exam-attempts/' . (int) $attempt['id'] . '/submit'), ENT_QUOTES, 'UTF-8') ?>">
            <?= Csrf::input() ?>
            <div class="exam-question-list">
                <?php foreach ($exam['questions'] as $index => $question): ?>
                    <fieldset class="exam-question">
                        <legend>
                            <span>Question <?= $index + 1 ?></span>
                            <strong><?= htmlspecialchars((string) $question['question_text'], ENT_QUOTES, 'UTF-8') ?></strong>
                        </legend>
                        <div class="exam-options">
                            <?php foreach ($question['options'] as $optionIndex => $option): ?>
                                <label class="exam-option">
                                    <input type="radio" name="answers[<?= (int) $question['id'] ?>][selected_option_id]" value="<?= (int) $option['id'] ?>">
                                    <span class="exam-option-key"><?= chr(65 + $optionIndex) ?></span>
                                    <span><?= htmlspecialchars((string) $option['option_text'], ENT_QUOTES, 'UTF-8') ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </fieldset>
                <?php endforeach; ?>
            </div>
            <div class="exam-submit-bar">
                <div><strong>Ready to submit?</strong><span>Review your selections before final submission.</span></div>
                <button class="btn" type="submit">Submit exam</button>
            </div>
        </form>
    <?php endif; ?>
</section>