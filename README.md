# EmoEat - Emotion-Based Food Recommendation

A web application that recommends foods based on your emotional state. Built with PHP, MySQL, and Docker — deployed on AWS EC2 with SSL and email support.

## Architecture

```
User → DNS (Namecheap) → EC2 (44.212.102.37)
         ↓
   Nginx (SSL/Reverse Proxy)
         ↓
   PHP 8.2 + Apache (App)
         ↓
   MySQL 8.0 (Database)
         ↓
   Mailpit → AWS SES (Email Relay)
```

## Live URLs

| Service | URL | Access |
|---------|-----|--------|
| App (HTTPS) | https://emoeat.health | Public |
| App (HTTP) | http://emoeat.health | Redirects to HTTPS |
| phpMyAdmin | http://emoeat.health:8081 | Public |
| Email UI | http://emoeat.health:8025 | Auth required (MAILPIT_USER/MAILPIT_PASSWORD) |

## Features

- **Emotion-based food recommendations** with emoji selection
- **User authentication** with roles (Admin/Client)
- **Password reset** via email (token-based, 1hr expiry)
- **Welcome email** on registration
- **Admin panel** — manage users, emotions, foods, activity logs
- **User profile** and recommendation history
- **108 unit tests** (PHPUnit)

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Frontend | PHP 8.2 + Bootstrap |
| Backend | PHP + Apache |
| Database | MySQL 8.0 |
| Proxy | Nginx (SSL termination) |
| SSL | Let's Encrypt (Certbot) |
| Email | AWS SES + Mailpit (relay + UI) |
| Containers | Docker Compose |
| Hosting | AWS EC2 (t3.small) |
| DNS | Namecheap |
| Testing | PHPUnit 10.5 |

## Credentials

### App Admin
- **Email:** admin@emoeat.com
- **Password:** password

### phpMyAdmin
- **Username:** root / **Password:** root_password
- **Username:** emoeat_user / **Password:** emoeat_pass

## Local Development

```bash
# Start all services (with Mailpit catching emails locally)
docker compose up -d --build

# View emails at http://localhost:8025
# App at http://localhost

# Run tests
vendor/bin/phpunit
```

## Production Deployment (EC2)

### First-time setup

```bash
git clone https://github.com/OunallahOussama/emoeat.git
cd emoeat
cp .env.example .env
nano .env  # Fill in SES credentials + DB passwords
chmod +x deploy/setup.sh
./deploy/setup.sh
```

### Update deployment

```bash
cd ~/emoeat
git pull origin main
docker-compose -f docker-compose.prod.yml up -d --build
```

### SSL certificate

```bash
# Initial cert
docker-compose -f docker-compose.prod.yml run --rm certbot certonly \
    --webroot --webroot-path /var/www/certbot \
    -d emoeat.health -d www.emoeat.health \
    --email admin@emoeat.health --agree-tos --no-eff-email

# Renewal (automated via cron)
docker-compose -f docker-compose.prod.yml run --rm certbot renew
docker-compose -f docker-compose.prod.yml restart nginx
```

### Access Email UI

Open **http://emoeat.health:8025** in your browser.
- Login: `MAILPIT_USER` / `MAILPIT_PASSWORD` from `.env`

## Email Configuration

Emails are sent via **AWS SES** with **Mailpit** as a relay:

```
PHP → msmtp → Mailpit (UI + logs) → AWS SES → Recipient inbox
```

### DNS Records (Namecheap)

| Type | Host | Value |
|------|------|-------|
| A | @ | 44.212.102.37 |
| A | www | 44.212.102.37 |
| CNAME | `s2hcxcovilotnuul47hqjkla6yqle4so._domainkey` | `s2hcxcovilotnuul47hqjkla6yqle4so.dkim.amazonses.com` |
| CNAME | `j7exvlvnnry7wx2ir3madbysqfcdvlty._domainkey` | `j7exvlvnnry7wx2ir3madbysqfcdvlty.dkim.amazonses.com` |
| CNAME | `ltv2mxng7yymysxirrmvthlnmsmzdm55._domainkey` | `ltv2mxng7yymysxirrmvthlnmsmzdm55.dkim.amazonses.com` |
| TXT | @ | `v=spf1 include:amazonses.com ~all` |
| TXT | _dmarc | `v=DMARC1; p=quarantine; rua=mailto:no-reply@emoeat.health` |

## Environment Variables (.env)

```env
SMTP_HOST=email-smtp.us-east-1.amazonaws.com
SMTP_PORT=587
SMTP_FROM=no-reply@emoeat.health
SMTP_USER=AKIA...
SMTP_PASSWORD=...
SMTP_TLS=on
DB_ROOT_PASSWORD=...
DB_NAME=emoeat
DB_USER=emoeat_user
DB_PASSWORD=...
MAILPIT_USER=admin
MAILPIT_PASSWORD=...
```

## Project Structure

```
├── config/Database.php       # PDO connection manager
├── connexion.php             # DB init + logActivity() helper
├── docker-compose.yml        # Local development
├── docker-compose.prod.yml   # Production (EC2 + SES)
├── Dockerfile                # PHP 8.2 + msmtp + dynamic SMTP
├── nginx.conf                # HTTP-only nginx config
├── nginx-ssl.conf            # HTTPS nginx config
├── docker/init.sql           # Database schema + seed data
├── deploy/
│   ├── deploy-aws.ps1        # PowerShell EC2 provisioning
│   └── setup.sh              # EC2 setup (Docker, SSL, cron)
├── tests/                    # 108 PHPUnit tests
├── .env.example              # Environment template
└── *.php                     # Application pages
```

## Testing

```bash
# Install dependencies
composer install

# Run all 108 tests
vendor/bin/phpunit

# Run specific test
vendor/bin/phpunit tests/LoginTest.php
```
