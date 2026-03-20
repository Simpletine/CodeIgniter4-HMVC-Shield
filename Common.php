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
use CodeIgniter\Shield\Models\LoginModel;
use Simpletine\HMVCShield\Config\HMVCPaths;

if (! function_exists('stn_view')) {
    /**
     * Resolves and renders a SimpleTine package view.
     *
     * Resolution order:
     *   1. app/Config/HMVCPaths::$viewsOverridePath  — user-published views
     *   2. Package built-in Views/ directory          — default
     *
     * @param array<string, mixed> $data
     */
    function stn_view(string $view, array $data = [], array $options = []): string
    {
        /** @var HMVCPaths $pathsConfig */
        $pathsConfig = config('HMVCPaths');
        $override    = $pathsConfig->viewsOverridePath ?? '';

        if ($override !== '') {
            // Normalise to an absolute path and use namespace-free view() call
            $overrideAbs = rtrim(ROOTPATH . ltrim($override, '/\\'), '/\\') . DIRECTORY_SEPARATOR;
            $viewFile    = $overrideAbs . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $view) . '.php';

            if (file_exists($viewFile)) {
                return view($overrideAbs . $view, $data, $options);
            }
        }

        // Fallback: package built-in views (namespace-based path)
        return view(HMVCSHIELDVIEWS . $view, $data, $options);
    }
}

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
        $logins = model(LoginModel::class);
        $dates  = $logins->lastLogin(user())->date;
        $date   = '';

        foreach ($dates as $row) {
            $date = $row->date;
        }

        return $text . Time::parse($date)->format('d M Y H:i:s');
    }
}

if (! function_exists('show_errors')) {
    /**
     * Displays session error messages in an alert box.
     *
     * This function checks for 'error' or 'errors' in the session data and
     * returns an HTML string to display these errors in a Bootstrap alert box.
     *
     * @return string The HTML string for displaying the errors.
     */
    function show_errors()
    {
        $output = '';

        if (session('error') !== null) {
            $output .= '<div class="alert alert-danger" role="alert">' . session('error') . '</div>';
        } elseif (session('errors') !== null) {
            $output .= '<div class="alert alert-danger" role="alert">';
            if (is_array(session('errors'))) {
                foreach (session('errors') as $error) {
                    $output .= $error . '<br>';
                }
            } else {
                $output .= session('errors');
            }
            $output .= '</div>';
        }

        return $output;
    }
}

if (! function_exists('is_route')) {
    function is_route($route_name)
    {
        return $route_name === service('router')->getMatchedRoute()[0];
    }
}
