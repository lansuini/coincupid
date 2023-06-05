<?php

namespace App\Models\Manager\Merchant;

use App\Models\Manager\LoginLog as Model;

class LoginLog extends Model
{
    protected $table = 'merchant_login_log';

    protected $connection = 'Master';
}
