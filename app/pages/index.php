<?php
require LCY_ROOT . 'inc/view.php';
require LCY_ROOT . 'inc/func.page.php';
require LCY_ROOT . 'inc/archive.php';

$page    = request_int('page', 1, 1, 100000);
$perPage = request_int('per_page', 10, 1, 50);
$total   = archive_count();

$classPage = new page([
    'total'     => $total,
    'now_index' => $page,
    'per_page'  => $perPage,
    'pageName'  => 'page',
    'url'       => '/?',
    'rewrite'   => false,
]);
$list = archive_list((int) $classPage->offset, $perPage);

render_page('home.twig', [
    'global' => view_global(),
    'router' => $routeVars,
    'seo'    => [
        'title'   => '首页',
        'keyword' => '首页',
        'desc'    => '首页',
    ],
    'total'  => $total,
    'list'   => $list,
    'page'   => $page,
    'pages'  => $classPage->show(4),
]);
release_resources();
exit;
