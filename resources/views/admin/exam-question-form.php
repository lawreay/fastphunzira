<?php
use App\Support\Csrf;
$examId=(int)($exam['id']??0); $examTitle=trim((string)($exam['title']??'Exam'));
?>
<section class="admin-form-page">
 <div class="admin-form-heading"><div><span class="eyebrow">Assessment authoring</span><h1>Add exam question</h1><p>Write the question, define its marks, and select exactly one correct answer.</p></div><a class="btn secondary" href="<?= htmlspecialchars(base_url('admin/courses'),ENT_QUOTES,'UTF-8') ?>">← Assessments</a></div>
 <div class="admin-form-layout">
  <form class="admin-editor-card" method="post" action="<?= htmlspecialchars(base_url('admin/exams/'.$examId.'/questions/store'),ENT_QUOTES,'UTF-8') ?>">
   <?= Csrf::input() ?>
   <div class="admin-editor-section"><span class="eyebrow">01 · Question</span><div class="admin-context-chip">Exam · <?= htmlspecialchars($examTitle,ENT_QUOTES,'UTF-8') ?></div>
    <div class="form-group"><label for="question-text">Question</label><textarea id="question-text" name="question_text" rows="6" required></textarea></div>
    <div class="form-group"><label for="marks">Marks</label><input id="marks" type="number" name="marks" min="0.1" step="0.1" value="1" required></div>
   </div>
   <div class="admin-editor-section"><span class="eyebrow">02 · Answer options</span><p class="form-help">Select the correct answer. The answer key is never sent to students during an exam attempt.</p>
    <div class="admin-option-list">
     <?php foreach(['A','B','C','D'] as $letter): ?>
      <fieldset class="admin-option-card"><legend><?= $letter ?></legend><div class="form-group"><label class="sr-only" for="option-<?= $letter ?>">Option <?= $letter ?></label><input id="option-<?= $letter ?>" name="options[<?= $letter ?>][option_text]" required></div><label class="admin-radio"><input type="radio" name="correct_option" value="<?= $letter ?>" <?= $letter==='A'?'checked':'' ?>><span>Correct answer</span></label></fieldset>
     <?php endforeach; ?>
    </div>
   </div>
   <div class="admin-editor-actions"><button class="btn" type="submit">Add question</button></div>
  </form>
  <aside class="admin-editor-sidebar"><div class="admin-editor-note"><span class="eyebrow">Question quality</span><h2>Keep one clear answer</h2><p>Write options that are distinct and make the intended answer unambiguous.</p></div><div class="admin-editor-note"><span class="eyebrow">Next step</span><h2>Review before publishing</h2><p>Add all required questions, check marks and answer keys, then publish the exam.</p></div></aside>
 </div>
 <form class="admin-publish-card" method="post" action="<?= htmlspecialchars(base_url('admin/exams/'.$examId.'/publish'),ENT_QUOTES,'UTF-8') ?>"><?= Csrf::input() ?><div><span class="eyebrow">Publishing</span><strong>Ready to publish this exam?</strong><p>Only publish after its questions and answer keys have been reviewed.</p></div><button class="btn" type="submit">Publish exam</button></form>
</section>