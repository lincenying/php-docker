# app-php

PHP 8.3 + Nginx + MySQL 8 的 Docker 项目。页面由 Twig 渲染，路由使用 FastRoute。源码和 Nginx 配置在构建时打进镜像，运行时不挂载代码目录。

## 服务

| 服务 | 容器名 | 说明 |
| --- | --- | --- |
| `app_php` | `app-php` | PHP-FPM，镜像 `lincenying/app-php` |
| `nginx_php` | `nginx-php` | Nginx，宿主机 `8084` 映射容器 `80` |
| `mysql_db_php` | `mysql-db-php` | MySQL 8，宿主机 `3306` |

访问：<http://localhost:8084>

## 配置

[`.env`](.env) 提供镜像标签，会提交到仓库：

```bash
TAG=1.26.1014
```

数据库路径和账号放在 [`.env.local`](.env.local)（已加入 `.gitignore`）。Compose 默认只读取 `.env`，启动时需要同时指定这两个文件。

```bash
MYSQL_DIR=/path/to/mysqldb
MYSQL_ROOT_PASSWORD=这是root密码
MYSQL_DATABASE=这是数据库名
MYSQL_USER=这是数据库用户
MYSQL_PASSWORD=这是数据库密码
```

`MYSQL_DIR` 会挂载到容器的 `/var/lib/mysql`。未设置时使用项目下的 `./data/mysqldb`。

PHP 连接数据库时优先读环境变量 `DB_HOST`、`DB_PORT`、`DB_DATABASE`、`DB_USERNAME`、`DB_PASSWORD`（在 `docker-compose.yml` 里与上面的 MySQL 变量对应）。没有环境变量时，回退到 [`app/inc/settings.ini.php`](app/inc/settings.ini.php)。

改端口、域名或 HTTPS 时编辑 [`nginx/conf.d/php.conf`](nginx/conf.d/php.conf)。站点根目录是容器内的 `/home/web/app-php/`。改完需要重新构建 Nginx 镜像。

使用外部数据库时，删掉 `docker-compose.yml` 里的 `mysql_db_php`，以及 `app_php` 的 `depends_on`，再把 `DB_HOST` 等改成外部地址。

## 启动

基础镜像拉不下来时，可以先换国内源再打标签：

```bash
docker pull swr.cn-north-4.myhuaweicloud.com/ddn-k8s/docker.io/php:8.3-fpm
docker tag swr.cn-north-4.myhuaweicloud.com/ddn-k8s/docker.io/php:8.3-fpm php:8.3-fpm
docker rmi swr.cn-north-4.myhuaweicloud.com/ddn-k8s/docker.io/php:8.3-fpm
```

构建并启动：

```bash
docker compose --env-file .env --env-file .env.local build
docker compose --env-file .env --env-file .env.local up -d
```

PHP 依赖在构建 `app-php` 镜像时执行 `composer install`，不需要再进容器安装。

有 [`app/mysql.sql`](app/mysql.sql) 时，它会挂到容器内 `/home/mysql/mysql.sql`，可这样导入：

```bash
docker exec -i mysql-db-php mysql -uuser -ppassword cyxiaowu < app/mysql.sql
```

常用命令：

```bash
docker exec -it app-php sh
docker exec -it mysql-db-php bash
docker compose --env-file .env --env-file .env.local logs -f
docker compose --env-file .env --env-file .env.local down
```

修改 PHP、静态文件或 Nginx 配置后重新构建对应镜像再启动。代码不会通过 volume 热更新。

## 路由

页面路由在 [`app/index.php`](app/index.php)：

- `/` 首页
- `/detail/{id}` 文章详情
- `/company`、`/contact`
- `/4a/login`、`/4a/serviceValidate`
- `/api/article/list`、`/api/article/lists`、`/api/article/detail`
- `POST /api/contact`、`/api/upload`、`/api/qiniu`
