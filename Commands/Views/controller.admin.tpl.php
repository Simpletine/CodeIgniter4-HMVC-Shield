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
        if (!user_id()) {
            return redirect()->route('login');
        }
        $this->data['page_title']  = 'Admin - {class}';
        $this->data['page_header'] = '{class}';
        $this->data['contents']    = [
            $this->folder_directory . '{lowerClass}',
        ];

        return $this->render();
    }

    /**
     * Renders the admin layout.
     * Uses stn_view() so that a published/overridden layout is preferred
     * over the package built-in Views/index.php automatically.
     */
    public function render(): string
    {
        return stn_view('index', $this->data);
    }
}