<?php
require 'inc/conn.php';
require 'vendor/autoload.php';

$dispatcher = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {
    $r->get('/', 'index');
    $r->get('/detail', 'detail');
    $r->get('/detail/{id:\d+}', 'detail');
    $r->get('/archive', 'archive');
    $r->get('/about', 'about');
    $r->get('/other', '404');
    $r->addGroup('/api', function (FastRoute\RouteCollector $r) {
        $r->get('/article/list', 'api/article-list');
        $r->get('/article/lists', 'api/article-lists');
        $r->get('/article/detail', 'api/article-detail');
        $r->get('/article/detail/{id:\d+}', 'api/article-detail');
        $r->get('/cookies', 'api/cookies');
        $r->get('/other', 'api/other');
        $r->post('/upload', 'api/upload');
        $r->post('/qiniu', 'api/qiniu');
    });
});

$httpMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri        = $_SERVER['REQUEST_URI'] ?? '/';
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

$isApi = $uri === '/api' || str_starts_with($uri, '/api/');
if ($httpMethod === 'OPTIONS' && $isApi) {
    require __DIR__ . '/pages/api/api-header.php';
    http_response_code(204);
    exit;
}

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        if ($isApi) {
            json_response([
                'code' => 404,
                'data' => null,
                'msg'  => '接口不存在',
            ], 404);
            exit;
        }
        $routeVars = [];
        include 'pages/404.php';
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        if (! headers_sent()) {
            header('Allow: ' . implode(', ', $allowedMethods));
        }
        if ($isApi) {
            json_response([
                'code' => 405,
                'data' => null,
                'msg'  => '请求方法不允许',
            ], 405);
            exit;
        }
        http_response_code(405);
        header('Content-Type: text/plain; charset=utf-8');
        echo "405 Method Not Allowed\n";
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler   = $routeInfo[1];
        $routeVars = $routeInfo[2];
        $pageFile  = __DIR__ . '/pages/' . $handler . '.php';
        $pagesRoot = realpath(__DIR__ . '/pages');
        $realPage  = realpath($pageFile);
        if (
            $pagesRoot === false
            || $realPage === false
            || ! is_file($realPage)
            || ! str_starts_with($realPage, $pagesRoot . DIRECTORY_SEPARATOR)
        ) {
            if ($isApi) {
                json_response([
                    'code' => 404,
                    'data' => null,
                    'msg'  => '接口不存在',
                ], 404);
                exit;
            }
            $routeVars = [];
            include 'pages/404.php';
            break;
        }
        include $realPage;
        break;
}
