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

/**
 * Manages storage and retrieval of HMVC configuration preferences.
 * Stores namespace patterns, class prefixes, table prefixes, and other setup preferences.
 */
class ConfigStorage
{
    /**
     * Path to the configuration storage file.
     */
    private string $configPath;

    /**
     * Default configuration structure.
     *
     * @var array<string, mixed>
     */
    private array $defaultConfig = [
        'namespace_base'    => 'App\\Modules',
        'class_prefix'      => '',
        'table_prefix'      => 'st_',
        'modules_directory' => 'Modules',
        'base_controller'   => 'App\\Controllers\\BaseController',
        'base_model'        => 'CodeIgniter\\Model',
        'created_at'        => null,
        'updated_at'        => null,
    ];

    public function __construct()
    {
        $this->configPath = WRITEPATH . 'simpletine_config.json';
    }

    /**
     * Loads configuration from storage file.
     *
     * @return array<string, mixed>
     */
    public function load(): array
    {
        if (! file_exists($this->configPath)) {
            return $this->defaultConfig;
        }

        $content = file_get_contents($this->configPath);
        if ($content === false) {
            return $this->defaultConfig;
        }

        $config = json_decode($content, true);
        if (! is_array($config)) {
            return $this->defaultConfig;
        }

        return array_merge($this->defaultConfig, $config);
    }

    /**
     * Saves configuration to storage file.
     *
     * @param array<string, mixed> $config Configuration data to save
     */
    public function save(array $config): bool
    {
        $config['updated_at'] = date('Y-m-d H:i:s');
        if (! isset($config['created_at'])) {
            $config['created_at'] = date('Y-m-d H:i:s');
        }

        $json = json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            return false;
        }

        $dir = dirname($this->configPath);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return file_put_contents($this->configPath, $json) !== false;
    }

    /**
     * Checks if configuration exists.
     */
    public function exists(): bool
    {
        return file_exists($this->configPath);
    }

    /**
     * Gets a specific configuration value.
     *
     * @return mixed
     */
    public function get(string $key, mixed $default = null)
    {
        $config = $this->load();

        return $config[$key] ?? $default;
    }

    /**
     * Sets a specific configuration value and saves.
     */
    public function set(string $key, mixed $value): bool
    {
        $config               = $this->load();
        $config[$key]         = $value;
        $config['updated_at'] = date('Y-m-d H:i:s');

        return $this->save($config);
    }

    /**
     * Resets configuration to defaults.
     */
    public function reset(): bool
    {
        return $this->save($this->defaultConfig);
    }
}
