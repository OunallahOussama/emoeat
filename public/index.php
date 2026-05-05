<?php
/**
 * Front Controller - public/index.php
 * All requests are routed through this file.
 */

require __DIR__ . '/../vendor/autoload.php';

$app = new App\Core\App();
$router = $app->getRouter();

require __DIR__ . '/../config/routes.php';

$app->run();
