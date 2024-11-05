FROM ubuntu:latest
LABEL authors="nikolay"

ENTRYPOINT ["top", "-b"]

FROM php:8.1-apache


ADD ng-mysymfony.conf /etc/apache2/sites-available/

RUN echo "ServerName ng-mysymfony.com" >> /etc/apache2/apache2.conf

ENV APACHE_DOCUMENT_ROOT=/var/www/ng-mysymfony/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

RUN a2enmod rewrite
RUN service apache2 restart

RUN apt update \
    && apt install -y zlib1g-dev git libicu-dev zip libzip-dev zip libmagickwand-dev librabbitmq-dev \
    && docker-php-ext-install intl opcache pdo pdo_mysql \
    && pecl install apcu imagick amqp \
    && docker-php-ext-enable apcu imagick amqp \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip

WORKDIR /var/www/ng-mysymfony

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN curl -sS https://get.symfony.com/cli/installer | bash
RUN mv /root/.symfony5/bin/symfony /usr/local/bin/symfony
RUN git config --global user.email "nikolaizarudnrv@gmail.com" \
    && git config --global user.name "NikolaiZarudnev"
