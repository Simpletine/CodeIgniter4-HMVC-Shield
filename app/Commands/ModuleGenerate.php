<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\CLI\GeneratorTrait;
use CodeIgniter\Controller;

/**
 * Generates a skeleton controller file.
 */
class ModuleGenerate extends BaseCommand
{
    use GeneratorTrait;

    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'Generators';

    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'make:module';

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
    protected $usage = 'make:module <name>';

    /**
     * The Command's Arguments
     *
     * @var array<string, string>
     */
    protected $arguments = [];

    /**
     * The Command's Options
     *
     * @var array<string, string>
     */
    protected $options = [];

    /**
     * Actually execute a command.
     */
    public function run(array $params)
    {
        $directory_main_folder = "Modules";
        if (!is_dir(APPPATH . $directory_main_folder)) {
            if (mkdir(APPPATH . $directory_main_folder, 0755, true)) {
                CLI::write("Modules Folder created", 'green');
            } else {
                CLI::error("Modules Folder create failed, please create a new folder(Modules) inside APP or try again");
                return;
            }
        }
        $directory_name = array_shift($params);
        $directory = "$directory_main_folder/$directory_name";
        if (!is_dir(APPPATH . $directory)) {
            mkdir(APPPATH . $directory, 0755, true);
            CLI::write('Module folder created - ' . APPPATH . $directory, 'green');
        }

        $this->generateDirectories($directory);
        $this->generateFiles($directory, $directory_name);

        CLI::write('Module "' . $directory_name . '" has been created.', 'green');
    }

    protected function generateDirectories($directory)
    {
        $directories = [
            'Controllers',
            'Models',
            'Views',
        ];

        foreach ($directories as $dir) {
            $folder_require = APPPATH . $directory . '/' . $dir;
            if (!is_dir($folder_require)) {
                mkdir($folder_require, 0755, true);
            }
        }
    }

    protected function generateFiles($directory, $directory_name)
    {
        helper('inflector');
        $namespace = str_replace('/', '\\', $directory);
        $controller_template = $this->getTemplate(
            'controller.tpl.php',
            [
            '{namespace}' => "$namespace\\Controllers",
            '{useStatement}' => 'App\Controllers\BaseController',
            '{class}' => pascalize($directory_name),
            '{extends}' => 'BaseController',
            '{directoryName}' => $directory_name,
            ]
        );

        $model_template = $this->getTemplate(
            'model.tpl.php',
            [
            '{namespace}' => "$namespace\\Models",
            '{useStatement}' => 'CodeIgniter\Model',
            '{class}' => pascalize($directory_name . 'Model'),
            '{extends}' => 'Model',
            '{directoryName}' => $directory_name,
            ]
        );

        $view_template = $this->getTemplate(
            'view.tpl.php',
            [
            '{moduleName}' => $directory_name
            ]
        );

        file_put_contents(APPPATH . $directory . '/Controllers/' . ucwords($directory_name) . '.php', $controller_template);
        file_put_contents(APPPATH . $directory . '/Models/' . pascalize($directory_name) . 'Model.php', $model_template);
        file_put_contents(APPPATH . $directory . '/Views/index.php', $view_template);
    }

    protected function getTemplate($templateFile, $placeholders)
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
        $templateContent = str_replace('<@php', '<?php', $templateContent);

        return $templateContent;
    } 
}
