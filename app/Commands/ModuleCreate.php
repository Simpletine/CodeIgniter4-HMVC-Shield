<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\CLI\GeneratorTrait;

/**
 * Generates a skeleton controller file.
 */
class ModuleCreate extends BaseCommand
{
    use GeneratorTrait;

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
        'module_name' => 'The name of the module to create'
    ];

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
        $moduleName = array_shift($params);
        if (!$moduleName) {
            CLI::error('Module name is required.');
            CLI::newLine();
            $this->showHelp();
            return;
        }

        $mainFolder = 'Modules';
        $this->createDirectory(APPPATH . $mainFolder, 'Modules Folder');

        $moduleDirectory = APPPATH . "$mainFolder/$moduleName";
        $this->createDirectory($moduleDirectory, "Module folder - $moduleDirectory");

        $this->generateDirectories($moduleDirectory);
        $this->generateFiles($moduleDirectory, $moduleName);

        CLI::write("Module \"$moduleName\" has been created.", 'green');
    }

    /**
     * Creates a directory if it doesn't exist.
     *
     * @param string $path
     * @param string $successMessage
     */
    private function createDirectory(string $path, string $successMessage)
    {
        if (!is_dir($path)) {
            if (mkdir($path, 0755, true)) {
                CLI::write($successMessage, 'green');
            } else {
                CLI::error("$successMessage create failed, please create the folder manually or try again.");
                exit;
            }
        }
    }

    /**
     * Generates necessary directories for the module.
     *
     * @param string $directory
     */
    private function generateDirectories(string $directory)
    {
        $directories = ['Config', 'Controllers', 'Models', 'Views'];

        foreach ($directories as $dir) {
            $path = "$directory/$dir";
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }
        }
    }

    /**
     * Generates necessary files for the module.
     *
     * @param string $directory
     * @param string $moduleName
     */
    private function generateFiles(string $directory, string $moduleName)
    {
        helper('inflector');
        $className = pascalize($moduleName);

        $templates = [
            'controller.tpl.php' => [
                'path' => "$directory/Controllers/Index.php",
                'placeholders' => [
                    '{namespace}' => "App\\Modules\\$moduleName\\Controllers",
                    '{useModelStatement}' => "App\\Modules\\$moduleName\\Models\\$className",
                    '{useStatement}' => 'App\Controllers\BaseController',
                    '{class}' => 'Index',
                    '{modelClass}' => $className,
                    '{extends}' => 'BaseController',
                    '{directoryName}' => $moduleName,
                ],
            ],
            'model.tpl.php' => [
                'path' => "$directory/Models/$className.php",
                'placeholders' => [
                    '{namespace}' => "App\\Modules\\$moduleName\\Models",
                    '{useStatement}' => 'CodeIgniter\Model',
                    '{class}' => $className,
                    '{table}' => "st_" . strtolower(underscore($moduleName)),
                    '{extends}' => 'Model',
                    '{directoryName}' => $moduleName,
                ],
            ],
            'view.tpl.php' => [
                'path' => "$directory/Views/index.php",
                'placeholders' => [
                    '{moduleName}' => $moduleName
                ],
            ],
            'route.tpl.php' => [
                'path' => "$directory/Config/Routes.php",
                'placeholders' => [
                    '{groupName}' => strtolower(dasherize(underscore($moduleName))),
                    '{namespace}' => "App\\Modules\\$moduleName\\Controllers",
                ],
            ],
        ];

        foreach ($templates as $template => $details) {
            $content = $this->getTemplate($template, $details['placeholders']);
            file_put_contents($details['path'], $content);
        }
    }

    /**
     * Loads a template and replaces placeholders.
     *
     * @param string $templateFile
     * @param array<string, string> $placeholders
     * @return string
     */
    private function getTemplate(string $templateFile, array $placeholders): string
    {
        $templatePath = APPPATH . 'Commands/Views/' . $templateFile;

        if (!file_exists($templatePath)) {
            CLI::write("Template file not found: $templateFile", 'red');
            return '';
        }

        $content = file_get_contents($templatePath);

        foreach ($placeholders as $placeholder => $value) {
            $content = str_replace($placeholder, $value, $content);
        }

        return str_replace('<@php', '<?php', $content);
    }
}
