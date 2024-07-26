FROM nextstage/php:8.2-fpm-apache-dev

WORKDIR /var/www/html
COPY package*.json ./
COPY . .
COPY default.conf /etc/apache2/sites-available/000-default.conf
#RUN composer install
#RUN npm install
RUN chown www-data:www-data -R /var/www/html
