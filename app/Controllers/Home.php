<?php

namespace App\Controllers;

class Home extends BaseController
{
    protected array $data = [];

    public function index(): string
    {
        return view('index', $this->data);
    }
}
