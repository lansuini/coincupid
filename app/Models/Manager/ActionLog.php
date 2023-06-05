<?php

namespace App\Models\Manager;

use Illuminate\Database\Eloquent\Model;

class ActionLog extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'id',
        'admin_id',
        'admin_username',
        'browser',
        'key',
        'before',
        'after',
        'target_id',
        'ip',
        'is_success',
        'url',
        'method',
        'params',
        'desc',
        'created'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var string[]
     */
    protected $hidden = [];

    protected $table = 'gm_action_log';

    protected $connection = 'Master';

    
    public $timestamps = false;


}
