<?php

use App\Core\Auth;
use App\Support\Csrf;

$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
$scriptName = $_SERVER['SCRIPT_NAME'] ?? $_SERVER['PHP_SELF'] ?? '';
$basePath = preg_replace('#/index\.php$#', '', $scriptName);
if ($basePath !== '' && $basePath !== '/' && str_starts_with($currentPath, $basePath)) {
    $currentPath = substr($currentPath, strlen($basePath));
    if ($currentPath === '') {
        $currentPath = '/';
    }
}
$currentPath = rtrim($currentPath, '/');
if ($currentPath === '') {
    $currentPath = '/';
}

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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars(base_url('assets/css/app.css'), ENT_QUOTES, 'UTF-8') ?>">
</head>
<body>
    <header class="topbar">
        <div class="container">
            <nav class="topnav">
                <div class="brand">
                    <span class="brand-mark">F</span>
                    <span>FastPhunzira</span>
                </div>
                <div class="nav-links" id="main-nav">
                    <a href="<?= htmlspecialchars(base_url(), ENT_QUOTES, 'UTF-8') ?>" class="<?= $currentPath === '/' ? 'active' : '' ?>">Home</a>
                    <a href="<?= htmlspecialchars(base_url('courses'), ENT_QUOTES, 'UTF-8') ?>" class="<?= $currentPath === '/courses' ? 'active' : '' ?>">Courses</a>
                    <a href="<?= htmlspecialchars(base_url('how-it-works'), ENT_QUOTES, 'UTF-8') ?>" class="<?= $currentPath === '/how-it-works' ? 'active' : '' ?>">How it works</a>
                    <a href="<?= htmlspecialchars(base_url('teachers'), ENT_QUOTES, 'UTF-8') ?>" class="<?= $currentPath === '/teachers' ? 'active' : '' ?>">For Teachers</a>
                    <a href="<?= htmlspecialchars(base_url('about'), ENT_QUOTES, 'UTF-8') ?>" class="<?= $currentPath === '/about' ? 'active' : '' ?>">About</a>
                    <?php if (Auth::check()): ?>
                        <a href="<?= htmlspecialchars(base_url('dashboard'), ENT_QUOTES, 'UTF-8') ?>" class="<?= $currentPath === '/dashboard' ? 'active' : '' ?>">Dashboard</a>
                    <?php else: ?>
                        <a href="<?= htmlspecialchars(base_url('login'), ENT_QUOTES, 'UTF-8') ?>" class="<?= $currentPath === '/login' ? 'active' : '' ?>">Login</a>
                    <?php endif; ?>
                </div>
                <div class="nav-actions">
                    <?php if (Auth::check()): ?>
                        <form method="POST" action="<?= htmlspecialchars(base_url('logout'), ENT_QUOTES, 'UTF-8') ?>" class="inline-form">
                            <input type="hidden" name="_token" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
                            <button type="submit" class="btn light">Logout</button>
                        </form>
                    <?php else: ?>
                        <a href="<?= htmlspecialchars(base_url('register'), ENT_QUOTES, 'UTF-8') ?>" class="btn primary">Get Started</a>
                    <?php endif; ?>
                    <button class="menu-toggle" type="button" aria-label="Open mobile menu" aria-expanded="false">
                        <span></span>
                    </button>
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

    <script src="<?= htmlspecialchars(base_url('assets/js/app.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
</body>
</html>
