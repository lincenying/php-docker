<?php
require 'api-header.php';

$expire = time() + 36000;
setcookie("user", "John Doe", $expire, "/");

$return         = [];
$return['code'] = 200;
$return['data'] = $_COOKIE;
$jsonStr        = json_encode($return, JSON_UNESCAPED_UNICODE);

echo $jsonStr;
