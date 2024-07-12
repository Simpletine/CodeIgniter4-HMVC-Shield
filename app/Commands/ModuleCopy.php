<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Clones an existing module and renames it to a new module.
 */
class ModuleCopy extends BaseCommand
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
     */
    public function run(array $params)
    {
        helper('inflector');

        $oldModule = array_shift($params);
        $newModule = array_shift($params);

        if (!$oldModule || !$newModule) {
            CLI::error('Both old and new module names are required.');
            $this->showHelp();
            return;
        }

        $sourceDir = APPPATH . "Modules/$oldModule";
        $destDir = APPPATH . "Modules/$newModule";

        if (!is_dir($sourceDir)) {
            CLI::error("Source module '$oldModule' does not exist.");
            return;
        }

        if (is_dir($destDir)) {
            CLI::error("Destination module '$newModule' already exists.");
            return;
        }

        $this->recursiveCopy($sourceDir, $destDir);
        $this->replacePlaceholders($destDir, $oldModule, $newModule);
        $this->renameModelFile($destDir, $oldModule, $newModule);

        CLI::write("Module '$oldModule' has been copied to '$newModule'.", 'green');
    }

    /**
     * Recursively copy a directory.
     *
     * @param string $source
     * @param string $dest
     */
    private function recursiveCopy(string $source, string $dest)
    {
        $directoryIterator = new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS);
        $iterator = new RecursiveIteratorIterator($directoryIterator, RecursiveIteratorIterator::SELF_FIRST);

        foreach ($iterator as $item) {
            $destPath = $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
            if ($item->isDir()) {
                mkdir($destPath, 0755, true);
            } else {
                copy($item, $destPath);
            }
        }
    }

    /**
     * Replace placeholders in copied files.
     *
     * @param string $directory
     * @param string $oldModule
     * @param string $newModule
     */
    private function replacePlaceholders(string $directory, string $oldModule, string $newModule)
    {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));

        $oldModuleCamel = pascalize($oldModule);
        $newModuleCamel = pascalize($newModule);
        $oldModulePlural = plural($oldModuleCamel);
        $newModulePlural = plural($newModuleCamel);

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $contents = file_get_contents($file->getPathname());
                $contents = str_ireplace(
                    [$oldModule, $oldModuleCamel, $oldModulePlural],
                    [$newModule, $newModuleCamel, $newModulePlural],
                    $contents
                );

                // Special handling for Routes.php file
                if (basename($file) === 'Routes.php') {
                    $contents = str_replace('group("' . strtolower($oldModule) . '"', 'group("' . strtolower($newModule) . '"', $contents);
                }

                file_put_contents($file->getPathname(), $contents);
            }
        }
    }

    /**
     * Rename the model file if necessary.
     *
     * @param string $directory
     * @param string $oldModule
     * @param string $newModule
     */
    private function renameModelFile(string $directory, string $oldModule, string $newModule)
    {
        $oldModelFile = "$directory/Models/" . pascalize($oldModule) . ".php";
        $newModelFile = "$directory/Models/" . pascalize($newModule) . ".php";

        if (file_exists($oldModelFile)) {
            rename($oldModelFile, $newModelFile);
        }
    }
}
