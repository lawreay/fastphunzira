<section class="card">
    <h1>My Certificates</h1>

    <?php if (empty($certificates)): ?>
        <p>You have not earned any certificates yet.</p>
    <?php else: ?>
        <div class="list-group">
            <?php foreach ($certificates as $certificate): ?>
                <div class="list-item">
                    <h3><?= htmlspecialchars((string) ($certificate['course_name'] ?? 'Certificate'), ENT_QUOTES, 'UTF-8') ?></h3>
                    <p><strong>Certificate Number:</strong> <?= htmlspecialchars((string) ($certificate['certificate_number'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                    <p><strong>Issued:</strong> <?= htmlspecialchars((string) ($certificate['issued_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                    <p><strong>Status:</strong> <?= htmlspecialchars((string) ($certificate['status'] ?? 'active'), ENT_QUOTES, 'UTF-8') ?></p>
                    <p>
                        <a href="<?= htmlspecialchars(base_url('/verify/' . urlencode((string) ($certificate['certificate_number'] ?? '')) . '?code=' . urlencode((string) ($certificate['verification_code'] ?? ''))), ENT_QUOTES, 'UTF-8') ?>">
                            View verification
                        </a>
                    </p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <p>
        <a class="btn" href="<?= htmlspecialchars(base_url('/dashboard'), ENT_QUOTES, 'UTF-8') ?>">Back to dashboard</a>
    </p>
</section>
