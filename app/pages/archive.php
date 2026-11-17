<?php
require LCY_ROOT . 'inc/view.php';
require LCY_ROOT . 'inc/archive.php';

$list   = archive_all();
$groups = [];
foreach ($list as $row) {
    $time            = (string) ($row['c_posttime'] ?? '');
    $year            = strlen($time) >= 4 ? substr($time, 0, 4) : '更早';
    $row['day']      = strlen($time) >= 10 ? substr($time, 5, 5) : '';
    $groups[$year][] = $row;
}

render_page('archive.twig', [
    'global' => view_global(),
    'router' => $routeVars ?? [],
    'seo'    => [
        'title'   => '归档',
        'keyword' => '归档',
        'desc'    => '按年份翻看笑一笑收录的段子',
    ],
    'total'  => count($list),
    'groups' => $groups,
]);
release_resources();
exit;
