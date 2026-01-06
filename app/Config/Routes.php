<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->group('api', function ($routes) {
    // API Auth User
    $routes->post('auth/sync', 'Auth::sync');

    // API Books
    $routes->get('books', 'Books::index');
    $routes->post('books', 'Books::create');
    $routes->get('books/(:segment)', 'Books::show/$1');
});