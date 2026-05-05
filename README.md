# EmoEat - Emotion-Based Food Recommendation

A web application that recommends foods based on your emotional state. Built with PHP (MVC architecture), MySQL, and Docker — deployed on AWS EC2 with SSL and email support.

## Architecture

```
User → DNS (Namecheap) → EC2 (44.212.102.37)
         ↓
   Nginx (SSL/Reverse Proxy)
         ↓
   PHP 8.2 + Apache (MVC App)
         ↓
   MySQL 8.0 (Database)
         ↓
   Mailpit → AWS SES (Email Relay)
```

### MVC Structure

```
Request → public/index.php (Front Controller)
              ↓
         App\Core\Router (URL matching)
              ↓
         App\Controllers\* (Business logic)
              ↓
         App\Models\* (Database via PDO)
              ↓
         App\Views\* (HTML templates)
```

## Live URLs

| Service | URL | Access |
|---------|-----|--------|
| App (HTTPS) | https://emoeat.health | Public |
| App (HTTP) | http://emoeat.health | Redirects to HTTPS |
| phpMyAdmin | https://emoeat.health/phpmyadmin/ | SSL secured |
| Email UI | https://emoeat.health/mailpit/ | Auth required (MAILPIT_USER/MAILPIT_PASSWORD) |

## Features

- **MVC architecture** with custom router, controllers, and models
- **Emotion-based food recommendations** with emoji selection and profile-based filtering
- **User authentication** with roles (Admin/Client) and session management
- **Password reset** via email (token-based, 1hr expiry)
- **Welcome email** on registration
- **Admin panel** — manage users, emotions, foods, rules, activity logs
- **User profile** with BMI calculator and dietary preferences
- **Recommendation history** tracking
- **CSRF protection** on forms
- **66 unit tests** (PHPUnit 10.5)

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Architecture | Custom MVC (PSR-4 autoloading) |
| Frontend | PHP 8.2 + Bootstrap |
| Backend | PHP + Apache (mod_rewrite) |
| Database | MySQL 8.0 (PDO) |
| Proxy | Nginx (SSL termination) |
| SSL | Let's Encrypt (Certbot) |
| Email | AWS SES + Mailpit (relay + UI) |
| Containers | Docker Compose |
| Hosting | AWS EC2 (t3.small) |
| DNS | Namecheap |
| Testing | PHPUnit 10.5 (66 tests) |

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

Open **https://emoeat.health/mailpit/** in your browser.
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
├── public/
│   ├── index.php             # Front controller (entry point)
│   ├── .htaccess             # Apache URL rewriting
│   ├── style.css             # Global styles
│   └── images/               # Food images
├── app/
│   ├── Core/
│   │   ├── App.php           # Bootstrap (session, URI, dispatch)
│   │   ├── Router.php        # Route matching with {param} support
│   │   ├── Controller.php    # Base controller (auth, views, redirect)
│   │   └── Model.php         # Base model (PDO injection)
│   ├── Controllers/
│   │   ├── HomeController.php
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   ├── AdminController.php
│   │   ├── ProfileController.php
│   │   ├── RecommendationController.php
│   │   └── HistoryController.php
│   ├── Models/
│   │   ├── User.php, Food.php, Emotion.php
│   │   ├── Recommendation.php, UserProfile.php
│   │   ├── UserEmotion.php, ActivityLog.php
│   │   └── PasswordResetToken.php
│   └── Views/
│       ├── layouts/main.php
│       ├── partials/navbar.php, footer.php
│       ├── home/, auth/, dashboard/
│       ├── admin/, profile/
│       ├── recommendation/, history/
│       └── ...
├── config/
│   ├── Database.php          # PDO connection (env-based)
│   └── routes.php            # All route definitions
├── tests/
│   ├── Core/RouterTest.php
│   ├── Models/               # 7 model tests
│   └── Controllers/          # 2 controller tests
├── docker-compose.yml        # Local development
├── docker-compose.prod.yml   # Production (EC2 + SES)
├── Dockerfile                # PHP 8.2 + Composer + msmtp
├── nginx.conf / nginx-ssl.conf
├── docker/init.sql           # Database schema + seed data
├── deploy/
│   ├── deploy-aws.ps1        # PowerShell EC2 provisioning
│   └── setup.sh              # EC2 setup (Docker, SSL, cron)
└── .env.example              # Environment template
```

## Testing

```bash
# Install dependencies
composer install

# Run all 66 tests
vendor/bin/phpunit

# Run specific test suite
vendor/bin/phpunit --testsuite Models
vendor/bin/phpunit --testsuite Controllers
vendor/bin/phpunit --testsuite Core

# Run inside Docker
docker exec emoeat-php vendor/bin/phpunit
```

### Test Suites

| Suite | Tests | Coverage |
|-------|-------|----------|
| Models | 52 | User, Food, Emotion, Recommendation, UserProfile, ActivityLog, PasswordResetToken |
| Controllers | 12 | RecommendationController, HistoryController (static helpers) |
| Core | 5 | Router (registration, matching, params, 404) |
