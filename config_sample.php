<?php
define( 'ROOT', __DIR__ . '/' );
define( 'DEBUG', str_ends_with( $_SERVER[ 'SERVER_NAME' ], 'localhost' ) );

// Base URL and Default URL
$base_url = 'http://link-qr.localhost/';
$default_url = 'https://de.wikipedia.org/';

// Database Configuration
$db = [
    'hostname' => 'localhost',
    'database' => 'link_qr',
    'username' => 'link_qr',
    'password' => 'link_qr'
];

// Log Configuration
$log = [
    'name' => 'qr',
    'location' => 'D:/Projekten/logs',
    'level' => 'Info'
];

// Log Rotate Configuration
$logrotate = [
    'cronExpression' => '0 0 * * */6',
    'maxFiles' => 2,
    'minSize' => 120,
    'compress' => false
];

$api = [
    'namespace' => 'Kingsoft\LinkQr',
    'allowedendpoints' => [ 'Code', 'User', 'UserEmail'],
    'allowedmethods' => [ 'GET', 'POST', 'PUT', 'DELETE' ]
];

// Output the configuration as an array (if needed for debugging)
define( 'SETTINGS', [
    'base_url' => $base_url,
    'default_url' => $default_url,
    'db' => $db,
    'api' => $api,
    'log' => $log,
    'logrotate' => $logrotate
]);
