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
    protected $description = 'Setup SimpleTine with optional database creation and Shield setup.';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'simpletine:setup';

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
        CLI::newLine(3);

        $asciiArt = <<<'EOT'
            ====================================================================================================================

                 _       __     __                             ______         _____ _                 __   _______
                | |     / /__  / /________  ____ ___  ___     /_  __/___     / ___/(_)___ ___  ____  / /__/_  __(_)___  ___
                | | /| / / _ \/ / ___/ __ \/ __ `__ \/ _ \     / / / __ \    \__ \/ / __ `__ \/ __ \/ / _ \/ / / / __ \/ _ \
                | |/ |/ /  __/ / /__/ /_/ / / / / / /  __/    / / / /_/ /   ___/ / / / / / / / /_/ / /  __/ / / / / / /  __/
                |__/|__/\___/_/\___/\____/_/ /_/ /_/\___/    /_/  \____/   /____/_/_/ /_/ /_/ .___/_/\___/_/ /_/_/ /_/\___/
                                                                                           /_/

            ====================================================================================================================
            EOT;

        CLI::write($asciiArt, 'yellow');
        CLI::newLine(2);

        // Ask for database setup
        $create_database = CLI::prompt('Do you need to create a new database?', ['y', 'n']);

        if (strtolower($create_database) === 'y') {
            $database_name = CLI::prompt('Enter the database name');
            CLI::write("Creating database '{$database_name}'...");
            $this->call('db:create', [$database_name]);
        } else {
            CLI::write('Skipping database creation.');
        }

        $shield_confirmation = CLI::prompt('Do you want to install shield?', ['y', 'n']);
        if (strtolower($shield_confirmation) === 'y') {
            CLI::write('Running Shield setup...');
            $this->call('shield:setup');
            CLI::write('Continue with simpletine setup...');
        } else {
            CLI::write('Skipping shield commands.');
        }

        $publish_confirmation = CLI::prompt('Do you want to publish assets, views, and config?', ['y', 'n']);
        if (strtolower($publish_confirmation) === 'y') {
            CLI::write('Publishing assets...');
            $this->call('publish:assets');
            CLI::write('Publishing views...');
            $this->call('publish:views');
            CLI::write('Publishing config...');
            $this->call('publish:config');
        } else {
            CLI::write('Skipping publish commands.');
        }

        CLI::write('SimpleTine setup completed.');
    }
}
