<?php
require 'inc/conn.php';

$action = $_GET['action'] ?? 'other';
if (! is_string($action)) {
    $action = 'other';
}
$action = str_replace('/', '-', $action);
if (! preg_match('/^[a-z0-9_-]+$/i', $action)) {
    $action = 'other';
}

$base = realpath(__DIR__ . '/pages/api');
if ($base === false) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo "服务器错误\n";
    exit;
}
$file = realpath($base . DIRECTORY_SEPARATOR . $action . '.php');
if ($file === false || ! is_file($file) || ! str_starts_with($file, $base . DIRECTORY_SEPARATOR)) {
    $file = $base . DIRECTORY_SEPARATOR . 'other.php';
}

include $file;
