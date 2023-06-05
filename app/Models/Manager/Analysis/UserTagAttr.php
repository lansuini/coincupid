<?php

namespace App\Models\Manager\Analysis;

use App\Models\Manager\LoginLog as Model;

class UserTagAttr extends Model
{
    protected $table = 'user_tag_attr';

    protected $connection = 'Master';
}
