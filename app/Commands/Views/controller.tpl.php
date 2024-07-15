<@php

namespace {namespace};

use {useStatement};
use {useModelStatement};

class {class} extends {extends}
{
    protected $folder_directory = "Modules\\{directoryName}\\Views\\";
    protected $model;
    protected $data = [];
    protected $rules = [];

    public function __construct()
    {
        $this->model = new {modelClass};
    }

    public function index()
    {
        return self::render('index');
    }

    public function render(string $page): string
    {
        return view( $this->folder_directory . $page, $this->data);
    }
}