<?php
require LCY_ROOT . 'vendor/autoload.php';
require 'api-header.php';

use Qiniu\Auth;
use Qiniu\Storage\UploadManager;

try {
    if (env_string('UPLOAD_TOKEN') === '') {
        throw new RuntimeException('缺少上传配置');
    }
    if (! upload_authorized()) {
        throw new RuntimeException('token 不正确');
    }
    $accessKey = env_string('QINIU_ACCESS_KEY');
    $secretKey = env_string('QINIU_SECRET_KEY');
    if ($accessKey === '' || $secretKey === '') {
        throw new RuntimeException('缺少七牛配置');
    }
    if (empty($_FILES['file']) || ! is_array($_FILES['file'])) {
        throw new RuntimeException('文件不能为空');
    }

    $file = $_FILES['file'];
    $tmp  = (string) ($file['tmp_name'] ?? '');
    $name = (string) ($file['name'] ?? '');
    $size = (int) ($file['size'] ?? 0);
    if ($size < 1 || $size > 5 * 1024 * 1024 || ! is_uploaded_image($tmp, $name)) {
        throw new RuntimeException('文件类型不允许');
    }

    $ext         = image_extension($name);
    $fileNewName = 'upload/' . date('YmdHis') . '_' . get_random_str(8, 'Aa') . '.' . $ext;
    $bucket      = env_string('QINIU_BUCKET', 'default');
    $domain      = rtrim(env_string('QINIU_DOMAIN', 'http://cdn.mmxiaowu.com'), '/');
    $auth        = new Auth($accessKey, $secretKey);
    $token       = $auth->uploadToken($bucket);
    $uploadMgr   = new UploadManager();
    [$ret, $err] = $uploadMgr->putFile($token, $fileNewName, $tmp);
    if ($err) {
        $message = '上传失败';
        if (is_object($err) && method_exists($err, 'message')) {
            $message = (string) $err->message();
        } elseif (is_string($err) && $err !== '') {
            $message = $err;
        }
        throw new RuntimeException($message !== '' ? $message : '上传失败');
    }

    $key = is_array($ret) ? (string) ($ret['key'] ?? $fileNewName) : $fileNewName;
    $url = $domain . '/' . ltrim($key, '/');
    json_response([
        'code' => 200,
        'data' => [
            'err_msg'   => '',
            'imgurl'    => $url,
            'smallurl'  => '',
            'markurl'   => '',
            'resizeimg' => '',
            'fileText'  => '',
            'filepath'  => $url,
            'oldurl'    => $name,
        ],
    ]);
} catch (Exception $e) {
    json_response([
        'code' => 300,
        'data' => null,
        'msg'  => public_error_message($e),
    ]);
}
