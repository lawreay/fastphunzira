<?php

use App\Core\Auth;
?>
<section class="card">
    <h1>Course Administration</h1>

    <?php if (!empty($success)): ?>
        <div class="alert success"><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert error"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <?php if (Auth::userCan('courses.manage')): ?>
        <p><a class="btn" href="<?= htmlspecialchars(base_url('admin/courses/create'), ENT_QUOTES, 'UTF-8') ?>">New course</a></p>
    <?php endif; ?>

    <table class="table">
        <thead>
        <tr>
            <th>Title</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($courses as $course): ?>
            <tr>
                <td><?= htmlspecialchars((string) ($course['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string) ($course['status'] ?? 'draft'), ENT_QUOTES, 'UTF-8') ?></td>
                <td class="actions">
                    <a class="btn secondary" href="<?= htmlspecialchars(base_url('courses/' . (int) ($course['id'] ?? 0)), ENT_QUOTES, 'UTF-8') ?>">View</a>
                    <a class="btn secondary" href="<?= htmlspecialchars(base_url('admin/courses/edit?id=' . (int) ($course['id'] ?? 0)), ENT_QUOTES, 'UTF-8') ?>">Edit</a>
                    <form class="inline-form" method="POST" action="<?= htmlspecialchars(base_url('admin/courses/publish'), ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="id" value="<?= (int) ($course['id'] ?? 0) ?>">
                        <button class="btn light" type="submit">Publish</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
