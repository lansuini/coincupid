<?php

namespace App\Models\Manager\Analysis;

use App\Models\Manager\LoginLog as Model;

class LoginLog extends Model
{
    protected $table = 'analysis_login_log';

    protected $connection = 'Master';
}
