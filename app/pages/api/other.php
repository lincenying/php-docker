<?php
require 'api-header.php';

json_response([
    'code' => 404,
    'data' => null,
    'msg'  => '接口不存在',
], 404);
