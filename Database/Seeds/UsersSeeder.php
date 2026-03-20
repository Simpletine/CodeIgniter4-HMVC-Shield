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

namespace Simpletine\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\UserModel;

/**
 * Scaffolds basic Users module seed data.
 * Called automatically during simpletine:setup when the Users module is scaffolded.
 *
 * To run manually:
 *   php spark db:seed Simpletine\Database\Seeds\UsersSeeder
 */
class UsersSeeder extends Seeder
{
    public function run(): void
    {
        /** @var UserModel $users */
        $users = model(UserModel::class);

        // Avoid duplicate seeding
        if ($users->where('email', 'super@admin.com')->first() !== null) {
            return;
        }

        // Create the superadmin user via Shield provider
        $provider = service('auth')->getProvider();

        $user = new User([
            'username' => 'superadmin',
            'active'   => true,
        ]);

        $provider->save($user);

        // Reload to get the generated ID
        $user = $provider->findById($provider->getInsertID());

        // Set credentials
        $user->createEmailIdentity([
            'email'    => 'super@admin.com',
            'password' => 'password',
        ]);

        echo "Default admin user created: super@admin.com / password\n";
        echo "Change these credentials immediately after first login.\n";
    }
}
