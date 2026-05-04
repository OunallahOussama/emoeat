#!/bin/bash
# ============================================
# EmoEat - EC2 Setup Script
# Run this AFTER SSH-ing into your EC2 instance
# Usage: chmod +x setup.sh && ./setup.sh
# ============================================

set -e

echo "========================================="
echo "  EmoEat - Server Setup"
echo "========================================="

# 1. Update system
echo "[1/5] Updating system..."
sudo apt update && sudo apt upgrade -y

# 2. Install Docker
echo "[2/5] Installing Docker..."
sudo apt install -y docker.io docker-compose-v2
sudo systemctl enable docker
sudo systemctl start docker
sudo usermod -aG docker $USER

# 3. Create project directory
echo "[3/5] Setting up project..."
mkdir -p ~/emoeat
cd ~/emoeat

# 4. Set permissions
echo "[4/5] Setting permissions..."
sudo chmod 666 /var/run/docker.sock

# 5. Launch app
echo "[5/5] Starting application..."
cd ~/emoeat
docker compose up -d --build

echo ""
echo "========================================="
echo "  DONE! Your app is running."
echo "========================================="
echo ""
echo "  App:        http://$(curl -s http://169.254.169.254/latest/meta-data/public-ipv4)"
echo "  phpMyAdmin: http://$(curl -s http://169.254.169.254/latest/meta-data/public-ipv4):8081"
echo ""
echo "  Default admin: admin@emoeat.com / password"
echo "========================================="
