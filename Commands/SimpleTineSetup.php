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
 * Sets up SimpleTine HMVC environment with configuration collection and storage.
 * Collects namespace patterns, class prefixes, and other preferences for module generation.
 */
class SimpleTineSetup extends BaseCommand
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
    protected $name = 'simpletine:setup';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Setup SimpleTine with configuration collection and optional database/Shield setup.';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'simpletine:setup [--skip-config]';

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
    protected $options = [
        '--skip-config' => 'Skip configuration collection and use existing or defaults',
        '--skip-db'     => 'Skip database creation step',
        '--skip-shield' => 'Skip Shield installation step',
        '--only-publish' => 'Only execute publish steps (assets, views, config)',
    ];

    /**
     * Configuration storage instance.
     */
    private ?ConfigStorage $configStorage = null;

    /**
     * Gets the configuration storage instance, creating it if necessary.
     */
    private function getConfigStorage(): ConfigStorage
    {
        if ($this->configStorage === null) {
            $this->configStorage = new ConfigStorage();
        }

        return $this->configStorage;
    }

    /**
     * Execute the command.
     *
     * @param array<string> $params
     */
    public function run(array $params): void
    {
        CLI::newLine();
        CLI::write('-------------------------------------', 'cyan');
        CLI::write('SimpleTine HMVC Shield - Setup', 'cyan');
        CLI::write('-------------------------------------', 'cyan');
        CLI::newLine();

        $onlyPublish = CLI::getOption('only-publish') !== null;
        $skipDb     = CLI::getOption('skip-db') !== null;
        $skipShield = CLI::getOption('skip-shield') !== null;

        if ($onlyPublish) {
            // Only execute publish steps
            $this->executePublishSteps();
        } else {
            // Full setup process
            // Configuration collection
            $skipConfig = CLI::getOption('skip-config') !== null;
            if (! $skipConfig) {
                $this->collectConfiguration();
            }

            // Database setup
            if (! $skipDb) {
                $this->executeDatabaseSetup();
            } else {
                CLI::write('Skipping database creation (--skip-db).', 'yellow');
            }

            // Shield setup
            if (! $skipShield) {
                $this->executeShieldSetup();
            } else {
                CLI::write('Skipping Shield installation (--skip-shield).', 'yellow');
            }

            // Publish assets, views, and config
            $this->executePublishSteps();
        }

        CLI::newLine();
        CLI::write('SimpleTine setup completed successfully!', 'green');
        CLI::newLine();
    }

    /**
     * Collects configuration preferences from user input.
     */
    private function collectConfiguration(): void
    {
        CLI::write('=== Configuration Setup ===', 'cyan');
        CLI::newLine();

        // Check for existing configuration
        $configStorage = $this->getConfigStorage();
        if ($configStorage->exists()) {
            $existingConfig = $configStorage->load();
            CLI::write('Found existing configuration:', 'yellow');
            CLI::write('  Namespace Base: ' . ($existingConfig['namespace_base'] ?? 'N/A'), 'white');
            CLI::write('  Class Prefix: ' . ($existingConfig['class_prefix'] ?? 'N/A'), 'white');
            CLI::write('  Table Prefix: ' . ($existingConfig['table_prefix'] ?? 'N/A'), 'white');
            CLI::write('  Modules Directory: ' . ($existingConfig['modules_directory'] ?? 'N/A'), 'white');
            CLI::newLine();

            $useExisting = CLI::prompt('Do you want to use this existing configuration?', ['y', 'n']);
            if (strtolower($useExisting) === 'y') {
                CLI::write('Using existing configuration.', 'green');
                CLI::newLine();

                return;
            }
        }

        // Collect new configuration
        CLI::write('Please provide the following configuration:', 'cyan');
        CLI::write('(Press Enter to use default values)', 'yellow');
        CLI::newLine();

        $namespaceBase = $this->promptWithDefault(
            'Namespace base for modules',
            'App\\Modules',
            fn ($value) => $this->validateNamespace($value, 'Namespace base must be a valid namespace (e.g., App\\Modules)')
        );
        $classPrefix  = $this->promptWithDefault(
            'Class prefix (optional, press Enter to skip)',
            '',
            fn ($value) => $value === '' || $this->validateClassName($value, 'Class prefix must be a valid class name')
        );
        $tablePrefix  = $this->promptWithDefault(
            'Database table prefix',
            'st_',
            fn ($value) => $this->validateTablePrefix($value, 'Table prefix must contain only letters, numbers, and underscores')
        );
        $modulesDir   = $this->promptWithDefault(
            'Modules directory name',
            'Modules',
            fn ($value) => $this->validateDirectoryName($value, 'Directory name must be a valid directory name')
        );
        $baseController = $this->promptWithDefault(
            'Base controller class',
            'App\\Controllers\\BaseController',
            fn ($value) => $this->validateNamespace($value, 'Base controller must be a valid class name with namespace')
        );
        $baseModel     = $this->promptWithDefault(
            'Base model class',
            'CodeIgniter\\Model',
            fn ($value) => $this->validateNamespace($value, 'Base model must be a valid class name with namespace')
        );

        $config = [
            'namespace_base'    => $namespaceBase,
            'class_prefix'      => $classPrefix,
            'table_prefix'      => $tablePrefix,
            'modules_directory' => $modulesDir,
            'base_controller'   => $baseController,
            'base_model'        => $baseModel,
        ];

        if ($configStorage->save($config)) {
            CLI::write('Configuration saved successfully!', 'green');
        } else {
            CLI::error('Failed to save configuration.');
        }

        CLI::newLine();
    }

    /**
     * Prompts for input with a default value and validation, avoiding validation rule parsing issues.
     * Uses CLI::input() directly instead of CLI::prompt() to bypass validation.
     * Repeats the prompt if validation fails.
     *
     * @param callable|null $validator Optional validation function that returns true if valid, or error message string if invalid
     * @return string The user input or default value if empty
     */
    private function promptWithDefault(string $label, string $default, ?callable $validator = null): string
    {
        while (true) {
            CLI::write("{$label} (default: {$default})", 'white');
            $input = trim(CLI::input());

            // Use default if empty
            if ($input === '') {
                return $default;
            }

            // Validate if validator provided
            if ($validator !== null) {
                $result = $validator($input);
                if ($result === true) {
                    return $input;
                }

                // $result contains error message
                CLI::error($result);
                CLI::newLine();
                continue;
            }

            return $input;
        }
    }

    /**
     * Validates a namespace format.
     */
    private function validateNamespace(string $value, string $errorMessage): bool|string
    {
        // Namespace should contain backslashes and valid characters
        if (! preg_match('/^[a-zA-Z_][a-zA-Z0-9_\\\\]*$/', $value)) {
            return $errorMessage;
        }

        // Should contain at least one backslash for namespace
        if (strpos($value, '\\') === false) {
            return $errorMessage;
        }

        return true;
    }

    /**
     * Validates a class name format.
     */
    private function validateClassName(string $value, string $errorMessage): bool|string
    {
        if (! preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $value)) {
            return $errorMessage;
        }

        return true;
    }

    /**
     * Validates a table prefix format.
     */
    private function validateTablePrefix(string $value, string $errorMessage): bool|string
    {
        if (! preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $value)) {
            return $errorMessage;
        }

        return true;
    }

    /**
     * Validates a directory name format.
     */
    private function validateDirectoryName(string $value, string $errorMessage): bool|string
    {
        // Directory name should not contain path separators or invalid characters
        if (preg_match('/[\/\\\\<>:"|?*\x00-\x1f]/', $value)) {
            return $errorMessage;
        }

        if (trim($value) === '') {
            return $errorMessage;
        }

        return true;
    }

    /**
     * Executes database setup step with error handling.
     */
    private function executeDatabaseSetup(): void
    {
        $createDatabase = CLI::prompt('Do you need to create a new database?', ['y', 'n']);
        if (strtolower($createDatabase) === 'y') {
            $databaseName = CLI::prompt('Enter the database name');
            CLI::write("Creating database '{$databaseName}'...", 'green');
            try {
                $this->call('db:create', [$databaseName]);
                CLI::write("Database '{$databaseName}' created successfully.", 'green');
            } catch (\Exception $e) {
                CLI::error("Failed to create database: {$e->getMessage()}");
                CLI::write('You can retry this step later or create the database manually.', 'yellow');
                CLI::newLine();
            }
        } else {
            CLI::write('Skipping database creation.', 'yellow');
        }
    }

    /**
     * Executes Shield setup step with error handling.
     */
    private function executeShieldSetup(): void
    {
        $shieldConfirmation = CLI::prompt('Do you want to install Shield?', ['y', 'n']);
        if (strtolower($shieldConfirmation) === 'y') {
            CLI::write('Running Shield setup...', 'green');
            try {
                $this->call('shield:setup');
                CLI::write('Shield setup completed.', 'green');
            } catch (\Exception $e) {
                CLI::error("Shield setup failed: {$e->getMessage()}");
                CLI::write('You can retry this step later by running: php spark shield:setup', 'yellow');
                CLI::newLine();
            }
        } else {
            CLI::write('Skipping Shield installation.', 'yellow');
        }
    }

    /**
     * Executes publish steps (assets, views, config) with error handling.
     * Can be called independently for recovery from failures.
     */
    private function executePublishSteps(): void
    {
        $publishConfirmation = CLI::prompt('Do you want to publish assets, views, and config?', ['y', 'n']);
        if (strtolower($publishConfirmation) !== 'y') {
            CLI::write('Skipping publish commands.', 'yellow');

            return;
        }

        $steps = [
            'assets' => 'publish:assets',
            'views'  => 'publish:views',
            'config' => 'publish:config',
        ];

        $failedSteps = [];

        foreach ($steps as $stepName => $command) {
            CLI::write("Publishing {$stepName}...", 'green');
            try {
                $this->call($command);
                CLI::write("{$stepName} published successfully.", 'green');
            } catch (\Exception $e) {
                CLI::error("Failed to publish {$stepName}: {$e->getMessage()}");
                $failedSteps[] = $stepName;
            }
            CLI::newLine();
        }

        if (! empty($failedSteps)) {
            CLI::newLine();
            CLI::write('Some publish steps failed:', 'yellow');
            foreach ($failedSteps as $step) {
                CLI::write("  - {$step}", 'yellow');
            }
            CLI::newLine();
            CLI::write('To retry failed steps, you can:', 'cyan');
            CLI::write('  1. Run individual commands:', 'white');
            foreach ($failedSteps as $step) {
                CLI::write("     php spark {$steps[$step]}", 'white');
            }
            CLI::write('  2. Or run setup again with --only-publish option:', 'white');
            CLI::write('     php spark simpletine:setup --only-publish', 'white');
            CLI::newLine();
        }
    }
}
