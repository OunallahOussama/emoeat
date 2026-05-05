FROM php:8.2-apache

RUN docker-php-ext-install pdo pdo_mysql mysqli

RUN a2enmod rewrite

RUN apt-get update && apt-get install -y msmtp msmtp-mta && rm -rf /var/lib/apt/lists/*

RUN echo "output_buffering = 4096" > /usr/local/etc/php/conf.d/buffering.ini

# Configure PHP to use msmtp for mail
RUN echo 'sendmail_path = "/usr/bin/msmtp -t"' > /usr/local/etc/php/conf.d/mail.ini

# Entrypoint script to configure msmtp from env vars at runtime
RUN echo '#!/bin/bash\n\
set -e\n\
SMTP_HOST=${SMTP_HOST:-mailpit}\n\
SMTP_PORT=${SMTP_PORT:-1025}\n\
SMTP_FROM=${SMTP_FROM:-noreply@emoeat.health}\n\
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

RUN chown -R www-data:www-data /var/www/html

EXPOSE 80

ENTRYPOINT ["docker-entrypoint.sh"]
