FROM php:7.4-apache
EXPOSE 80/tcp
COPY . /var/www/html/
RUN chmod -R 777 /var/www/html/*
RUN apt update
RUN apt upgrade --yes
RUN apt install libldap2-dev --yes
RUN docker-php-ext-install opcache
RUN docker-php-ext-enable opcache.so
RUN docker-php-ext-install ldap
RUN docker-php-ext-enable ldap.so
WORKDIR /var/www/html/