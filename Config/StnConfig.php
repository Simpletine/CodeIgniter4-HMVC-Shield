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

/**
 * SimpleTine UI configuration.
 *
 * Publish this file to app/Config/StnConfig.php via `php spark publish:config`
 * and customise freely. The app-level copy takes precedence over the
 * package-bundled default automatically through CI4's config cascade.
 *
 * -------------------------------------------------------------------------
 * SIDEBAR FORMAT (v1.4+)
 * -------------------------------------------------------------------------
 * Each sidebar item is a simple associative array:
 *
 *   // Plain link
 *   ['icon' => 'fas fa-home', 'label' => 'Dashboard', 'link' => '/']
 *
 *   // Dropdown (children key)
 *   ['icon' => 'fas fa-users', 'label' => 'Users', 'link' => '#', 'children' => [
 *       ['icon' => 'far fa-circle', 'label' => 'All Users',  'link' => '/users'],
 *       ['icon' => 'fas fa-cog', 'label' => 'New User',   'link' => '/users/new'],
 *   ]]
 *
 * BACKWARD COMPATIBILITY:
 *   Items using the legacy format (containing an 'anchor' key) are still
 *   rendered correctly. New and old items may coexist in the same array.
 * -------------------------------------------------------------------------
 */
class StnConfig extends BaseConfig
{
    // =========================================================================
    // Branding
    // =========================================================================

    /**
     * Application / company name displayed in the navbar brand and browser title.
     */
    public string $appName = 'SimpleTine';

    /**
     * Redirect target after a successful login or when an authenticated user
     * visits the root path ("/"). Publish app/Config/StnConfig.php and
     * change this to suit your project layout.
     */
    public string $homeRedirect = '/admin';

    /**
     * Path to the brand logo image (relative to webroot or absolute URL).
     * Leave empty to hide the logo and show only text.
     */
    public string $appLogo = '/assets/simpletine/img/AdminLTELogo.png';

    /**
     * Footer copyright text (supports basic HTML).
     * Change the year and company name after publishing to app/Config/StnConfig.php.
     */
    public string $footerCopyright = '&copy; 2026 SimpleTine. All rights reserved.';

    /**
     * Optional version string shown in the footer (e.g. "v1.4.0"). Leave empty to hide.
     */
    public string $footerVersion = '';

    // =========================================================================
    // Sidebar
    // =========================================================================

    /**
     * Sidebar navigation items (new format).
     * Supports 'children' for nested dropdowns.
     *
     * @var array<int, array<string, mixed>>
     */
    public array $sidebars = [
        // Plain item
        [
            'icon'  => 'fas fa-tachometer-alt',
            'label' => 'Dashboard',
            'link'  => '/',
        ],
        // Dropdown item
        [
            'icon'     => 'fas fa-users',
            'label'    => 'Users',
            'link'     => '#',
            'children' => [
                ['icon' => 'far fa-circle', 'label' => 'All Users', 'link' => '/users'],
                ['icon' => 'far fa-circle', 'label' => 'New User', 'link' => '/users/create'],
            ],
        ],
        // Logout (plain item)
        [
            'icon'       => 'fas fa-sign-out-alt',
            'label'      => 'Logout',
            'link'       => '/logout',
            'link_class' => 'nav-link bg-danger',
        ],
    ];

    // =========================================================================
    // Navbar — Left
    // =========================================================================

    /**
     * Left-side navbar items.
     * Uses the same structure as $sidebars (icon / label / link / children).
     * The sidebar-toggle button is always prepended automatically.
     *
     * @var array<int, array<string, mixed>>
     */
    public array $navbarLeft = [
        [
            'icon'  => 'fas fa-home',
            'label' => 'Home',
            'link'  => '/',
        ],
    ];

    // =========================================================================
    // Navbar — Right feature toggles
    // =========================================================================

    /**
     * Controls which built-in right-side navbar widgets are rendered.
     * Set any key to false to disable that widget entirely.
     *
     * @var array<string, bool>
     */
    public array $navbarRight = [
        'show_search'        => true,
        'show_notifications' => true,
        'show_messages'      => true,
        'show_fullscreen'    => true,
        'show_control_panel' => true,
        'show_user_menu'     => true,
    ];
}
