<?php

use App\Core\Auth;
$user = Auth::user();
?>
<section class="card">
    <h1>Dashboard</h1>
    <p>Welcome, <?= htmlspecialchars((string) ($user['full_name'] ?? 'Student'), ENT_QUOTES, 'UTF-8') ?>.</p>
    <p>Your current role is <strong><?= htmlspecialchars((string) ($user['role'] ?? 'student'), ENT_QUOTES, 'UTF-8') ?></strong>.</p>

    <?php if (Auth::userCan('courses.manage')): ?>
        <div class="admin-links">
            <p><a href="<?= htmlspecialchars(base_url('/admin/certificates'), ENT_QUOTES, 'UTF-8') ?>">Review certificates</a></p>
            <p><a href="<?= htmlspecialchars(base_url('/admin/audit-logs'), ENT_QUOTES, 'UTF-8') ?>">Review audit logs</a></p>
        </div>
    <?php endif; ?>
</section>
