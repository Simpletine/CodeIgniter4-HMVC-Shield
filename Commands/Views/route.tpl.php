<@php

$routes->group(
    '{groupName}', ['namespace' => '{namespace}'], function ($routes) {
        $routes->get('/', 'Index::index');
    }
);