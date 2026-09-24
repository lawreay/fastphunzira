<?php

use App\Support\Csrf;
?>
<section class="card">
    <h1>Create account</h1>
    <form method="POST" action="<?= htmlspecialchars(base_url('register'), ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="_token" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
        <div class="form-group">
            <label for="full_name">Full name</label>
            <input id="full_name" name="full_name" type="text" placeholder="Jane Doe" required>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" placeholder="you@example.com" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input id="password" name="password" type="password" placeholder="Password" required>
        </div>
        <div class="form-group">
            <label for="password_confirmation">Confirm password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" placeholder="Confirm password" required>
        </div>
        <button class="btn" type="submit">Register</button>
    </form>
</section>
