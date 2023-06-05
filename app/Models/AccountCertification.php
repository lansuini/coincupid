<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redis;

class AccountCertification extends Model
{
    protected $primaryKey = 'uid';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'uid',
        'status',
        'educational',
        'income',
        'occupation_cate',
        'occupation_post',
        'portrait1',
        'portrait2',
        'portrait3',
        'verify_video',
        'failed_type',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var string[]
     */
    protected $hidden = [
    ];

    protected $table = 'account_certification';

    protected $connection = 'Master';
    
    public $timestamps = false;
}
