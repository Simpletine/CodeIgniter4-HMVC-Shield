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
 * Manages sidebar entries in StnConfig.php for individual modules.
 *
 * Supports publishing (adding) and unpublishing (removing) sidebar items
 * without touching routes or other configuration.
 *
 * Config resolution order:
 *   1. app/Config/StnConfig.php  (user-published — preferred)
 *   2. package Config/StnConfig.php (package default — fallback)
 *
 * Run `php spark publish:config` first to obtain a user-editable copy.
 */
class ModuleSidebar extends BaseModuleCommand
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
    protected $name = 'module:sidebar';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Publish or unpublish a module\'s sidebar entry in StnConfig.php.';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'module:sidebar <module> <action> [child_label] [child_link] [--crud] [--icon=<icon_class>] [--label=<label>] [--link=<link>] [--new-label=<new_label>]';

    /**
     * The Command's Arguments
     *
     * @var array<string, string>
     */
    protected $arguments = [
        'module'      => 'The name of the module',
        'action'      => 'Action: publish, unpublish, update, add-child, remove-child, update-child, list',
        'child_label' => '[add-child / remove-child / update-child] Current display label of the child item (used for locating)',
        'child_link'  => '[add-child] URL path for the child item (e.g. /blogs/archive)',
    ];

    /**
     * The Command's Options
     *
     * @var array<string, string>
     */
    protected $options = [
        '--crud'      => '[publish] Verify DB table exists, write All/New children and CRUD routes automatically.',
        '--icon'      => '[add-child / update / update-child] FontAwesome icon class (default for add-child: "far fa-circle").',
        '--label'     => '[update] New display label for the parent sidebar entry.',
        '--link'      => '[update / update-child] New URL path for the parent entry or the matched child item.',
        '--new-label' => '[update-child] New display label for the matched child item.',
    ];

    /**
     * Execute the command.
     *
     * @param list<string> $params
     */
    public function run(array $params): void
    {
        $moduleName = array_shift($params);
        $action     = strtolower((string) array_shift($params));

        if (! $moduleName || ! $action) {
            CLI::error('Module name and action are required.');
            CLI::newLine();
            $this->showHelp();

            return;
        }

        match ($action) {
            'publish'   => $this->publishSidebar($moduleName, CLI::getOption('crud') !== null),
            'unpublish' => $this->unpublishSidebar($moduleName),
            'update'    => $this->updateSidebar(
                $moduleName,
                CLI::getOption('label'),
                CLI::getOption('link'),
                CLI::getOption('icon'),
            ),
            'add-child' => $this->addSidebarChild(
                $moduleName,
                (string) ($params[0] ?? ''),
                (string) ($params[1] ?? ''),
                CLI::getOption('icon') ?? 'far fa-circle',
            ),
            'remove-child' => $this->removeSidebarChild($moduleName, (string) ($params[0] ?? '')),
            'update-child' => $this->updateSidebarChild(
                $moduleName,
                (string) ($params[0] ?? ''),
                CLI::getOption('new-label'),
                CLI::getOption('link'),
                CLI::getOption('icon'),
            ),
            'list'  => $this->listSidebarChildren($moduleName),
            default => CLI::error("Unknown action: {$action}. Use: publish, unpublish, update, add-child, remove-child, update-child, list"),
        };
    }
}
