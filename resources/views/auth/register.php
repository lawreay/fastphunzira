<?php

use App\Support\Csrf;
?>
<section class="auth-page">
    <div class="auth-shell auth-shell-single">
        <div class="auth-card auth-card-wide">
            <div class="auth-card-heading"><span class="eyebrow">Create your account</span><h1>Start learning with FastPhunzira.</h1><p>Set up your account to browse courses, enroll, learn, take assessments, and earn certificates.</p></div>
            <form method="POST" action="<?= htmlspecialchars(base_url('register'), ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="_token" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
                <div class="form-group"><label for="full_name">Full name</label><input id="full_name" name="full_name" type="text" autocomplete="name" placeholder="Your full name" required></div>
                <div class="form-group"><label for="email">Email address</label><input id="email" name="email" type="email" autocomplete="email" placeholder="you@example.com" required></div>
                <div class="form-grid-2"><div class="form-group"><label for="password">Password</label><input id="password" name="password" type="password" autocomplete="new-password" placeholder="Create a password" required></div><div class="form-group"><label for="password_confirmation">Confirm password</label><input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" placeholder="Repeat your password" required></div></div>
                <button class="btn btn-block" type="submit">Create account</button>
            </form>
            <p class="auth-switch">Already have an account? <a href="<?= htmlspecialchars(base_url('login'), ENT_QUOTES, 'UTF-8') ?>">Sign in</a></p>
        </div>
    </div>
</section>
