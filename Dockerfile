FROM php:8.3-fpm

# 安装系统依赖和PHP扩展
RUN sed -i 's|deb.debian.org|mirrors.aliyun.com|g' /etc/apt/sources.list.d/debian.sources \
	&& apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    && docker-php-ext-install pdo pdo_mysql zip \
    && rm -rf /var/lib/apt/lists/*

# 安装Composer
RUN curl -sS https://getcomposer.org/installer | php -- \
    --install-dir=/usr/local/bin --filename=composer

# 设置工作目录
WORKDIR /home/web/app-php

# 先复制依赖清单，利用 Docker 层缓存
COPY app/composer.json app/composer.lock ./

# 安装 PHP 依赖（不执行脚本，避免构建期副作用）
RUN composer install --no-dev --no-scripts --prefer-dist --no-interaction \
    && composer dump-autoload --optimize

# 复制应用源码（覆盖/补齐其余文件，保留已安装的 vendor）
COPY app/ ./

RUN chown -R www-data:www-data /home/web/app-php \
    && chmod -R 755 /home/web/app-php

# 第一次执行时, 如果镜像拉不下来, 可以执行以下命令:
# docker pull swr.cn-north-4.myhuaweicloud.com/ddn-k8s/docker.io/php:8.3-fpm
# docker tag swr.cn-north-4.myhuaweicloud.com/ddn-k8s/docker.io/php:8.3-fpm php:8.3-fpm
# 构建镜像
# docker build -t lincenying/app-php:1.26.0727 -f ./Dockerfile .
# 运行镜像
# docker run -d --name container-php-app lincenying/app-php:1.26.0727
# 进入镜像
# docker exec -it container-php-app /bin/sh
