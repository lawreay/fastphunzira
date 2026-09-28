<?php

use App\Core\Auth;
$user = Auth::user();
?>
<section class="app-page">
    <div class="page-heading">
        <div><span class="eyebrow">Student area</span><h1>Welcome back, <?= htmlspecialchars((string) ($user['full_name'] ?? 'Student'), ENT_QUOTES, 'UTF-8') ?>.</h1><p>Pick up your learning where you left off.</p></div>
        <a class="btn" href="<?= htmlspecialchars(base_url('courses'), ENT_QUOTES, 'UTF-8') ?>">Browse courses</a>
    </div>

    <section class="membership-banner <?= (($membership['plan'] ?? 'regular') === 'premium' && ($membership['status'] ?? '') === 'active') ? 'is-premium' : '' ?>">
        <div>
            <span class="eyebrow">Membership</span>
            <h2><?= (($membership['plan'] ?? 'regular') === 'premium' && ($membership['status'] ?? '') === 'active') ? 'Premium student' : 'Regular student' ?></h2>
            <p><?= (($membership['plan'] ?? 'regular') === 'premium' && ($membership['status'] ?? '') === 'active') ? 'You have access to regular and premium courses.' : 'Upgrade when you want access to premium courses.' ?></p>
        </div>
        <?php if (!(($membership['plan'] ?? 'regular') === 'premium' && ($membership['status'] ?? '') === 'active') && $payChanguEnabled && $premiumPrice > 0): ?>
            <form method="POST" action="<?= htmlspecialchars(base_url('premium/checkout'), ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="_token" value="<?= htmlspecialchars(App\Support\Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
                <button class="btn" type="submit">Upgrade · <?= htmlspecialchars($premiumCurrency . ' ' . number_format((float) $premiumPrice, 2), ENT_QUOTES, 'UTF-8') ?></button>
            </form>
        <?php endif; ?>
    </section>

    <div class="dashboard-grid">
        <article class="dashboard-card"><span class="dashboard-icon">01</span><h2>My courses</h2><p>Open your enrolled courses and continue learning.</p><a href="<?= htmlspecialchars(base_url('my-courses'), ENT_QUOTES, 'UTF-8') ?>">View my courses →</a></article>
        <article class="dashboard-card"><span class="dashboard-icon">02</span><h2>Certificates</h2><p>View certificates you have earned and their verification details.</p><a href="<?= htmlspecialchars(base_url('student/certificates'), ENT_QUOTES, 'UTF-8') ?>">View certificates →</a></article>
        <article class="dashboard-card"><span class="dashboard-icon">03</span><h2>Explore learning</h2><p>Find a published course and build your next skill.</p><a href="<?= htmlspecialchars(base_url('courses'), ENT_QUOTES, 'UTF-8') ?>">Explore courses →</a></article>
    </div>

    <?php if (Auth::userCan('courses.manage')): ?>
        <section class="admin-panel">
            <div><span class="eyebrow">Administration</span><h2>Platform management</h2><p>Your account has course-management permissions.</p></div>
            <div class="actions"><a class="btn secondary" href="<?= htmlspecialchars(base_url('admin/courses'), ENT_QUOTES, 'UTF-8') ?>">Manage courses</a><a class="btn secondary" href="<?= htmlspecialchars(base_url('admin/certificates'), ENT_QUOTES, 'UTF-8') ?>">Review certificates</a><a class="btn secondary" href="<?= htmlspecialchars(base_url('admin/settings'), ENT_QUOTES, 'UTF-8') ?>">Settings</a><a class="btn secondary" href="<?= htmlspecialchars(base_url('admin/audit-logs'), ENT_QUOTES, 'UTF-8') ?>">Audit logs</a></div>
        </section>
    <?php endif; ?>
</section>
