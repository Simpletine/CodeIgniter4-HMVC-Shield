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

class SimpleTine extends BaseCommand
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
    protected $name = 'simpletine';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Quick look about SimpleTine.';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'welcome';

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
        CLI::newLine(3);

        CLI::write('-------------------------------------', 'cyan');
        CLI::write('Commands available and functionality:', 'cyan');
        CLI::write('-------------------------------------', 'cyan');
        CLI::newLine(1);

        CLI::write('For General Module', 'yellow');
        CLI::write('php spark module:create Blogs', 'blue');
        CLI::write('- Create a new module named `Blogs`', 'white');
        CLI::newLine(1);

        CLI::write('php spark module:copy Blogs Items', 'blue');
        CLI::write('- Clone an existing module of `Blogs` and renamed to `Items`', 'white');
        CLI::newLine(1);

        CLI::write('php spark module:controller Blogs Categories', 'blue');
        CLI::write('- Create a controller for the module `Blogs` named `Categories.php`', 'white');
        CLI::newLine(1);

        CLI::write('php spark module:model Blogs Categories', 'blue');
        CLI::write('- Create a model for the module `Blogs` named `Categories.php`', 'white');
        CLI::newLine(1);

        CLI::newLine(2);
        CLI::write('For Publisher', 'yellow');
        CLI::write('php spark publish:assets', 'blue');
        CLI::write('- Publish required assets to `public` folder', 'white');
        CLI::newLine(1);

        CLI::write('php spark publish:views', 'blue');
        CLI::write('- Publish views with `AdminLTE` to `Config/Auth.php`', 'white');
        CLI::newLine(1);

        CLI::newLine(2);
        CLI::write('For Admin', 'yellow');
        CLI::write('php spark module:create Admin --admin', 'blue');
        CLI::write('- Create a new module named `Admin` with `AdminLTE`', 'white');
        CLI::newLine(1);
        CLI::write('php spark module:controller Admin Users --admin', 'blue');
        CLI::write('- Create a new controller and view for `Admin` module with `AdminLTE`', 'white');
        CLI::newLine(2);
    }
}
