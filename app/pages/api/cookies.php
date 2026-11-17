<?php
require 'api-header.php';

setcookie('user', 'John Doe', [
    'expires'  => time() + 36000,
    'path'     => '/',
    'httponly' => true,
    'samesite' => 'Lax',
]);

json_response([
    'code' => 200,
    'data' => [
        'name' => 'user',
    ],
]);
