<?php
use App\Support\Csrf;
?>
<section class="card">
    <h1><?= htmlspecialchars((string) $quiz['title'], ENT_QUOTES, 'UTF-8') ?></h1>
    <p><?= htmlspecialchars((string) ($quiz['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>

    <?php if (!$attempt): ?>
        <p>This quiz allows <?= (int) $quiz['attempts_allowed'] ?> attempt(s) and requires <?= (float) $quiz['pass_percentage'] ?>% to pass.</p>
        <form method="post" action="/quizzes/<?= (int) $quiz['id'] ?>/start">
            <?= Csrf::input() ?>
            <button class="btn" type="submit">Start Quiz</button>
        </form>
    <?php else: ?>
        <form method="post" action="/quiz-attempts/<?= (int) $attempt['id'] ?>/submit">
            <?= Csrf::input() ?>
            <?php foreach ($quiz['questions'] as $index => $question): ?>
                <fieldset style="margin:20px 0;padding:16px;">
                    <legend><strong><?= $index + 1 ?>. <?= htmlspecialchars((string) $question['question_text'], ENT_QUOTES, 'UTF-8') ?></strong></legend>
                    <?php foreach ($question['options'] as $option): ?>
                        <label style="display:block;margin:10px 0;">
                            <input type="radio" name="answers[<?= (int) $question['id'] ?>]" value="<?= (int) $option['id'] ?>">
                            <?= htmlspecialchars((string) $option['option_text'], ENT_QUOTES, 'UTF-8') ?>
                        </label>
                    <?php endforeach; ?>
                </fieldset>
            <?php endforeach; ?>
            <button class="btn" type="submit">Submit Quiz</button>
        </form>
    <?php endif; ?>
</section>
