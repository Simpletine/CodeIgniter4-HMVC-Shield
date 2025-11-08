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
 * Generates a model in the specified module.
 * Uses stored configuration for namespace patterns, table prefixes, and base model class.
 */
class ModuleModel extends BaseModuleCommand
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
    protected $name = 'module:model';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Generate a model into module';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'module:model <module> <file>';

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
    protected $options = [];

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
        $modelDirectory = $modulePath . DIRECTORY_SEPARATOR . 'Models';

        if (! is_dir($modelDirectory)) {
            if (! mkdir($modelDirectory, 0755, true)) {
                CLI::error("Failed to create Models directory for module: {$moduleName}");

                return;
            }
        }

        helper('inflector');
        $className   = $this->buildClassName($fileName, 'Model');
        $namespace   = $this->buildNamespace($moduleName, 'Models');
        $tablePrefix = $this->getTablePrefix();
        $baseModel   = $this->getBaseModel();
        $tableName   = $tablePrefix . strtolower(underscore($fileName));

        $modelTemplate = $this->getTemplate(
            'model.tpl.php',
            [
                '{namespace}'     => $namespace,
                '{useStatement}'  => $baseModel,
                '{class}'         => $className,
                '{table}'         => $tableName,
                '{extends}'       => basename(str_replace('\\', '/', $baseModel)),
                '{directoryName}' => $moduleName,
            ]
        );

        if ($modelTemplate === '') {
            return;
        }

        $modelPath = $modelDirectory . DIRECTORY_SEPARATOR . "{$className}.php";
        if (file_exists($modelPath)) {
            CLI::write("Model '{$className}' already exists in module '{$moduleName}'.", 'yellow');

            return;
        }

        if (file_put_contents($modelPath, $modelTemplate) !== false) {
            CLI::write("Model '{$className}' created in module '{$moduleName}'.", 'green');
        } else {
            CLI::error("Failed to create model '{$className}' in module '{$moduleName}'.");
        }
    }
}
