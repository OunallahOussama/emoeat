#!/bin/bash
# ============================================
# EmoEat - EC2 Production Setup Script
# Run this AFTER SSH-ing into your EC2 instance
# Usage: chmod +x setup.sh && ./setup.sh
# ============================================

set -e

DOMAIN="emoeat.health"
EMAIL="admin@emoeat.health"
PROJECT_DIR="$HOME/emoeat"

echo "========================================="
echo "  EmoEat - Production Server Setup"
echo "========================================="

# 1. Update system
echo "[1/7] Updating system..."
sudo apt update && sudo apt upgrade -y

# 2. Install Docker
echo "[2/7] Installing Docker..."
sudo apt install -y docker.io docker-compose-v2
sudo systemctl enable docker
sudo systemctl start docker
sudo usermod -aG docker $USER
sudo chmod 666 /var/run/docker.sock

# 3. Create project directory
echo "[3/7] Setting up project..."
mkdir -p "$PROJECT_DIR"
cd "$PROJECT_DIR"

# 4. Check .env file
echo "[4/7] Checking .env configuration..."
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

# 5. Start services (HTTP only first for SSL cert)
echo "[5/7] Starting services (HTTP mode for SSL setup)..."
cd "$PROJECT_DIR"
docker compose -f docker-compose.prod.yml up -d --build

# 6. Get SSL certificate from Let's Encrypt
echo "[6/7] Obtaining SSL certificate for $DOMAIN..."
sleep 5  # Wait for nginx to start

docker compose -f docker-compose.prod.yml run --rm certbot certonly \
    --webroot \
    --webroot-path /var/www/certbot \
    -d "$DOMAIN" \
    -d "www.$DOMAIN" \
    --email "$EMAIL" \
    --agree-tos \
    --no-eff-email

# 7. Restart nginx with SSL
echo "[7/7] Restarting nginx with SSL..."
docker compose -f docker-compose.prod.yml restart nginx

# Setup auto-renewal cron
echo "0 3 * * * cd $PROJECT_DIR && docker compose -f docker-compose.prod.yml run --rm certbot renew && docker compose -f docker-compose.prod.yml restart nginx" | sudo crontab -

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
