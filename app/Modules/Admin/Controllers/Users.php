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

namespace App\Modules\Admin\Controllers;

use App\Controllers\BaseController;
use Modules\Admin\Models\UsersModel;

class Users extends BaseController
{
    protected $folder_directory = 'Modules\\Admin\\Views\\';
    protected $model;
    protected $data  = [];
    protected $rules = [];

    public function __construct()
    {
        $this->model = new UsersModel();
    }

    public function index()
    {
        if (! user_id()) {
            return redirect()->route('login');
        }
        $this->data['page_title']    = 'Admin - Users';
        $this->data['page_header']   = 'Users';
        $this->data['is_datatables'] = true;
        $this->data['rows']          = $this->model->findAll();
        $this->data['contents']      = [
            $this->folder_directory . 'users',
        ];
        $this->data['scripts'] = [
            $this->folder_directory . 'scripts/users.tpl',
        ];

        return self::render();
    }

    public function render(): string
    {
        return view('index', $this->data);
    }
}
