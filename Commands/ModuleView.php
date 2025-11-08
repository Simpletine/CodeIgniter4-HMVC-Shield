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

/**
 * Generates view files (including widgets) within a specified module.
 * Supports creating standard views and admin-style views with templates.
 */
class ModuleView extends BaseModuleCommand
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
    protected $name = 'module:view';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Generate a view file (or widget) in a specified module.';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'module:view <module> <view_name> [--admin] [--widget]';

    /**
     * The Command's Arguments
     *
     * @var array<string, string>
     */
    protected $arguments = [
        'module'    => 'The name of the existing module directory',
        'view_name' => 'The name of the view file to create',
    ];

    /**
     * The Command's Options
     *
     * @var array<string, string>
     */
    protected $options = [
        '--admin'  => 'Use admin template for the view',
        '--widget' => 'Create a widget view (partial)',
    ];

    /**
     * Execute the command.
     *
     * @param array<string> $params
     */
    public function run(array $params): void
    {
        $moduleName = array_shift($params);
        $viewName   = array_shift($params);

        if (! $moduleName || ! $viewName) {
            CLI::error('Both module name and view name are required.');
            $this->showHelp();

            return;
        }

        $modulePath = $this->ensureModuleDirectory($moduleName);
        $viewsPath  = $modulePath . DIRECTORY_SEPARATOR . 'Views';

        if (! is_dir($viewsPath)) {
            if (! mkdir($viewsPath, 0755, true)) {
                CLI::error("Failed to create Views directory for module: {$moduleName}");

                return;
            }
        }

        $isAdmin  = CLI::getOption('admin') !== null;
        $isWidget = CLI::getOption('widget') !== null;

        $viewFileName = strtolower($viewName) . '.php';
        $viewFilePath = $viewsPath . DIRECTORY_SEPARATOR . $viewFileName;

        if (file_exists($viewFilePath)) {
            CLI::write("View '{$viewName}' already exists in module '{$moduleName}'.", 'yellow');

            return;
        }

        $templateFile = $isAdmin ? 'view.admin.tpl.php' : 'view.tpl.php';
        $placeholders = [
            '{moduleName}' => $moduleName,
            '{viewName}'   => $viewName,
        ];

        if ($isWidget) {
            $placeholders['{viewName}'] = ucfirst($viewName) . ' Widget';
        }

        $content = $this->getTemplate($templateFile, $placeholders);
        if ($content === '') {
            return;
        }

        if (file_put_contents($viewFilePath, $content) !== false) {
            $viewType = $isWidget ? 'widget' : 'view';
            CLI::write("{$viewType} '{$viewName}' created successfully in module '{$moduleName}'.", 'green');
        } else {
            CLI::error("Failed to create view '{$viewName}' in module '{$moduleName}'.");
        }
    }
}

