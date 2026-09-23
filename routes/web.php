<?php

$app = require __DIR__ . '/../bootstrap/app.php';
$authService = $app['auth'];

use App\Core\Auth;
use App\Support\Csrf;

return [
    ['GET', '/', function () {
        return [
            'view' => 'landing',
            'title' => 'FastPhunzira',
        ];
    }],
    ['GET', '/login', function () {
        return [
            'view' => 'auth/login',
            'title' => 'Login',
        ];
    }],
    ['POST', '/login', function () use ($authService) {
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');
        $token = $_POST['_token'] ?? null;

        if (!Csrf::validate($token)) {
            $_SESSION['flash_error'] = 'Invalid security token.';

            return ['redirect' => '/login'];
        }

        $result = $authService->login(['email' => $email, 'password' => $password]);

        if (!$result['success']) {
            $_SESSION['flash_error'] = $result['message'];

            return ['redirect' => '/login'];
        }

        $_SESSION['flash_success'] = 'Welcome back!';

        return ['redirect' => '/dashboard'];
    }],
    ['GET', '/register', function () {
        return [
            'view' => 'auth/register',
            'title' => 'Register',
        ];
    }],
    ['POST', '/register', function () use ($authService) {
        $token = $_POST['_token'] ?? null;

        if (!Csrf::validate($token)) {
            $_SESSION['flash_error'] = 'Invalid security token.';

            return ['redirect' => '/register'];
        }

        $result = $authService->register([
            'full_name' => trim((string) ($_POST['full_name'] ?? '')),
            'email' => trim((string) ($_POST['email'] ?? '')),
            'password' => (string) ($_POST['password'] ?? ''),
            'password_confirmation' => (string) ($_POST['password_confirmation'] ?? ''),
        ]);

        if (!$result['success']) {
            $_SESSION['flash_error'] = $result['errors'][0]['message'] ?? $result['message'];

            return ['redirect' => '/register'];
        }

        $_SESSION['flash_success'] = 'Registration successful. Please log in.';

        return ['redirect' => '/login'];
    }],
    ['POST', '/logout', function () use ($authService) {
        $token = $_POST['_token'] ?? null;

        if (!Csrf::validate($token)) {
            $_SESSION['flash_error'] = 'Invalid security token.';

            return ['redirect' => '/dashboard'];
        }

        $authService->logout();
        $_SESSION['flash_success'] = 'You have been logged out.';

        return ['redirect' => '/login'];
    }],
    ['GET', '/dashboard', function () {
        if (!Auth::check()) {
            $_SESSION['flash_error'] = 'Please log in to continue.';

            return ['redirect' => '/login'];
        }

        return [
            'view' => 'dashboard',
            'title' => 'Dashboard',
        ];
    }],
];
