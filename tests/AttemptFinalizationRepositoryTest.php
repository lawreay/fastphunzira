<?php

namespace App\Tests;

use App\Repositories\ExamAttemptRepository;
use App\Repositories\QuizAttemptRepository;
use PDO;
use PHPUnit\Framework\TestCase;

final class AttemptFinalizationRepositoryTest extends TestCase
{
    public function testQuizFinalizationUpdatesAnInProgressAttempt(): void
    {
        $pdo = new PDO('sqlite::memory:');
        $pdo->exec("CREATE TABLE quiz_attempts (
            id INTEGER PRIMARY KEY,
            status TEXT NOT NULL,
            score INTEGER NOT NULL DEFAULT 0,
            percentage REAL NOT NULL DEFAULT 0,
            passed INTEGER NOT NULL DEFAULT 0,
            submitted_at TEXT NULL
        )");
        $pdo->exec("INSERT INTO quiz_attempts (id, status) VALUES (1, 'in_progress')");

        $result = (new QuizAttemptRepository($pdo))->finalize(1, [
            'score' => 2,
            'percentage' => 100,
            'passed' => true,
        ]);

        $this->assertSame('submitted', $result['status']);
        $this->assertSame(2, (int) $result['score']);
    }

    public function testQuizFinalizationRejectsAnAttemptThatIsNoLongerInProgress(): void
    {
        $pdo = new PDO('sqlite::memory:');
        $pdo->exec("CREATE TABLE quiz_attempts (
            id INTEGER PRIMARY KEY,
            status TEXT NOT NULL,
            score INTEGER NOT NULL DEFAULT 0,
            percentage REAL NOT NULL DEFAULT 0,
            passed INTEGER NOT NULL DEFAULT 0,
            submitted_at TEXT NULL
        )");
        $pdo->exec("INSERT INTO quiz_attempts (id, status) VALUES (1, 'submitted')");

        $result = (new QuizAttemptRepository($pdo))->finalize(1, [
            'score' => 2,
            'percentage' => 100,
            'passed' => true,
        ]);

        $this->assertNull($result);
    }

    public function testExamFinalizationRejectsAnAttemptThatIsNoLongerInProgress(): void
    {
        $pdo = new PDO('sqlite::memory:');
        $pdo->exec("CREATE TABLE exam_attempts (
            id INTEGER PRIMARY KEY,
            status TEXT NOT NULL,
            score REAL NOT NULL DEFAULT 0,
            percentage REAL NOT NULL DEFAULT 0,
            passed INTEGER NOT NULL DEFAULT 0,
            submitted_at TEXT NULL
        )");
        $pdo->exec("INSERT INTO exam_attempts (id, status) VALUES (1, 'submitted')");

        $result = (new ExamAttemptRepository($pdo))->finalize(1, [
            'score' => 2,
            'percentage' => 100,
            'passed' => true,
        ]);

        $this->assertNull($result);
    }

    public function testExamFinalizationUpdatesAnInProgressAttempt(): void
    {
        $pdo = new PDO('sqlite::memory:');
        $pdo->exec("CREATE TABLE exam_attempts (
            id INTEGER PRIMARY KEY,
            status TEXT NOT NULL,
            score REAL NOT NULL DEFAULT 0,
            percentage REAL NOT NULL DEFAULT 0,
            passed INTEGER NOT NULL DEFAULT 0,
            submitted_at TEXT NULL
        )");
        $pdo->exec("INSERT INTO exam_attempts (id, status) VALUES (1, 'in_progress')");

        $result = (new ExamAttemptRepository($pdo))->finalize(1, [
            'score' => 2,
            'percentage' => 100,
            'passed' => true,
        ]);

        $this->assertSame('submitted', $result['status']);
        $this->assertSame(2.0, (float) $result['score']);
    }
}
