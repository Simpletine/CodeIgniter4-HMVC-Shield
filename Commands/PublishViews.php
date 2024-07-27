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
 * Clones an existing module and renames it to a new module.
 */
class PublishViews extends BaseCommand
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
    protected $name = 'publish:views';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Replaces configuration values in a specified file.';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'publish:views';

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
     * Execute the command.
     */
    public function run(array $params)
    {
        $filePath = APPPATH . 'Config/Auth.php';
        $search   = [
            '\CodeIgniter\Shield\Views\login',
            '\CodeIgniter\Shield\Views\register',
        ];

        $authConfig = config('Simpletine\HMVCShield\Config\Auth');
        $replace    = $authConfig->views;

        if (file_exists($filePath)) {
            $content = file_get_contents($filePath);
            $content = str_replace($search, $replace, $content);

            if (file_put_contents($filePath, $content) !== false) {
                CLI::write('Configuration values replaced successfully.', 'green');
            } else {
                CLI::error('Failed to write to the configuration file.');
            }
        } else {
            CLI::error('Configuration file not found.');
        }
    }
}
