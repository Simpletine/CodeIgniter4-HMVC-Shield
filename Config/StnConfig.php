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

namespace Config;

use CodeIgniter\Config\BaseConfig;

class StnConfig extends BaseConfig
{
    public array $sidebars = [
        // General Item Sample
        [
            'label'      => 'Dashboard',
            'attributes' => [
                'class' => 'nav-item',
            ],
            'icon_class' => 'fas fa-tachometer-alt',
            'anchor'     => [
                'href'  => '/',
                'class' => 'nav-link',
            ],
        ],
        // Dropdown Items Sample
        [
            'label'      => 'Members',
            'attributes' => [
                'class' => 'nav-item',
            ],
            'icon_class' => 'fas fa-users',
            'anchor'     => [
                'href'  => '#',
                'class' => 'nav-link',
            ],
            'dropdown_items' => [
                [
                    'label'      => 'Members',
                    'icon_class' => 'far fa-circle',
                    'anchor'     => [
                        'href'  => '/members',
                        'class' => 'nav-link',
                    ],
                ],
                [
                    'label'      => 'New Member',
                    'icon_class' => 'far fa-circle',
                    'anchor'     => [
                        'href'  => '/members/new',
                        'class' => 'nav-link',
                    ],
                ],
            ],
        ],
        [
            'label'      => 'Logout',
            'attributes' => [
                'class' => 'nav-item',
            ],
            'icon_class' => 'fas fas fa-sign-out-alt',
            'anchor'     => [
                'href'  => '/logout',
                'class' => 'nav-link bg-danger',
            ],
        ],
    ];
}
