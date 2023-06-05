<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountLoginLog extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'id',
        'type',
        'uid',
        'username',
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

    protected $table = 'account_login_log';

    
    public $timestamps = false;

    protected $connection = 'Master';
}
