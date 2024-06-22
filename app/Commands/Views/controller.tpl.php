<@php

namespace {namespace};

use {useStatement};

class {class} extends {extends}
{
    protected $folder_directory = "Modules\\{directoryName}\\Views\\";

    public function index()
    {
        return view($this->folder_directory . 'index');
    }
}