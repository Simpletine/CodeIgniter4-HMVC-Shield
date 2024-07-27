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

namespace Simpletine\HMVCShield\Config;

use CodeIgniter\Shield\Config\Auth as ShieldAuth;

class Auth extends ShieldAuth
{
    /**
     * --------------------------------------------------------------------
     * View files for Shield
     * --------------------------------------------------------------------
     */
    public array $views = [
        'login'    => HMVCSHIELDVIEWS . 'auth\login',
        'register' => HMVCSHIELDVIEWS . 'auth\register',
    ];
}
