<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Ats::index', ['filter' => 'auth']);

// Authentication
$routes->get('login', 'Auth::index');
$routes->post('login', 'Auth::login');
$routes->get('logout', 'Auth::logout');

// ATS Application Routes
$routes->group('ats', ['filter' => 'auth'], static function ($routes) {
    $routes->get('/', 'Ats::index');
    $routes->get('getData', 'Ats::getData');
    $routes->get('getRecord/(:any)', 'Ats::getRecord/$1');
    $routes->post('store', 'Ats::store');
    $routes->post('delete/(:any)', 'Ats::deleteRecord/$1');
    $routes->get('analyticsData', 'Ats::analyticsData');
    $routes->get('getWilayah', 'Ats::getWilayah');
    $routes->get('export/excel', 'Ats::exportExcel');
    $routes->get('print/card/(:any)', 'Ats::printCard/$1');
    $routes->get('print/report', 'Ats::printReport');
    $routes->post('changePassword', 'Ats::changePassword');
    
    // User Management (Admin Only)
    $routes->get('getUsers', 'Ats::getUsers');
    $routes->post('saveUser', 'Ats::saveUser');
    $routes->post('deleteUser/(:num)', 'Ats::deleteUser/$1');
});
