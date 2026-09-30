<?php

use App\Support\Csrf;
?>
<section class="auth-page auth-page-login" data-reveal>
    <div class="auth-shell">
        <div class="auth-intro">
            <span class="eyebrow">Welcome back</span>
            <h1>Continue your learning journey.</h1>
            <p>Sign in to access your courses, progress, assessments, results, and certificates.</p>
            <div class="auth-points"><span>✓ Your courses and progress</span><span>✓ Quizzes and final exams</span><span>✓ Certificates and verification</span></div>
            <div class="auth-visual-strip">
                <img src="https://images.unsplash.com/photo-1531482615713-2afd69097998?auto=format&amp;fit=crop&amp;w=900&amp;q=88" alt="Black students collaborating during a workshop" loading="lazy">
                <span>Keep learning with people who are moving forward too.</span>
            </div>
        </div>
        <div class="auth-card">
            <div class="auth-card-heading"><h2>Sign in</h2><p>Use the email address connected to your account.</p></div>
            <form method="POST" action="<?= htmlspecialchars(base_url('login'), ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="_token" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
                <div class="form-group"><label for="email">Email address</label><input id="email" name="email" type="email" autocomplete="email" placeholder="you@example.com" required></div>
                <div class="form-group"><div class="field-label-row"><label for="password">Password</label><a href="<?= htmlspecialchars(base_url('forgot-password'), ENT_QUOTES, 'UTF-8') ?>">Forgot password?</a></div><input id="password" name="password" type="password" autocomplete="current-password" placeholder="Enter your password" required></div>
                <button class="btn btn-block" type="submit">Sign in</button>
            </form>
            <p class="auth-switch">Don't have an account? <a href="<?= htmlspecialchars(base_url('register'), ENT_QUOTES, 'UTF-8') ?>">Create one</a></p>
        </div>
    </div>
</section>
