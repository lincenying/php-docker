<?php
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
if (is_string($origin) && $origin !== '' && cors_origin_allowed($origin)) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Vary: Origin');
    header('Access-Control-Allow-Credentials: true');
}
header('Access-Control-Allow-Methods: OPTIONS, GET, POST, PUT');
header('Access-Control-Allow-Headers: X-Requested-With, Content-Type, X-CSRF-Token, Authorization');
