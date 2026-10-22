#!/bin/sh
set -e

src=/opt/app-php
dest=/home/web/app-php

# 源码放在 /opt，运行目录可能是共享卷。每次启动同步，镜像更新后卷里不会留下旧代码。
# 不覆盖 upload：full 栈把宿主机目录挂在这里。
if [ -f "$src/index.php" ]; then
    mkdir -p "$dest"
    find "$dest" -mindepth 1 -maxdepth 1 ! -name upload -exec rm -rf {} +
    tar -C "$src" --exclude=upload -cf /tmp/app-php.tar .
    tar -C "$dest" -xf /tmp/app-php.tar
    rm -f /tmp/app-php.tar
    find "$dest" -mindepth 1 -maxdepth 1 ! -name upload -exec chown -R www-data:www-data {} +
fi

upload_dir=$dest/upload
mkdir -p "$upload_dir" "$dest/cache" "$dest/logs"

# 绑定挂载常为 root:root 755。chown 在部分桌面版 Docker 上不会生效，再退回目录可写。
if ! su -s /bin/sh www-data -c "test -w '$upload_dir'"; then
    chown www-data:www-data "$upload_dir" 2>/dev/null || true
fi
if ! su -s /bin/sh www-data -c "test -w '$upload_dir'"; then
    chmod 777 "$upload_dir" || true
fi

exec docker-php-entrypoint "$@"
