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

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Publishes the SimpleTine configuration file to the application's Config directory.
 * Publishes: StnConfig.php, HMVCPaths.php, MigrationPaths.php
 */
class PublishConfig extends BaseCommand
{
    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'SimpleTine';

    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'publish:config';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Publishes SimpleTine config files (StnConfig, HMVCPaths, MigrationPaths) to app/Config/.';

    /**
     * @var string
     */
    protected $usage = 'publish:config [--force]';

    /**
     * @var array<string, string>
     */
    protected $arguments = [];

    /**
     * @var array<string, string>
     */
    protected $options = [
        '--force' => 'Overwrite existing config files without prompting',
    ];

    /**
     * Config files to publish: source (relative to package Config/) => destination class name.
     *
     * @var array<string, string>
     */
    private array $configFiles = [
        'StnConfig.php'      => 'StnConfig.php',
        'Paths.php'          => 'HMVCPaths.php',
        'MigrationPaths.php' => 'MigrationPaths.php',
    ];

    /**
     * Execute the command.
     *
     * @param list<string> $params
     */
    public function run(array $params): void
    {
        $force = CLI::getOption('force') !== null;

        CLI::write('Publishing config files to: ' . APPPATH . 'Config/', 'cyan');
        CLI::newLine();

        foreach ($this->configFiles as $sourceFile => $destFile) {
            $sourcePath = __DIR__ . '/../Config/' . $sourceFile;
            $destPath   = APPPATH . 'Config/' . $destFile;

            if (! file_exists($sourcePath)) {
                CLI::error("  Source not found: {$sourcePath}");

                continue;
            }

            if (file_exists($destPath) && ! $force) {
                $overwrite = CLI::prompt("  {$destFile} already exists. Overwrite?", ['y', 'n']);
                if (strtolower($overwrite) !== 'y') {
                    CLI::write("  Skipped: {$destFile}", 'yellow');

                    continue;
                }
            }

            if (copy($sourcePath, $destPath)) {
                CLI::write("  Published: {$destFile}", 'green');
            } else {
                CLI::error("  Failed: {$destFile}");
            }
        }

        CLI::newLine();
        CLI::write('Done. Customise any published config in app/Config/.', 'green');
    }

    /**
     * Recursively copies all files and directories from source to destination.
     */
    private function recursiveCopy(string $source, string $dest): void
    {
        $dir = opendir($source);
        @mkdir($dest);

        while (false !== ($file = readdir($dir))) {
            if (($file !== '.') && ($file !== '..')) {
                if (is_dir($source . '/' . $file)) {
                    $this->recursiveCopy($source . '/' . $file, $dest . '/' . $file);
                } else {
                    copy($source . '/' . $file, $dest . '/' . $file);
                }
            }
        }
        closedir($dir);
    }
}
