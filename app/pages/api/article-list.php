<?php
require 'api-header.php';
require LCY_ROOT . 'inc/archive.php';

$page  = request_int('page', 1, 1, 100000);
$limit = request_int('limit', 10, 1, 50);

try {
    $list = archive_list(($page - 1) * $limit, $limit);
    json_response([
        'code' => 200,
        'data' => $list,
    ]);
} catch (Exception $e) {
    json_response([
        'code' => 300,
        'data' => null,
        'msg'  => public_error_message($e),
    ]);
}
