<?php
require LCY_ROOT . 'inc/view.php';
require LCY_ROOT . 'inc/archive.php';

$id  = route_int(isset($routeVars) && is_array($routeVars) ? $routeVars : [], 'id');
$row = archive_row($id);
if ($row === null) {
    http_response_code(404);
    render_404('没有找到这篇文章。');
}

$title            = (string) ($row['c_title'] ?? '详情');
$row['c_content'] = archive_html($row);

render_page('detail.twig', [
    'global'   => view_global(),
    'router'   => $routeVars ?? [],
    'seo'      => [
        'title'   => $title,
        'keyword' => $title,
        'desc'    => $title,
    ],
    'row'      => $row,
    'listPage' => request_int('page', 1, 1, 100000),
]);
release_resources();
exit;
