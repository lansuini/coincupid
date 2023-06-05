<?php

namespace App\Models\Manager;

use Illuminate\Database\Eloquent\Model;

class LoginLog extends Model
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
        'is_success',
        'desc',
        'ip',
        'created'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var string[]
     */
    protected $hidden = [
    ];

    protected $table = 'gm_login_log';

    
    public $timestamps = false;

    protected $connection = 'Master';
}
