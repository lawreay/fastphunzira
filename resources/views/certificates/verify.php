<?php
$certificate = $certificate ?? null;
?>
<section class="certificate-verification-page">
    <div class="verification-heading">
        <span class="eyebrow">Public verification</span>
        <h1>Verify a certificate</h1>
        <p>Confirm that a FastPhunzira certificate exists and check its current status.</p>
    </div>

    <?php if (!empty($error)): ?>
        <div class="verification-alert" role="alert"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <?php if (!empty($certificate)): ?>
        <section class="verification-card is-valid">
            <div class="verification-status"><span>Verified certificate</span><strong><?= htmlspecialchars(ucfirst((string) ($certificate['status'] ?? 'active')), ENT_QUOTES, 'UTF-8') ?></strong></div>
            <h2><?= htmlspecialchars((string) $certificate['course_name'], ENT_QUOTES, 'UTF-8') ?></h2>
            <div class="verification-details">
                <div><span>Certificate number</span><strong><?= htmlspecialchars((string) $certificate['certificate_number'], ENT_QUOTES, 'UTF-8') ?></strong></div>
                <div><span>Student</span><strong><?= htmlspecialchars((string) $certificate['student_name'], ENT_QUOTES, 'UTF-8') ?></strong></div>
                <div><span>Score</span><strong><?= number_format((float) $certificate['score'], 1) ?>%</strong></div>
                <div><span>Issued</span><strong><?= htmlspecialchars((string) $certificate['issued_at'], ENT_QUOTES, 'UTF-8') ?></strong></div>
            </div>
            <div class="verification-note">This result was returned by the FastPhunzira certificate verification service.</div>
        </section>
    <?php else: ?>
        <section class="verification-card">
            <h2>Enter verification code</h2>
            <p class="verification-help">Use the certificate number in the page address and provide the verification code associated with it.</p>
            <form method="GET" action="<?= htmlspecialchars(base_url('/verify/' . urlencode((string) ($certificate_number ?? ''))), ENT_QUOTES, 'UTF-8') ?>">
                <div class="field"><label for="code">Verification code</label><input id="code" name="code" type="text" required maxlength="64" autocomplete="off"></div>
                <button class="btn" type="submit">Verify certificate</button>
            </form>
        </section>
    <?php endif; ?>

    <div class="verification-footer"><a href="<?= htmlspecialchars(base_url('/'), ENT_QUOTES, 'UTF-8') ?>">Back to home</a></div>
</section>