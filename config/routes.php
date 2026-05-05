<?php
/**
 * Route definitions for EmoEat MVC
 * $router is injected from public/index.php
 */

// Home
$router->get('/', 'HomeController@index');

// Authentication
$router->get('/login', 'AuthController@loginForm');
$router->post('/login', 'AuthController@login');
$router->get('/register', 'AuthController@registerForm');
$router->post('/register', 'AuthController@register');
$router->get('/logout', 'AuthController@logout');
$router->get('/forgot-password', 'AuthController@forgotPasswordForm');
$router->post('/forgot-password', 'AuthController@forgotPassword');
$router->get('/reset-password', 'AuthController@resetPasswordForm');
$router->post('/reset-password', 'AuthController@resetPassword');

// User Dashboard
$router->get('/dashboard', 'DashboardController@index');

// Admin
$router->get('/admin/dashboard', 'AdminController@dashboard');
$router->get('/admin/users', 'AdminController@users');
$router->post('/admin/users', 'AdminController@usersPost');
$router->get('/admin/foods', 'AdminController@foods');
$router->post('/admin/foods', 'AdminController@foodsPost');
$router->get('/admin/emotions', 'AdminController@emotions');
$router->post('/admin/emotions', 'AdminController@emotionsPost');
$router->get('/admin/activity-log', 'AdminController@activityLog');

// Profile
$router->get('/profile', 'ProfileController@index');
$router->post('/profile', 'ProfileController@save');

// Recommendation
$router->get('/recommendation', 'RecommendationController@index');
$router->post('/recommendation', 'RecommendationController@getRecommendation');

// History
$router->get('/history', 'HistoryController@index');
