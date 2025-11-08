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
 * Updates Auth.php configuration to use AdminLTE views instead of default Shield views.
 * Replaces view paths in the authentication configuration for a consistent admin interface.
 */
class PublishViews extends BaseCommand
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
    protected $name = 'publish:views';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Updates Auth.php configuration to use AdminLTE views for login and registration.';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'publish:views [config_file]';

    /**
     * The Command's Arguments
     *
     * @var array<string, string>
     */
    protected $arguments = [
        'config_file' => 'Optional: Path to Auth.php config file (default: Config/Auth.php)',
    ];

    /**
     * The Command's Options
     *
     * @var array<string, string>
     */
    protected $options = [
        '--backup' => 'Create a backup of the original config file before modification',
    ];

    /**
     * Execute the command.
     *
     * @param array<string> $params
     */
    public function run(array $params): void
    {
        $filePath = $this->getConfigFilePath($params);

        if (! file_exists($filePath)) {
            CLI::error("Configuration file not found: {$filePath}");
            CLI::write('Please ensure the Auth.php file exists in your Config directory.', 'yellow');

            return;
        }

        // Create backup if requested
        if (CLI::getOption('backup') !== null) {
            $this->createBackup($filePath);
        }

        // Get replacement configuration
        $authConfig = config('Simpletine\HMVCShield\Config\Auth');
        if ($authConfig === null) {
            CLI::error('Could not load SimpleTine Auth configuration.');

            return;
        }

        $search  = [
            '\CodeIgniter\Shield\Views\login',
            '\CodeIgniter\Shield\Views\register',
        ];
        $replace = $authConfig->views;

        if (count($search) !== count($replace)) {
            CLI::error('Search and replace arrays must have the same length.');

            return;
        }

        // Read and replace content
        $content = file_get_contents($filePath);
        if ($content === false) {
            CLI::error("Failed to read configuration file: {$filePath}");

            return;
        }

        $originalContent = $content;
        $content         = str_replace($search, $replace, $content);

        // Check if any replacements were made
        if ($content === $originalContent) {
            CLI::write('No changes needed. Configuration already uses AdminLTE views.', 'yellow');

            return;
        }

        // Write updated content
        if (file_put_contents($filePath, $content) !== false) {
            CLI::write('Configuration updated successfully.', 'green');
            CLI::write("Updated file: {$filePath}", 'white');
        } else {
            CLI::error('Failed to write to the configuration file.');
        }
    }

    /**
     * Gets the configuration file path from parameter or default location.
     *
     * @param array<string> $params
     */
    private function getConfigFilePath(array $params): string
    {
        $configFile = array_shift($params);

        if ($configFile !== null && $configFile !== '') {
            // If relative path, assume it's in APPPATH/Config
            $isAbsolute = (strpos($configFile, '/') === 0) || preg_match('/^[A-Z]:\\\\/', $configFile);
            if (! $isAbsolute) {
                $configFile = APPPATH . 'Config/' . ltrim($configFile, '/');
            }

            return $configFile;
        }

        return APPPATH . 'Config/Auth.php';
    }

    /**
     * Creates a backup copy of the configuration file before modification.
     */
    private function createBackup(string $filePath): void
    {
        $backupPath = $filePath . '.backup.' . date('Y-m-d_His');

        if (copy($filePath, $backupPath)) {
            CLI::write("Backup created: {$backupPath}", 'green');
        } else {
            CLI::error("Failed to create backup: {$backupPath}");
        }
    }
}
