<?php

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
    ['GET', '/register', function () {
        return [
            'view' => 'auth/register',
            'title' => 'Register',
        ];
    }],
    ['GET', '/dashboard', function () {
        return [
            'view' => 'dashboard',
            'title' => 'Dashboard',
        ];
    }],
];
