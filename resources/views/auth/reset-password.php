<?php

use App\Support\Csrf;
?>
<section class="auth-page">
    <div class="auth-shell auth-shell-single">
        <div class="auth-card auth-card-wide">
            <div class="auth-card-heading">
                <span class="eyebrow">Set a new password</span>
                <h1>Reset your password</h1>
                <p>Create a new password for your account.</p>
            </div>
            <form method="POST" action="<?= htmlspecialchars(base_url('reset-password/' . rawurlencode($token ?? '')), ENT_QUOTES, 'UTF-8') ?>">
                <?= Csrf::input() ?>
                <div class="form-group">
                    <label for="password">New password</label>
                    <input id="password" name="password" type="password" autocomplete="new-password" minlength="8" placeholder="At least 8 characters" required>
                </div>
                <div class="form-group">
                    <label for="password_confirmation">Confirm password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="8" placeholder="Repeat your password" required>
                </div>
                <button class="btn btn-block" type="submit">Reset password</button>
            </form>
            <div class="auth-links">
                <a href="<?= htmlspecialchars(base_url('login'), ENT_QUOTES, 'UTF-8') ?>">Back to sign in</a>
            </div>
        </div>
    </div>
</section>
