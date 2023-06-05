<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redis;

class AccountDelete extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'uid',
        'is_delete',
        'username',
        'mobile_area',
        'mobile_number',
        'delete_time',
        'created',
        'updated',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var string[]
     */
    protected $hidden = [];

    protected $table = 'account_delete';

    protected $connection = 'Master';
    
    public $timestamps = false;
}
