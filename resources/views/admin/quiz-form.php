<?php
use App\Support\Csrf;
$courseId=(int)($course['id']??0); $courseTitle=trim((string)($course['title']??'Untitled course'));
?>
<section class="admin-form-page">
 <div class="admin-form-heading"><div><span class="eyebrow">Practice authoring</span><h1>Create quiz</h1><p>Configure a practice quiz for learners in this course.</p></div><a class="btn secondary" href="<?= htmlspecialchars(base_url('admin/courses/'.$courseId.'/edit'),ENT_QUOTES,'UTF-8') ?>">← Course</a></div>
 <div class="admin-form-layout">
  <form class="admin-editor-card" method="post" action="<?= htmlspecialchars(base_url('admin/quizzes/store'),ENT_QUOTES,'UTF-8') ?>">
   <?= Csrf::input() ?><input type="hidden" name="course_id" value="<?= $courseId ?>">
   <div class="admin-editor-section"><span class="eyebrow">01 · Quiz details</span><div class="admin-context-chip">Course · <?= htmlspecialchars($courseTitle,ENT_QUOTES,'UTF-8') ?></div>
    <div class="form-group"><label for="quiz-title">Title</label><input id="quiz-title" name="title" required maxlength="255"></div>
    <div class="form-group"><label for="quiz-description">Description</label><textarea id="quiz-description" name="description" rows="5"></textarea></div>
   </div>
   <div class="admin-editor-section"><span class="eyebrow">02 · Practice rules</span><div class="admin-form-grid">
    <div class="form-group"><label for="pass-percentage">Pass percentage</label><input id="pass-percentage" type="number" name="pass_percentage" min="0" max="100" value="50" required></div>
    <div class="form-group"><label for="quiz-attempts">Attempts allowed</label><input id="quiz-attempts" type="number" name="attempts_allowed" min="1" value="1" required></div>
    <div class="form-group"><label for="quiz-status">Status</label><select id="quiz-status" name="status"><option value="draft">Draft</option><option value="published">Published</option></select></div>
   </div></div>
   <div class="admin-editor-actions"><button class="btn" type="submit">Create quiz</button><a class="btn secondary" href="<?= htmlspecialchars(base_url('admin/courses/'.$courseId.'/edit'),ENT_QUOTES,'UTF-8') ?>">Cancel</a></div>
  </form>
  <aside class="admin-editor-sidebar"><div class="admin-editor-note"><span class="eyebrow">Practice</span><h2>Build for repetition</h2><p>Quizzes are practice assessments. Keep them focused enough that learners can retry and learn from mistakes.</p></div><div class="admin-editor-note"><span class="eyebrow">Workflow</span><h2>Add questions next</h2><p>Create the quiz first, then add its questions and answer keys before publishing.</p></div></aside>
 </div>
</section>