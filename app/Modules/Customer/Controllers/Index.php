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

namespace App\Modules\Customer\Controllers;

use App\Controllers\BaseController;
use App\Modules\Customer\Models\Customer;

class Index extends BaseController
{
    protected $folder_directory = 'Modules\\Customer\\Views\\';
    protected $model;
    protected $data  = [];
    protected $rules = [];

    public function __construct()
    {
        $this->model = new Customer();
    }

    public function index()
    {
        return self::render('welcome_message');
    }

    public function render(string $page): string
    {
        return view($this->folder_directory . $page, $this->data);
    }
}
