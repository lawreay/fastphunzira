<?php
use AppSupportCsrf;
?>
<section class="card">
    <h1>Create Exam</h1>
    <p>Course: <?= htmlspecialchars((string) $course['title'], ENT_QUOTES, 'UTF-8') ?></p>

    <form method="post" action="<?= htmlspecialchars(base_url('admin/exams/store'), ENT_QUOTES, 'UTF-8') ?>">
        <?= Csrf::input() ?>
        <input type="hidden" name="course_id" value="<?= (int) $course['id'] ?>">

        <label>Title<br>
            <input name="title" required>
        </label><br>

        <label>Description<br>
            <textarea name="description" rows="4"></textarea>
        </label><br>

        <label>Time limit (minutes)<br>
            <input type="number" name="time_limit" min="1" value="60" required>
        </label><br>

        <label>Pass percentage<br>
            <input type="number" name="passing_score" min="0" max="100" step="0.1" value="70" required>
        </label><br>

        <label>Attempts allowed<br>
            <input type="number" name="attempts_allowed" min="1" value="1" required>
        </label><br>

        <button class="btn" type="submit">Create Exam</button>
    </form>
</section>
