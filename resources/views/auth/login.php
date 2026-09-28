<?php

use App\Support\Csrf;
?>
<section class="auth-shell">
    <div class="auth-visual">
        <div class="auth-visual-inner">
            <span class="eyebrow light">welcome back</span>
            <h2>Keep your momentum going.</h2>
            <p>
                Continue learning, keep your progress on track, and move closer to your next certificate.
            </p>

            <div class="mini-stat-list">
                <div class="mini-stat">
                    <strong>12</strong>
                    <span>active lessons</span>
                </div>
                <div class="mini-stat">
                    <strong>86%</strong>
                    <span>average progress</span>
                </div>
            </div>

            <div class="auth-preview-card">
                <div class="preview-row">
                    <span class="mini-label">Current course</span>
                    <span class="status-pill">On track</span>
                </div>
                <h3>HTML Fundamentals</h3>
                <div class="progress-line">
                    <span style="width: 72%;"></span>
                </div>
                <small>72% complete</small>
            </div>
        </div>
    </div>

    <div class="auth-panel">
        <div class="auth-header">
            <span class="eyebrow dark">sign in</span>
            <h1>Access your learning dashboard</h1>
        </div>

        <form class="auth-form" method="POST" action="<?= htmlspecialchars(base_url('login'), ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="_token" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">

            <div class="form-group">
                <label for="email">Email address</label>
                <input id="email" name="email" type="email" placeholder="you@example.com" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" placeholder="Enter your password" required>
            </div>

            <div class="form-row">
                <label class="checkbox-wrap">
                    <input type="checkbox" name="remember" value="1">
                    <span>Remember me</span>
                </label>
                <a href="#" class="text-link">Forgot password?</a>
            </div>

            <button class="btn primary full-width" type="submit">Sign in</button>
        </form>

        <div class="auth-divider"><span>or</span></div>

        <p class="auth-switch">
            New here?
            <a href="<?= htmlspecialchars(base_url('register'), ENT_QUOTES, 'UTF-8') ?>">Create an account</a>
        </p>
    </div>
</section>
