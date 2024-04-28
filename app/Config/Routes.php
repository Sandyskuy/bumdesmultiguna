<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
// File: app/Config/Routes.php


$routes->group('api', ['namespace' => 'App\Controllers'], function ($routes) {
    $routes->match(['post', 'options'], 'register', 'AuthController::register');
    $routes->match(['post', 'options'], 'login', 'AuthController::login');
    $routes->post('logout', 'AuthController::logout');
});

$routes->group('barang', ['namespace' => 'App\Controllers'], function ($routes) {
    $routes->get('/', 'Barang::index');
    $routes->match(['get', 'options'], 'detail/(:num)', 'Barang::detail/$1');
    $routes->match(['post', 'options'], 'postbarang', 'Barang::create');
    $routes->match(['put', 'options'], 'updatebarang/(:num)', 'Barang::update/$1');
    $routes->match(['delete', 'options'], 'deletebarang/(:num)', 'Barang::delete/$1');
});


$routes->group('kategori', ['namespace' => 'App\Controllers'], function ($routes) {
    $routes->get('/', 'Kategori::index');
    $routes->match(['post', 'options'], 'postkategori', 'Kategori::create');
    $routes->match(['put', 'options'], 'updatekategori/(:num)', 'Kategori::update/$1');
    $routes->match(['delete', 'options'], 'deletekategori/(:num)', 'Kategori::delete/$1');
});

$routes->group('admin', ['namespace' => 'App\Controllers'], function ($routes) {
    $routes->get('/', 'Admin::index'); // Menampilkan daftar admin
    $routes->get('/(:num)', 'Admin::show:$1'); // Menampilkan daftar admin
    $routes->match(['post', 'options'], 'postuser', 'Admin::create'); // Membuat admin baru
    $routes->match(['put', 'options'], 'updateuser/(:num)', 'Admin::update/$1'); // Memperbarui admin
    $routes->match(['delete', 'options'], 'deleteuser/(:num)', 'Admin::delete/$1'); // Menghapus admin
    $routes->get('create-super-admin', 'Admin::createSuperAdmin');
});

$routes->group('users', ['namespace' => 'App\Controllers'], function ($routes) {
    $routes->get('/', 'Users::index'); // Menampilkan daftar pengguna dengan peran 'buyer'
    $routes->get('(:num)', 'Users::show/$1'); // Menampilkan detail pengguna berdasarkan ID
});

$routes->resource('review', ['controller' => 'Review']);
$routes->match(['post', 'options'], 'postreview', 'Review::create');

$routes->group('transaksi', ['namespace' => 'App\Controllers\Transaksi'], function ($routes) {
    $routes->get('/', 'Transaksi::index');
    $routes->match(['post', 'options'], 'checkout', 'Transaksi::checkout');
    $routes->match(['post', 'options'], 'Cart', 'Transaksi::addToCart');
    $routes->delete('Cart/(:num)', 'Transaksi::removeFromCart/$1');
    $routes->get('Cart', 'Transaksi::viewCart');
});


$routes->group('detail-transaksi', ['namespace' => 'App\Controllers\Transaksi'], function ($routes) {
    $routes->get('/', 'DetailTransaksi::index');
    $routes->get('show/(:num)', 'DetailTransaksi::show/$1');
    $routes->get('by-transaksi/(:num)', 'DetailTransaksi::getDetailTransaksiByTransaksi/$1');
});