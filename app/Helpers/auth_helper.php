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

use CodeIgniter\I18n\Time;
use CodeIgniter\Shield\Entities\User;

if (! function_exists('user')) {
    /**
     * Retrieves the currently logged-in user.
     *
     * @return User|null Returns the currently logged-in user object, or null if not logged in.
     */
    function user()
    {
        $auth = service('auth');

        return $auth->user();
    }
}

if (! function_exists('username')) {
    /**
     * Retrieves the username of the currently logged-in user and capitalizes the first letter.
     *
     * @return string|null Returns the capitalized username of the current user, or null if not logged in.
     */
    function username()
    {
        $user = user();

        return $user ? ucfirst($user->username) : null;
    }
}

if (! function_exists('email')) {
    /**
     * Retrieves the email of the currently logged-in user.
     *
     * @return string|null Returns the email of the current user, or null if not logged in.
     */
    function email()
    {
        $user = user();

        return $user ? $user->email : null;
    }
}

if (! function_exists('last_login')) {
    /**
     * Retrieves the last login information for user.
     */
    function last_login(string $text = '')
    {
        $logins = model(CodeIgniter\Shield\Models\LoginModel::class);
        $dates  = $logins->lastLogin(user())->date;
        $date   = '';

        foreach ($dates as $row) {
            $date = $row->date;
        }

        return $text . Time::parse($date)->format('d M Y H:i:s');
    }
}
