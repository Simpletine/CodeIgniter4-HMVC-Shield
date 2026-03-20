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
 * Dynamically registers Database/Migrations paths for every discovered module
 * so that `php spark migrate` (and all related migrate commands) processes
 * module migrations without any extra flags.
 *
 * How it works:
 *   CI4 reads $psr4 from this class when resolving namespaces for migrations.
 *   We glob APPPATH/Modules and register each module namespace automatically.
 *
 * Published to app/Config/MigrationPaths.php via `publish:config`.
 * After publishing, users can add extra namespaces or remove auto-discovery.
 */
class MigrationPaths extends BaseConfig
{
    /**
     * PSR-4 namespace → filesystem path mappings for migrations.
     * Auto-populated at construction time by scanning the modules directory.
     *
     * @var array<string, string>
     */
    public array $psr4 = [];

    /**
     * Absolute filesystem paths to scan for migration files (non-namespaced mode).
     * Populated alongside $psr4.
     *
     * @var list<string>
     */
    public array $paths = [];

    public function __construct()
    {
        parent::__construct();

        /** @var HMVCPaths $pathsConfig */
        $pathsConfig = config('HMVCPaths');
        $modulesDir  = $pathsConfig->modulesDirectory ?? 'Modules';

        foreach (glob(APPPATH . $modulesDir . '/*', GLOB_ONLYDIR) as $moduleDir) {
            $migrationDir = $moduleDir . DIRECTORY_SEPARATOR . 'Database' . DIRECTORY_SEPARATOR . 'Migrations';

            if (is_dir($migrationDir)) {
                // Derive namespace from directory name: App\Modules\<Name>
                $moduleName             = basename($moduleDir);
                $namespace              = 'App\\' . $modulesDir . '\\' . $moduleName;
                $this->psr4[$namespace] = $migrationDir;
                $this->paths[]          = $migrationDir;
            }
        }
    }
}
