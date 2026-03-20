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
use CodeIgniter\Shield\Authentication\Passwords;
use CodeIgniter\Shield\Models\UserModel;

/**
 * Handles the /profile page.
 * Only authenticated users can access this controller.
 * Provides password-change functionality only.
 */
class ProfileController extends Controller
{
    /**
     * Display the profile / change-password form.
     */
    public function index(): RedirectResponse|string
    {
        if (! user_id()) {
            return redirect()->route('login');
        }

        $data = [
            'page_title'  => 'Profile',
            'page_header' => 'My Profile',
            'contents'    => [
                'Simpletine\\HMVCShield\\Views\\profile_form',
            ],
        ];

        return stn_view('index', $data);
    }

    /**
     * Handle the change-password POST request.
     */
    public function update(): RedirectResponse
    {
        if (! user_id()) {
            return redirect()->route('login');
        }

        $rules = [
            'current_password' => 'required|min_length[1]',
            'new_password'     => 'required|min_length[8]',
            'confirm_password' => 'required|matches[new_password]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $currentPassword = $this->request->getPost('current_password');
        $newPassword     = $this->request->getPost('new_password');

        /** @var Passwords $passwords */
        $passwords = service('passwords');

        /** @var UserModel $userModel */
        $userProvider = auth()->getProvider();
        $currentUser  = user();

        // Verify current password against the stored hash
        $identity = $currentUser->getEmailIdentity();
        if ($identity === null || ! $passwords->verify($currentPassword, $identity->secret)) {
            return redirect()->back()->withInput()->with('error', 'Current password is incorrect.');
        }

        // Hash and persist the new password
        $identity->secret      = $passwords->hash($newPassword);
        $identity->force_reset = false;

        $userProvider->saveIdentity($identity);

        return redirect()->to('/profile')->with('message', 'Password updated successfully.');
    }
}
