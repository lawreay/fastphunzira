<?php
use App\Support\Csrf;
$quizId=(int)($quiz['id']??0); $quizTitle=trim((string)($quiz['title']??'Quiz')); $quizStatus=strtolower((string)($quiz['status']??'draft'));
?>
<section class="admin-form-page">
 <div class="admin-form-heading"><div><span class="eyebrow">Practice authoring</span><h1>Add quiz question</h1><p>Write a clear practice question and define its answer key.</p></div><a class="btn secondary" href="<?= htmlspecialchars(base_url('admin/courses'),ENT_QUOTES,'UTF-8') ?>">← Quizzes</a></div>
 <div class="admin-form-layout">
  <form class="admin-editor-card" method="post" action="<?= htmlspecialchars(base_url('admin/quizzes/'.$quizId.'/questions/store'),ENT_QUOTES,'UTF-8') ?>">
   <?= Csrf::input() ?>
   <div class="admin-editor-section"><span class="eyebrow">01 · Question</span><div class="admin-context-chip">Quiz · <?= htmlspecialchars($quizTitle,ENT_QUOTES,'UTF-8') ?></div>
    <div class="form-group"><label for="quiz-question">Question</label><textarea id="quiz-question" name="question_text" rows="6" required></textarea></div>
   </div>
   <div class="admin-editor-section"><span class="eyebrow">02 · Answer options</span><p class="form-help">Choose the correct option. Students receive the question and options, not the answer key.</p>
    <div class="admin-option-list"><?php foreach(['A','B','C','D'] as $letter): ?><fieldset class="admin-option-card"><legend><?= $letter ?></legend><div class="form-group"><label class="sr-only" for="quiz-option-<?= $letter ?>">Option <?= $letter ?></label><input id="quiz-option-<?= $letter ?>" name="options[<?= $letter ?>][option_text]" required></div><label class="admin-radio"><input type="radio" name="correct_option" value="<?= $letter ?>" <?= $letter==='A'?'checked':'' ?>><span>Correct answer</span></label></fieldset><?php endforeach; ?></div>
   </div>
   <div class="admin-editor-actions"><button class="btn" type="submit">Add question</button></div>
  </form>
  <aside class="admin-editor-sidebar"><div class="admin-editor-note"><span class="eyebrow">Question quality</span><h2>Make the answer clear</h2><p>Avoid ambiguous options and keep distractors plausible enough to test understanding.</p></div><div class="admin-editor-note"><span class="eyebrow">Publishing</span><h2><?= $quizStatus==='published'?'Quiz published':'Draft quiz' ?></h2><p><?= $quizStatus==='published'?'This quiz is already published. Review changes carefully.':'Add the required questions before publishing the quiz.' ?></p></div></aside>
 </div>
 <?php if ($quizStatus !== 'published'): ?><form class="admin-publish-card" method="post" action="<?= htmlspecialchars(base_url('admin/quizzes/'.$quizId.'/publish'),ENT_QUOTES,'UTF-8') ?>"><?= Csrf::input() ?><div><span class="eyebrow">Publishing</span><strong>Ready to publish this quiz?</strong><p>Publish only after checking the questions and answer keys.</p></div><button class="btn" type="submit">Publish quiz</button></form><?php else: ?><div class="admin-publish-card"><div><span class="eyebrow">Status</span><strong>Quiz published</strong><p>The quiz is currently available according to its existing publication rules.</p></div></div><?php endif; ?>
</section>