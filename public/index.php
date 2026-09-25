<?php

declare(strict_types=1);

function base_url(string $path = ''): string
{
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? $_SERVER['PHP_SELF'] ?? '/index.php';
    $base = preg_replace('#/index\.php$#', '', $scriptName);
    $base = rtrim((string) $base, '/');

    if ($base === '' || $base === '/' || $base === '.') {
        return '/' . ltrim($path, '/');
    }

    return $base . '/' . ltrim($path, '/');
}

$app = require __DIR__ . '/../bootstrap/app.php';
$routes = require __DIR__ . '/../routes/web.php';

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
$scriptName = $_SERVER['SCRIPT_NAME'] ?? $_SERVER['PHP_SELF'] ?? '';
$basePath = preg_replace('#/index\.php$#', '', $scriptName);
if ($basePath !== '' && $basePath !== '/' && str_starts_with($uri, $basePath)) {
    $uri = substr($uri, strlen($basePath));
    if ($uri === '') {
        $uri = '/';
    }
}
$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

$route = null;
$routeParams = [];
foreach ($routes as $candidate) {
    [$allowedMethod, $path, $handler] = $candidate;
    if ($allowedMethod !== $method) {
        continue;
    }

    if ($path === $uri) {
        $route = $handler;
        break;
    }

    if (str_contains($path, '{')) {
        $regex = preg_quote($path, '/');
        $regex = preg_replace('/\\\\\{[a-zA-Z0-9_]+\\\\\}/', '([^/]+)', $regex);
        if (preg_match('#^' . $regex . '$#', $uri, $matches) === 1) {
            $route = $handler;
            $routeParams = array_slice($matches, 1);
            break;
        }
    }
}

if ($route === null) {
    http_response_code(404);
    echo '404 Not Found';
    exit;
}

$result = $route(...$routeParams);

if (isset($result['redirect'])) {
    header('Location: ' . $result['redirect']);
    exit;
}

extract($result, EXTR_SKIP);

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
