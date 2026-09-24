<?php

use App\Support\Csrf;
?>
<section class="card">
    <h1>Login</h1>
    <form method="POST" action="<?= htmlspecialchars(base_url('login'), ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="_token" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
        <div class="form-group">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" placeholder="you@example.com" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input id="password" name="password" type="password" placeholder="Password" required>
        </div>
        <button class="btn" type="submit">Login</button>
    </form>
</section>
