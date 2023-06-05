<?php

namespace App\Models\Manager\Analysis;

use App\Models\Manager\Admin as Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redis;
use App\Http\Library\PHPGangsta_GoogleAuthenticator;
class Admin extends Model
{
    protected $table = 'analysis_admin';

    protected $connection = 'Master';

    public $tag = 'ANA_';
}
