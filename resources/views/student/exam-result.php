<section class="card">
    <h1>Exam Result</h1>
    <p><strong>Status:</strong> <?= htmlspecialchars(ucfirst((string) $attempt['status']), ENT_QUOTES, 'UTF-8') ?></p>
    <p><strong>Score:</strong> <?= (float) $attempt['score'] ?></p>
    <p><strong>Percentage:</strong> <?= (float) $attempt['percentage'] ?>%</p>
    <p><strong>Result:</strong> <?= !empty($attempt['passed']) ? 'Passed' : 'Not passed' ?></p>

    <p>
        <a class="btn" href="<?= htmlspecialchars(base_url('dashboard'), ENT_QUOTES, 'UTF-8') ?>">Back to dashboard</a>
    </p>
</section>
