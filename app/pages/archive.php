<?php
require LCY_ROOT . 'vendor/autoload.php';

$loader = new \Twig\Loader\FilesystemLoader(LCY_ROOT . 'twig');
$twig   = new \Twig\Environment($loader, [
    'cache'       => LCY_ROOT . 'cache',
    'auto_reload' => true, //根据文件更新时间，自动更新缓存
    'debug'       => true,
]);
$twigFile = 'archive.twig';

// 业务代码开始 ======>

$sql         = 'SELECT c_id, c_title, c_posttime FROM archive ORDER BY c_posttime DESC, c_id DESC';
$memCacheKey = get_sql_md5($sql, []);
$list        = [];
if ($onMemCache == false || ! ($list = $memCache->get($memCacheKey))) {
    $list = $db->query($sql, []);
    $onMemCache && $memCache->set($memCacheKey, $list, 0, 86400);
}

$groups = [];
foreach ($list as $row) {
    $time            = $row['c_posttime'] ?? '';
    $year            = strlen($time) >= 4 ? substr($time, 0, 4) : '更早';
    $row['day']      = strlen($time) >= 10 ? substr($time, 5, 5) : '';
    $groups[$year][] = $row;
}

$seo = [
    'title'   => '归档',
    'keyword' => '归档',
    'desc'    => '按年份翻看笑一笑收录的段子',
];

$twigData = [
    'global' => $global,
    'router' => $routeVars,
    'seo'    => $seo,
    'total'  => count($list),
    'groups' => $groups,
];

// <=========== 业务代码结束

echo $twig->render($twigFile, $twigData);
$db->CloseConnection();
if ($onMemCache && $memCache) {
    $memCache->close();
}

exit();
