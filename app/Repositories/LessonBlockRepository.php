<?php
namespace App\Repositories;
use PDO;
final class LessonBlockRepository implements LessonBlockRepositoryInterface {
    public function __construct(private PDO $pdo) {}
    public function create(array $block): array {
        $s=$this->pdo->prepare('INSERT INTO lesson_blocks (lesson_id,type,title,content,storage_path,original_name,mime_type,file_size,download_allowed,sort_order) VALUES (:lesson_id,:type,:title,:content,:storage_path,:original_name,:mime_type,:file_size,:download_allowed,:sort_order)');
        $s->execute([
            ':lesson_id'=>(int)($block['lesson_id']??0), ':type'=>(string)($block['type']??'text'),
            ':title'=>trim((string)($block['title']??'')), ':content'=>$block['content']??null,
            ':storage_path'=>$block['storage_path']??null, ':original_name'=>$block['original_name']??null,
            ':mime_type'=>$block['mime_type']??null, ':file_size'=>(int)($block['file_size']??0),
            ':download_allowed'=>!empty($block['download_allowed'])?1:0, ':sort_order'=>(int)($block['sort_order']??0)
        ]);
        $block['id']=(int)$this->pdo->lastInsertId(); return $block;
    }
    public function findById(int $id): ?array { $s=$this->pdo->prepare('SELECT * FROM lesson_blocks WHERE id=:id LIMIT 1'); $s->execute([':id'=>$id]); $r=$s->fetch(); return $r===false?null:$r; }
    public function findByLesson(int $lessonId): array { $s=$this->pdo->prepare('SELECT * FROM lesson_blocks WHERE lesson_id=:lesson_id ORDER BY sort_order ASC,id ASC'); $s->execute([':lesson_id'=>$lessonId]); return $s->fetchAll()?:[]; }
    public function update(int $id,array $data): ?array {
        $allowed=['title','content','download_allowed','sort_order']; $fields=[];$params=[':id'=>$id];
        foreach($data as $k=>$v){if(!in_array($k,$allowed,true))continue;$fields[]="$k=:$k";$params[":$k"]=$k==='download_allowed'?(!empty($v)?1:0):$v;}
        if(!$fields)return $this->findById($id); $s=$this->pdo->prepare('UPDATE lesson_blocks SET '.implode(',',$fields).' WHERE id=:id');$s->execute($params);return $this->findById($id);
    }
    public function delete(int $id): bool { $s=$this->pdo->prepare('DELETE FROM lesson_blocks WHERE id=:id');$s->execute([':id'=>$id]);return $s->rowCount()>0; }
}