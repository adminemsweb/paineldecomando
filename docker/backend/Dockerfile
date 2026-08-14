FROM php:8.3-apache
RUN apt-get update && apt-get install -y --no-install-recommends libcurl4-openssl-dev \
    && docker-php-ext-install pdo_mysql curl \
    && rm -rf /var/lib/apt/lists/* \
    && a2enmod rewrite headers
COPY apache.conf /etc/apache2/sites-available/000-default.conf
WORKDIR /var/www/html
