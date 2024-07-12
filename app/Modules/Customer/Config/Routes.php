<?php

$routes->group(
    'customer', ['namespace' => 'App\Modules\Customer\Controllers'], function ($routes) {
        $routes->get('/', 'Index::index');
    }
);