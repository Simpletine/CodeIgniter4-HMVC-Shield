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

namespace Simpletine\HMVCShield\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Handles the root path ("/").
 * Unauthenticated users are redirected to the login page.
 * Authenticated users are redirected to StnConfig::$homeRedirect (default: /admin).
 */
class HomeController extends Controller
{
    public function index(): RedirectResponse
    {
        if (! user_id()) {
            return redirect()->route('login');
        }

        $homeRedirect = config('StnConfig')->homeRedirect ?? '/admin';

        return redirect()->to($homeRedirect);
    }
}
