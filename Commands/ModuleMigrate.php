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

namespace Simpletine\HMVCShield\Commands;

use CodeIgniter\CLI\CLI;
use Throwable;

/**
 * Runs (or rolls back) migrations scoped to a single module.
 *
 * Usage:
 *   php spark module:migrate <module_name>               Run all pending migrations
 *   php spark module:migrate <module_name> --rollback    Roll back the last batch
 *   php spark module:migrate <module_name> --fresh       Drop all tables and re-run
 *   php spark module:migrate <module_name> --status      Show migration status
 */
class ModuleMigrate extends BaseModuleCommand
{
    /**
     * @var string
     */
    protected $group = 'SimpleTine';

    /**
     * @var string
     */
    protected $name = 'module:migrate';

    /**
     * @var string
     */
    protected $description = 'Runs migrations for a specific module (or all modules when no name given).';

    /**
     * @var string
     */
    protected $usage = 'module:migrate [module_name] [options]';

    /**
     * @var array<string, string>
     */
    protected $arguments = [
        'module_name' => 'The module name to migrate. Omit to migrate every module.',
    ];

    /**
     * @var array<string, string>
     */
    protected $options = [
        '--rollback' => 'Roll back the last migration batch for the module',
        '--fresh'    => 'Drop all tables defined by the module and re-run migrations',
        '--status'   => 'Show the migration status for the module',
        '--all'      => 'Operate on every discovered module (default when no name given)',
    ];

    /**
     * Execute the command.
     *
     * @param list<string> $params
     */
    public function run(array $params): void
    {
        $moduleName = array_shift($params);

        $modulesDir    = $this->getModulesDirectory();
        $namespaceBase = $this->getNamespaceBase();

        if ($moduleName) {
            $this->migrateModule($moduleName, $modulesDir, $namespaceBase);
        } else {
            // Migrate every discovered module
            $baseDir = APPPATH . $modulesDir;

            if (! is_dir($baseDir)) {
                CLI::error("Modules directory not found: {$baseDir}");

                return;
            }

            $dirs = glob($baseDir . '/*', GLOB_ONLYDIR);

            if (empty($dirs)) {
                CLI::write('No modules found.', 'yellow');

                return;
            }

            foreach ($dirs as $dir) {
                $this->migrateModule(basename($dir), $modulesDir, $namespaceBase);
            }
        }
    }

    /**
     * Runs the appropriate migrate sub-command for one module.
     */
    private function migrateModule(string $moduleName, string $modulesDir, string $namespaceBase): void
    {
        $migrationDir = APPPATH . $modulesDir . DIRECTORY_SEPARATOR . $moduleName
            . DIRECTORY_SEPARATOR . 'Database' . DIRECTORY_SEPARATOR . 'Migrations';

        if (! is_dir($migrationDir)) {
            CLI::write("No migrations directory for module \"{$moduleName}\" — skipping.", 'yellow');

            return;
        }

        // Derive the module namespace (e.g. App\Modules\Blog)
        $namespace = $namespaceBase . '\\' . $moduleName;

        CLI::write("Migrating module: {$moduleName}  [{$namespace}]", 'cyan');

        try {
            if (CLI::getOption('rollback') !== null) {
                $this->call('migrate:rollback', ['--namespace' => $namespace]);
            } elseif (CLI::getOption('fresh') !== null) {
                $this->call('migrate:refresh', ['--namespace' => $namespace]);
            } elseif (CLI::getOption('status') !== null) {
                $this->call('migrate:status', ['--namespace' => $namespace]);
            } else {
                $this->call('migrate', ['--namespace' => $namespace]);
            }

            CLI::write("Module \"{$moduleName}\" migrated successfully.", 'green');
        } catch (Throwable $e) {
            CLI::error("Migration failed for \"{$moduleName}\": {$e->getMessage()}");
        }

        CLI::newLine();
    }
}
