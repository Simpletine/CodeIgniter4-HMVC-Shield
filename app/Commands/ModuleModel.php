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

class ModuleModel extends BaseCommand
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
     */
    public function run(array $params)
    {
        helper('inflector');

        $directoryMainFolder = 'Modules';
        if (! is_dir(APPPATH . $directoryMainFolder)) {
            if (mkdir(APPPATH . $directoryMainFolder, 0755, true)) {
                CLI::write('Modules Folder created', 'green');
            } else {
                CLI::error('Modules Folder creation failed. Please create a new folder (Modules) inside APP or try again.');

                return;
            }
        }

        $directoryName = array_shift($params);
        $fileName      = array_shift($params);

        if (! $directoryName || ! $fileName) {
            CLI::error('Both module name and file name are required.');

            return;
        }

        $moduleDirectory = "{$directoryMainFolder}/{$directoryName}";
        if (! is_dir(APPPATH . $moduleDirectory)) {
            mkdir(APPPATH . $moduleDirectory, 0755, true);
            CLI::write('Module folder created - ' . APPPATH . $moduleDirectory, 'green');
        }

        $modelDirectory = APPPATH . $moduleDirectory . '/Models';
        if (! is_dir($modelDirectory)) {
            mkdir($modelDirectory, 0755, true);
        }

        $namespace = str_replace('/', '\\', $moduleDirectory);
        $className = pascalize($fileName . 'Model');
        $tableName = 'st_' . strtolower(underscore($fileName));

        $modelTemplate = $this->getTemplate(
            'model.tpl.php',
            [
                '{namespace}'     => "{$namespace}\\Models",
                '{useStatement}'  => 'CodeIgniter\Model',
                '{class}'         => $className,
                '{table}'         => $tableName,
                '{extends}'       => 'Model',
                '{directoryName}' => $directoryName,
            ]
        );

        $modelPath = APPPATH . $moduleDirectory . "/Models/{$className}.php";
        file_put_contents($modelPath, $modelTemplate);

        CLI::write("Model '{$className}' created in module '{$directoryName}'.", 'green');
    }

    /**
     * Get the template content with placeholders replaced.
     */
    protected function getTemplate(string $templateFile, array $placeholders): string
    {
        $templatePath = APPPATH . 'Commands/Views/' . $templateFile;

        if (! file_exists($templatePath)) {
            CLI::write('Template file not found: ' . $templateFile, 'red');

            return '';
        }

        $templateContent = file_get_contents($templatePath);

        foreach ($placeholders as $placeholder => $value) {
            $templateContent = str_replace($placeholder, $value, $templateContent);
        }

        return str_replace('<@php', '<?php', $templateContent);
    }
}
