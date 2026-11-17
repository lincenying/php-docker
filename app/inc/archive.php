<?php

function archive_cached(string $sql, array $params, callable $query)
{
    global $db, $onMemCache, $memCache;
    $key = get_sql_md5($sql, $params);
    if ($onMemCache && $memCache) {
        $cached = $memCache->get($key);
        if ($cached !== false) {
            return $cached;
        }
    }
    $result = $query($db);
    if ($onMemCache && $memCache) {
        $memCache->set($key, $result, 0, 86400);
    }
    return $result;
}

function archive_count(): int
{
    $sql = 'SELECT COUNT(*) FROM archive';
    return (int) archive_cached($sql, [], function ($db) use ($sql) {
        return $db->single($sql);
    });
}

function archive_list(int $offset, int $limit): array
{
    $offset = max(0, $offset);
    $limit  = max(1, $limit);
    $sql    = 'SELECT * FROM archive ORDER BY c_id DESC LIMIT ' . $offset . ', ' . $limit;
    $list   = archive_cached($sql, [], function ($db) use ($sql) {
        return $db->query($sql, []);
    });
    return is_array($list) ? $list : [];
}

function archive_all(): array
{
    $sql  = 'SELECT c_id, c_title, c_posttime FROM archive ORDER BY c_posttime DESC, c_id DESC';
    $list = archive_cached($sql, [], function ($db) use ($sql) {
        return $db->query($sql, []);
    });
    return is_array($list) ? $list : [];
}

function archive_row(int $id): ?array
{
    if ($id < 1) {
        return null;
    }
    $sql    = 'SELECT * FROM archive WHERE c_id = ?';
    $params = [$id];
    $row    = archive_cached($sql, $params, function ($db) use ($sql, $params) {
        return $db->row($sql, $params);
    });
    return is_array($row) ? $row : null;
}

function archive_html(array $row): string
{
    require_once LCY_ROOT . 'inc/func.ubb.php';
    $content = (string) ($row['c_content'] ?? '');
    $ubb     = new Ubb();
    $ubb->setString($content);
    return (string) $ubb->parse();
}
