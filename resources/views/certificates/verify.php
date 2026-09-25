<section class="card">
    <h1>Certificate Verification</h1>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <?php if (!empty($certificate)): ?>
        <p><strong>Certificate Number:</strong> <?= htmlspecialchars((string) $certificate['certificate_number'], ENT_QUOTES, 'UTF-8') ?></p>
        <p><strong>Student:</strong> <?= htmlspecialchars((string) $certificate['student_name'], ENT_QUOTES, 'UTF-8') ?></p>
        <p><strong>Course:</strong> <?= htmlspecialchars((string) $certificate['course_name'], ENT_QUOTES, 'UTF-8') ?></p>
        <p><strong>Score:</strong> <?= (float) $certificate['score'] ?>%</p>
        <p><strong>Issued:</strong> <?= htmlspecialchars((string) $certificate['issued_at'], ENT_QUOTES, 'UTF-8') ?></p>
        <p><strong>Status:</strong> <?= htmlspecialchars((string) $certificate['status'], ENT_QUOTES, 'UTF-8') ?></p>
    <?php else: ?>
        <form method="GET" action="<?= htmlspecialchars(base_url('/verify/' . urlencode((string) ($certificate_number ?? ''))), ENT_QUOTES, 'UTF-8') ?>">
            <div class="field">
                <label for="code">Verification code</label>
                <input id="code" name="code" type="text" required maxlength="64" value="" />
            </div>
            <button type="submit">Verify Certificate</button>
        </form>
    <?php endif; ?>

    <p>
        <a href="<?= htmlspecialchars(base_url('/'), ENT_QUOTES, 'UTF-8') ?>">Back to home</a>
    </p>
</section>
