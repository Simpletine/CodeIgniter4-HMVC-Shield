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
