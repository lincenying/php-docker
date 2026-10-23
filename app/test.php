<?php
require 'inc/conn.php';

function dd($v, $t = '', $sql = '')
{
    echo '<pre style="border: 1px solid #ccc; padding: 5px 20px 20px; margin-bottom: 10px;">';
    echo '<h1>' . $t . '</h1>';
    echo '<h4>' . $sql . '</h4>';
    echo '<p>return:</p>';
    print_r($v);
    echo '</pre>';
}

echo $get1 ?? 'unset get1';
echo $get2 ?? 'unset get2';

print_r($_GET);

// 查询多条数据, 返回数组对象
$sql         = 'SELECT * FROM cyxw_archive';
$memCacheKey = get_sql_md5($sql);
$person      = [];
if ($onMemCache == false || ! ($person = $memCache->get($memCacheKey))) {
    $person = $db->query($sql);
    echo '$memCacheKey 未命中缓存';
    $onMemCache && $memCache->set($memCacheKey, $person, 0, 86400);
}
dd($person, "查询多条数据, 返回数组", "query('SELECT * FROM cyxw_archive')");

// 查询数据数量, 返回数值
$persons_num = $db->single('SELECT count(*) as total FROM cyxw_archive');
dd($persons_num, "查询数据数量, 返回数值", "single('SELECT count(*) FROM cyxw_archive')");

// 查询单条数据 返回对应字段
$sql  = 'SELECT c_title FROM cyxw_archive WHERE c_id > ?';
$data = ['1'];
echo get_sql_md5($sql, $data);
$firstname = $db->single($sql, $data);
dd($firstname, "查询单条数据, 返回对应字段", "single('SELECT c_title FROM cyxw_archive WHERE c_id > ?', ['1'])");

// 查询单条数据, 返回对应字段数组, 键名为字段名
$sql  = 'SELECT c_id, c_title FROM cyxw_archive WHERE c_id > :id order by c_id desc';
$data = ['id' => '1'];
echo get_sql_md5($sql, $data);
$id_age = $db->row($sql, $data);
dd($id_age, "查询单条数据, 返回对应字段数组, 键名为字段名", "row('SELECT c_id, c_title FROM cyxw_archive WHERE c_id > :id ', ['id' => '1'])");

// 查询单条数据, 返回对应字段数组, 键名为数字
$sql  = 'SELECT c_id, c_title FROM cyxw_archive WHERE c_title = :f';
$data = ['f' => '林岑影'];
echo get_sql_md5($sql, $data);
$id_age_num = $db->row($sql, $data, PDO::FETCH_NUM);
dd($id_age_num, "查询单条数据, 返回对应字段数组, 键名为数字", "row('SELECT c_id, c_title FROM cyxw_archive WHERE c_title = :f', ['f' => '林岑影'], PDO::FETCH_NUM)");

// 查询多条数据, 返回对应字段数组, 键名为数字
$ages = $db->column('SELECT c_title FROM cyxw_archive');
dd($ages, "查询多条数据, 返回对应字段数组, 键名为数字", "column('SELECT c_title FROM cyxw_archive')");

// 下面的语句将返回受影响的行
// 更新语句
$update = $db->query('UPDATE cyxw_archive SET c_title = :f WHERE c_id = :id', ['f' => '丑是天生的债', 'id' => '1']);
dd($update, "更新语句", "query('UPDATE cyxw_archive SET c_title = :f WHERE c_id = :id', ['f' => '丑是天生的债', 'id' => '1'])");

// 插入语句
// $insert = $db->query(
//     'INSERT INTO  cyxw_archive (c_card,c_userid,c_cb,c_title,c_cashuser,c_status) VALUES (:card,:userid,:cb,:cashname,:cashuser,:status)',
//     [
//         'card' => '1231234232',
//         'userid' => '123',
//         'cb' => '123',
//         'cashname' => '魅影',
//         'cashuser' => '魅影',
//         'status' => '1',
//     ],
// );

// dd($insert, "插入语句", "'INSERT INTO  cyxw_archive (c_card,c_userid,c_cb,c_title,c_cashuser,c_status) VALUES (:card,:userid,:cb,:cashname,:cashuser,:status)'");

// 删除语句
$delete = $db->query('DELETE FROM cyxw_archive WHERE c_id = :id', ['id' => '4']);
dd($delete, "删除语句", "query('DELETE FROM cyxw_archive WHERE c_id = :id', ['id' => '4'])");

phpinfo();
