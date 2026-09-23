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
                    <a href="/login">Login</a>
                    <a href="/register">Register</a>
                </div>
            </nav>
        </div>
    </header>

    <main class="container">
        <?= $body ?? '' ?>
    </main>

    <script src="/assets/js/app.js"></script>
</body>
</html>
