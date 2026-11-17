<?php

function view_global(): array
{
    global $global;
    return [
        'uri' => $global['uri'] ?? '/',
    ];
}

function render_page(string $template, array $data): void
{
    static $twig = null;
    if ($twig === null) {
        require_once LCY_ROOT . 'vendor/autoload.php';
        $loader = new \Twig\Loader\FilesystemLoader(LCY_ROOT . 'twig');
        $debug  = app_debug();
        $twig   = new \Twig\Environment($loader, [
            'cache'       => LCY_ROOT . 'cache',
            'auto_reload' => true,
            'debug'       => $debug,
        ]);
    }
    echo $twig->render($template, $data);
}

function render_404(string $message = ''): void
{
    http_response_code(404);
    render_page('404.twig', [
        'global'  => view_global(),
        'router'  => (isset($GLOBALS['routeVars']) && is_array($GLOBALS['routeVars'])) ? $GLOBALS['routeVars'] : [],
        'message' => $message,
        'seo'     => [
            'title'   => '404 Page Not Found',
            'keyword' => '404 Page Not Found',
            'desc'    => '404 Page Not Found',
        ],
    ]);
    release_resources();
    exit;
}

function release_resources(): void
{
    global $db, $onMemCache, $memCache;
    if (isset($db) && $db instanceof DB) {
        $db->CloseConnection();
    }
    if (! empty($onMemCache) && $memCache) {
        $memCache->close();
    }
}
