<?php
use App\Support\Csrf;
?>
<section class="card">
    <h1>Add Exam Question</h1>
    <p><?= htmlspecialchars((string) $exam['title'], ENT_QUOTES, 'UTF-8') ?></p>

    <form method="post" action="<?= htmlspecialchars(base_url('admin/exams/' . (int) $exam['id'] . '/questions/store'), ENT_QUOTES, 'UTF-8') ?>">
        <?= Csrf::input() ?>

        <label>Question<br>
            <textarea name="question_text" rows="4" required></textarea>
        </label><br>

        <label>Marks<br>
            <input type="number" name="marks" min="0.1" step="0.1" value="1" required>
        </label><br>

        <?php foreach (['A', 'B', 'C', 'D'] as $letter): ?>
            <fieldset style="margin:12px 0;padding:12px;">
                <label><?= $letter ?> option<br>
                    <input name="options[<?= $letter ?>][option_text]" required>
                </label>
                <label>
                    <input type="radio" name="correct_option" value="<?= $letter ?>" <?= $letter === 'A' ? 'checked' : '' ?>>
                    Correct answer
                </label>
            </fieldset>
        <?php endforeach; ?>

        <button class="btn" type="submit">Add Question</button>
    </form>
</section>

<form method="post" action="<?= htmlspecialchars(base_url('admin/exams/' . (int) $exam['id'] . '/publish'), ENT_QUOTES, 'UTF-8') ?>" style="margin-top:16px;">
    <?= Csrf::input() ?>
    <button class="btn" type="submit">Publish Exam</button>
</form>
