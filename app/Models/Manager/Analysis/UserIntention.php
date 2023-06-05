<?php

namespace App\Models\Manager\Analysis;

use App\Models\Manager\LoginLog as Model;

class UserIntention extends Model
{
    protected $table = 'user_intention';

    protected $connection = 'Master';
}
