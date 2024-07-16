<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) 2021 CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

$routes->group(
    'customer',
    ['namespace' => 'App\Modules\Customer\Controllers'],
    static function ($routes) {
        $routes->get('/', 'Index::index');
    }
);
