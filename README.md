# EmoEat - Emotion-Based Food Recommendation

## Live URLs

| Service | URL |
|---------|-----|
| App (HTTPS) | https://emoeat.health |
| App (HTTP) | http://emoeat.health (redirects to HTTPS) |

## Credentials

### App Admin
- **Email:** admin@emoeat.com
- **Password:** password

### Oracle Database
- **Host:** emoeat.health
- **Port:** 1521
- **Service:** XEPDB1
- **Username:** emoeat_user
- **Password:** emoeat_pass
- **SYS Password:** oracle_root

### SQL*Plus connection (from container)
```bash
docker exec -it emoeat-oracle sqlplus emoeat_user/emoeat_pass@//localhost:1521/XEPDB1
```

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

- **PHP 8.2** + Apache + OCI8/PDO_OCI
- **Oracle XE 21c** (gvenzl/oracle-xe:21-slim)
- **Nginx** (reverse proxy + SSL)
- **Let's Encrypt** (SSL certificate)
- **Docker Compose** (orchestration)
