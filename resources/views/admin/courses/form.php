<?php

use App\Support\Csrf;
?>
<section class="card">
    <h1><?= !empty($course) ? 'Edit Course' : 'Create Course' ?></h1>

    <?php if (!empty($error)): ?>
        <div class="alert error"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= htmlspecialchars(base_url(!empty($course) ? 'admin/courses/update' : 'admin/courses/store'), ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="_token" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
        <?php if (!empty($course)): ?>
            <input type="hidden" name="id" value="<?= (int) ($course['id'] ?? 0) ?>">
        <?php endif; ?>

        <div class="form-group">
            <label for="title">Title</label>
            <input id="title" type="text" name="title" value="<?= htmlspecialchars((string) ($course['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
        </div>

        <div class="form-group">
            <label for="slug">Slug</label>
            <input id="slug" type="text" name="slug" value="<?= htmlspecialchars((string) ($course['slug'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" required><?= htmlspecialchars((string) ($course['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>

        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="draft" <?= (($course['status'] ?? 'draft') === 'draft') ? 'selected' : '' ?>>Draft</option>
                <option value="published" <?= (($course['status'] ?? 'draft') === 'published') ? 'selected' : '' ?>>Published</option>
            </select>
        </div>

        <button class="btn" type="submit">Save course</button>
        <a class="btn secondary" href="<?= htmlspecialchars(base_url('admin/courses'), ENT_QUOTES, 'UTF-8') ?>">Cancel</a>
    </form>
</section>
