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

namespace Simpletine\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Shield\Entities\User;

class SeedAdminUser extends Migration
{
    public function up()
    {
        $users = service('auth')->setAuthenticator(null)->getProvider();

        $user = new User([
            'username' => 'superadmin',
            'email'    => 'super@admin.com',
            'password' => 'password',
        ]);
        $users->save($user);
        $user = $users->findById($users->getInsertID());
        $user->addGroup('superadmin');
    }

    public function down()
    {
    }
}
