FROM php:8.3-fpm

# 安装系统依赖和PHP扩展
RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    && docker-php-ext-install pdo pdo_mysql zip

# 安装Composer
RUN curl -sS https://getcomposer.org/installer | php -- \
    --install-dir=/usr/local/bin --filename=composer

# 设置工作目录
WORKDIR /home/web/php-template

RUN chown -R www-data:www-data /home/web/php-template
RUN chown -R 755 /home/web/php-template

# 第一次执行时, 如果node镜像拉不下来, 可以执行以下命令:
# docker pull swr.cn-north-4.myhuaweicloud.com/ddn-k8s/docker.io/php:8.3-fpm
# docker tag swr.cn-north-4.myhuaweicloud.com/ddn-k8s/docker.io/php:8.3-fpm php:8.3-fpm
# 构建镜像
# docker build -t lincenying/php-app-server:1.25.1029 -f ./Dockerfile .
# 运行镜像
# docker run -d -p 4008:4000 --name container-php-app lincenying/php-app-server:1.25.1029
# 进入镜像
# docker exec -it container-php-app /bin/sh
# 停止容器
# docker stop container-php-app
# 删除容器
# docker rm container-php-app
# 删除镜像
# docker rmi lincenying/php-app-server:1.25.1029
