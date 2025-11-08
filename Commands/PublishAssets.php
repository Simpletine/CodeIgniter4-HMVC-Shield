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
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Publishes AdminLTE assets from the package to the user's public directory.
 * Allows users to specify custom destination paths for asset deployment.
 */
class PublishAssets extends BaseCommand
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
    protected $name = 'publish:assets';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Publishes AdminLTE assets to the public directory with customizable destination path.';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'publish:assets [destination_path]';

    /**
     * The Command's Arguments
     *
     * @var array<string, string>
     */
    protected $arguments = [
        'destination_path' => 'Optional: Relative path from public directory (e.g., "assets/simpletine")',
    ];

    /**
     * The Command's Options
     *
     * @var array<string, string>
     */
    protected $options = [
        '--force' => 'Overwrite existing files if destination already exists',
    ];

    /**
     * Execute the command.
     *
     * @param array<string> $params
     */
    public function run(array $params): void
    {
        $sourcePath = __DIR__ . '/../assets';

        if (! is_dir($sourcePath)) {
            CLI::error("Source assets directory not found: {$sourcePath}");

            return;
        }

        // Get destination path from user input or parameter
        $destinationPath = $this->getDestinationPath($params);

        if ($destinationPath === '') {
            CLI::error('Destination path is required.');

            return;
        }

        $fullDestinationPath = $this->normalizePath(FCPATH . $destinationPath);

        // Check if destination exists
        if (is_dir($fullDestinationPath) && ! CLI::getOption('force')) {
            $overwrite = CLI::prompt("Destination '{$destinationPath}' already exists. Overwrite?", ['y', 'n']);
            if (strtolower($overwrite) !== 'y') {
                CLI::write('Operation cancelled.', 'yellow');

                return;
            }
        }

        // Perform the copy operation
        $copied = $this->copyDirectory($sourcePath, $fullDestinationPath);

        if ($copied) {
            CLI::write("Assets published successfully to: {$destinationPath}", 'green');
            CLI::write("Full path: {$fullDestinationPath}", 'white');
        } else {
            CLI::error('Failed to publish assets.');
        }
    }

    /**
     * Gets the destination path from user input or command parameter.
     *
     * @param array<string> $params
     */
    private function getDestinationPath(array $params): string
    {
        $destinationPath = array_shift($params);

        if ($destinationPath === null || $destinationPath === '') {
            $defaultPath = 'assets';
            CLI::write('Enter the destination path (relative to public directory):', 'cyan');
            $destinationPath = $this->promptWithDefault('Destination path', $defaultPath);
        }

        // Remove leading/trailing slashes and normalize
        $destinationPath = trim($destinationPath, '/\\');

        return $destinationPath;
    }

    /**
     * Prompts for input with a default value, avoiding validation rule parsing issues.
     * Uses CLI::input() directly instead of CLI::prompt() to bypass validation.
     *
     * @return string The user input or default value if empty
     */
    private function promptWithDefault(string $label, string $default): string
    {
        CLI::write("{$label} (default: {$default})", 'white');
        $input = trim(CLI::input());

        return $input !== '' ? $input : $default;
    }

    /**
     * Normalizes a file path by converting separators and removing redundant parts.
     */
    private function normalizePath(string $path): string
    {
        $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
        $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), static fn ($part) => $part !== '');

        return implode(DIRECTORY_SEPARATOR, $parts);
    }

    /**
     * Efficiently copies a directory tree using RecursiveDirectoryIterator.
     * Provides better performance than recursive opendir/readdir approach.
     */
    private function copyDirectory(string $source, string $destination): bool
    {
        if (! is_dir($source)) {
            CLI::error("Source directory does not exist: {$source}");

            return false;
        }

        // Create destination directory if it doesn't exist
        if (! is_dir($destination)) {
            if (! mkdir($destination, 0755, true)) {
                CLI::error("Failed to create destination directory: {$destination}");

                return false;
            }
        }

        try {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );

            $copiedFiles = 0;
            $skippedFiles = 0;

            foreach ($iterator as $item) {
                $destPath = $destination . DIRECTORY_SEPARATOR . $iterator->getSubPathName();

                if ($item->isDir()) {
                    if (! is_dir($destPath)) {
                        if (! mkdir($destPath, 0755, true)) {
                            CLI::error("Failed to create directory: {$destPath}");

                            return false;
                        }
                    }
                } else {
                    // Check if file already exists
                    if (file_exists($destPath) && ! CLI::getOption('force')) {
                        $skippedFiles++;
                        continue;
                    }

                    if (copy($item->getPathname(), $destPath)) {
                        $copiedFiles++;
                    } else {
                        CLI::error("Failed to copy file: {$item->getPathname()}");

                        return false;
                    }
                }
            }

            if ($copiedFiles > 0) {
                CLI::write("Copied {$copiedFiles} file(s) successfully.", 'green');
            }

            if ($skippedFiles > 0) {
                CLI::write("Skipped {$skippedFiles} existing file(s). Use --force to overwrite.", 'yellow');
            }

            return true;
        } catch (\Exception $e) {
            CLI::error("Error during copy operation: {$e->getMessage()}");

            return false;
        }
    }
}
