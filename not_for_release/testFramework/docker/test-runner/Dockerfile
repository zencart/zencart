ARG PHP_VERSION=8.4

FROM php:${PHP_VERSION}-apache-bookworm

LABEL org.opencontainers.image.source="https://github.com/zencart/zencart"
LABEL org.opencontainers.image.description="Zen Cart test runner image"
LABEL org.opencontainers.image.licenses="GPL-2.0-only"

ENV COMPOSER_ALLOW_SUPERUSER=1
ENV APACHE_DOCUMENT_ROOT=/var/www/html

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        bash \
        ca-certificates \
        default-mysql-client \
        git \
        libcurl4-openssl-dev \
        libfreetype6-dev \
        libicu-dev \
        libjpeg62-turbo-dev \
        libonig-dev \
        libpng-dev \
        libzip-dev \
        rsync \
        unzip \
        zip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd \
    && docker-php-ext-install intl \
    && docker-php-ext-install mysqli \
    && docker-php-ext-install pdo_mysql \
    && docker-php-ext-install zip \
    && a2enmod rewrite \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2.9.8 /usr/bin/composer /usr/bin/composer

RUN { \
        echo "memory_limit=512M"; \
        echo "max_execution_time=120"; \
        echo "display_errors=1"; \
        echo "log_errors=1"; \
        echo "error_reporting=E_ALL"; \
    } > /usr/local/etc/php/conf.d/zencart-test.ini

RUN php -r 'foreach (["curl", "gd", "intl", "mbstring", "mysqli", "pdo_mysql", "zip"] as $extension) { if (!extension_loaded($extension)) { fwrite(STDERR, "Missing PHP extension: {$extension}\n"); exit(1); } }'

WORKDIR /var/www/html
