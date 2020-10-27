FROM php:7.4-apache
EXPOSE 80/tcp
COPY . /var/www/html/
RUN chmod -R 777 /var/www/html/*
WORKDIR /var/www/html/