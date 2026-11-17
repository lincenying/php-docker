<?php
require 'api-header.php';
require LCY_ROOT . 'inc/func.upload.php';

try {
    if (env_string('UPLOAD_TOKEN') === '') {
        throw new RuntimeException('缺少上传配置');
    }
    if (! upload_authorized()) {
        throw new RuntimeException('token 不正确');
    }
    if (empty($_FILES['file']) || ! is_array($_FILES['file'])) {
        throw new RuntimeException('文件不能为空');
    }
    $upload = new upload([
        'files'       => $_FILES['file'],
        'isSmall'     => 0,
        'isMark'      => 0,
        'waterText'   => '',
        'isResize'    => 0,
        'filepath'    => '',
        'attaMaxSize' => 5 * 1024 * 1024,
    ]);
    $data = $upload->upMore();
    if (! is_array($data) || ($data['err_msg'] ?? '') !== '') {
        throw new RuntimeException((string) ($data['err_msg'] ?? '文件上传失败'));
    }
    json_response([
        'code' => 200,
        'data' => $data,
    ]);
} catch (Exception $e) {
    json_response([
        'code' => 300,
        'data' => null,
        'msg'  => public_error_message($e),
    ]);
}
