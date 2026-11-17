<?php
ini_set('log_errors', '1');
error_reporting(E_ALL);

date_default_timezone_set('Asia/Shanghai');
session_cache_limiter('private, must-revalidate');
ini_set('session.auto_start', '0');

define('LCY_ROOT', dirname(__DIR__) . '/');

require LCY_ROOT . 'inc/config.php';
require LCY_ROOT . 'inc/db.class.php';
require LCY_ROOT . 'inc/function.php';

ini_set('display_errors', app_debug() ? '1' : '0');

set_exception_handler(static function (Throwable $e): void {
    $path  = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $path  = is_string($path) ? $path : '/';
    $isApi = $path === '/api' || str_starts_with($path, '/api/');
    $message = app_debug() ? $e->getMessage() : public_error_message($e, '服务器错误');
    if ($isApi) {
        json_response([
            'code' => 500,
            'data' => null,
            'msg'  => $message,
        ], 500);
        return;
    }
    if (! headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/plain; charset=utf-8');
    }
    echo $message;
});

$db = new Db();

$memCache = null;
if ($onMemCache) {
    if (class_exists('Memcache')) {
        $memCache = new Memcache();
        if (! $memCache->connect('127.0.0.1', 11211)) {
            $onMemCache = false;
            $memCache   = null;
        }
    } else {
        $onMemCache = false;
    }
}

$global              = [];
$global['cacheTime'] = 900;
$global['clientIp']  = get_ip();
$global['uri']       = $_SERVER['REQUEST_URI'] ?? '/';
