<?php
use AppSupportCsrf;
?>
<section class="card">
    <h1><?= htmlspecialchars((string) $exam['title'], ENT_QUOTES, 'UTF-8') ?></h1>
    <p><?= htmlspecialchars((string) ($exam['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
    <p>
        <strong>Time limit:</strong> <?= (int) $exam['time_limit'] ?> minutes |
        <strong>Pass mark:</strong> <?= (float) $exam['passing_score'] ?>%
    </p>

    <?php if (!$attempt): ?>
        <form method="post" action="<?= htmlspecialchars(base_url('exams/' . (int) $exam['id'] . '/start'), ENT_QUOTES, 'UTF-8') ?>">
            <?= Csrf::input() ?>
            <button class="btn" type="submit">Start Exam</button>
        </form>
    <?php else: ?>
        <p><strong>Server expiry:</strong> <?= htmlspecialchars((string) $attempt['expires_at'], ENT_QUOTES, 'UTF-8') ?></p>

        <form method="post" action="<?= htmlspecialchars(base_url('exam-attempts/' . (int) $attempt['id'] . '/submit'), ENT_QUOTES, 'UTF-8') ?>">
            <?= Csrf::input() ?>

            <?php foreach ($exam['questions'] as $index => $question): ?>
                <fieldset style="margin:20px 0;padding:16px;">
                    <legend>
                        <strong><?= $index + 1 ?>. <?= htmlspecialchars((string) $question['question_text'], ENT_QUOTES, 'UTF-8') ?></strong>
                    </legend>

                    <?php foreach ($question['options'] as $option): ?>
                        <label style="display:block;margin:10px 0;">
                            <input
                                type="radio"
                                name="answers[<?= (int) $question['id'] ?>][selected_option_id]"
                                value="<?= (int) $option['id'] ?>"
                            >
                            <?= htmlspecialchars((string) $option['option_text'], ENT_QUOTES, 'UTF-8') ?>
                        </label>
                    <?php endforeach; ?>
                </fieldset>
            <?php endforeach; ?>

            <button class="btn" type="submit">Submit Exam</button>
        </form>
    <?php endif; ?>
</section>
