#!/bin/sh
# Generate self-signed cert if Let's Encrypt cert doesn't exist
CERT_DIR="/etc/letsencrypt/live/emoeat.health"
if [ ! -f "$CERT_DIR/fullchain.pem" ]; then
    echo "No SSL cert found, generating self-signed certificate..."
    mkdir -p "$CERT_DIR"
    apk add --no-cache openssl > /dev/null 2>&1
    openssl req -x509 -nodes -days 365 \
        -newkey rsa:2048 \
        -keyout "$CERT_DIR/privkey.pem" \
        -out "$CERT_DIR/fullchain.pem" \
        -subj "/CN=emoeat.health" 2>/dev/null
    echo "Self-signed certificate generated."
fi

exec nginx -g 'daemon off;'
