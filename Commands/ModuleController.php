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
 * Generates a controller in the specified module.
 * Uses stored configuration for namespace patterns and base controller class.
 */
class ModuleController extends BaseModuleCommand
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
    protected $name = 'module:controller';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Generate a controller into a module';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'module:controller <module> <file>';

    /**
     * The Command's Arguments
     *
     * @var array
     */
    protected $arguments = [
        'module' => 'The name of the existing module directory',
        'file'   => 'The name of the file to create',
    ];

    /**
     * The Command's Options
     *
     * @var array
     */
    protected $options = [
        '--admin' => 'Use admin template',
    ];

    /**
     * Execute the command.
     *
     * @param array<string> $params
     */
    public function run(array $params): void
    {
        $moduleName = array_shift($params);
        $fileName   = array_shift($params);

        if (! $moduleName || ! $fileName) {
            CLI::error('Both module name and file name are required.');
            $this->showHelp();

            return;
        }

        $modulePath = $this->ensureModuleDirectory($moduleName);
        $controllerDirectory = $modulePath . DIRECTORY_SEPARATOR . 'Controllers';

        if (! is_dir($controllerDirectory)) {
            if (! mkdir($controllerDirectory, 0755, true)) {
                CLI::error("Failed to create Controllers directory for module: {$moduleName}");

                return;
            }
        }

        $isAdmin       = CLI::getOption('admin') !== null;
        $className     = $this->buildClassName($fileName);
        $templateFile  = $isAdmin ? 'controller.admin.tpl.php' : 'controller.new.tpl.php';
        $lowerClassName = strtolower($className);
        $namespace     = $this->buildNamespace($moduleName, 'Controllers');
        $modelsNs      = $this->buildNamespace($moduleName, 'Models');
        $baseController = $this->getBaseController();

        $controllerTemplate = $this->getTemplate(
            $templateFile,
            [
                '{namespace}'         => $namespace,
                '{useModelStatement}' => $modelsNs . '\\' . $className,
                '{useStatement}'      => $baseController,
                '{class}'             => $className,
                '{lowerClass}'        => $lowerClassName,
                '{modelClass}'        => $className,
                '{extends}'           => basename(str_replace('\\', '/', $baseController)),
                '{directoryName}'     => $moduleName,
            ]
        );

        if ($controllerTemplate === '') {
            return;
        }

        $controllerPath = $controllerDirectory . DIRECTORY_SEPARATOR . "{$className}.php";
        if (! file_exists($controllerPath)) {
            file_put_contents($controllerPath, $controllerTemplate);
            CLI::write("Controller '{$className}' created in module '{$moduleName}'.", 'green');
        } else {
            CLI::write("Controller '{$className}' already exists in module '{$moduleName}'.", 'yellow');

            return;
        }

        if ($isAdmin) {
            $viewsPath = $modulePath . DIRECTORY_SEPARATOR . 'Views';
            if (! is_dir($viewsPath)) {
                if (! mkdir($viewsPath, 0755, true)) {
                    CLI::error("Failed to create Views directory for module: {$moduleName}");

                    return;
                }
            }

            $viewTemplate = $this->getTemplate(
                'view.admin.tpl.php',
                [
                    '{directoryName}' => $moduleName,
                ]
            );

            if ($viewTemplate !== '') {
                $viewPath = $viewsPath . DIRECTORY_SEPARATOR . "{$lowerClassName}.php";
                if (! file_exists($viewPath)) {
                    file_put_contents($viewPath, $viewTemplate);
                    CLI::write("View '{$fileName}' created in module '{$moduleName}'.", 'green');
                } else {
                    CLI::write("View '{$fileName}' already exists in module '{$moduleName}'.", 'yellow');
                }
            }
        }
    }

}
