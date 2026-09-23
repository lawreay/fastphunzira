<?php

use App\Core\Auth;
$user = Auth::user();
?>
<section class="card">
    <h1>Dashboard</h1>
    <p>Welcome, <?= htmlspecialchars((string) ($user['full_name'] ?? 'Student'), ENT_QUOTES, 'UTF-8') ?>.</p>
    <p>Your current role is <strong><?= htmlspecialchars((string) ($user['role'] ?? 'student'), ENT_QUOTES, 'UTF-8') ?></strong>.</p>
    <p>Foundation shell is ready. Authentication, courses, exams, and certificates will be added in later phases.</p>
</section>
