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
 * Generates a complete module structure with MVC files.
 * Uses stored configuration for namespace patterns, class prefixes, and other preferences.
 */
class ModuleCreate extends BaseModuleCommand
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
    protected $name = 'module:create';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Generates a new module MVC files.';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'module:create [module_name]';

    /**
     * The Command's Arguments
     *
     * @var array<string, string>
     */
    protected $arguments = [
        'module_name' => 'The name of the module to create',
    ];

    /**
     * The Command's Options
     *
     * @var array<string, string>
     */
    protected $options = [
        '--admin'   => 'Use AdminLTE admin template',
        '--crud'    => 'Generate Shield-based user CRUD (controller / views / routes). No migration needed — uses Shield native tables.',
        '--sidebar' => 'Add this module to the AdminLTE sidebar after creation (skips the interactive prompt).',
    ];

    /**
     * Execute the command.
     *
     * @param list<string> $params
     */
    public function run(array $params): void
    {
        $moduleName = array_shift($params);

        if (! $moduleName) {
            CLI::error('Module name is required.');
            CLI::newLine();
            $this->showHelp();

            return;
        }

        $moduleDirectory = $this->ensureModuleDirectory($moduleName);
        $this->generateDirectories($moduleDirectory);
        $this->generateFiles($moduleDirectory, $moduleName);

        $isCrud     = CLI::getOption('crud') !== null;
        $addSidebar = CLI::getOption('sidebar') !== null;

        if ($isCrud) {
            CLI::write("Module \"{$moduleName}\" has been created with CRUD (Shield users) successfully.", 'green');
        } else {
            CLI::write("Module \"{$moduleName}\" has been created successfully.", 'green');
        }

        // When --sidebar is absent, ask interactively
        if (! $addSidebar) {
            $answer     = CLI::prompt("Add \"{$moduleName}\" to the sidebar navigation?", ['y', 'n']);
            $addSidebar = strtolower($answer) === 'y';
        }

        if ($addSidebar) {
            $this->publishSidebar($moduleName, $isCrud);
        }
    }

    /**
     * Generates necessary directories for the module.
     * Includes Database/Migrations and Database/Seeds for module-level migrations.
     */
    private function generateDirectories(string $directory)
    {
        $directories = [
            'Config',
            'Controllers',
            'Models',
            'Views',
            'Database',
            'Database/Migrations',
            'Database/Seeds',
        ];

        foreach ($directories as $dir) {
            $path = "{$directory}/{$dir}";
            if (! is_dir($path)) {
                mkdir($path, 0755, true);
            }
        }
    }

    /**
     * Generates necessary files for the module using stored configuration.
     */
    private function generateFiles(string $directory, string $moduleName): void
    {
        helper('inflector');
        $className      = $this->buildClassName($moduleName);
        $isAdmin        = CLI::getOption('admin') !== null;
        $isCrud         = CLI::getOption('crud') !== null;
        $namespaceBase  = $this->getNamespaceBase();
        $controllersNs  = $this->buildNamespace($moduleName, 'Controllers');
        $modelsNs       = $this->buildNamespace($moduleName, 'Models');
        $baseController = $this->getBaseController();
        $baseModel      = $this->getBaseModel();
        $tablePrefix    = $this->getTablePrefix();
        $groupName      = strtolower(dasherize(underscore($moduleName)));
        $extends        = basename(str_replace('\\', '/', $baseController));

        if ($isCrud) {
            // --crud: Shield-based user CRUD (no migration, uses Shield native tables)
            $templates = [
                'controller.crud.tpl.php' => [
                    'path'         => "{$directory}/Controllers/Index.php",
                    'placeholders' => [
                        '{namespace}'     => $controllersNs,
                        '{useStatement}'  => $baseController,
                        '{class}'         => 'Index',
                        '{extends}'       => $extends,
                        '{directoryName}' => $moduleName,
                        '{groupName}'     => $groupName,
                    ],
                ],
                'view.crud.index.tpl.php' => [
                    'path'         => "{$directory}/Views/index.php",
                    'placeholders' => [
                        '{groupName}' => $groupName,
                    ],
                ],
                'view.crud.create.tpl.php' => [
                    'path'         => "{$directory}/Views/create.php",
                    'placeholders' => [
                        '{groupName}' => $groupName,
                    ],
                ],
                'view.crud.edit.tpl.php' => [
                    'path'         => "{$directory}/Views/edit.php",
                    'placeholders' => [
                        '{groupName}' => $groupName,
                    ],
                ],
                'route.crud.tpl.php' => [
                    'path'         => "{$directory}/Config/Routes.php",
                    'placeholders' => [
                        '{groupName}' => $groupName,
                        '{namespace}' => $controllersNs,
                    ],
                ],
            ];
        } else {
            $templates = [
                $isAdmin ? 'controller.admin.tpl.php' : 'controller.tpl.php' => [
                    'path'         => "{$directory}/Controllers/Index.php",
                    'placeholders' => [
                        '{namespace}'         => $controllersNs,
                        '{useModelStatement}' => $modelsNs . '\\' . $className,
                        '{useStatement}'      => $baseController,
                        '{class}'             => 'Index',
                        '{lowerClass}'        => 'index',
                        '{modelClass}'        => $className,
                        '{extends}'           => $extends,
                        '{directoryName}'     => $moduleName,
                    ],
                ],
                'model.tpl.php' => [
                    'path'         => "{$directory}/Models/{$className}.php",
                    'placeholders' => [
                        '{namespace}'     => $modelsNs,
                        '{useStatement}'  => $baseModel,
                        '{class}'         => $className,
                        '{table}'         => $tablePrefix . strtolower(underscore($moduleName)),
                        '{extends}'       => basename(str_replace('\\', '/', $baseModel)),
                        '{directoryName}' => $moduleName,
                    ],
                ],
                'view.tpl.php' => [
                    'path'         => "{$directory}/Views/index.php",
                    'placeholders' => [
                        '{moduleName}' => $moduleName,
                    ],
                ],
                'route.tpl.php' => [
                    'path'         => "{$directory}/Config/Routes.php",
                    'placeholders' => [
                        '{groupName}' => $groupName,
                        '{namespace}' => $controllersNs,
                    ],
                ],
            ];
        }

        foreach ($templates as $template => $details) {
            $content = $this->getTemplate($template, $details['placeholders']);
            if ($content !== '') {
                file_put_contents($details['path'], $content);
            }
        }
    }
}
