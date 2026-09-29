<?php
use App\Support\Csrf;
$moduleId=(int)($moduleId??0); $courseId=(int)($courseId??0); $lesson=$lesson??null; $isEditing=$lesson!==null; $lessonId=(int)($lesson['id']??0);
$blocks=$blocks??[];
?>
<section class="admin-form-page">
 <div class="admin-form-heading">
  <div><span class="eyebrow">Lesson builder</span><h1><?= $isEditing?'Edit lesson':'Create lesson' ?></h1><p>Start with the lesson information, then build the learning experience in the order students will see it.</p></div>
  <a class="btn secondary" href="<?= htmlspecialchars(base_url('admin/courses/'.$courseId.'/edit?tab=modules'),ENT_QUOTES,'UTF-8') ?>">← Course</a>
 </div>

 <form id="lesson-builder-form" class="lesson-builder" method="POST" enctype="multipart/form-data" action="<?= htmlspecialchars(base_url($isEditing?'admin/lessons/'.$lessonId.'/save-media':'admin/modules/'.$moduleId.'/lessons/store-media'),ENT_QUOTES,'UTF-8') ?>">
  <input type="hidden" name="_token" value="<?= htmlspecialchars(Csrf::token(),ENT_QUOTES,'UTF-8') ?>">

  <section class="admin-editor-card lesson-core">
   <div class="lesson-step-heading"><span class="lesson-step-number">1</span><div><span class="eyebrow">Lesson information</span><h2>Start with the basics</h2><p>These fields are always part of the lesson.</p></div></div>
   <div class="form-group"><label for="title">Lesson title</label><input id="title" name="title" type="text" maxlength="180" value="<?= htmlspecialchars((string)($lesson['title']??''),ENT_QUOTES,'UTF-8') ?>" required></div>
   <div class="form-group"><label for="summary">Summary</label><textarea id="summary" name="summary" rows="4"><?= htmlspecialchars((string)($lesson['summary']??''),ENT_QUOTES,'UTF-8') ?></textarea></div>
   <div class="form-group"><label for="content">Lesson content</label><textarea id="content" name="content" rows="10" required><?= htmlspecialchars((string)($lesson['content']??''),ENT_QUOTES,'UTF-8') ?></textarea><small>This is the main written lesson content.</small></div>
   <div class="form-group"><label for="sort_order">Lesson order</label><input id="sort_order" name="sort_order" type="number" min="0" value="<?= (int)($lesson['sort_order']??0) ?>"></div>
  </section>

  <section class="admin-editor-card lesson-builder-content">
   <div class="lesson-step-heading"><span class="lesson-step-number">2</span><div><span class="eyebrow">Learning content</span><h2>Build the lesson</h2><p>Add videos and learning resources in the exact order students should follow.</p></div></div>
   <div id="lesson-blocks" class="lesson-block-list">
    <?php foreach($blocks as $index=>$block): ?>
      <?php $type=(string)($block['type']??'text'); ?>
      <div class="lesson-builder-block" data-index="<?= (int)$index ?>">
       <input type="hidden" name="blocks[<?= (int)$index ?>][id]" value="<?= (int)($block['id']??0) ?>">
       <input type="hidden" name="blocks[<?= (int)$index ?>][type]" value="<?= htmlspecialchars($type,ENT_QUOTES,'UTF-8') ?>">
       <div class="lesson-block-top"><span class="lesson-block-drag"><?= (int)$index+1 ?></span><strong><?= htmlspecialchars(ucfirst($type),ENT_QUOTES,'UTF-8') ?></strong><button type="button" class="lesson-block-remove">Remove</button></div>
       <?php if($type==='youtube'): ?>
        <div class="form-group"><label>Video title</label><input name="blocks[<?= (int)$index ?>][title]" value="<?= htmlspecialchars((string)($block['title']??''),ENT_QUOTES,'UTF-8') ?>"></div>
        <div class="form-group"><label>YouTube URL</label><input type="url" name="blocks[<?= (int)$index ?>][content]" value="<?= htmlspecialchars((string)($block['content']??''),ENT_QUOTES,'UTF-8') ?>" placeholder="https://www.youtube.com/watch?v=..." required></div>
       <?php elseif($type==='text'): ?>
        <div class="form-group"><label>Section title</label><input name="blocks[<?= (int)$index ?>][title]" value="<?= htmlspecialchars((string)($block['title']??''),ENT_QUOTES,'UTF-8') ?>"></div>
        <div class="form-group"><label>Text content</label><textarea name="blocks[<?= (int)$index ?>][content]" rows="7"><?= htmlspecialchars((string)($block['content']??''),ENT_QUOTES,'UTF-8') ?></textarea></div>
       <?php else: ?>
        <div class="form-group"><label>Resource title</label><input name="blocks[<?= (int)$index ?>][title]" value="<?= htmlspecialchars((string)($block['title']??$block['original_name']??''),ENT_QUOTES,'UTF-8') ?>"></div>
        <div class="lesson-existing-file"><strong><?= htmlspecialchars((string)($block['original_name']??'Uploaded file'),ENT_QUOTES,'UTF-8') ?></strong><span><?= strtoupper($type) ?> · <?= number_format(((int)($block['file_size']??0))/1048576,2) ?> MB</span></div>
        <label class="admin-checkbox"><input type="checkbox" name="blocks[<?= (int)$index ?>][download_allowed]" value="1" <?= !empty($block['download_allowed'])?'checked':'' ?>><span><strong>Allow student download</strong><small>Students can view it without this permission, but downloading is controlled separately.</small></span></label>
        <div class="form-group"><label>Replace file <span class="optional">optional</span></label><input name="block_files[<?= (int)$index ?>]" type="file"></div>
       <?php endif; ?>
       <div class="form-group"><label>Order</label><input name="blocks[<?= (int)$index ?>][sort_order]" type="number" min="0" value="<?= (int)($block['sort_order']??$index) ?>"></div>
      </div>
    <?php endforeach; ?>
   </div>

   <div id="lesson-add-menu" class="lesson-add-menu" hidden>
    <button type="button" data-add-block="text"><span>✎</span><strong>Text section</strong><small>Add another written content section.</small></button>
    <button type="button" data-add-block="youtube"><span>▶</span><strong>YouTube video</strong><small>Paste a YouTube video link.</small></button>
    <button type="button" data-add-block="video"><span>▣</span><strong>Upload video</strong><small>Upload a local course video.</small></button>
    <button type="button" data-add-block="material"><span>▤</span><strong>Learning resource</strong><small>PDF, audio, document, HTML and supported files.</small></button>
   </div>
   <button id="lesson-add-button" class="lesson-add-button" type="button"><span>＋</span> Add content</button>
  </section>

  <div class="admin-editor-actions"><button class="btn" type="submit"><?= $isEditing?'Save lesson':'Create lesson' ?></button><a class="btn secondary" href="<?= htmlspecialchars(base_url('admin/courses/'.$courseId.'/edit?tab=modules'),ENT_QUOTES,'UTF-8') ?>">Cancel</a></div>
 </form>
</section>

<script>
(function(){
 const list=document.getElementById('lesson-blocks'), menu=document.getElementById('lesson-add-menu'), add=document.getElementById('lesson-add-button');
 let next=list.querySelectorAll('.lesson-builder-block').length;
 const esc=s=>String(s??'').replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
 function block(type,i){
  const labels={text:['Text section','Add written content'],youtube:['YouTube video','Paste a YouTube link'],video:['Upload video','Upload a local video'],material:['Learning resource','Upload a study file']};
  const l=labels[type];
  let body='';
  if(type==='text') body='<div class="form-group"><label>Section title</label><input name="blocks['+i+'][title]" placeholder="Optional section heading"></div><div class="form-group"><label>Text content</label><textarea name="blocks['+i+'][content]" rows="7" required></textarea></div>';
  if(type==='youtube') body='<div class="form-group"><label>Video title</label><input name="blocks['+i+'][title]" placeholder="e.g. Introduction to the topic"></div><div class="form-group"><label>YouTube URL</label><input type="url" name="blocks['+i+'][content]" placeholder="https://www.youtube.com/watch?v=..." required></div>';
  if(type==='video') body='<div class="form-group"><label>Video title</label><input name="blocks['+i+'][title]" placeholder="e.g. Practical demonstration"></div><div class="form-group"><label>Choose video</label><input name="block_files['+i+']" type="file" accept="video/mp4,video/webm,video/ogg" required></div>';
  if(type==='material') body='<div class="form-group"><label>Material title</label><input name="blocks['+i+'][title]" placeholder="e.g. Lesson notes" required></div><div class="form-group"><label>Choose file</label><input name="block_files['+i+']" type="file" accept=".pdf,.mp3,.wav,.ogg,.m4a,.mp4,.webm,.html,.htm,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.txt" required></div><label class="admin-checkbox"><input type="checkbox" name="blocks['+i+'][download_allowed]" value="1" checked><span><strong>Allow student download</strong><small>Turn this off for view-only resources.</small></span></label>';
  return '<div class="lesson-builder-block" data-index="'+i+'"><input type="hidden" name="blocks['+i+'][id]" value="0"><input type="hidden" name="blocks['+i+'][type]" value="'+type+'"><div class="lesson-block-top"><span class="lesson-block-drag">'+(i+1)+'</span><strong>'+esc(l[0])+'</strong><button type="button" class="lesson-block-remove">Remove</button></div>'+body+'<div class="form-group"><label>Order</label><input name="blocks['+i+'][sort_order]" type="number" min="0" value="'+i+'"></div></div>';
 }
 add.addEventListener('click',()=>menu.hidden=!menu.hidden);
 menu.addEventListener('click',e=>{const b=e.target.closest('[data-add-block]');if(!b)return;list.insertAdjacentHTML('beforeend',block(b.dataset.addBlock,next++));menu.hidden=true;});
 list.addEventListener('click',e=>{const b=e.target.closest('.lesson-block-remove');if(b){b.closest('.lesson-builder-block').remove();Array.from(list.children).forEach((el,i)=>el.querySelector('.lesson-block-drag').textContent=i+1);}});
 document.addEventListener('click',e=>{if(!menu.contains(e.target)&&e.target!==add)menu.hidden=true;});
})();
</script>
