<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Login::index');
$routes->get('/admin', 'Admin::index', ['filter' => 'role:superadmin']);
$routes->get('/admin/index', 'Admin::index', ['filter' => 'role:superadmin']);

$routes->resource('barang', ['controller' => 'Barang']);
$routes->match(['post', 'options'], 'postbarang', 'Barang::create');
$routes->match(['put', 'options'], 'updatebarang/(:num)', 'Barang::update/$1');
$routes->match(['delete', 'options'], 'deletebarang/(:num)', 'Barang::delete/$1');

$routes->resource('kategori', ['controller' => 'Kategori']);
$routes->match(['post', 'options'], 'postkategori', 'Kategori::create');
$routes->match(['put', 'options'], 'updatekategori/(:num)', 'Kategori::update/$1');
$routes->match(['delete', 'options'], 'deletekategori/(:num)', 'Kategori::delete/$1');


