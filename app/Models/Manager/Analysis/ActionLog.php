<?php

namespace App\Models\Manager\Analysis;

use App\Models\Manager\ActionLog as Model;

class ActionLog extends Model
{
    protected $table = 'analysis_action_log';

    protected $connection = 'Master';
}
