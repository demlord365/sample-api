FROM php:7.4-fpm

RUN apt-get update \
  && apt-get install -y \
     apt-utils \
     vim \
     man \
     curl \
     git \
     pkg-config \
     icu-devtools \
     libicu-dev \
     libcurl4 \
     libcurl4-gnutls-dev \
     libfreetype6-dev \
     libjpeg62-turbo-dev \
     libpng-dev \
     libbz2-dev \
     libssl-dev \
     libgmp-dev \
     libtidy-dev \
     libxml2-dev \
     libxslt1-dev \
     libonig-dev \
     libzip-dev \
  &&  ln -s /usr/include/x86_64-linux-gnu/gmp.h /usr/include/gmp.h

RUN docker-php-ext-install mysqli \
    && docker-php-ext-install pdo_mysql \
    && docker-php-ext-install intl \
    && docker-php-ext-install zip \
    && docker-php-ext-install bz2 \
    && docker-php-ext-install calendar \
    && docker-php-ext-install bcmath \
    && docker-php-ext-install exif \
    && docker-php-ext-install gettext \
    && docker-php-ext-install gmp \
    && docker-php-ext-install mbstring \
    && docker-php-ext-install pcntl \
    && docker-php-ext-install shmop \
    && docker-php-ext-install sockets \
    && docker-php-ext-install sysvmsg \
    && docker-php-ext-install sysvsem \
    && docker-php-ext-install sysvshm \
    && docker-php-ext-install tidy \
    && docker-php-ext-install xsl \
    && docker-php-source delete

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
  && docker-php-ext-install -j$(nproc) gd \
  && docker-php-source delete

# Устанавливаем composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer \
 && chmod 755 /usr/bin/composer

RUN apt-get update && apt-get install -y \
        libmemcached-dev \
        zlib1g-dev \
    && pecl install memcached \
    && docker-php-ext-enable memcached

# Устанавливаем xdebug
RUN pecl install xdebug-3.1.6 && docker-php-ext-enable xdebug

## Устанавливаем в качестве рабочей директории '/app'.
WORKDIR /var/www/html

CMD ["php-fpm"]