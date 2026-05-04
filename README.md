# EmoEat - Emotion-Based Food Recommendation

## Live URLs

| Service | URL |
|---------|-----|
| App (HTTPS) | https://emoeat.health |
| App (HTTP) | http://emoeat.health (redirects to HTTPS) |
| phpMyAdmin | http://emoeat.health:8081 |

## Credentials

### App Admin
- **Email:** admin@emoeat.com
- **Password:** password

### phpMyAdmin
- **Server:** db
- **Username:** root
- **Password:** root_password

Or:
- **Username:** emoeat_user
- **Password:** emoeat_pass

### MySQL (direct)
- **Host:** emoeat.health
- **Port:** 3306
- **Database:** emoeat
- **Username:** emoeat_user
- **Password:** emoeat_pass

## Docker Commands

```bash
# Start all services
docker-compose up -d --build

# Stop all services
docker-compose down

# View logs
docker-compose logs -f

# Rebuild PHP container only
docker-compose up -d --build php

# Renew SSL certificate
docker-compose run --rm certbot renew
docker-compose restart nginx
```

## Deployment (EC2)

```bash
cd ~/emoeat
git pull
docker-compose down
docker-compose up -d --build
```

## Stack

- **PHP 8.2** + Apache
- **MySQL 8.0**
- **Nginx** (reverse proxy + SSL)
- **Let's Encrypt** (SSL certificate)
- **phpMyAdmin** (database management)
- **Docker Compose** (orchestration)
