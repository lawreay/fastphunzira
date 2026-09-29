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

<?php if (!empty($course)): ?>
<section class="admin-content-manager">
    <div class="admin-content-heading">
        <div><span class="eyebrow">Course structure</span><h2>Build the learning path</h2><p>Add, edit and remove modules. Manage practice quizzes and exams from the same course workspace.</p></div>
    </div>

    <div class="admin-content-grid">
        <div class="admin-content-panel">
            <div class="admin-content-panel-heading"><div><span class="eyebrow">Modules</span><h3>Course modules</h3></div><span class="admin-count"><?= count($modules ?? []) ?></span></div>

            <form class="admin-inline-create" method="post" action="<?= htmlspecialchars(base_url('admin/courses/' . (int) $course['id'] . '/modules/store'), ENT_QUOTES, 'UTF-8') ?>">
                <?= Csrf::input() ?>
                <input name="title" placeholder="New module title" required>
                <input name="description" placeholder="Short module description" required>
                <input name="sort_order" type="number" min="0" value="<?= count($modules ?? []) ?>" aria-label="Module order">
                <button class="btn" type="submit">Add module</button>
            </form>

            <?php if (empty($modules)): ?>
                <div class="admin-empty-content"><strong>No modules yet</strong><span>Create the first module above, then add lessons to it.</span></div>
            <?php else: ?>
                <div class="admin-managed-list">
                    <?php foreach ($modules as $module): ?>
                        <div class="admin-managed-item">
                            <form method="post" action="<?= htmlspecialchars(base_url('admin/modules/' . (int) $module['id'] . '/update'), ENT_QUOTES, 'UTF-8') ?>" class="admin-managed-main">
                                <?= Csrf::input() ?>
                                <div class="admin-managed-fields">
                                    <input name="title" value="<?= htmlspecialchars((string) ($module['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
                                    <input name="description" value="<?= htmlspecialchars((string) ($module['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
                                    <input name="sort_order" type="number" min="0" value="<?= (int) ($module['sort_order'] ?? 0) ?>" aria-label="Module order">
                                </div>
                                <div class="admin-managed-actions">
                                    <a class="btn secondary" href="<?= htmlspecialchars(base_url('admin/modules/' . (int) $module['id'] . '/lessons/create'), ENT_QUOTES, 'UTF-8') ?>">Add lesson</a>
                                    <button class="btn secondary" type="submit">Save</button>
                                </div>
                            </form>
                            <form method="post" action="<?= htmlspecialchars(base_url('admin/modules/' . (int) $module['id'] . '/delete'), ENT_QUOTES, 'UTF-8') ?>" onsubmit="return confirm('Remove this module and all lessons inside it? This cannot be undone.');">
                                <?= Csrf::input() ?>
                                <button class="btn danger" type="submit">Remove</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="admin-content-panel">
            <div class="admin-content-panel-heading"><div><span class="eyebrow">Practice</span><h3>Quizzes</h3></div><span class="admin-count"><?= count($quizzes ?? []) ?></span></div>
            <div class="admin-resource-actions"><a class="btn" href="<?= htmlspecialchars(base_url('admin/courses/' . (int) $course['id'] . '/quizzes/create'), ENT_QUOTES, 'UTF-8') ?>">Add quiz</a></div>
            <?php if (empty($quizzes)): ?>
                <div class="admin-empty-content"><strong>No quizzes yet</strong><span>Create practice assessments for this course.</span></div>
            <?php else: ?>
                <div class="admin-managed-list">
                <?php foreach ($quizzes as $quiz): ?>
                    <div class="admin-resource-item">
                        <div><strong><?= htmlspecialchars((string) ($quiz['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong><span><?= htmlspecialchars(ucfirst((string) ($quiz['status'] ?? 'draft')), ENT_QUOTES, 'UTF-8') ?> · <?= (int) ($quiz['attempts_allowed'] ?? 1) ?> attempt(s)</span></div>
                        <div class="admin-managed-actions">
                            <a class="btn secondary" href="<?= htmlspecialchars(base_url('admin/quizzes/' . (int) $quiz['id'] . '/questions/create'), ENT_QUOTES, 'UTF-8') ?>">Questions</a>
                            <?php if (($quiz['status'] ?? 'draft') !== 'archived'): ?>
                            <form method="post" action="<?= htmlspecialchars(base_url('admin/quizzes/' . (int) $quiz['id'] . '/archive'), ENT_QUOTES, 'UTF-8') ?>" onsubmit="return confirm('Archive this quiz? Students will no longer see it as published.');"><?= Csrf::input() ?><button class="btn danger" type="submit">Remove</button></form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="admin-content-panel">
            <div class="admin-content-panel-heading"><div><span class="eyebrow">Assessment</span><h3>Exams</h3></div><span class="admin-count"><?= count($exams ?? []) ?></span></div>
            <div class="admin-resource-actions"><a class="btn" href="<?= htmlspecialchars(base_url('admin/courses/' . (int) $course['id'] . '/exams/create'), ENT_QUOTES, 'UTF-8') ?>">Add exam</a></div>
            <?php if (empty($exams)): ?>
                <div class="admin-empty-content"><strong>No exams yet</strong><span>Create timed assessments for this course.</span></div>
            <?php else: ?>
                <div class="admin-managed-list">
                <?php foreach ($exams as $exam): ?>
                    <div class="admin-resource-item">
                        <div><strong><?= htmlspecialchars((string) ($exam['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong><span><?= htmlspecialchars(ucfirst((string) ($exam['status'] ?? 'draft')), ENT_QUOTES, 'UTF-8') ?> · <?= (int) ($exam['time_limit'] ?? 0) ?> min</span></div>
                        <div class="admin-managed-actions">
                            <a class="btn secondary" href="<?= htmlspecialchars(base_url('admin/exams/' . (int) $exam['id'] . '/questions/create'), ENT_QUOTES, 'UTF-8') ?>">Questions</a>
                            <?php if (($exam['status'] ?? 'draft') !== 'archived'): ?>
                            <form method="post" action="<?= htmlspecialchars(base_url('admin/exams/' . (int) $exam['id'] . '/archive'), ENT_QUOTES, 'UTF-8') ?>" onsubmit="return confirm('Archive this exam? Students will no longer see it as published.');"><?= Csrf::input() ?><button class="btn danger" type="submit">Remove</button></form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?>
