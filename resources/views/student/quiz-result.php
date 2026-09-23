<section class="card">
    <h1>Quiz Result</h1>
    <p><strong>Status:</strong> <?= htmlspecialchars(ucfirst((string) $attempt['status']), ENT_QUOTES, 'UTF-8') ?></p>
    <p><strong>Score:</strong> <?= (int) $attempt['score'] ?></p>
    <p><strong>Percentage:</strong> <?= (float) $attempt['percentage'] ?>%</p>
    <p><strong>Result:</strong> <?= !empty($attempt['passed']) ? 'Passed' : 'Not passed' ?></p>
    <p><a class="btn" href="/dashboard">Back to dashboard</a></p>
</section>
