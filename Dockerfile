# Usamos la imagen oficial de PHP con Apache
FROM php:8.1-apache

# Habilitamos los módulos necesarios para que PHP hable con MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copiamos todo tu código (frontend y backend) a la carpeta pública del servidor
COPY . /var/www/html/

# Le damos permisos al servidor para leer los archivos
RUN chown -R www-data:www-data /var/www/html/ \
    && chmod -R 755 /var/www/html/

# Exponemos el puerto 80 para la web
EXPOSE 80