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

namespace Modules\Admin\Models;

use CodeIgniter\Shield\Models\UserModel as ShieldUserModel;

class UsersModel extends ShieldUserModel
{
    protected bool $fetchIdentities = true;

    public function findAll(int $limit = 0, int $offset = 0): array
    {
        $users = parent::findAll($limit, $offset);

        if ($this->fetchIdentities) {
            foreach ($users as $user) {
                foreach ($user->identities as $identity) {
                    $user->email = $identity->secret;
                }
            }
        }

        return $users;
    }
}
