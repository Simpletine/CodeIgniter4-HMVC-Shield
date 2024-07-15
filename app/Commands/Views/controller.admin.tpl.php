<@php

namespace {namespace};

use {useStatement};

class {class} extends {extends}
{
    protected $folder_directory = "Modules\\{directoryName}\\Views\\";
    protected $model;
    protected $data = [];
    protected $rules = [];

    public function __construct()
    {
    }

    public function index()
    {
        if(!user_id()) {
            return redirect()->route('login');
        }
        $this->data['page_title'] = 'Admin - {class}';
        $this->data['page_header'] = '{class}';
        $this->data['contents'] = [
            $this->folder_directory . '{lowerClass}',
        ];
        return self::render();
    }

    public function render(): string
    {
        return view('index', $this->data);
    }
}