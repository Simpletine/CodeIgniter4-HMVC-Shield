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

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class VendorPublisher extends BaseCommand
{
    protected $group       = 'SimpleTine';
    protected $name        = 'vendor:publish';
    protected $description = 'Publish vendor files to a target directory with specific options.';
    protected $usage       = 'vendor:publish [source] [target] [--option]';
    protected $arguments   = [
        'source' => 'The source directory or file to publish.',
        'target' => 'The target directory where the files should be published.',
    ];
    protected $options = [
        '--file'   => 'Publish a specific file.',
        '--folder' => 'Publish a specific folder.',
        '--all'    => 'Publish everything from the source.',
    ];

    public function run(array $params)
    {
        $source = $params[0] ?? null;
        $target = $params[1] ?? null;

        if (! $source || ! $target) {
            CLI::error('You must provide both source and target paths.');

            return;
        }

        $option = CLI::getOption('file') ? 'file' : (CLI::getOption('folder') ? 'folder' : (CLI::getOption('all') ? 'all' : null));

        if (! $option) {
            CLI::error('You must specify one of the options: --file, --folder, or --all.');

            return;
        }

        // Handle options
        switch ($option) {
            case 'file':
                $this->publishFile($source, $target);
                break;

            case 'folder':
                $this->publishFolder($source, $target);
                break;

            case 'all':
                $this->publishAll($source, $target);
                break;

            default:
                CLI::error('Invalid option specified.');
                break;
        }
    }

    private function publishFile($source, $target)
    {
        if (! is_file($source)) {
            CLI::error("The source file '{$source}' does not exist.");

            return;
        }

        if (! @copy($source, $target . '/' . basename($source))) {
            CLI::error('Failed to copy the file.');
        } else {
            CLI::write("File published to '{$target}'.", 'green');
        }
    }

    private function publishFolder($source, $target)
    {
        if (! is_dir($source)) {
            CLI::error("The source folder '{$source}' does not exist.");

            return;
        }

        if (! @mkdir($target, 0755, true) && ! is_dir($target)) {
            CLI::error('Failed to create the target directory.');

            return;
        }

        foreach (scandir($source) as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $srcPath  = $source . DIRECTORY_SEPARATOR . $item;
            $destPath = $target . DIRECTORY_SEPARATOR . $item;

            if (is_dir($srcPath)) {
                $this->publishFolder($srcPath, $destPath);
            } else {
                $this->publishFile($srcPath, $destPath);
            }
        }

        CLI::write("Folder published to '{$target}'.", 'green');
    }

    private function publishAll($source, $target)
    {
        if (! is_dir($source)) {
            CLI::error("The source directory '{$source}' does not exist.");

            return;
        }

        $this->publishFolder($source, $target);
    }
}
