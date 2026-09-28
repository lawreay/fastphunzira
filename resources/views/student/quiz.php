<?php
use App\Support\Csrf;

$quizId = (int) ($quiz['id'] ?? 0);
$quizTitle = trim((string) ($quiz['title'] ?? 'Quiz'));
$quizDescription = trim((string) ($quiz['description'] ?? ''));
$attemptsAllowed = (int) ($quiz['attempts_allowed'] ?? 0);
$passPercentage = (float) ($quiz['pass_percentage'] ?? 0);
$questions = is_array($quiz['questions'] ?? null) ? $quiz['questions'] : [];
?>

<section class="quiz-page">
    <div class="quiz-breadcrumb">
        <a href="<?= htmlspecialchars(base_url('courses'), ENT_QUOTES, 'UTF-8') ?>">Catalogue</a>
        <span aria-hidden="true">/</span>
        <a href="<?= htmlspecialchars(base_url('courses/' . (int) ($quiz['course_id'] ?? 0) . '/quizzes'), ENT_QUOTES, 'UTF-8') ?>">Practice</a>
        <span aria-hidden="true">/</span>
        <strong><?= htmlspecialchars($quizTitle, ENT_QUOTES, 'UTF-8') ?></strong>
    </div>

    <?php if (!$attempt): ?>
        <section class="quiz-start">
            <div class="quiz-start-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="M6 4h12v16H6zM9 8h6M9 12h6M9 16h3"/></svg>
            </div>
            <span class="eyebrow">Practice quiz</span>
            <h1><?= htmlspecialchars($quizTitle, ENT_QUOTES, 'UTF-8') ?></h1>

            <?php if ($quizDescription !== ''): ?>
                <p class="quiz-start-description"><?= htmlspecialchars($quizDescription, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>

            <div class="quiz-start-meta">
                <div>
                    <span>Questions</span>
                    <strong><?= count($questions) ?></strong>
                </div>
                <div>
                    <span>Attempts allowed</span>
                    <strong><?= $attemptsAllowed ?></strong>
                </div>
                <div>
                    <span>Pass mark</span>
                    <strong><?= number_format($passPercentage, 0) ?>%</strong>
                </div>
            </div>

            <div class="quiz-start-note">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v18M5 8h10a4 4 0 0 1 0 8H7a2 2 0 0 1 0-4h8"/></svg>
                <span>Your answers are submitted together and scored on the server.</span>
            </div>

            <form method="post" action="<?= htmlspecialchars(base_url('quizzes/' . $quizId . '/start'), ENT_QUOTES, 'UTF-8') ?>">
                <?= Csrf::input() ?>
                <button class="btn quiz-start-action" type="submit">
                    Start quiz
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h13m-5-5 5 5-5 5"/></svg>
                </button>
            </form>
        </section>
    <?php else: ?>
        <header class="quiz-attempt-header">
            <div>
                <span class="eyebrow">Quiz in progress</span>
                <h1><?= htmlspecialchars($quizTitle, ENT_QUOTES, 'UTF-8') ?></h1>
                <p>Answer each question, review your selections, then submit the quiz when you are finished.</p>
            </div>
            <div class="quiz-question-count">
                <strong><?= count($questions) ?></strong>
                <span><?= count($questions) === 1 ? 'question' : 'questions' ?></span>
            </div>
        </header>

        <?php if (empty($questions)): ?>
            <section class="quiz-empty">
                <span class="eyebrow">Quiz content</span>
                <h2>No questions are available</h2>
                <p>This quiz has no questions yet, so there is currently nothing for the human to click.</p>
                <a class="btn secondary" href="<?= htmlspecialchars(base_url('courses/' . (int) ($quiz['course_id'] ?? 0) . '/quizzes'), ENT_QUOTES, 'UTF-8') ?>">Back to practice</a>
            </section>
        <?php else: ?>
            <form class="quiz-attempt-form" method="post" action="<?= htmlspecialchars(base_url('quiz-attempts/' . (int) $attempt['id'] . '/submit'), ENT_QUOTES, 'UTF-8') ?>">
                <?= Csrf::input() ?>

                <div class="quiz-question-list">
                    <?php foreach ($questions as $index => $question): ?>
                        <fieldset class="quiz-question">
                            <legend>
                                <span class="quiz-question-number">Question <?= $index + 1 ?></span>
                                <span class="quiz-question-text"><?= htmlspecialchars((string) ($question['question_text'] ?? 'Question'), ENT_QUOTES, 'UTF-8') ?></span>
                            </legend>

                            <div class="quiz-options">
                                <?php foreach (($question['options'] ?? []) as $optionIndex => $option): ?>
                                    <?php $optionId = (int) ($option['id'] ?? 0); ?>
                                    <label class="quiz-option">
                                        <input type="radio" name="answers[<?= (int) ($question['id'] ?? 0) ?>]" value="<?= $optionId ?>">
                                        <span class="quiz-option-key"><?= chr(65 + $optionIndex) ?></span>
                                        <span class="quiz-option-text"><?= htmlspecialchars((string) ($option['option_text'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </fieldset>
                    <?php endforeach; ?>
                </div>

                <div class="quiz-submit-card">
                    <div>
                        <span class="eyebrow">Ready to submit?</span>
                        <strong>Review your answers before finishing.</strong>
                    </div>
                    <button class="btn" type="submit">
                        Submit quiz
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h13m-5-5 5 5-5 5"/></svg>
                    </button>
                </div>
            </form>
        <?php endif; ?>
    <?php endif; ?>
</section>
