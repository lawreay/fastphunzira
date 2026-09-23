<?php
use App\Support\Csrf;
?>
<section class="card">
    <h1>Add Question</h1>
    <p><?= htmlspecialchars((string) $quiz['title'], ENT_QUOTES, 'UTF-8') ?></p>

    <form method="post" action="/admin/quizzes/<?= (int) $quiz['id'] ?>/questions/store">
        <?= Csrf::input() ?>
        <label>Question<br><textarea name="question_text" rows="4" required></textarea></label><br>

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
