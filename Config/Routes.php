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

service('auth')->routes($routes);

/**
 * Root path: redirect to login when unauthenticated, otherwise to StnConfig::$homeRedirect.
 */
$routes->get('/', '\Simpletine\HMVCShield\Controllers\HomeController::index');

/**
 * Profile: password-change form (GET) and update handler (POST).
 */
$routes->get('profile', '\Simpletine\HMVCShield\Controllers\ProfileController::index');
$routes->post('profile', '\Simpletine\HMVCShield\Controllers\ProfileController::update');

/**
 * --------------------------------------------------------------------
 * HMVC Routing
 * Module routes are auto-discovered from each module's Config/Routes.php.
 * The modules directory is read from Config/HMVCPaths.php (overridable by
 * publishing the file to app/Config/HMVCPaths.php).
 * --------------------------------------------------------------------
 */
$_hmvcModulesDir = config('HMVCPaths')->modulesDirectory ?? 'Modules';

foreach (glob(APPPATH . $_hmvcModulesDir . '/*', GLOB_ONLYDIR) as $item_dir) {
    if (file_exists($item_dir . '/Config/Routes.php')) {
        require_once $item_dir . '/Config/Routes.php';
    }
}

unset($_hmvcModulesDir);
