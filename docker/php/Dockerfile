FROM php:8.2-fpm
RUN apt-get update && apt-get install -y --no-install-recommends \
    git curl unzip \
    fontconfig libfreetype6 libjpeg62-turbo libpng16-16 \
    libx11-6 libxcb1 libxext6 libxrender1 xfonts-75dpi xfonts-base \
    && rm -rf /var/lib/apt/lists/*

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions \
    soap pdo_mysql mysqli zip gd mbstring curl xml bcmath

RUN { \
    echo "memory_limit=2G"; \
    echo "upload_max_filesize=1G"; \
    echo "post_max_size=500M"; \
    echo "max_execution_time=3600"; \
    echo "max_input_time=3600"; \
    } > /usr/local/etc/php/conf.d/custom.ini

RUN echo "listen = 0.0.0.0:9000" >> /usr/local/etc/php-fpm.d/www.conf

COPY --from=composer:2.2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
CMD ["php-fpm"]
