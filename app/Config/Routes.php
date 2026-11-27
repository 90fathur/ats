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
    $routes->get('getDesaByKecamatan', 'Ats::getDesaByKecamatan');
    $routes->get('export/excel', 'Ats::exportExcel');
    $routes->get('export/matriks', 'Ats::exportMatriks');
    $routes->get('export/kinerja-excel', 'Ats::exportKinerjaExcel');
    $routes->get('print/card/(:any)', 'Ats::printCard/$1');
    $routes->get('print/report', 'Ats::printReport');
    $routes->get('print/matriks', 'Ats::printMatriks');
    $routes->get('print/akun', 'Ats::printAkun');
    $routes->get('printAkun', 'Ats::printAkun');
    $routes->get('print/kinerja-petugas', 'Ats::printKinerjaPetugas');
    $routes->get('getMatriksData', 'Ats::getMatriksData');
    $routes->post('changePassword', 'Ats::changePassword');
    $routes->post('updateProfile', 'Ats::updateProfile');
    
    // User Management & Tabulasi Kinerja Petugas
    $routes->get('getUsers', 'Ats::getUsers');
    $routes->get('getTabulasiPetugas', 'Ats::getTabulasiPetugas');
    $routes->post('saveUser', 'Ats::saveUser');
    $routes->post('deleteUser/(:num)', 'Ats::deleteUser/$1');
    $routes->get('getMasterTarget', 'Ats::getMasterTarget');
    $routes->post('saveMasterTarget', 'Ats::saveMasterTarget');
    $routes->post('resetMasterTarget', 'Ats::resetMasterTarget');

    // Activity Logs & History
    $routes->get('getLogs', 'Ats::getLogs');
});
