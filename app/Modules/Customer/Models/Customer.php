<?php

namespace App\Modules\Customer\Models;

use CodeIgniter\Model;

class Customer extends Model
{
    protected $table            = 'st_customer'; 
    protected $useTimestamps    = true;
    protected $useSoftDeletes   = true;
    protected $allowedFields    = [];
}