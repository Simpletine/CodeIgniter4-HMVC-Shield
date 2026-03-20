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

namespace Simpletine\HMVCShield\Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Central path configuration for HMVC Shield.
 *
 * Users can override any of these values by publishing this file to
 * app/Config/HMVCPaths.php (using the same class name "HMVCPaths" under
 * the "Config" namespace). CI4's cascading config will prefer the app-level
 * copy automatically.
 *
 * Alternatively, call `php spark publish:config` which will copy this file
 * to app/Config/ for you.
 */
class Paths extends BaseConfig
{
    /**
     * Name of the modules directory, relative to APPPATH.
     * Change to e.g. "Domain" to store modules at app/Domain/.
     */
    public string $modulesDirectory = 'Modules';

    /**
     * Default views subdirectory name inside each module.
     */
    public string $moduleViewsDirectory = 'Views';

    /**
     * Public assets base directory, relative to FCPATH (webroot).
     * Assets are served from: FCPATH . $assetsDirectory
     */
    public string $assetsDirectory = 'assets/simpletine';

    /**
     * Override path for package views after publish:views is run.
     * When non-empty, stn_view() will resolve views from this path instead
     * of the package-bundled Views/ directory.
     *
     * Example: 'app/Views/simpletine/' (relative to ROOTPATH)
     * Leave empty to use the package's built-in views.
     */
    public string $viewsOverridePath = '';
}
