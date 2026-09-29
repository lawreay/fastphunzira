<?php
use App\Support\Csrf;

$isEditing = !empty($course);
$courseId = (int) ($course['id'] ?? 0);
$activeTab = strtolower((string) ($_GET['tab'] ?? 'details'));
$allowedTabs = ['details', 'modules', 'exams', 'quizzes'];
if (!$isEditing) {
    $activeTab = 'details';
} elseif (!in_array($activeTab, $allowedTabs, true)) {
    $activeTab = 'details';
}
$tabUrl = static fn(string $tab): string => base_url('admin/courses/' . $courseId . '/edit?tab=' . rawurlencode($tab));
?>

<section class="admin-form-page">
    <div class="admin-form-heading">
        <div>
            <span class="eyebrow"><?= $isEditing ? 'Course administration' : 'Course setup' ?></span>
            <h1><?= $isEditing ? 'Edit Course' : 'Create Course' ?></h1>
            <p><?= $isEditing ? 'Manage the course details and its learning content from one workspace.' : 'Set up the course information before adding learning content.' ?></p>
        </div>
        <a class="btn secondary" href="<?= htmlspecialchars(base_url('admin/courses'), ENT_QUOTES, 'UTF-8') ?>">← Courses</a>
    </div>

    <?php if ($isEditing): ?>
        <nav class="admin-course-tabs" aria-label="Course administration">
            <a class="<?= $activeTab === 'details' ? 'is-active' : '' ?>" href="<?= htmlspecialchars($tabUrl('details'), ENT_QUOTES, 'UTF-8') ?>">Course details</a>
            <a class="<?= $activeTab === 'modules' ? 'is-active' : '' ?>" href="<?= htmlspecialchars($tabUrl('modules'), ENT_QUOTES, 'UTF-8') ?>">Modules <span><?= count($modules ?? []) ?></span></a>
            <a class="<?= $activeTab === 'exams' ? 'is-active' : '' ?>" href="<?= htmlspecialchars($tabUrl('exams'), ENT_QUOTES, 'UTF-8') ?>">Exams <span><?= count($exams ?? []) ?></span></a>
            <a class="<?= $activeTab === 'quizzes' ? 'is-active' : '' ?>" href="<?= htmlspecialchars($tabUrl('quizzes'), ENT_QUOTES, 'UTF-8') ?>">Quizzes <span><?= count($quizzes ?? []) ?></span></a>
        </nav>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert error"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <?php if ($activeTab === 'details'): ?>
        <div class="admin-form-layout">
            <form class="admin-editor-card" method="POST" action="<?= htmlspecialchars(base_url($isEditing ? 'admin/courses/update' : 'admin/courses/store'), ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="_token" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
                <?php if ($isEditing): ?><input type="hidden" name="id" value="<?= $courseId ?>"><?php endif; ?>

                <div class="admin-editor-section">
                    <span class="eyebrow">01 · Course information</span>
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
                        <textarea id="description" name="description" rows="7" required><?= htmlspecialchars((string) ($course['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                </div>

                <div class="admin-editor-section">
                    <span class="eyebrow">02 · Publishing</span>
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="draft" <?= (($course['status'] ?? 'draft') === 'draft') ? 'selected' : '' ?>>Draft</option>
                            <option value="published" <?= (($course['status'] ?? 'draft') === 'published') ? 'selected' : '' ?>>Published</option>
                        </select>
                        <small>Publish the course only when its content is ready for students.</small>
                    </div>
                </div>

                <div class="admin-editor-actions">
                    <button class="btn" type="submit">Save course</button>
                    <a class="btn secondary" href="<?= htmlspecialchars(base_url('admin/courses'), ENT_QUOTES, 'UTF-8') ?>">Cancel</a>
                </div>
            </form>

            <aside class="admin-editor-sidebar">
                <div class="admin-editor-note"><span class="eyebrow">Course workspace</span><h2>Build from here</h2><p>Use Modules for lessons, Exams for graded assessments, and Quizzes for practice.</p></div>
                <?php if ($isEditing): ?>
                    <div class="admin-editor-note"><span class="eyebrow">Current content</span><h2><?= count($modules ?? []) ?> modules · <?= count($exams ?? []) ?> exams · <?= count($quizzes ?? []) ?> quizzes</h2><p>The counts are also available in the tabs above.</p></div>
                <?php endif; ?>
            </aside>
        </div>
    <?php endif; ?>

    <?php if ($isEditing && $activeTab === 'modules'): ?>
        <section class="admin-tab-panel">
            <div class="admin-tab-heading"><div><span class="eyebrow">Course structure</span><h2>Modules</h2><p>Modules organize the lessons students follow through the course.</p></div><span class="admin-count"><?= count($modules ?? []) ?></span></div>
            <form class="admin-inline-create" method="post" action="<?= htmlspecialchars(base_url('admin/courses/' . $courseId . '/modules/store'), ENT_QUOTES, 'UTF-8') ?>">
                <?= Csrf::input() ?><input name="title" placeholder="New module title" required><input name="description" placeholder="Short module description" required><input name="sort_order" type="number" min="0" value="<?= count($modules ?? []) ?>" aria-label="Module order"><button class="btn" type="submit">Add module</button>
            </form>
            <?php if (empty($modules)): ?><div class="admin-empty-content"><strong>No modules yet</strong><span>Create the first module to start building the course.</span></div><?php else: ?>
                <div class="admin-managed-list"><?php foreach ($modules as $module): ?>
                    <?php $moduleId = (int) ($module['id'] ?? 0); $lessons = $moduleLessons[$moduleId] ?? []; ?>
                    <div class="admin-managed-item admin-module-item">
                        <form method="post" action="<?= htmlspecialchars(base_url('admin/modules/' . $moduleId . '/update'), ENT_QUOTES, 'UTF-8') ?>" class="admin-managed-main">
                            <?= Csrf::input() ?>
                            <div class="admin-managed-fields"><input name="title" value="<?= htmlspecialchars((string) ($module['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required><input name="description" value="<?= htmlspecialchars((string) ($module['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required><input name="sort_order" type="number" min="0" value="<?= (int) ($module['sort_order'] ?? 0) ?>" aria-label="Module order"></div>
                            <div class="admin-module-content">
                                <div class="admin-module-lessons-heading">
                                    <div><strong>Lessons</strong><span><?= count($lessons) ?> lesson<?= count($lessons) === 1 ? '' : 's' ?></span></div>
                                    <a class="btn" href="<?= htmlspecialchars(base_url('admin/modules/' . $moduleId . '/lessons/create-media'), ENT_QUOTES, 'UTF-8') ?>">+ Add lesson</a>
                                </div>
                                <?php if (empty($lessons)): ?>
                                    <div class="admin-module-empty">No lessons yet. Add the first lesson to this module.</div>
                                <?php else: ?>
                                    <div class="admin-module-lessons">
                                        <?php foreach ($lessons as $lesson): ?>
                                            <div class="admin-module-lesson">
                                                <div>
                                                    <strong><?= htmlspecialchars((string) ($lesson['title'] ?? 'Untitled lesson'), ENT_QUOTES, 'UTF-8') ?></strong>
                                                    <span><?= (int) ($lesson['sort_order'] ?? 0) ?></span>
                                                </div>
                                                <div class="admin-module-lesson-actions">
                                                    <a class="btn secondary" href="<?= htmlspecialchars(base_url('admin/lessons/' . (int) ($lesson['id'] ?? 0) . '/edit-media'), ENT_QUOTES, 'UTF-8') ?>">Edit</a>
                                                    <form method="post" action="<?= htmlspecialchars(base_url('admin/lessons/' . (int) ($lesson['id'] ?? 0) . '/delete'), ENT_QUOTES, 'UTF-8') ?>" onsubmit="return confirm('Delete this lesson? This cannot be undone.');">
                                                        <?= Csrf::input() ?>
                                                        <button class="btn danger" type="submit">Delete</button>
                                                    </form>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="admin-managed-actions"><button class="btn secondary" type="submit">Save module</button></div>
                        </form>
                        <form method="post" action="<?= htmlspecialchars(base_url('admin/modules/' . $moduleId . '/delete'), ENT_QUOTES, 'UTF-8') ?>" onsubmit="return confirm('Remove this module and all lessons inside it? This cannot be undone.');"><?= Csrf::input() ?><button class="btn danger" type="submit">Remove module</button></form>
                    </div>
                <?php endforeach; ?></div>
            <?php endif; ?>
        </section>
    <?php endif; ?>

    <?php if ($isEditing && $activeTab === 'quizzes'): ?>
        <section class="admin-tab-panel">
            <div class="admin-tab-heading"><div><span class="eyebrow">Practice</span><h2>Quizzes</h2><p>Manage practice assessments and their questions.</p></div><span class="admin-count"><?= count($quizzes ?? []) ?></span></div>
            <div class="admin-resource-actions"><a class="btn" href="<?= htmlspecialchars(base_url('admin/courses/' . $courseId . '/quizzes/create'), ENT_QUOTES, 'UTF-8') ?>">Add quiz</a></div>
            <?php if (empty($quizzes)): ?><div class="admin-empty-content"><strong>No quizzes yet</strong><span>Create a practice quiz for this course.</span></div><?php else: ?>
                <div class="admin-managed-list"><?php foreach ($quizzes as $quiz): ?>
                    <div class="admin-resource-item"><div><strong><?= htmlspecialchars((string) ($quiz['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong><span><?= htmlspecialchars(ucfirst((string) ($quiz['status'] ?? 'draft')), ENT_QUOTES, 'UTF-8') ?> · <?= (int) ($quiz['attempts_allowed'] ?? 1) ?> attempt(s)</span></div><div class="admin-managed-actions"><a class="btn secondary" href="<?= htmlspecialchars(base_url('admin/quizzes/' . (int) $quiz['id'] . '/questions/create'), ENT_QUOTES, 'UTF-8') ?>">Questions</a><?php if (($quiz['status'] ?? 'draft') !== 'archived'): ?><form method="post" action="<?= htmlspecialchars(base_url('admin/quizzes/' . (int) $quiz['id'] . '/archive'), ENT_QUOTES, 'UTF-8') ?>" onsubmit="return confirm('Archive this quiz? Students will no longer see it as published.');"><?= Csrf::input() ?><button class="btn danger" type="submit">Remove</button></form><?php endif; ?></div></div>
                <?php endforeach; ?></div>
            <?php endif; ?>
        </section>
    <?php endif; ?>

    <?php if ($isEditing && $activeTab === 'exams'): ?>
        <section class="admin-tab-panel">
            <div class="admin-tab-heading"><div><span class="eyebrow">Assessment</span><h2>Exams</h2><p>Manage timed assessments and their questions.</p></div><span class="admin-count"><?= count($exams ?? []) ?></span></div>
            <div class="admin-resource-actions"><a class="btn" href="<?= htmlspecialchars(base_url('admin/courses/' . $courseId . '/exams/create'), ENT_QUOTES, 'UTF-8') ?>">Add exam</a></div>
            <?php if (empty($exams)): ?><div class="admin-empty-content"><strong>No exams yet</strong><span>Create a timed assessment for this course.</span></div><?php else: ?>
                <div class="admin-managed-list"><?php foreach ($exams as $exam): ?>
                    <div class="admin-resource-item"><div><strong><?= htmlspecialchars((string) ($exam['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong><span><?= htmlspecialchars(ucfirst((string) ($exam['status'] ?? 'draft')), ENT_QUOTES, 'UTF-8') ?> · <?= (int) ($exam['time_limit'] ?? 0) ?> min</span></div><div class="admin-managed-actions"><a class="btn secondary" href="<?= htmlspecialchars(base_url('admin/exams/' . (int) $exam['id'] . '/questions/create'), ENT_QUOTES, 'UTF-8') ?>">Questions</a><?php if (($exam['status'] ?? 'draft') !== 'archived'): ?><form method="post" action="<?= htmlspecialchars(base_url('admin/exams/' . (int) $exam['id'] . '/archive'), ENT_QUOTES, 'UTF-8') ?>" onsubmit="return confirm('Archive this exam? Students will no longer see it as published.');"><?= Csrf::input() ?><button class="btn danger" type="submit">Remove</button></form><?php endif; ?></div></div>
                <?php endforeach; ?></div>
            <?php endif; ?>
        </section>
    <?php endif; ?>
</section>
