<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->group('api', function ($routes) {
    // API Auth User
    $routes->post('auth/sync', 'Auth::sync');
    $routes->get('auth/(:segment)', 'Auth::show/$1');

    // API Books
    $routes->get('books', 'Books::index');
    $routes->post('books', 'Books::create');
    $routes->get('books/(:segment)', 'Books::show/$1');

    // API Reading Progress
    $routes->post('reading-progress/update', 'ReadingProgress::updateProgress');

    // API Loans
    $routes->get('loans', 'Loans::index');
    $routes->post('loans', 'Loans::create');
    $routes->post('loans/return', 'Loans::returnBook');
    $routes->put('loans/(:segment)', 'Loans::update/$1');

    // API Categories
    $routes->get('categories', 'Categories::index');

    // API Borrowers
    $routes->get('borrowers/search', 'Borrowers::searchBorrowers');
});
