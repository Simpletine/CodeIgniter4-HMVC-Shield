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
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Clones an existing module structure and renames all references to create a new module.
 * Updates namespaces, class names, and route configurations in the copied module.
 */
class ModuleCopy extends BaseModuleCommand
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
    protected $name = 'module:copy';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Clone an existing module and rename it to a new module.';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'module:copy <old_module> <new_module>';

    /**
     * The Command's Arguments
     *
     * @var array<string, string>
     */
    protected $arguments = [
        'old_module' => 'The name of the existing module to copy',
        'new_module' => 'The name of the new module to create',
    ];

    /**
     * The Command's Options
     *
     * @var array<string, string>
     */
    protected $options = [];

    /**
     * Execute the command.
     *
     * @param list<string> $params
     */
    public function run(array $params): void
    {
        helper('inflector');

        $oldModule = array_shift($params);
        $newModule = array_shift($params);

        if (! $oldModule || ! $newModule) {
            CLI::error('Both old and new module names are required.');
            $this->showHelp();

            return;
        }

        $modulesDir = $this->getModulesDirectory();
        $sourceDir  = APPPATH . "{$modulesDir}/{$oldModule}";
        $destDir    = APPPATH . "{$modulesDir}/{$newModule}";

        if (! is_dir($sourceDir)) {
            CLI::error("Source module '{$oldModule}' does not exist.");

            return;
        }

        if (is_dir($destDir)) {
            CLI::error("Destination module '{$newModule}' already exists.");

            return;
        }

        $this->recursiveCopy($sourceDir, $destDir);
        $this->replacePlaceholders($destDir, $oldModule, $newModule);
        $this->renameModelFile($destDir, $oldModule, $newModule);

        CLI::write("Module '{$oldModule}' has been copied to '{$newModule}'.", 'green');
    }

    /**
     * Recursively copies all files and directories from source to destination.
     * Preserves directory structure during the copy operation.
     */
    private function recursiveCopy(string $source, string $dest): void
    {
        $directoryIterator = new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS);
        $iterator          = new RecursiveIteratorIterator($directoryIterator, RecursiveIteratorIterator::SELF_FIRST);

        foreach ($iterator as $item) {
            $destPath = $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
            if ($item->isDir()) {
                mkdir($destPath, 0755, true);
            } else {
                copy($item->getPathname(), $destPath);
            }
        }
    }

    /**
     * Replaces module name references throughout all files in the copied module.
     * Updates class names, namespaces, and route configurations to match the new module name.
     */
    private function replacePlaceholders(string $directory, string $oldModule, string $newModule): void
    {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));

        $oldModuleCamel  = pascalize($oldModule);
        $newModuleCamel  = pascalize($newModule);
        $oldModulePlural = plural($oldModuleCamel);
        $newModulePlural = plural($newModuleCamel);

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $contents = file_get_contents($file->getPathname());
                $contents = str_ireplace(
                    [$oldModule, $oldModuleCamel, $oldModulePlural],
                    [$newModule, $newModuleCamel, $newModulePlural],
                    $contents,
                );

                // Special handling for Routes.php file
                if (basename($file->getPathname()) === 'Routes.php') {
                    $contents = str_replace('group("' . strtolower($oldModule) . '"', 'group("' . strtolower($newModule) . '"', $contents);
                }

                file_put_contents($file->getPathname(), $contents);
            }
        }
    }

    /**
     * Renames the model file to match the new module name convention.
     */
    private function renameModelFile(string $directory, string $oldModule, string $newModule): void
    {
        $oldModelFile = "{$directory}/Models/" . pascalize($oldModule) . '.php';
        $newModelFile = "{$directory}/Models/" . pascalize($newModule) . '.php';

        if (file_exists($oldModelFile)) {
            rename($oldModelFile, $newModelFile);
        }
    }
}
