<?php

declare(strict_types=1);

$app = require __DIR__ . '/../bootstrap/app.php';
$routes = require __DIR__ . '/../routes/web.php';

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

$route = null;
foreach ($routes as $candidate) {
    [$allowedMethod, $path, $handler] = $candidate;
    if ($allowedMethod === $method && $path === $uri) {
        $route = $handler;
        break;
    }
}

if ($route === null) {
    http_response_code(404);
    echo '404 Not Found';
    exit;
}

$result = $route();

if (isset($result['redirect'])) {
    header('Location: ' . $result['redirect']);
    exit;
}

$view = $result['view'] ?? 'landing';
$title = $result['title'] ?? 'FastPhunzira';

$viewPath = __DIR__ . '/../resources/views/' . $view . '.php';
if (!is_file($viewPath)) {
    http_response_code(500);
    echo 'View not found: ' . htmlspecialchars($view, ENT_QUOTES, 'UTF-8');
    exit;
}

ob_start();
require $viewPath;
$body = ob_get_clean();

require __DIR__ . '/../resources/views/layouts/app.php';
