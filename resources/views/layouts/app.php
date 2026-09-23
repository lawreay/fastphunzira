<?php

use App\Core\Auth;
use App\Support\Csrf;

$error = $_SESSION['flash_error'] ?? null;
$success = $_SESSION['flash_success'] ?? null;

unset($_SESSION['flash_error'], $_SESSION['flash_success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'FastPhunzira', ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
    <header>
        <div class="container">
            <nav>
                <div class="brand">FastPhunzira</div>
                <div>
                    <a href="/">Home</a>
                    <?php if (Auth::check()): ?>
                        <a href="/dashboard">Dashboard</a>
                        <form method="POST" action="/logout" class="inline-form">
                            <input type="hidden" name="_token" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
                            <button type="submit" class="btn light">Logout</button>
                        </form>
                    <?php else: ?>
                        <a href="/login">Login</a>
                        <a href="/register">Register</a>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
    </header>

    <main class="container">
        <?php if ($error): ?>
            <div class="alert error"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert success"><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <?= $body ?? '' ?>
    </main>

    <script src="/assets/js/app.js"></script>
</body>
</html>
