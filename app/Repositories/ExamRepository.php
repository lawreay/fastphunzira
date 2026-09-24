<?php
namespace App\Repositories;

use PDO;

final class ExamRepository implements ExamRepositoryInterface
{
    public function __construct(private PDO $pdo) {}

    public function create(array $exam): array
    {
        $s=$this->pdo->prepare('INSERT INTO exams (course_id,title,description,time_limit,passing_score,attempts_allowed,total_questions,status,created_by) VALUES (:course_id,:title,:description,:time_limit,:passing_score,:attempts_allowed,0,:status,:created_by)');
        $s->execute([':course_id'=>(int)$exam['course_id'],':title'=>$exam['title'],':description'=>$exam['description']??'',':time_limit'=>(int)$exam['time_limit'],':passing_score'=>(float)$exam['passing_score'],':attempts_allowed'=>(int)$exam['attempts_allowed'],':status'=>$exam['status']??'draft',':created_by'=>(int)$exam['created_by']]);
        $exam['id']=(int)$this->pdo->lastInsertId(); $exam['total_questions']=0; return $exam;
    }

    public function findById(int $id): ?array
    {
        $s=$this->pdo->prepare('SELECT * FROM exams WHERE id=:id LIMIT 1'); $s->execute([':id'=>$id]);
        $row=$s->fetch(); return $row===false?null:$row;
    }

    public function findByCourse(int $courseId): array
    {
        $s=$this->pdo->prepare('SELECT * FROM exams WHERE course_id=:course_id ORDER BY created_at DESC'); $s->execute([':course_id'=>$courseId]);
        return $s->fetchAll()?:[];
    }

    public function updateStatus(int $id,string $status): ?array
    {
        $s=$this->pdo->prepare('UPDATE exams SET status=:status WHERE id=:id'); $s->execute([':status'=>$status,':id'=>$id]);
        return $this->findById($id);
    }

    public function addQuestion(int $examId,int $questionId,float $marks,int $sortOrder): array
    {
        $s=$this->pdo->prepare('INSERT INTO exam_questions (exam_id,question_id,marks,sort_order) VALUES (:exam_id,:question_id,:marks,:sort_order)');
        $s->execute([':exam_id'=>$examId,':question_id'=>$questionId,':marks'=>$marks,':sort_order'=>$sortOrder]);
        $s=$this->pdo->prepare('UPDATE exams SET total_questions=(SELECT COUNT(*) FROM exam_questions WHERE exam_id=:exam_id) WHERE id=:exam_id2');
        $s->execute([':exam_id'=>$examId,':exam_id2'=>$examId]);
        return ['id'=>(int)$this->pdo->lastInsertId(),'exam_id'=>$examId,'question_id'=>$questionId,'marks'=>$marks,'sort_order'=>$sortOrder];
    }

    public function findQuestions(int $examId): array
    {
        $s=$this->pdo->prepare('SELECT eq.question_id,eq.marks,eq.sort_order,q.question_text,q.question_type FROM exam_questions eq JOIN questions q ON q.id=eq.question_id WHERE eq.exam_id=:exam_id ORDER BY eq.sort_order,eq.id');
        $s->execute([':exam_id'=>$examId]); return $s->fetchAll()?:[];
    }
}