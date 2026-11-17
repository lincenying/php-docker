<?php
require LCY_ROOT . 'inc/view.php';

render_page('about.twig', [
    'global' => view_global(),
    'router' => $routeVars ?? [],
    'seo'    => [
        'title'   => '关于',
        'keyword' => '关于',
        'desc'    => '笑一笑是一本随手记下的段子',
    ],
]);
release_resources();
exit;
