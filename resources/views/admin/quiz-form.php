<?php
use App\Support\Csrf;
?>
<section class="card">
    <h1>Create Quiz</h1>
    <form method="post" action="/admin/quizzes/store">
        <?= Csrf::input() ?>
        <input type="hidden" name="course_id" value="<?= (int) $course['id'] ?>">

        <label>Title<br><input name="title" required></label><br>
        <label>Description<br><textarea name="description" rows="4"></textarea></label><br>
        <label>Pass percentage<br><input type="number" name="pass_percentage" min="0" max="100" value="50" required></label><br>
        <label>Attempts allowed<br><input type="number" name="attempts_allowed" min="1" value="1" required></label><br>
        <label>Status<br>
            <select name="status">
                <option value="draft">Draft</option>
                <option value="published">Published</option>
            </select>
        </label><br>
        <button class="btn" type="submit">Create Quiz</button>
    </form>
</section>
