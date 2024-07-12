<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Generates a controller in the specified module.
 */
class ModuleController extends BaseCommand
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
        'file' => 'The name of the file to create',
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
     * @param array $params
     */
    public function run(array $params)
    {
        helper('inflector');

        $directoryMainFolder = 'Modules';
        if (!is_dir(APPPATH . $directoryMainFolder)) {
            if (mkdir(APPPATH . $directoryMainFolder, 0755, true)) {
                CLI::write('Modules Folder created', 'green');
            } else {
                CLI::error('Modules Folder creation failed. Please create a new folder (Modules) inside APP or try again.');
                return;
            }
        }

        $directoryName = array_shift($params);
        $fileName = array_shift($params);

        if (!$directoryName || !$fileName) {
            CLI::error('Both module name and file name are required.');
            return;
        }

        $moduleDirectory = "$directoryMainFolder/$directoryName";
        if (!is_dir(APPPATH . $moduleDirectory)) {
            mkdir(APPPATH . $moduleDirectory, 0755, true);
            CLI::write('Module folder created - ' . APPPATH . $moduleDirectory, 'green');
        }

        $controllerDirectory = APPPATH . $moduleDirectory . '/Controllers';
        if (!is_dir($controllerDirectory)) {
            mkdir($controllerDirectory, 0755, true);
        }

        $namespace = str_replace('/', '\\', $moduleDirectory);
        $className = pascalize($fileName);
        $controllerTemplate = $this->getTemplate(
            'controller.new.tpl.php',
            [
                '{namespace}' => "$namespace\\Controllers",
                '{useModelStatement}' => "$namespace\\Models\\$className",
                '{useStatement}' => 'App\Controllers\BaseController',
                '{class}' => $className,
                '{modelClass}' => $className,
                '{extends}' => 'BaseController',
                '{directoryName}' => $directoryName,
            ]
        );

        $controllerPath = APPPATH . $moduleDirectory . "/Controllers/$className.php";
        file_put_contents($controllerPath, $controllerTemplate);

        CLI::write("Controller '$className' created in module '$directoryName'.", 'green');
    }

    /**
     * Get the template content with placeholders replaced.
     *
     * @param string $templateFile
     * @param array $placeholders
     * @return string
     */
    protected function getTemplate(string $templateFile, array $placeholders): string
    {
        $templatePath = APPPATH . 'Commands/Views/' . $templateFile;

        if (!file_exists($templatePath)) {
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
