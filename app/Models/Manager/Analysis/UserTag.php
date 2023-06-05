<?php

namespace App\Models\Manager\Analysis;

use App\Models\Manager\LoginLog as Model;

class UserTag extends Model
{
    protected $table = 'user_tag';

    protected $connection = 'Master';
}
