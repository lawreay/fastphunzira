<?php

use App\Core\Auth;
$user = Auth::user();
$membershipPlan = strtolower((string) ($membership['plan'] ?? 'regular'));
$membershipStatus = strtolower((string) ($membership['status'] ?? 'active'));
$isPremium = $membershipPlan === 'premium' && $membershipStatus === 'active'
    && (empty($membership['expires_at']) || strtotime((string) $membership['expires_at']) >= time());
$membershipLabel = $isPremium ? 'Premium' : 'Regular';
$expiresAt = !empty($membership['expires_at']) ? strtotime((string) $membership['expires_at']) : false;
?>
<section class="app-page dashboard-page" data-reveal>
    <div class="page-heading dashboard-welcome">
        <div>
            <span class="eyebrow">Student dashboard</span>
            <h1>Welcome, <?= htmlspecialchars((string) ($user['full_name'] ?? 'Student'), ENT_QUOTES, 'UTF-8') ?> <span aria-hidden="true">👋</span></h1>
            <p>Pick up your learning where you left off.</p>
        </div>
        <div class="dashboard-membership-pill <?= $isPremium ? 'is-premium' : '' ?>">
            <span>Membership</span>
            <strong><?= htmlspecialchars($membershipLabel, ENT_QUOTES, 'UTF-8') ?></strong>
        </div>
    </div>

    <section class="membership-banner <?= $isPremium ? 'is-premium' : '' ?>" data-reveal>
        <div>
            <span class="eyebrow">Membership</span>
            <h2><?= $isPremium ? 'Premium membership' : 'Regular membership' ?></h2>
            <p>
                <?= $isPremium ? 'You have access to regular and premium courses.' : 'You currently have access to standard courses.' ?>
                <?php if ($isPremium && $expiresAt !== false): ?>
                    <span class="membership-expiry">Expires <?= htmlspecialchars(date('d M Y', $expiresAt), ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
            </p>
        </div>
        <?php if (!$isPremium && $payChanguEnabled && $premiumPrice > 0): ?>
            <form method="POST" action="<?= htmlspecialchars(base_url('premium/checkout'), ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="_token" value="<?= htmlspecialchars(App\Support\Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
                <button class="btn" type="submit">Upgrade · <?= htmlspecialchars($premiumCurrency . ' ' . number_format((float) $premiumPrice, 2), ENT_QUOTES, 'UTF-8') ?></button>
            </form>
        <?php endif; ?>
    </section>

    <div class="dashboard-grid" data-reveal>
        <article class="dashboard-card dashboard-card-primary">
            <span class="dashboard-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M5 4h12a2 2 0 0 1 2 2v14H7a2 2 0 0 0-2 2V4Zm0 16a2 2 0 0 1 2-2h12M8 8h7M8 12h5"/></svg></span>
            <h2>My courses</h2><p>Open your enrolled courses and continue learning.</p><a href="<?= htmlspecialchars(base_url('my-courses'), ENT_QUOTES, 'UTF-8') ?>">View my courses <span aria-hidden="true">→</span></a>
        </article>
        <article class="dashboard-card">
            <span class="dashboard-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M6 4h12v16H6zM9 8h6M9 12h6M9 16h3"/></svg></span>
            <h2>Certificates</h2><p>View certificates you have earned and their verification details.</p><a href="<?= htmlspecialchars(base_url('student/certificates'), ENT_QUOTES, 'UTF-8') ?>">View certificates <span aria-hidden="true">→</span></a>
        </article>
        <article class="dashboard-card">
            <span class="dashboard-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="m12 3 2.1 5.2L20 10l-5.9 1.8L12 17l-2.1-5.2L4 10l5.9-1.8L12 3Zm6 11 1 2.2L21 17l-2-.8L18 14Z"/></svg></span>
            <h2>Explore learning</h2><p>Find a published course and build your next skill.</p><a href="<?= htmlspecialchars(base_url('courses'), ENT_QUOTES, 'UTF-8') ?>">Explore courses <span aria-hidden="true">→</span></a>
        </article>
    </div>

    <?php if (Auth::userCan('courses.manage')): ?>
        <section class="admin-panel" data-reveal>
            <div><span class="eyebrow">Administration</span><h2>Platform management</h2><p>Your account has course-management permissions.</p></div>
            <div class="actions"><a class="btn secondary" href="<?= htmlspecialchars(base_url('admin/courses'), ENT_QUOTES, 'UTF-8') ?>">Manage courses</a><a class="btn secondary" href="<?= htmlspecialchars(base_url('admin/certificates'), ENT_QUOTES, 'UTF-8') ?>">Review certificates</a><a class="btn secondary" href="<?= htmlspecialchars(base_url('admin/settings'), ENT_QUOTES, 'UTF-8') ?>">Settings</a><a class="btn secondary" href="<?= htmlspecialchars(base_url('admin/audit-logs'), ENT_QUOTES, 'UTF-8') ?>">Audit logs</a></div>
        </section>
    <?php endif; ?>
</section>
