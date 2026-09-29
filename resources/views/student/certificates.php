<?php
$certificateCount = count($certificates ?? []);
?>
<section class="certificates-page">
    <div class="certificates-heading">
        <div>
            <span class="eyebrow">Achievement</span>
            <h1>My certificates</h1>
            <p>Your earned certificates and their public verification details.</p>
        </div>
        <div class="certificates-count"><strong><?= $certificateCount ?></strong><span><?= $certificateCount === 1 ? 'certificate' : 'certificates' ?></span></div>
    </div>

    <?php if (empty($certificates)): ?>
        <section class="certificates-empty">
            <span class="eyebrow">Keep learning</span>
            <h2>No certificates yet</h2>
            <p>Complete the required learning and assessment milestones to earn certificates when they become available.</p>
            <a class="btn" href="<?= htmlspecialchars(base_url('courses'), ENT_QUOTES, 'UTF-8') ?>">Browse courses</a>
        </section>
    <?php else: ?>
        <div class="certificate-list">
            <?php foreach ($certificates as $certificate): ?>
                <?php $status = strtolower((string) ($certificate['status'] ?? 'active')); ?>
                <article class="certificate-card">
                    <div class="certificate-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M6 3h12v18l-6-3-6 3zM9 8h6M9 12h6"/></svg>
                    </div>
                    <div class="certificate-main">
                        <span class="eyebrow">Certificate</span>
                        <h2><?= htmlspecialchars((string) ($certificate['course_name'] ?? 'Certificate'), ENT_QUOTES, 'UTF-8') ?></h2>
                        <p>Certificate number: <strong><?= htmlspecialchars((string) ($certificate['certificate_number'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong></p>
                        <div class="certificate-meta"><span>Issued <?= htmlspecialchars((string) ($certificate['issued_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span><span class="status-badge"><?= htmlspecialchars(ucfirst($status), ENT_QUOTES, 'UTF-8') ?></span></div>
                    </div>
                    <a class="btn" href="<?= htmlspecialchars(base_url('/verify/' . urlencode((string) ($certificate['certificate_number'] ?? '')) . '?code=' . urlencode((string) ($certificate['verification_code'] ?? ''))), ENT_QUOTES, 'UTF-8') ?>">View verification</a>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="certificates-footer"><a href="<?= htmlspecialchars(base_url('dashboard'), ENT_QUOTES, 'UTF-8') ?>">Back to dashboard</a></div>
</section>