<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redis;

class AccountMobile extends Model
{
    protected $primaryKey = 'uid';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'id',
        'uid',
        'mobile_area',
        'mobile_number',
        'created',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var string[]
     */
    protected $hidden = [
    ];

    protected $table = 'account_mobile';

    protected $connection = 'Master';
    
    public $timestamps = false;
}
