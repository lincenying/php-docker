FROM php:8.3-fpm

COPY --from=composer:2.8 /usr/bin/composer /usr/local/bin/composer

# 安装系统依赖和 PHP 扩展
RUN sed -i 's|deb.debian.org|mirrors.ustc.edu.cn|g' /etc/apt/sources.list.d/debian.sources \
    && apt-get update && apt-get install -y --no-install-recommends \
        libzip-dev \
        unzip \
    && docker-php-ext-install -j"$(nproc)" pdo pdo_mysql zip \
    && docker-php-ext-enable opcache \
    && rm -rf /var/lib/apt/lists/*

COPY docker/zz-app.ini /usr/local/etc/php/conf.d/zz-app.ini
COPY docker/zzz-fpm.conf /usr/local/etc/php-fpm.d/zzz-fpm.conf
COPY docker/php-entrypoint.sh /usr/local/bin/php-entrypoint

# 构建产物放在 /opt，启动时同步到 /home/web/app-php，避免共享卷盖住镜像里的代码
WORKDIR /opt/app-php

# 先复制依赖清单，利用 Docker 层缓存
COPY app/composer.json app/composer.lock ./

# 安装 PHP 依赖（不执行脚本，避免构建期副作用）
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --no-dev --no-scripts --prefer-dist --no-interaction --no-progress \
    && composer dump-autoload --optimize

# 复制应用源码（覆盖/补齐其余文件，保留已安装的 vendor）
COPY app/ ./

RUN chmod +x /usr/local/bin/php-entrypoint \
    && mkdir -p upload cache logs /home/web/app-php \
    && chown -R www-data:www-data /opt/app-php \
    && chmod -R u=rwX,go=rX /opt/app-php \
    && chmod 775 upload cache logs

ENTRYPOINT ["php-entrypoint"]
CMD ["php-fpm"]

# 第一次执行时, 如果镜像拉不下来, 可以执行以下命令:
# docker pull swr.cn-north-4.myhuaweicloud.com/ddn-k8s/docker.io/php:8.3-fpm
# docker tag swr.cn-north-4.myhuaweicloud.com/ddn-k8s/docker.io/php:8.3-fpm php:8.3-fpm
# 构建镜像
# docker build -t lincenying/app-php:1.26.1023 -f ./Dockerfile .
# 运行镜像
# docker run -d --name container-php-app lincenying/app-php:1.26.1023
# 进入镜像
# docker exec -it container-php-app /bin/sh
