<?php
require 'api-header.php';
require LCY_ROOT . 'inc/archive.php';

$page  = request_int('page', 1, 1, 100000);
$limit = request_int('limit', 10, 1, 50);

try {
    $total    = archive_count();
    $list     = archive_list(($page - 1) * $limit, $limit);
    $lastPage = (int) ceil($total / $limit);
    json_response([
        'code' => 200,
        'data' => [
            'list'      => $list,
            'total'     => $total,
            'page'      => $page,
            'perPage'   => $limit,
            'totalPage' => $lastPage,
            'hasNext'   => $lastPage > $page,
            'hasPrev'   => $page > 1,
        ],
    ]);
} catch (Exception $e) {
    json_response([
        'code' => 300,
        'data' => null,
        'msg'  => public_error_message($e),
    ]);
}
