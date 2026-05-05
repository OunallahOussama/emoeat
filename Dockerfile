FROM php:8.2-apache

RUN docker-php-ext-install pdo pdo_mysql mysqli

RUN a2enmod rewrite

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN apt-get update && apt-get install -y msmtp msmtp-mta unzip && rm -rf /var/lib/apt/lists/*

RUN echo "output_buffering = 4096" > /usr/local/etc/php/conf.d/buffering.ini

# Configure PHP to use msmtp for mail
RUN echo 'sendmail_path = "/usr/bin/msmtp -t"' > /usr/local/etc/php/conf.d/mail.ini

# Set Apache DocumentRoot to public/
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!/var/www/html/public!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Entrypoint script to configure msmtp from env vars at runtime
RUN echo '#!/bin/bash\n\
set -e\n\
SMTP_HOST=${SMTP_HOST:-mailpit}\n\
SMTP_PORT=${SMTP_PORT:-1025}\n\
SMTP_FROM=${SMTP_FROM:-no-reply@emoeat.health}\n\
SMTP_USER=${SMTP_USER:-}\n\
SMTP_PASSWORD=${SMTP_PASSWORD:-}\n\
SMTP_TLS=${SMTP_TLS:-off}\n\
\n\
if [ -n "$SMTP_USER" ]; then\n\
  cat > /etc/msmtprc <<EOF\n\
account default\n\
host $SMTP_HOST\n\
port $SMTP_PORT\n\
from $SMTP_FROM\n\
auth on\n\
user $SMTP_USER\n\
password $SMTP_PASSWORD\n\
tls on\n\
tls_starttls on\n\
logfile /dev/stderr\n\
EOF\n\
else\n\
  cat > /etc/msmtprc <<EOF\n\
account default\n\
host $SMTP_HOST\n\
port $SMTP_PORT\n\
from $SMTP_FROM\n\
auth off\n\
tls off\n\
logfile /dev/stderr\n\
EOF\n\
fi\n\
chmod 644 /etc/msmtprc\n\
exec apache2-foreground' > /usr/local/bin/docker-entrypoint.sh && chmod +x /usr/local/bin/docker-entrypoint.sh

COPY . /var/www/html/

# Install Composer dependencies
RUN cd /var/www/html && composer install --no-dev --optimize-autoloader --no-interaction

RUN chown -R www-data:www-data /var/www/html

EXPOSE 80

ENTRYPOINT ["docker-entrypoint.sh"]
