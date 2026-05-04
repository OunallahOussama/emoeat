FROM php:8.2-apache

# Install Oracle Instant Client and OCI8/PDO_OCI
RUN apt-get update && apt-get install -y \
    libaio1 \
    unzip \
    wget \
    && rm -rf /var/lib/apt/lists/*

# Download and install Oracle Instant Client
RUN mkdir -p /opt/oracle \
    && cd /opt/oracle \
    && wget -q https://download.oracle.com/otn_software/linux/instantclient/2340000/instantclient-basic-linux.x64-23.4.0.24.05.zip \
    && wget -q https://download.oracle.com/otn_software/linux/instantclient/2340000/instantclient-sdk-linux.x64-23.4.0.24.05.zip \
    && unzip instantclient-basic-linux.x64-23.4.0.24.05.zip \
    && unzip instantclient-sdk-linux.x64-23.4.0.24.05.zip \
    && rm -f *.zip \
    && echo /opt/oracle/instantclient_23_4 > /etc/ld.so.conf.d/oracle-instantclient.conf \
    && ldconfig

ENV LD_LIBRARY_PATH=/opt/oracle/instantclient_23_4
ENV ORACLE_HOME=/opt/oracle/instantclient_23_4

# Install PHP OCI8 and PDO_OCI extensions
RUN docker-php-ext-configure pdo_oci --with-pdo-oci=instantclient,/opt/oracle/instantclient_23_4 \
    && docker-php-ext-install pdo_oci \
    && echo "instantclient,/opt/oracle/instantclient_23_4" | pecl install oci8-3.3.0 \
    && docker-php-ext-enable oci8

RUN a2enmod rewrite

RUN echo "output_buffering = 4096" > /usr/local/etc/php/conf.d/buffering.ini

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
