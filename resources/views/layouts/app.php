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
    <meta name="theme-color" content="#0f172a">
    <title><?= htmlspecialchars($title ?? 'FastPhunzira', ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="<?= htmlspecialchars(base_url('assets/css/app.css'), ENT_QUOTES, 'UTF-8') ?>">
</head>
<body>
    <header class="site-header">
        <div class="container">
            <nav class="site-nav" aria-label="Main navigation">
                <a class="site-brand" href="<?= htmlspecialchars(base_url(), ENT_QUOTES, 'UTF-8') ?>" aria-label="FastPhunzira home">
                    <span class="brand-mark" aria-hidden="true">F</span>
                    <span class="brand-copy">
                        <strong>FastPhunzira</strong>
                        <small>Learn. Practice. Achieve.</small>
                    </span>
                </a>

                <div class="site-nav-links">
                    <a href="<?= htmlspecialchars(base_url(), ENT_QUOTES, 'UTF-8') ?>">Home</a>
                    <a href="<?= htmlspecialchars(base_url('courses'), ENT_QUOTES, 'UTF-8') ?>">Courses</a>
                    <?php if (Auth::check()): ?>
                        <a href="<?= htmlspecialchars(base_url('dashboard'), ENT_QUOTES, 'UTF-8') ?>">Dashboard</a>
                        <?php if (Auth::userCan('courses.manage')): ?>
                            <a href="<?= htmlspecialchars(base_url('admin/settings'), ENT_QUOTES, 'UTF-8') ?>">Admin</a>
                        <?php endif; ?>
                        <form method="POST" action="<?= htmlspecialchars(base_url('logout'), ENT_QUOTES, 'UTF-8') ?>" class="inline-form">
                            <input type="hidden" name="_token" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
                            <button type="submit" class="btn light nav-logout">Logout</button>
                        </form>
                    <?php else: ?>
                        <a href="<?= htmlspecialchars(base_url('login'), ENT_QUOTES, 'UTF-8') ?>">Login</a>
                        <a class="nav-register" href="<?= htmlspecialchars(base_url('register'), ENT_QUOTES, 'UTF-8') ?>">Get started</a>
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

    <footer class="site-footer">
        <div class="container">
            <div class="footer-main">
                <div class="footer-brand">
                    <a class="site-brand footer-brand-link" href="<?= htmlspecialchars(base_url(), ENT_QUOTES, 'UTF-8') ?>">
                        <span class="brand-mark" aria-hidden="true">F</span>
                        <span class="brand-copy">
                            <strong>FastPhunzira</strong>
                            <small>Learn. Practice. Achieve.</small>
                        </span>
                    </a>
                    <p>A simple learning platform for building skills, testing knowledge, and recognizing achievement.</p>
                </div>

                <div class="footer-links">
                    <div>
                        <h2>Learn</h2>
                        <a href="<?= htmlspecialchars(base_url('courses'), ENT_QUOTES, 'UTF-8') ?>">Browse courses</a>
                        <?php if (Auth::check()): ?>
                            <a href="<?= htmlspecialchars(base_url('my-courses'), ENT_QUOTES, 'UTF-8') ?>">My courses</a>
                            <a href="<?= htmlspecialchars(base_url('dashboard'), ENT_QUOTES, 'UTF-8') ?>">Dashboard</a>
                        <?php else: ?>
                            <a href="<?= htmlspecialchars(base_url('register'), ENT_QUOTES, 'UTF-8') ?>">Create account</a>
                        <?php endif; ?>
                    </div>

                    <div>
                        <h2>Platform</h2>
                        <a href="<?= htmlspecialchars(base_url(), ENT_QUOTES, 'UTF-8') ?>">About FastPhunzira</a>
                        <a href="<?= htmlspecialchars(base_url('courses'), ENT_QUOTES, 'UTF-8') ?>">Learning catalogue</a>
                        <a href="<?= htmlspecialchars(base_url('login'), ENT_QUOTES, 'UTF-8') ?>">Sign in</a>
                    </div>

                    <div>
                        <h2>Account</h2>
                        <?php if (Auth::check()): ?>
                            <a href="<?= htmlspecialchars(base_url('student/certificates'), ENT_QUOTES, 'UTF-8') ?>">My certificates</a>
                            <a href="<?= htmlspecialchars(base_url('dashboard'), ENT_QUOTES, 'UTF-8') ?>">My dashboard</a>
                        <?php else: ?>
                            <a href="<?= htmlspecialchars(base_url('login'), ENT_QUOTES, 'UTF-8') ?>">Login</a>
                            <a href="<?= htmlspecialchars(base_url('register'), ENT_QUOTES, 'UTF-8') ?>">Register</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="footer-bottom">
                <span>&copy; <?= date('Y') ?> FastPhunzira. All rights reserved.</span>
                <span>Learn · Practice · Examine · Certify</span>
            </div>
        </div>
    </footer>

    <script src="<?= htmlspecialchars(base_url('assets/js/app.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
</body>
</html>
