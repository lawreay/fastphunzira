<?php

use App\Support\Csrf;
?>
<section class="auth-page">
    <div class="auth-shell auth-shell-single">
        <div class="auth-card auth-card-wide">
            <div class="auth-card-heading">
                <span class="eyebrow">Account recovery</span>
                <h1>Forgot your password?</h1>
                <p>Enter the email connected to your account and we will send a secure reset link.</p>
            </div>
            <div class="notice-panel">
                <strong>Recovery flow</strong>
                <span>Use the secure reset link to create a new password. Links expire after 1 hour.</span>
            </div>
            <form method="POST" action="<?= htmlspecialchars(base_url('forgot-password'), ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="_token" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
                <div class="form-group">
                    <label for="reset-email">Email address</label>
                    <input id="reset-email" name="email" type="email" autocomplete="email" placeholder="you@example.com" required>
                </div>
                <button class="btn btn-block" type="submit">Request reset link</button>
            </form>
            <div class="auth-links">
                <a href="<?= htmlspecialchars(base_url('login'), ENT_QUOTES, 'UTF-8') ?>">Back to sign in</a>
                <a href="<?= htmlspecialchars(base_url('register'), ENT_QUOTES, 'UTF-8') ?>">Create an account</a>
            </div>
        </div>
    </div>
</section>
