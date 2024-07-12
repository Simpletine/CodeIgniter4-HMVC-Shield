<?php

namespace App\Modules\Admin\Models;

use CodeIgniter\Model;

class Admin extends Model
{
    protected $table            = 'st_admin'; 
    protected $useTimestamps    = true;
    protected $useSoftDeletes   = true;
    protected $allowedFields    = [];
}