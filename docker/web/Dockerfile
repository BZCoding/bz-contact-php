FROM php:7.0-fpm-alpine

RUN apk update && apk add --no-cache --virtual .mongodb-ext-build-deps autoconf build-base openssl-dev pcre-dev

RUN docker-php-ext-install bcmath

RUN pecl install mongodb && docker-php-ext-enable mongodb

RUN docker-php-source delete

RUN apk del .mongodb-ext-build-deps autoconf build-base

RUN curl -sS https://getcomposer.org/installer | php \
        && mv composer.phar /usr/local/bin/ \
        && ln -s /usr/local/bin/composer.phar /usr/local/bin/composer

# For prod image
COPY ./app /var/www/html/app
COPY ./composer.json /var/www/html/composer.json
WORKDIR /var/www/html

# For prod image (--prefer-source requires git)
ENV DATABASE_URI="something"
ENV MAILER_HOST="mailer"
RUN composer install --no-interaction

ENV PATH="~/.composer/vendor/bin:./vendor/bin:${PATH}"

