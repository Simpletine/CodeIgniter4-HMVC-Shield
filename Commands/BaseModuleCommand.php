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

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Base class for module-related commands with shared configuration and utility methods.
 * Provides common functionality for module file generation and configuration access.
 */
abstract class BaseModuleCommand extends BaseCommand
{
    /**
     * Configuration storage instance.
     */
    protected ?ConfigStorage $configStorage = null;

    /**
     * Gets the configuration storage instance, creating it if necessary.
     */
    protected function getConfigStorage(): ConfigStorage
    {
        if ($this->configStorage === null) {
            $this->configStorage = new ConfigStorage();
        }

        return $this->configStorage;
    }

    /**
     * Gets the modules directory path.
     * Priority: app/Config/HMVCPaths.php > ConfigStorage > default 'Modules'.
     */
    protected function getModulesDirectory(): string
    {
        $pathsConfig = config('HMVCPaths');
        if ($pathsConfig !== null && isset($pathsConfig->modulesDirectory) && $pathsConfig->modulesDirectory !== '') {
            return $pathsConfig->modulesDirectory;
        }

        return $this->getConfigStorage()->get('modules_directory', 'Modules');
    }

    /**
     * Gets the namespace base from configuration.
     */
    protected function getNamespaceBase(): string
    {
        return $this->getConfigStorage()->get('namespace_base', 'App\\Modules');
    }

    /**
     * Gets the class prefix from configuration.
     */
    protected function getClassPrefix(): string
    {
        return $this->getConfigStorage()->get('class_prefix', '');
    }

    /**
     * Gets the table prefix from configuration.
     */
    protected function getTablePrefix(): string
    {
        return $this->getConfigStorage()->get('table_prefix', 'st_');
    }

    /**
     * Gets the base controller class from configuration.
     */
    protected function getBaseController(): string
    {
        return $this->getConfigStorage()->get('base_controller', 'App\\Controllers\\BaseController');
    }

    /**
     * Gets the base model class from configuration.
     */
    protected function getBaseModel(): string
    {
        return $this->getConfigStorage()->get('base_model', 'CodeIgniter\\Model');
    }

    /**
     * Ensures the modules directory exists.
     */
    protected function ensureModulesDirectory(): string
    {
        $modulesDir = $this->getModulesDirectory();
        $path       = APPPATH . $modulesDir;

        if (! is_dir($path)) {
            if (mkdir($path, 0755, true)) {
                CLI::write("Created modules directory: {$modulesDir}", 'green');
            } else {
                CLI::error("Failed to create modules directory: {$modulesDir}");

                exit(1);
            }
        }

        return $path;
    }

    /**
     * Ensures a specific module directory exists.
     */
    protected function ensureModuleDirectory(string $moduleName): string
    {
        $modulesPath = $this->ensureModulesDirectory();
        $modulePath  = $modulesPath . DIRECTORY_SEPARATOR . $moduleName;

        if (! is_dir($modulePath)) {
            if (mkdir($modulePath, 0755, true)) {
                CLI::write("Created module directory: {$moduleName}", 'green');
            } else {
                CLI::error("Failed to create module directory: {$moduleName}");

                exit(1);
            }
        }

        return $modulePath;
    }

    /**
     * Gets template content with placeholders replaced.
     *
     * @param array<string, string> $placeholders
     */
    protected function getTemplate(string $templateFile, array $placeholders): string
    {
        $templatePath = __DIR__ . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR . $templateFile;

        if (! file_exists($templatePath)) {
            CLI::error("Template file not found: {$templateFile}");

            return '';
        }

        $content = file_get_contents($templatePath);
        if ($content === false) {
            CLI::error("Failed to read template file: {$templateFile}");

            return '';
        }

        foreach ($placeholders as $placeholder => $value) {
            $content = str_replace($placeholder, $value, $content);
        }

        return str_replace('<@php', '<?php', $content);
    }

    /**
     * Builds namespace for a module component.
     */
    protected function buildNamespace(string $moduleName, string $component): string
    {
        $namespaceBase = $this->getNamespaceBase();
        $classPrefix   = $this->getClassPrefix();

        if ($classPrefix !== '') {
            return $namespaceBase . '\\' . $classPrefix . '\\' . $moduleName . '\\' . $component;
        }

        return $namespaceBase . '\\' . $moduleName . '\\' . $component;
    }

    /**
     * Builds class name with prefix if configured.
     */
    protected function buildClassName(string $name, string $suffix = ''): string
    {
        helper('inflector');
        $className = pascalize($name);
        if ($suffix !== '') {
            $className .= pascalize($suffix);
        }

        return $className;
    }

    // =========================================================================
    // Sidebar helpers
    // =========================================================================

    /**
     * Resolves the StnConfig.php path to write to.
     * Priority: app/Config/StnConfig.php (user-published) > package Config/StnConfig.php.
     */
    protected function getStnConfigPath(): ?string
    {
        $appConfig = APPPATH . 'Config' . DIRECTORY_SEPARATOR . 'StnConfig.php';
        if (file_exists($appConfig)) {
            return $appConfig;
        }

        $pkgConfig = realpath(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'StnConfig.php');

        return $pkgConfig ?: null;
    }

    /**
     * Adds a sidebar entry for $moduleName to StnConfig.php.
     * Inserts the new item before the Logout entry (if present) or before the closing bracket.
     *
     * @param bool $isCrud When true, generates a parent+children CRUD dropdown entry.
     */
    protected function publishSidebar(string $moduleName, bool $isCrud = false): void
    {
        helper('inflector');
        $groupName  = strtolower(dasherize(underscore($moduleName)));
        $configPath = $this->getStnConfigPath();

        if ($configPath === null) {
            CLI::error('StnConfig.php not found. Run "php spark publish:config" first.');

            return;
        }

        $content = file_get_contents($configPath);
        if ($content === false) {
            CLI::error("Cannot read {$configPath}.");

            return;
        }

        // Guard: skip if entry already exists
        if (str_contains($content, "'/{$groupName}'") || str_contains($content, "\"/{$groupName}\"")) {
            CLI::write("Sidebar entry for '{$moduleName}' already exists — skipped.", 'yellow');

            return;
        }

        // For --crud: verify the module's table exists before writing the sidebar entry
        if ($isCrud) {
            $tablePrefix = $this->getTablePrefix();
            $tableName   = $tablePrefix . strtolower(underscore($moduleName));

            if (! db_connect()->tableExists($tableName)) {
                CLI::error("Table '{$tableName}' does not exist. Run the module migration first, then re-run: php spark module:sidebar {$moduleName} publish --crud");

                return;
            }

            CLI::write("Table '{$tableName}' found — proceeding.", 'cyan');
        }

        // Build entry string (indented 8 spaces to match surrounding $sidebars items)
        if ($isCrud) {
            $entry = "        // {$moduleName}\n"
                . "        [\n"
                . "            'icon'     => 'fas fa-users',\n"
                . "            'label'    => '{$moduleName}',\n"
                . "            'link'     => '#',\n"
                . "            'children' => [\n"
                . "                ['icon' => 'far fa-circle', 'label' => 'All {$moduleName}', 'link' => '/{$groupName}'],\n"
                . "                ['icon' => 'far fa-circle', 'label' => 'New {$moduleName}',  'link' => '/{$groupName}/create'],\n"
                . "            ],\n"
                . '        ],';
        } else {
            $entry = "        // {$moduleName}\n"
                . "        [\n"
                . "            'icon'  => 'fas fa-cog',\n"
                . "            'label' => '{$moduleName}',\n"
                . "            'link'  => '/{$groupName}',\n"
                . '        ],';
        }

        // Locate $sidebars array and find its closing bracket via bracket counting
        $declPos = strpos($content, 'public array $sidebars = [');
        if ($declPos === false) {
            CLI::error('Could not locate $sidebars declaration in StnConfig.php.');

            return;
        }

        $start = strpos($content, '[', $declPos);
        $depth = 0;
        $end   = $start;
        $len   = strlen($content);

        for ($i = $start; $i < $len; $i++) {
            if ($content[$i] === '[') {
                $depth++;
            } elseif ($content[$i] === ']') {
                $depth--;
                if ($depth === 0) {
                    $end = $i;
                    break;
                }
            }
        }

        // Determine insertion point: before the Logout item if found, else before closing ]
        $arrayContent = substr($content, $start, $end - $start);
        $logoutRelPos = strpos($arrayContent, "'/logout'");

        if ($logoutRelPos !== false) {
            // Walk back from '/logout' to find the opening [ of that array item
            $priorText   = substr($arrayContent, 0, $logoutRelPos);
            $itemOpenRel = strrpos($priorText, "\n        [");

            if ($itemOpenRel !== false) {
                // If there is a comment line immediately before the [, include it in the cut
                $beforeItem = substr($priorText, 0, $itemOpenRel);
                $commentRel = strrpos($beforeItem, "\n        // ");
                if ($commentRel !== false && ($itemOpenRel - $commentRel) < 120) {
                    $insertAt = $start + $commentRel + 1; // position right after the preceding \n
                } else {
                    $insertAt = $start + $itemOpenRel + 1;
                }
            } else {
                // Fallback: before closing ]
                $beforeClose = substr($content, 0, $end);
                $insertAt    = strrpos($beforeClose, "\n") + 1;
            }
        } else {
            // No Logout item found — insert before the closing ]
            $beforeClose = substr($content, 0, $end);
            $insertAt    = strrpos($beforeClose, "\n") + 1;
        }

        $newContent = substr($content, 0, $insertAt) . $entry . "\n" . substr($content, $insertAt);

        if (file_put_contents($configPath, $newContent) !== false) {
            $label = str_ends_with($configPath, 'app' . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'StnConfig.php')
                ? 'app/Config/StnConfig.php'
                : 'Config/StnConfig.php (package default — run "php spark publish:config" for a user-editable copy)';
            CLI::write("Sidebar entry for '{$moduleName}' published to {$label}.", 'green');

            // For --crud: auto-write CRUD routes after the sidebar is published
            if ($isCrud) {
                $this->writeCrudRoutes($moduleName);
            }
        } else {
            CLI::error("Failed to write to {$configPath}.");
        }
    }

    /**
     * Removes a sidebar entry for $moduleName from StnConfig.php.
     * Detects by the "// {ModuleName}" comment marker and removes the
     * following array block (handles both plain and nested CRUD entries).
     */
    protected function unpublishSidebar(string $moduleName): void
    {
        helper('inflector');
        $groupName  = strtolower(dasherize(underscore($moduleName)));
        $configPath = $this->getStnConfigPath();

        if ($configPath === null) {
            CLI::error('StnConfig.php not found.');

            return;
        }

        $content = file_get_contents($configPath);
        if ($content === false) {
            CLI::error("Cannot read {$configPath}.");

            return;
        }

        // Primary: find the comment marker "// {ModuleName}"
        $commentMarker = '// ' . $moduleName;
        $markerPos     = strpos($content, $commentMarker);

        if ($markerPos === false) {
            // Fallback: search by the link path value
            $linkNeedle = "'/{$groupName}'";
            $markerPos  = strpos($content, $linkNeedle);

            if ($markerPos === false) {
                CLI::write("No sidebar entry found for '{$moduleName}'.", 'yellow');

                return;
            }

            // Walk back to the start of the enclosing array item's opening line
            $priorItem = strrpos(substr($content, 0, $markerPos), "\n        [");
            $markerPos = ($priorItem !== false) ? $priorItem + 1 : $markerPos;
        }

        // Start of the line containing the marker
        $lineStart = strrpos(substr($content, 0, $markerPos), "\n");
        $lineStart = ($lineStart !== false) ? $lineStart : 0;

        // Find the opening [ of the array item after the marker
        $arrayStart = strpos($content, '[', $markerPos);
        if ($arrayStart === false) {
            CLI::error("Malformed sidebar entry for '{$moduleName}'.");

            return;
        }

        // Count brackets to find the matching ]
        $depth    = 0;
        $arrayEnd = $arrayStart;
        $len      = strlen($content);

        for ($i = $arrayStart; $i < $len; $i++) {
            if ($content[$i] === '[') {
                $depth++;
            } elseif ($content[$i] === ']') {
                $depth--;
                if ($depth === 0) {
                    $arrayEnd = $i;
                    break;
                }
            }
        }

        // Include trailing comma and the newline after it
        $removeEnd = $arrayEnd + 1;
        if (isset($content[$removeEnd]) && $content[$removeEnd] === ',') {
            $removeEnd++;
        }

        // Remove from the line-start of the comment to the end of the entry
        $newContent = substr($content, 0, $lineStart) . substr($content, $removeEnd);

        if (file_put_contents($configPath, $newContent) !== false) {
            CLI::write("Sidebar entry for '{$moduleName}' removed from StnConfig.php.", 'green');
        } else {
            CLI::error("Failed to write to {$configPath}.");
        }
    }

    // =========================================================================
    // CRUD route helper
    // =========================================================================

    /**
     * Writes (or verifies) the full CRUD route group for $moduleName.
     *
     * Uses route.crud.tpl.php when creating from scratch.
     * Skips silently when all CRUD routes already exist.
     */
    protected function writeCrudRoutes(string $moduleName): void
    {
        helper('inflector');
        $modulePath = $this->ensureModuleDirectory($moduleName);
        $configDir  = $modulePath . DIRECTORY_SEPARATOR . 'Config';
        $routesFile = $configDir . DIRECTORY_SEPARATOR . 'Routes.php';
        $groupName  = strtolower(dasherize(underscore($moduleName)));
        $namespace  = $this->buildNamespace($moduleName, 'Controllers');

        if (! is_dir($configDir)) {
            mkdir($configDir, 0755, true);
        }

        if (! file_exists($routesFile)) {
            $content = $this->getTemplate('route.crud.tpl.php', [
                '{groupName}' => $groupName,
                '{namespace}' => $namespace,
            ]);

            if ($content !== '' && file_put_contents($routesFile, $content) !== false) {
                CLI::write("CRUD routes file created: {$moduleName}/Config/Routes.php", 'green');
            } else {
                CLI::error("Failed to create CRUD routes for '{$moduleName}'.");
            }

            return;
        }

        // File exists — check whether CRUD routes are already present
        $existing = file_get_contents($routesFile);
        if ($existing === false) {
            CLI::error("Cannot read routes file: {$routesFile}");

            return;
        }

        // Detect by the store route which is unique to the CRUD template
        if (str_contains($existing, "'store'") || str_contains($existing, 'store')) {
            CLI::write("CRUD routes already present in '{$moduleName}/Config/Routes.php' — skipped.", 'yellow');

            return;
        }

        // Append CRUD routes into the existing group if the closing `});` marker is found
        $crudBlock = "        // CRUD routes\n"
            . "        \$routes->get('/',              'Index::index');\n"
            . "        \$routes->get('create',         'Index::create');\n"
            . "        \$routes->post('store',         'Index::store');\n"
            . "        \$routes->get('(:num)/edit',    'Index::edit/\$1');\n"
            . "        \$routes->post('(:num)/update', 'Index::update/\$1');\n"
            . "        \$routes->post('(:num)/delete', 'Index::delete/\$1');\n"
            . '    ';

        $insertAt = strrpos($existing, '});');
        if ($insertAt === false) {
            CLI::error("Cannot locate route group closure in '{$moduleName}/Config/Routes.php'.");

            return;
        }

        $newContent = substr_replace($existing, $crudBlock, $insertAt, 0);

        if (file_put_contents($routesFile, $newContent) !== false) {
            CLI::write("CRUD routes appended to '{$moduleName}/Config/Routes.php'.", 'green');
        } else {
            CLI::error("Failed to write CRUD routes to '{$moduleName}/Config/Routes.php'.");
        }
    }

    // =========================================================================
    // Child-item helpers
    // =========================================================================

    /**
     * Adds a child item to an existing parent sidebar entry for $moduleName.
     *
     * The parent entry must already exist (run `publish` first).
     * Duplicate links are silently skipped.
     */
    protected function addSidebarChild(string $moduleName, string $label, string $link, string $icon): void
    {
        if ($label === '' || $link === '') {
            CLI::error('child_label and child_link are required for add-child.');
            CLI::write('Example: php spark module:sidebar Blogs add-child "Archive" "/blogs/archive"', 'yellow');

            return;
        }

        $configPath = $this->getStnConfigPath();
        if ($configPath === null) {
            CLI::error('StnConfig.php not found.');

            return;
        }

        $content = file_get_contents($configPath);
        if ($content === false) {
            CLI::error("Cannot read {$configPath}.");

            return;
        }

        // Locate the parent entry via its comment marker
        $markerPos = strpos($content, '// ' . $moduleName);
        if ($markerPos === false) {
            CLI::error("No sidebar entry found for '{$moduleName}'. Run: php spark module:sidebar {$moduleName} publish");

            return;
        }

        // Find the 'children' => [ key within the parent entry
        $childrenKey = strpos($content, "'children' => [", $markerPos);
        if ($childrenKey === false) {
            CLI::error("Parent entry for '{$moduleName}' has no 'children' array. Re-publish with --crud or add the children key manually.");

            return;
        }

        // Guard: skip if this link already exists inside the children block
        $childrenOpen = strpos($content, '[', $childrenKey + strlen("'children' => "));
        // Find the matching closing ] of the children array
        $depth         = 0;
        $childrenClose = $childrenOpen;
        $len           = strlen($content);

        for ($i = $childrenOpen; $i < $len; $i++) {
            if ($content[$i] === '[') {
                $depth++;
            } elseif ($content[$i] === ']') {
                $depth--;
                if ($depth === 0) {
                    $childrenClose = $i;
                    break;
                }
            }
        }

        $childrenBlock = substr($content, $childrenOpen, $childrenClose - $childrenOpen);

        if (str_contains($childrenBlock, "'{$link}'") || str_contains($childrenBlock, "\"{$link}\"")) {
            CLI::write("Child link '{$link}' already exists in '{$moduleName}' sidebar — skipped.", 'yellow');

            return;
        }

        $newChild = "                ['icon' => '{$icon}', 'label' => '{$label}', 'link' => '{$link}'],\n";

        // Find the last non-whitespace position inside the children array to insert after
        $insertAt    = $childrenClose;
        $beforeClose = substr($content, $childrenOpen, $childrenClose - $childrenOpen);
        $lastNewline = strrpos($beforeClose, "\n");
        if ($lastNewline !== false) {
            $insertAt = $childrenOpen + $lastNewline + 1;
        }

        $newContent = substr($content, 0, $insertAt) . $newChild . substr($content, $insertAt);

        if (file_put_contents($configPath, $newContent) !== false) {
            CLI::write("Child item '{$label}' → '{$link}' added to '{$moduleName}' sidebar.", 'green');
        } else {
            CLI::error("Failed to write to {$configPath}.");
        }
    }

    /**
     * Removes a child item matched by label from the parent sidebar entry.
     */
    protected function removeSidebarChild(string $moduleName, string $label): void
    {
        if ($label === '') {
            CLI::error('child_label is required for remove-child.');
            CLI::write('Example: php spark module:sidebar Blogs remove-child "Archive"', 'yellow');

            return;
        }

        $configPath = $this->getStnConfigPath();
        if ($configPath === null) {
            CLI::error('StnConfig.php not found.');

            return;
        }

        $content = file_get_contents($configPath);
        if ($content === false) {
            CLI::error("Cannot read {$configPath}.");

            return;
        }

        // Match the child array line containing the given label
        $pattern    = '/[ \t]*\[\'icon\'\s*=>\s*\'[^\']*\'\s*,\s*\'label\'\s*=>\s*\'' . preg_quote($label, '/') . '\'[^\]]*\],?\r?\n/';
        $newContent = preg_replace($pattern, '', $content, 1, $count);

        if ($count === 0 || $newContent === null) {
            // Fallback: try double-quoted label
            $patternDq  = '/[ \t]*\["icon"\s*=>\s*"[^"]*"\s*,\s*"label"\s*=>\s*"' . preg_quote($label, '/') . '"[^\]]*\],?\r?\n/';
            $newContent = preg_replace($patternDq, '', $content, 1, $count);
        }

        if ($count === 0 || $newContent === null) {
            CLI::write("Child item '{$label}' not found in '{$moduleName}' sidebar.", 'yellow');

            return;
        }

        if (file_put_contents($configPath, $newContent) !== false) {
            CLI::write("Child item '{$label}' removed from '{$moduleName}' sidebar.", 'green');
        } else {
            CLI::error("Failed to write to {$configPath}.");
        }
    }

    /**
     * Updates fields on an existing parent sidebar entry for $moduleName.
     * Locates the entry via its "// {ModuleName}" comment marker.
     * At least one of $label, $link, $icon must be provided.
     */
    protected function updateSidebar(string $moduleName, ?string $label, ?string $link, ?string $icon): void
    {
        if ($label === null && $link === null && $icon === null) {
            CLI::error('Provide at least one option: --label, --link, or --icon.');
            CLI::write('Example: php spark module:sidebar Blogs update --label="My Blogs" --icon="fas fa-rss"', 'yellow');

            return;
        }

        $configPath = $this->getStnConfigPath();
        if ($configPath === null) {
            CLI::error('StnConfig.php not found. Run "php spark publish:config" first.');

            return;
        }

        $content = file_get_contents($configPath);
        if ($content === false) {
            CLI::error("Cannot read {$configPath}.");

            return;
        }

        $markerPos = strpos($content, '// ' . $moduleName);
        if ($markerPos === false) {
            CLI::write("No sidebar entry found for '{$moduleName}'.", 'yellow');

            return;
        }

        // Find the opening [ of the entry array after the marker
        $arrayStart = strpos($content, '[', $markerPos);
        if ($arrayStart === false) {
            CLI::error("Malformed sidebar entry for '{$moduleName}'.");

            return;
        }

        // Bracket-count to find the end of the parent entry
        $depth    = 0;
        $arrayEnd = $arrayStart;
        $len      = strlen($content);

        for ($i = $arrayStart; $i < $len; $i++) {
            if ($content[$i] === '[') {
                $depth++;
            } elseif ($content[$i] === ']') {
                $depth--;
                if ($depth === 0) {
                    $arrayEnd = $i;
                    break;
                }
            }
        }

        // Extract and patch the parent block only
        $block    = substr($content, $arrayStart, $arrayEnd - $arrayStart + 1);
        $newBlock = $block;

        if ($icon !== null) {
            $newBlock = preg_replace(
                "/'icon'\\s*=>\\s*'[^']*'/",
                "'icon' => '{$icon}'",
                $newBlock,
                1,
            );
        }

        if ($label !== null) {
            $newBlock = preg_replace(
                "/'label'\\s*=>\\s*'[^']*'/",
                "'label' => '{$label}'",
                $newBlock,
                1,
            );
        }

        if ($link !== null) {
            // Replace only the first 'link' occurrence (the parent's own link, not children)
            $newBlock = preg_replace(
                "/'link'\\s*=>\\s*'[^']*'/",
                "'link' => '{$link}'",
                $newBlock,
                1,
            );
        }

        if ($newBlock === null || $newBlock === $block) {
            CLI::write("No changes made to '{$moduleName}' sidebar entry.", 'yellow');

            return;
        }

        $newContent = substr($content, 0, $arrayStart) . $newBlock . substr($content, $arrayEnd + 1);

        if (file_put_contents($configPath, $newContent) !== false) {
            $configLabel = str_ends_with($configPath, 'app' . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'StnConfig.php')
                ? 'app/Config/StnConfig.php'
                : 'Config/StnConfig.php';
            CLI::write("Sidebar entry for '{$moduleName}' updated in {$configLabel}.", 'green');
        } else {
            CLI::error("Failed to write to {$configPath}.");
        }
    }

    /**
     * Updates fields on a child item inside an existing parent sidebar entry.
     * Locates the parent via its "// {ModuleName}" comment marker, then finds
     * the child by its current label. At least one of $newLabel, $link, $icon
     * must be provided.
     */
    protected function updateSidebarChild(string $moduleName, string $currentLabel, ?string $newLabel, ?string $link, ?string $icon): void
    {
        if ($currentLabel === '') {
            CLI::error('child_label (current label) is required for update-child.');
            CLI::write('Example: php spark module:sidebar Blogs update-child "All Blogs" --new-label="Archive" --link="/blogs/archive"', 'yellow');

            return;
        }

        if ($newLabel === null && $link === null && $icon === null) {
            CLI::error('Provide at least one option: --new-label, --link, or --icon.');
            CLI::write('Example: php spark module:sidebar Blogs update-child "All Blogs" --new-label="Archive" --link="/blogs/archive"', 'yellow');

            return;
        }

        $configPath = $this->getStnConfigPath();
        if ($configPath === null) {
            CLI::error('StnConfig.php not found.');

            return;
        }

        $content = file_get_contents($configPath);
        if ($content === false) {
            CLI::error("Cannot read {$configPath}.");

            return;
        }

        // Locate the parent entry marker
        $markerPos = strpos($content, '// ' . $moduleName);
        if ($markerPos === false) {
            CLI::error("No sidebar entry found for '{$moduleName}'. Run: php spark module:sidebar {$moduleName} publish");

            return;
        }

        // Locate the 'children' => [ key within the parent entry
        $childrenKey = strpos($content, "'children' => [", $markerPos);
        if ($childrenKey === false) {
            CLI::error("Sidebar entry for '{$moduleName}' has no 'children' array. Re-publish with --crud or add the children key manually.");

            return;
        }

        // Find the children array range via bracket counting
        $childrenOpen  = strpos($content, '[', $childrenKey + strlen("'children' => "));
        $depth         = 0;
        $childrenClose = $childrenOpen;
        $len           = strlen($content);

        for ($i = $childrenOpen; $i < $len; $i++) {
            if ($content[$i] === '[') {
                $depth++;
            } elseif ($content[$i] === ']') {
                $depth--;
                if ($depth === 0) {
                    $childrenClose = $i;
                    break;
                }
            }
        }

        $childrenBlock = substr($content, $childrenOpen, $childrenClose - $childrenOpen + 1);

        // Locate the child line by its current label
        $escapedLabel = preg_quote($currentLabel, '/');
        $pattern      = '/(?<=\n)([ \t]*\[\'icon\'\s*=>\s*\'[^\']*\'\s*,\s*\'label\'\s*=>\s*\'' . $escapedLabel . '\'[^\]]*\],?)/m';

        if (! preg_match($pattern, $childrenBlock, $match, PREG_OFFSET_CAPTURE)) {
            CLI::write("Child item '{$currentLabel}' not found in '{$moduleName}' sidebar.", 'yellow');

            return;
        }

        $childLine    = $match[1][0];
        $childLinePos = $match[1][1]; // offset within $childrenBlock

        $newChildLine = $childLine;

        if ($icon !== null) {
            $newChildLine = preg_replace("/'icon'\\s*=>\\s*'[^']*'/", "'icon' => '{$icon}'", $newChildLine, 1);
        }

        if ($newLabel !== null) {
            $newChildLine = preg_replace("/'label'\\s*=>\\s*'[^']*'/", "'label' => '{$newLabel}'", $newChildLine, 1);
        }

        if ($link !== null) {
            $newChildLine = preg_replace("/'link'\\s*=>\\s*'[^']*'/", "'link' => '{$link}'", $newChildLine, 1);
        }

        if ($newChildLine === null || $newChildLine === $childLine) {
            CLI::write("No changes made to child item '{$currentLabel}' in '{$moduleName}' sidebar.", 'yellow');

            return;
        }

        $newChildrenBlock = substr($childrenBlock, 0, $childLinePos) . $newChildLine . substr($childrenBlock, $childLinePos + strlen($childLine));
        $newContent       = substr($content, 0, $childrenOpen) . $newChildrenBlock . substr($content, $childrenClose + 1);

        if (file_put_contents($configPath, $newContent) !== false) {
            $displayLabel = $newLabel ?? $currentLabel;
            CLI::write("Child item '{$currentLabel}' → '{$displayLabel}' updated in '{$moduleName}' sidebar.", 'green');
        } else {
            CLI::error("Failed to write to {$configPath}.");
        }
    }

    /**
     * Lists all child items of the parent sidebar entry for $moduleName.
     */
    protected function listSidebarChildren(string $moduleName): void
    {
        $configPath = $this->getStnConfigPath();
        if ($configPath === null) {
            CLI::error('StnConfig.php not found.');

            return;
        }

        $content = file_get_contents($configPath);
        if ($content === false) {
            CLI::error("Cannot read {$configPath}.");

            return;
        }

        $markerPos = strpos($content, '// ' . $moduleName);
        if ($markerPos === false) {
            CLI::write("No sidebar entry found for '{$moduleName}'.", 'yellow');

            return;
        }

        $childrenKey = strpos($content, "'children' => [", $markerPos);
        if ($childrenKey === false) {
            CLI::write("Sidebar entry for '{$moduleName}' has no children (plain link item).", 'yellow');

            return;
        }

        $childrenOpen  = strpos($content, '[', $childrenKey + strlen("'children' => "));
        $depth         = 0;
        $childrenClose = $childrenOpen;
        $len           = strlen($content);

        for ($i = $childrenOpen; $i < $len; $i++) {
            if ($content[$i] === '[') {
                $depth++;
            } elseif ($content[$i] === ']') {
                $depth--;
                if ($depth === 0) {
                    $childrenClose = $i;
                    break;
                }
            }
        }

        $childrenBlock = substr($content, $childrenOpen + 1, $childrenClose - $childrenOpen - 1);

        // Extract each child: ['icon' => '...', 'label' => '...', 'link' => '...']
        preg_match_all(
            '/\[\'icon\'\s*=>\s*\'([^\']*)\'\s*,\s*\'label\'\s*=>\s*\'([^\']*)\'\s*,\s*\'link\'\s*=>\s*\'([^\']*)\'\]/',
            $childrenBlock,
            $matches,
            PREG_SET_ORDER,
        );

        if (empty($matches)) {
            CLI::write("No children found for '{$moduleName}' sidebar.", 'yellow');

            return;
        }

        CLI::write("Sidebar children for '{$moduleName}':", 'cyan');
        CLI::newLine();

        foreach ($matches as $idx => $m) {
            $num   = str_pad((string) ($idx + 1), 2, ' ', STR_PAD_LEFT);
            $icon  = str_pad($m[1], 20);
            $label = str_pad($m[2], 20);
            $link  = $m[3];
            CLI::write("  {$num}. [{$icon}] {$label} → {$link}");
        }
    }
}
