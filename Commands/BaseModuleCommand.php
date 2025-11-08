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
     * Gets the modules directory path from configuration.
     */
    protected function getModulesDirectory(): string
    {
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
        $path      = APPPATH . $modulesDir;

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
}

