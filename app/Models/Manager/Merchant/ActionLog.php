<?php

namespace App\Models\Manager\Merchant;

use App\Models\Manager\ActionLog as Model;

class ActionLog extends Model
{
    protected $table = 'merchant_action_log';

    protected $connection = 'Master';
}
