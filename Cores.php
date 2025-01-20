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

if (! function_exists('stn_config')) {
    /**
     * Get configuration from the given file, or default values if not found.
     *
     * @param string      $name     The configuration file name.
     * @param string|null $property The configuration property name.
     */
    function stn_config(string $name, ?string $property = null)
    {
        if (class_exists("Config\\{$name}")) {
            $config = config($name);
        } else {
            $config = config(HMVCSHIELD_CONFIG . $name);
        }

        return $config->{$property} ?? $config;
    }
}
