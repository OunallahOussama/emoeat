# EmoEat - Development

Emotion-based food recommendation app built with PHP 8.2 (MVC architecture).

## Prerequisites

- PHP 8.2+
- Composer
- MySQL 8.0
- Apache with `mod_rewrite`

## Setup

```bash
# Install dependencies
composer install

# Copy environment config
cp .env.example .env
# Edit .env with your DB credentials

# Create database
mysql -u root -p < path/to/emoeat.sql

# Start local server (Apache or PHP built-in)
php -S localhost:8000 -t public/
```

## Project Structure

```
app/
├── Controllers/    # Request handlers
├── Core/           # Framework (App, Router, Controller, Model)
├── Models/         # Database models
└── Views/          # PHP templates
config/
├── Database.php    # PDO connection
└── routes.php      # Route definitions
public/             # Web root (index.php, .htaccess, style.css)
tests/              # PHPUnit tests
```

## Running Tests

```bash
composer install
./vendor/bin/phpunit
```

## Routes

| Method | URI | Controller |
|--------|-----|-----------|
| GET | `/` | HomeController@index |
| GET/POST | `/login` | AuthController |
| GET/POST | `/register` | AuthController |
| GET | `/logout` | AuthController |
| GET/POST | `/forgot-password` | AuthController |
| GET/POST | `/reset-password` | AuthController |
| GET | `/dashboard` | DashboardController |
| POST | `/dashboard` | DashboardController@store |
| GET | `/history` | HistoryController |
| GET | `/recommendation` | RecommendationController |
| GET | `/profile` | ProfileController |
| POST | `/profile` | ProfileController@update |
| GET | `/admin/*` | AdminController |

## Branching

- `main` — Production (includes Docker, deployment, docs)
- `develop` — Code only (this branch)
