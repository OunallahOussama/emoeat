# EmoEat

Emotion-based food recommendation application built with a custom **MVC framework** in PHP 8.2.

## Architecture Overview

EmoEat follows the **Model-View-Controller** pattern with a front controller design:

```
Browser Request
      │
      ▼
public/index.php  (Front Controller)
      │
      ▼
App\Core\App      (Bootstrap: session, URI parsing)
      │
      ▼
App\Core\Router   (Match URI → Controller@method)
      │
      ▼
Controller        (Handle logic, call Models, render View)
   │       │
   ▼       ▼
 Model    View
(DB ops)  (HTML response)
```

### Request Lifecycle

1. All requests hit `public/index.php` via Apache rewrite rules (`.htaccess`)
2. `App\Core\App` starts the session, parses the request URI and method
3. `App\Core\Router` matches the URI against routes defined in `config/routes.php`
4. The matched controller method is invoked with any route parameters
5. Controllers extend `App\Core\Controller` which provides `view()`, `redirect()`, and auth helpers
6. Models extend `App\Core\Model` and interact with MySQL via PDO
7. Views are plain PHP templates using a shared layout (`layouts/main.php`)

## Prerequisites

- PHP 8.2+
- Composer
- MySQL 8.0
- Apache with `mod_rewrite`

## Setup

```bash
# Install dependencies
composer install

# Configure database connection in config/Database.php
# (uses env vars: DB_HOST, DB_NAME, DB_USER, DB_PASSWORD)

# Create database and tables
mysql -u root -p < path/to/emoeat.sql

# Start local server
php -S localhost:8000 -t public/
```

## Project Structure

```
app/
├── Core/
│   ├── App.php             # Bootstrap & dispatch
│   ├── Router.php          # Route registration & matching
│   ├── Controller.php      # Base controller (view, redirect, auth)
│   └── Model.php           # Base model (PDO wrapper)
├── Controllers/
│   ├── HomeController.php
│   ├── AuthController.php          # Login, register, password reset
│   ├── DashboardController.php     # Emotion logging
│   ├── RecommendationController.php
│   ├── HistoryController.php
│   ├── ProfileController.php
│   └── AdminController.php         # User/food/emotion CRUD
├── Models/
│   ├── User.php
│   ├── Food.php
│   ├── Emotion.php
│   ├── Recommendation.php
│   ├── UserEmotion.php
│   ├── UserProfile.php
│   ├── ActivityLog.php
│   └── PasswordResetToken.php
└── Views/
    ├── layouts/main.php            # Base HTML layout
    ├── partials/                    # navbar, footer
    ├── home/                        # Landing page
    ├── auth/                        # Login, register, reset
    ├── dashboard/                   # Main user dashboard
    ├── recommendation/              # Food suggestions
    ├── history/                     # Emotion history
    ├── profile/                     # User profile
    └── admin/                       # Admin panel views

config/
├── Database.php    # PDO singleton (no namespace, global)
└── routes.php      # All GET/POST route definitions

public/
├── index.php       # Front controller entry point
├── .htaccess       # Apache URL rewriting
└── style.css       # Application styles

tests/              # PHPUnit test suites (Models, Controllers, Core)
```

## Key Design Decisions

| Decision | Rationale |
|----------|-----------|
| Custom framework (no Laravel/Symfony) | Lightweight, educational, zero bloat |
| Front controller pattern | Single entry point, clean URLs, centralized auth |
| PSR-4 autoloading | `App\\` → `app/`, `Tests\\` → `tests/` |
| Session-based auth | Simple, stateful, role-based (CLIENT/ADMIN) |
| Shared layout with partials | DRY templates, consistent UI |
| Route params `{id}` | Dynamic URLs without query strings |

## Running Tests

```bash
./vendor/bin/phpunit
```

Three test suites: **Models**, **Controllers**, **Core** (Router).

## Routes

| Method | URI | Controller@Method |
|--------|-----|-------------------|
| GET | `/` | HomeController@index |
| GET | `/login` | AuthController@loginForm |
| POST | `/login` | AuthController@login |
| GET | `/register` | AuthController@registerForm |
| POST | `/register` | AuthController@register |
| GET | `/logout` | AuthController@logout |
| GET | `/forgot-password` | AuthController@forgotPasswordForm |
| POST | `/forgot-password` | AuthController@forgotPassword |
| GET | `/reset-password` | AuthController@resetPasswordForm |
| POST | `/reset-password` | AuthController@resetPassword |
| GET | `/dashboard` | DashboardController@index |
| POST | `/dashboard` | DashboardController@store |
| GET | `/history` | HistoryController@index |
| GET | `/recommendation` | RecommendationController@index |
| GET | `/profile` | ProfileController@index |
| POST | `/profile` | ProfileController@update |
| GET | `/admin/dashboard` | AdminController@dashboard |
| GET | `/admin/users` | AdminController@users |
| GET | `/admin/foods` | AdminController@foods |
| GET | `/admin/emotions` | AdminController@emotions |
| GET | `/admin/activity-log` | AdminController@activityLog |

## Branching

- `main` — Production (includes Docker, nginx, deployment scripts, docs)
- `develop` — Application source code only (this branch)
