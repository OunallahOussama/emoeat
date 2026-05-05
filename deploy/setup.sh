#!/bin/bash
# ============================================
# EmoEat - EC2 Production Setup Script
# Run this AFTER SSH-ing into your EC2 instance
# Supports Ubuntu (apt) and Amazon Linux (dnf/yum)
# Usage: chmod +x setup.sh && ./setup.sh
# ============================================

set -e

DOMAIN="emoeat.health"
EMAIL="admin@emoeat.health"
PROJECT_DIR="$HOME/emoeat"

echo "========================================="
echo "  EmoEat - Production Server Setup"
echo "========================================="

# 1. Update system & install Docker
echo "[1/6] Installing Docker..."
if command -v apt >/dev/null 2>&1; then
    sudo apt update && sudo apt upgrade -y
    sudo apt install -y docker.io docker-compose-v2 git
elif command -v dnf >/dev/null 2>&1; then
    sudo dnf update -y
    sudo dnf install -y docker git
    sudo dnf install -y docker-compose-plugin 2>/dev/null || sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose && sudo chmod +x /usr/local/bin/docker-compose
elif command -v yum >/dev/null 2>&1; then
    sudo yum update -y
    sudo yum install -y docker git
    sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose && sudo chmod +x /usr/local/bin/docker-compose
fi

sudo systemctl enable docker
sudo systemctl start docker
sudo usermod -aG docker $USER
sudo chmod 666 /var/run/docker.sock

# Detect compose command
if docker compose version >/dev/null 2>&1; then
    COMPOSE_CMD="docker compose"
elif command -v docker-compose >/dev/null 2>&1; then
    COMPOSE_CMD="docker-compose"
else
    echo "ERROR: neither 'docker compose' nor 'docker-compose' is available."
    exit 1
fi
echo "  Using: $COMPOSE_CMD"

# 2. Setup project directory
echo "[2/6] Setting up project..."
if [ -d "$PROJECT_DIR/.git" ]; then
    cd "$PROJECT_DIR"
    git pull origin main
else
    git clone https://github.com/OunallahOussama/emoeat.git "$PROJECT_DIR"
    cd "$PROJECT_DIR"
fi

# 3. Check .env file
echo "[3/6] Checking .env configuration..."
if [ ! -f "$PROJECT_DIR/.env" ]; then
    echo ""
    echo "ERROR: .env file not found!"
    echo "Create $PROJECT_DIR/.env with your SES credentials:"
    echo ""
    echo "  SMTP_HOST=email-smtp.us-east-1.amazonaws.com"
    echo "  SMTP_PORT=587"
    echo "  SMTP_FROM=no-reply@emoeat.health"
    echo "  SMTP_USER=YOUR_SES_SMTP_USERNAME"
    echo "  SMTP_PASSWORD=YOUR_SES_SMTP_PASSWORD"
    echo "  SMTP_TLS=on"
    echo "  DB_ROOT_PASSWORD=CHANGE_ME_STRONG_PASSWORD"
    echo "  DB_PASSWORD=CHANGE_ME_STRONG_PASSWORD"
    echo ""
    cp .env.example .env 2>/dev/null || true
    echo "Template copied to .env — edit it and re-run this script."
    exit 1
fi

# 4. Start services (HTTP only first for SSL cert)
echo "[4/6] Starting services (HTTP mode for SSL setup)..."
cd "$PROJECT_DIR"
$COMPOSE_CMD -f docker-compose.prod.yml up -d --build

# 5. Get SSL certificate from Let's Encrypt
echo "[5/6] Obtaining SSL certificate for $DOMAIN..."
sleep 5  # Wait for nginx to start

$COMPOSE_CMD -f docker-compose.prod.yml run --rm certbot certonly \
    --webroot \
    --webroot-path /var/www/certbot \
    -d "$DOMAIN" \
    -d "www.$DOMAIN" \
    --email "$EMAIL" \
    --agree-tos \
    --no-eff-email

# 6. Restart nginx with SSL
echo "[6/6] Restarting nginx with SSL..."
$COMPOSE_CMD -f docker-compose.prod.yml restart nginx

# Setup auto-renewal cron
echo "0 3 * * * cd $PROJECT_DIR && $COMPOSE_CMD -f docker-compose.prod.yml run --rm certbot renew && $COMPOSE_CMD -f docker-compose.prod.yml restart nginx" | sudo crontab -

PUBLIC_IP=$(curl -s http://169.254.169.254/latest/meta-data/public-ipv4 2>/dev/null || echo "YOUR_IP")

echo ""
echo "========================================="
echo "  DEPLOYMENT COMPLETE!"
echo "========================================="
echo ""
echo "  App:    https://$DOMAIN"
echo "  IP:     $PUBLIC_IP"
echo ""
echo "  Admin:  admin@emoeat.com / password"
echo "  Email:  Sending via AWS SES as noreply@$DOMAIN"
echo ""
echo "  SSL auto-renewal: cron every day at 3am"
echo "========================================="
