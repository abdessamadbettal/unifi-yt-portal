<?php

session_start();

require 'logger.php';

Logger::info('Session started', [
    'session_id' => session_id(),
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
    'request_uri' => $_SERVER['REQUEST_URI'] ?? 'unknown'
]);

if (!(isset($_SESSION['id']) or isset($_GET['id']))) {
    Logger::warning('Direct access attempt blocked - no session or GET id');
    exit('This page cannot be accessed directly. It only works when using a hotspot.');
}

if (isset($_GET['id'])) {
    Logger::info('New guest connection', [
        'mac' => $_GET['id'] ?? 'unknown',
        'ap' => $_GET['ap'] ?? 'unknown'
    ]);
}

require 'vendor/autoload.php';

try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../");
    $dotenv->load();
    Logger::info('Environment variables loaded successfully');
} catch (Exception $e) {
    Logger::error('Failed to load environment variables', ['error' => $e->getMessage()]);
    exit('Configuration error. Please contact administrator.');
}

$business_name = $_SERVER['BUSINESS_NAME'];
Logger::debug('Business name set', ['business_name' => $business_name]);