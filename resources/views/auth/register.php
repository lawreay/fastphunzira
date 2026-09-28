<?php

use App\Support\Csrf;
?>
<section class="auth-shell register-shell">
    <div class="auth-visual">
        <div class="auth-visual-inner">
            <span class="eyebrow light">join fastphunzira</span>
            <h2>Start your learning journey today.</h2>
            <p>
                Create your account to unlock guided courses, practice activities, and certification-ready learning paths.
            </p>

            <div class="mini-stat-list">
                <div class="mini-stat">
                    <strong>4x</strong>
                    <span>faster onboarding</span>
                </div>
                <div class="mini-stat">
                    <strong>1</strong>
                    <span>connected dashboard</span>
                </div>
            </div>

            <div class="auth-preview-card">
                <div class="preview-row">
                    <span class="mini-label">Learning path</span>
                    <span class="status-pill">New</span>
                </div>
                <h3>Beginner Track</h3>
                <ul>
                    <li>Structured lessons</li>
                    <li>Practice assessments</li>
                    <li>Certification support</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="auth-panel">
        <div class="auth-header">
            <span class="eyebrow dark">create account</span>
            <h1>Set up your student profile</h1>
        </div>

        <form class="auth-form" method="POST" action="<?= htmlspecialchars(base_url('register'), ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="_token" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">

            <div class="form-group">
                <label for="full_name">Full name</label>
                <input id="full_name" name="full_name" type="text" placeholder="Jane Doe" required>
            </div>

            <div class="form-group">
                <label for="email">Email address</label>
                <input id="email" name="email" type="email" placeholder="you@example.com" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" placeholder="Create a secure password" required>
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirm password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" placeholder="Repeat your password" required>
            </div>

            <button class="btn primary full-width" type="submit">Create account</button>
        </form>

        <p class="auth-switch">
            Already have an account?
            <a href="<?= htmlspecialchars(base_url('login'), ENT_QUOTES, 'UTF-8') ?>">Sign in</a>
        </p>
    </div>
</section>
