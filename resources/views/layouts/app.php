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
                    <span class="brand-mark" aria-hidden="true">
                        <svg viewBox="0 0 24 24" focusable="false"><path d="M6 4h13v4H10v3h8v4h-8v5H6V4Z"/></svg>
                    </span>
                    <span class="brand-copy">
                        <strong>FastPhunzira</strong>
                        <small>Learn. Practice. Achieve.</small>
                    </span>
                </a>

                <div class="site-nav-links">
                    <a href="<?= htmlspecialchars(base_url(), ENT_QUOTES, 'UTF-8') ?>">
                        <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1V10Z"/></svg>
                        <span>Home</span>
                    </a>
                    <a href="<?= htmlspecialchars(base_url('courses'), ENT_QUOTES, 'UTF-8') ?>">
                        <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 4h12a2 2 0 0 1 2 2v14H7a2 2 0 0 0-2 2V4Zm0 16a2 2 0 0 1 2-2h12M8 8h7M8 12h7"/></svg>
                        <span>Courses</span>
                    </a>
                    <?php if (Auth::check()): ?>
                        <a href="<?= htmlspecialchars(base_url('dashboard'), ENT_QUOTES, 'UTF-8') ?>">
                            <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 19V5m0 14h16M8 16v-4m4 4V8m4 8V5m4 11V3"/></svg>
                            <span>Dashboard</span>
                        </a>
                        <?php if (Auth::userCan('courses.manage')): ?>
                            <a href="<?= htmlspecialchars(base_url('admin/settings'), ENT_QUOTES, 'UTF-8') ?>">
                                <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="m9.5 3 .6 2.1a7.8 7.8 0 0 1 2.8 0L13.5 3l2.1.9-.7 2a8 8 0 0 1 2 2l2-.7.9 2.1-2.1.6a7.8 7.8 0 0 1 0 2.8l2.1.6-.9 2.1-2-.7a8 8 0 0 1-2 2l.7 2-2.1.9-.6-2.1a7.8 7.8 0 0 1-2.8 0l-.6 2.1-2.1-.9.7-2a8 8 0 0 1-2-2l-2 .7-.9-2.1 2.1-.6a7.8 7.8 0 0 1 0-2.8l-2.1-.6.9-2.1 2 .7a8 8 0 0 1 2-2l-.7-2L9.5 3ZM12 15.5A3.5 3.5 0 1 0 12 8a3.5 3.5 0 0 0 0 7.5Z"/></svg>
                                <span>Admin</span>
                            </a>
                        <?php endif; ?>
                        <form method="POST" action="<?= htmlspecialchars(base_url('logout'), ENT_QUOTES, 'UTF-8') ?>" class="inline-form">
                            <input type="hidden" name="_token" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
                            <button type="submit" class="btn light nav-logout">
                                <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M10 5H5v14h5m5-4 4-3-4-3m4 3H9"/></svg>
                                <span>Logout</span>
                            </button>
                        </form>
                    <?php else: ?>
                        <a href="<?= htmlspecialchars(base_url('login'), ENT_QUOTES, 'UTF-8') ?>">
                            <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm-7 8a7 7 0 0 1 14 0"/></svg>
                            <span>Login</span>
                        </a>
                        <a class="nav-register" href="<?= htmlspecialchars(base_url('register'), ENT_QUOTES, 'UTF-8') ?>">
                            <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14m-7-7h14"/></svg>
                            <span>Get started</span>
                        </a>
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
                        <span class="brand-mark" aria-hidden="true">
                            <svg viewBox="0 0 24 24" focusable="false"><path d="M6 4h13v4H10v3h8v4h-8v5H6V4Z"/></svg>
                        </span>
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
                        <a href="<?= htmlspecialchars(base_url('courses'), ENT_QUOTES, 'UTF-8') ?>">
                            <svg class="footer-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 4h12a2 2 0 0 1 2 2v14H7a2 2 0 0 0-2 2V4Zm0 16a2 2 0 0 1 2-2h12"/></svg>
                            Browse courses
                        </a>
                        <?php if (Auth::check()): ?>
                            <a href="<?= htmlspecialchars(base_url('dashboard'), ENT_QUOTES, 'UTF-8') ?>">
                                <svg class="footer-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 19V5m0 14h16M8 16v-4m4 4V8m4 8V5"/></svg>
                                Dashboard
                            </a>
                        <?php else: ?>
                            <a href="<?= htmlspecialchars(base_url('register'), ENT_QUOTES, 'UTF-8') ?>">
                                <svg class="footer-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14m-7-7h14"/></svg>
                                Create account
                            </a>
                        <?php endif; ?>
                    </div>

                    <div>
                        <h2>Platform</h2>
                        <a href="<?= htmlspecialchars(base_url(), ENT_QUOTES, 'UTF-8') ?>">
                            <svg class="footer-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Zm-2-9 6-4-2 6-6 4 2-6Z"/></svg>
                            FastPhunzira home
                        </a>
                        <a href="<?= htmlspecialchars(base_url('courses'), ENT_QUOTES, 'UTF-8') ?>">
                            <svg class="footer-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5h16v14H4zM8 9h8M8 13h5"/></svg>
                            Learning catalogue
                        </a>
                        <a href="<?= htmlspecialchars(base_url('login'), ENT_QUOTES, 'UTF-8') ?>">
                            <svg class="footer-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm-7 8a7 7 0 0 1 14 0"/></svg>
                            Sign in
                        </a>
                    </div>

                    <div>
                        <h2>Account</h2>
                        <?php if (Auth::check()): ?>
                            <a href="<?= htmlspecialchars(base_url('dashboard'), ENT_QUOTES, 'UTF-8') ?>">
                                <svg class="footer-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 4h10a2 2 0 0 1 2 2v13H7a2 2 0 0 0-2 2V4Zm4 4h5M9 12h5M9 16h3"/></svg>
                                Student area
                            </a>
                        <?php else: ?>
                            <a href="<?= htmlspecialchars(base_url('login'), ENT_QUOTES, 'UTF-8') ?>">
                                <svg class="footer-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M10 5H5v14h5m5-4 4-3-4-3m4 3H9"/></svg>
                                Login
                            </a>
                            <a href="<?= htmlspecialchars(base_url('register'), ENT_QUOTES, 'UTF-8') ?>">
                                <svg class="footer-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14m-7-7h14"/></svg>
                                Register
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="footer-bottom">
                <span>&copy; <?= date('Y') ?> FastPhunzira. All rights reserved.</span>
                <span class="footer-developer"><span>Developed by</span><strong>Lawreay</strong><span class="footer-divider" aria-hidden="true"></span><span>Learning technology for Malawi</span></span>
                <span class="footer-promise">
                    <svg class="footer-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="m12 3 2.2 5.1L20 10l-5.8 1.9L12 17l-2.2-5.1L4 10l5.8-1.9L12 3Zm6 11 .9 2.1L21 17l-2.1.9L18 20l-.9-2.1L15 17l2.1-.9L18 14Z"/></svg>
                    Learn · Practice · Examine · Certify
                </span>
            </div>
        </div>
    </footer>

    <script src="<?= htmlspecialchars(base_url('assets/js/app.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
</body>
</html>
