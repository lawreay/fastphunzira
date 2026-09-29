<?php
namespace App\Repositories;
final class InMemoryLessonBlockRepository implements LessonBlockRepositoryInterface {
 private array $blocks=[];
 public function create(array $b):array{$b['id']=count($this->blocks)+1;$this->blocks[$b['id']]=$b;return $b;}
 public function findById(int $id):?array{return $this->blocks[$id]??null;}
 public function findByLesson(int $lessonId):array{return array_values(array_filter($this->blocks,fn($b)=>(int)($b['lesson_id']??0)===$lessonId));}
 public function update(int $id,array $d):?array{if(!isset($this->blocks[$id]))return null;$this->blocks[$id]=array_merge($this->blocks[$id],$d);return $this->blocks[$id];}
 public function delete(int $id):bool{if(!isset($this->blocks[$id]))return false;unset($this->blocks[$id]);return true;}
}