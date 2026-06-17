<?php
declare(strict_types=1);

define('APP_NAME', '選考舵');
define('APP_TIMEZONE', 'Asia/Tokyo');
define('ROOT_PATH', dirname(__DIR__, 2));
define('UPLOAD_DIR', ROOT_PATH . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'company_images');
define('MAX_UPLOAD_BYTES', 5 * 1024 * 1024);

define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'job_hunt_manager');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

date_default_timezone_set(APP_TIMEZONE);

$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$basePath = '';

if ($scriptName !== '') {
    $phpPosition = strpos($scriptName, '/PHP/');
    if ($phpPosition !== false) {
        $basePath = substr($scriptName, 0, $phpPosition);
    } else {
        $directory = str_replace('\\', '/', dirname($scriptName));
        $basePath = ($directory === '/' || $directory === '.') ? '' : $directory;
    }
}

define('BASE_PATH', rtrim($basePath, '/'));

if (session_status() === PHP_SESSION_NONE) {
    $isSecure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => BASE_PATH !== '' ? BASE_PATH : '/',
        'secure' => $isSecure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}
