<@php

$routes->group(
    '{groupName}', ['namespace' => '{namespace}'], function ($routes) {
        // List
        $routes->get('/',              'Index::index');
        // Create form + store
        $routes->get('create',         'Index::create');
        $routes->post('store',         'Index::store');
        // Edit form + update
        $routes->get('(:num)/edit',    'Index::edit/$1');
        $routes->post('(:num)/update', 'Index::update/$1');
        // Delete
        $routes->post('(:num)/delete', 'Index::delete/$1');
    }
);
