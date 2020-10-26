FROM php:7.3-apache
EXPOSE 80/tcp
COPY . /var/www/html/
WORKDIR /var/www/html/