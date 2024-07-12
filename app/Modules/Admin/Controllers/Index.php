<?php

namespace App\Modules\Admin\Controllers;

use App\Controllers\BaseController;
use App\Modules\Admin\Models\Admin;

class Index extends BaseController
{
    protected $folder_directory = "Modules\\Admin\\Views\\";
    protected $model;
    protected $data = [];
    protected $rules = [];

    public function __construct()
    {
        $this->model = new Admin;
    }

    public function index()
    {
        return self::render('welcome_message');
    }

    public function render(string $page): string
    { 
        return view( $this->folder_directory . $page, $this->data);
    }
}