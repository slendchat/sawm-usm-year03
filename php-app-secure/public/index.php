<?php

// PDO initializtion
require __DIR__ . '/../config/config.php';

/**
 * Loader
 * @param  string  $class  Fully-qualified class name (including namespace).
 * @return void
 */
spl_autoload_register(function($class) {
    // namespace prefix
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../app/';

    // return if wrong class
    if (strpos($class, $prefix) !== 0) {
        return;
    }

    // убираем префикс, переводим несуществующие \ в /, добавляем .php
    $relativeClass = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Router and register application routes
use App\Core\Router;

$router = new Router();
$router->get('/', 'HomeController@index');

// Auth
$router->get('/login',       'AuthController@showLoginForm');
$router->post('/login',      'AuthController@login');
$router->get('/register',    'AuthController@showRegisterForm');
$router->post('/register',   'AuthController@register');
$router->get('/logout',      'AuthController@logout');

// Tickets
$router->get('/ticket/create',   'TicketController@createForm');
$router->post('/ticket/create',  'TicketController@create');

$router->get('/ticket/edit',     'TicketController@editForm');
$router->post('/ticket/edit',    'TicketController@edit');

$router->get('/ticket/delete',   'TicketController@delete');

$router->post('/ticket/status',  'TicketController@changeStatus');

$router->get('/tickets',         'TicketController@index');
$router->get('/ticket',          'TicketController@show');

// admin only
$router->get('/admin/users/create',  'AdminController@showCreateForm' );
$router->post('/admin/users/create',  'AdminController@create' );


// Dispatcher - calls the corresponding controller action or sends a 404.
$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
