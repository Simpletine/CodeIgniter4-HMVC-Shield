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
 * Manages routes within module route configuration files.
 * Supports adding, listing, and removing routes from module Routes.php files.
 */
class ModuleRoute extends BaseModuleCommand
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
    protected $name = 'module:route';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Manage routes in a module (add, list, remove).';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'module:route <module> <action> [route_path] [controller::method] [--method=GET]';

    /**
     * The Command's Arguments
     *
     * @var array<string, string>
     */
    protected $arguments = [
        'module'            => 'The name of the module',
        'action'            => 'Action: add, list, remove, publish, unpublish',
        'route_path'        => 'The route path (e.g., "new", "edit/:id") — required for add/remove',
        'controller_method' => 'Controller and method (e.g., "Blogs::create") — required for add',
    ];

    /**
     * The Command's Options
     *
     * @var array<string, string>
     */
    protected $options = [
        '--method' => 'HTTP method (GET, POST, PUT, DELETE, etc.)',
    ];

    /**
     * Execute the command.
     *
     * @param list<string> $params
     */
    public function run(array $params): void
    {
        $moduleName = array_shift($params);
        $action     = array_shift($params);

        if (! $moduleName || ! $action) {
            CLI::error('Module name and action are required.');
            $this->showHelp();

            return;
        }

        $action = strtolower($action);

        // Sidebar-only actions: do not require a Routes.php file
        if (in_array($action, ['publish', 'unpublish'], true)) {
            match ($action) {
                'publish'   => $this->publishSidebar($moduleName),
                'unpublish' => $this->unpublishSidebar($moduleName),
            };

            return;
        }

        $modulePath = $this->ensureModuleDirectory($moduleName);
        $routesFile = $modulePath . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'Routes.php';

        // Ensure Config directory exists
        $configDir = dirname($routesFile);
        if (! is_dir($configDir)) {
            if (! mkdir($configDir, 0755, true)) {
                CLI::error("Failed to create Config directory for module: {$moduleName}");

                return;
            }
        }

        // Create Routes.php if it doesn't exist
        if (! file_exists($routesFile)) {
            $this->createInitialRoutesFile($routesFile, $moduleName);
        }

        match ($action) {
            'add'    => $this->addRoute($routesFile, $moduleName, $params),
            'list'   => $this->listRoutes($routesFile, $moduleName),
            'remove' => $this->removeRoute($routesFile, $moduleName, $params),
            default  => CLI::error("Unknown action: {$action}. Use: add, list, remove, publish, unpublish"),
        };
    }

    /**
     * Creates initial Routes.php file for a module.
     */
    private function createInitialRoutesFile(string $routesFile, string $moduleName): void
    {
        helper('inflector');
        $namespace = $this->buildNamespace($moduleName, 'Controllers');
        $groupName = strtolower(dasherize($moduleName));

        $content = $this->getTemplate('route.tpl.php', [
            '{groupName}' => $groupName,
            '{namespace}' => $namespace,
        ]);

        file_put_contents($routesFile, $content);
        CLI::write("Created initial Routes.php for module '{$moduleName}'.", 'green');
    }

    /**
     * Adds a route to the module's Routes.php file.
     *
     * @param list<string> $params
     */
    private function addRoute(string $routesFile, string $moduleName, array $params): void
    {
        $routePath        = array_shift($params);
        $controllerMethod = array_shift($params);

        if (! $routePath || ! $controllerMethod) {
            CLI::error('Route path and controller::method are required for adding a route.');
            CLI::write('Example: php spark module:route Blogs add "new" "Blogs::create" --method=POST', 'yellow');

            return;
        }

        $httpMethod = CLI::getOption('method') ?? 'GET';
        $httpMethod = strtoupper($httpMethod);

        $content = file_get_contents($routesFile);
        if ($content === false) {
            CLI::error("Failed to read routes file: {$routesFile}");

            return;
        }

        // Check if route already exists
        if (str_contains($content, "'{$routePath}'") || str_contains($content, "\"{$routePath}\"")) {
            CLI::write("Route '{$routePath}' already exists in module '{$moduleName}'.", 'yellow');

            return;
        }

        // Find the closing of the routes group
        $insertPosition = strrpos($content, '});');
        if ($insertPosition === false) {
            CLI::error('Could not find route group closure in Routes.php');

            return;
        }

        // Build route line
        $routeLine  = "        \$routes->{$httpMethod}('{$routePath}', '{$controllerMethod}');\n";
        $newContent = substr_replace($content, $routeLine . '    ', $insertPosition, 0);

        if (file_put_contents($routesFile, $newContent) !== false) {
            CLI::write("Route '{$routePath}' ({$httpMethod}) added successfully to module '{$moduleName}'.", 'green');
        } else {
            CLI::error("Failed to add route to module '{$moduleName}'.");
        }
    }

    /**
     * Lists all routes in the module's Routes.php file.
     */
    private function listRoutes(string $routesFile, string $moduleName): void
    {
        $content = file_get_contents($routesFile);
        if ($content === false) {
            CLI::error("Failed to read routes file: {$routesFile}");

            return;
        }

        CLI::write("Routes in module '{$moduleName}':", 'cyan');
        CLI::newLine();

        // Extract route definitions using regex
        preg_match_all('/\$routes->(\w+)\s*\([\'"]([^\'"]+)[\'"],\s*[\'"]([^\'"]+)[\'"]\);/', $content, $matches, PREG_SET_ORDER);

        if (empty($matches)) {
            CLI::write('No routes found.', 'yellow');

            return;
        }

        foreach ($matches as $match) {
            $method = str_pad($match[1], 6, ' ', STR_PAD_RIGHT);
            $path   = $match[2];
            $target = $match[3];

            CLI::write("  [{$method}] {$path} -> {$target}", 'white');
        }
    }

    /**
     * Removes a route from the module's Routes.php file.
     *
     * @param list<string> $params
     */
    private function removeRoute(string $routesFile, string $moduleName, array $params): void
    {
        $routePath = array_shift($params);

        if (! $routePath) {
            CLI::error('Route path is required for removing a route.');

            return;
        }

        $content = file_get_contents($routesFile);
        if ($content === false) {
            CLI::error("Failed to read routes file: {$routesFile}");

            return;
        }

        // Remove route line
        $pattern    = '/\s*\$routes->\w+\s*\([\'"]' . preg_quote($routePath, '/') . '[\'"],\s*[\'"][^\'"]+[\'"]\);\s*\n/';
        $newContent = preg_replace($pattern, '', $content);

        if ($newContent === $content) {
            CLI::write("Route '{$routePath}' not found in module '{$moduleName}'.", 'yellow');

            return;
        }

        if (file_put_contents($routesFile, $newContent) !== false) {
            CLI::write("Route '{$routePath}' removed successfully from module '{$moduleName}'.", 'green');
        } else {
            CLI::error("Failed to remove route from module '{$moduleName}'.");
        }
    }
}
