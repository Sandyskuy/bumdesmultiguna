<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
// File: app/Config/Routes.php

$routes->resource('barang', ['controller' => 'Barang']);
$routes->match(['post', 'options'], 'postbarang', 'Barang::create');
$routes->match(['put', 'options'], 'updatebarang/(:num)', 'Barang::update/$1');
$routes->match(['delete', 'options'], 'deletebarang/(:num)', 'Barang::delete/$1');

$routes->resource('kategori', ['controller' => 'Kategori']);
$routes->match(['post', 'options'], 'postkategori', 'Kategori::create');
$routes->match(['put', 'options'], 'updatekategori/(:num)', 'Kategori::update/$1');
$routes->match(['delete', 'options'], 'deletekategori/(:num)', 'Kategori::delete/$1');

$routes->resource('admin', ['controller' => 'Admin']);
$routes->match(['post', 'options'], 'postuser', 'Admin::create');
$routes->match(['put', 'options'], 'updateuser/(:num)', 'Admin::update/$1');
$routes->match(['delete', 'options'], 'deleteuser/(:num)', 'Admin::delete/$1');

$routes->resource('review', ['controller' => 'Review']);
$routes->match(['post', 'options'], 'postreview', 'Review::create');
$routes->match(['put', 'options'], 'updatereview/(:num)', 'Review::update/$1');
$routes->match(['delete', 'options'], 'deletereview/(:num)', 'Review::delete/$1');

$routes->resource('transaksi', ['controller' => 'Transaksi']);
$routes->match(['post', 'options'], 'posttransaksi', 'Transaksi::create');
$routes->match(['put', 'options'], 'updatetransaksi/(:num)', 'Transaksi::update/$1');
$routes->match(['delete', 'options'], 'deletetransaksi/(:num)', 'Transaksi::delete/$1');

$routes->resource('detail-transaksi', ['controller' => 'DetailTransaksi']);
$routes->match(['post', 'options'], 'postdetailtransaksi', 'DetailTransaksi::create');
$routes->match(['put', 'options'], 'updatedetailtransaksi/(:num)', 'DetailTransaksi::update/$1');
$routes->match(['delete', 'options'], 'deletedetailtransaksi/(:num)', 'DetailTransaksi::delete/$1');

