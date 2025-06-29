<?php

$host = $_SERVER['HTTP_HOST'] ?? 'prueba.test';

// Determinamos el entorno
if (in_array($host, ['localhost', '127.0.0.1', 'prueba.test'])) {
    define('APP_ENV', 'development');
} else {
    define('APP_ENV', 'production');
}

$config = [
    'development' => [
        'host' => 'localhost',
        'db_name' => 'prueba',
        'username' => 'root',
        'password' => '',
        'display_errors' => true
    ],
    'production' => [
        'host' => 'localhost',
        'db_name' => '',
        'username' => '',
        'password' => '',
        'display_errors' => false
    ]
];

$current = $config[APP_ENV];

if (!$current['display_errors']) {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
} else {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}