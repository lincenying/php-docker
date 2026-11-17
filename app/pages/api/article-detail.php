<?php
require 'api-header.php';
require LCY_ROOT . 'inc/archive.php';

$id = route_int(isset($routeVars) && is_array($routeVars) ? $routeVars : [], 'id');

try {
    if ($id < 1) {
        throw new RuntimeException('ID不能为空');
    }
    $row = archive_row($id);
    if ($row === null) {
        throw new RuntimeException('没有找到该文章');
    }
    $row['c_content'] = archive_html($row);
    json_response([
        'code' => 200,
        'data' => $row,
    ]);
} catch (Exception $e) {
    json_response([
        'code' => 300,
        'data' => null,
        'msg'  => public_error_message($e),
    ]);
}
