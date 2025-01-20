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
    // Brand site variable
    public string $site_name           = 'SimpleTine';
    public string $site_url            = 'http://simpletine.com';
    public array $site_name_attributes = [
        'class' => 'brand-text font-weight-light',
    ];
    public array $site_logo_panel = [
        'src'   => '/assets/simpletine/img/AdminLTELogo.png',
        'class' => 'brand-image img-circle elevation-3',
        'alt'   => 'Site Logo',
        'style' => 'opacity: .8',
    ];
    public string $site_route_to         = 'admin';
    public array $site_anchor_attributes = [
        'class' => 'brand-link',
    ];
    public array $sidebar_user_panel = [
        'show_panel'  => true,
        'panel_image' => [
            'src'   => '/assets/simpletine/img/user2-160x160.jpg',
            'class' => 'img-circle elevation-2',
            'alt'   => 'User Image',
        ],
        'panel_html' => null,
    ];
    public array $sidebar_search_panel = [
        'show_panel'  => true,
        'search_icon' => [
            'class' => 'fas fa-search fa-fw',
        ],
        'panel_html' => null,
    ];
    public array $sidebars = [ // Sting as HTML
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
    public array $sidebar_control_panel = [
        'show_panel' => false,
        'panel_html' => null,
    ];
    public array $footer = [
        'show_footer'       => true,
        'footer_attributes' => [
            'class' => 'main-footer',
        ],
        'footer_html' => null,
    ];
}
