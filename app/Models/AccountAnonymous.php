<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountAnonymous extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'id',
        'uid',
        'key',
        'is_bind_mobile',
        'created',
        'updated',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var string[]
     */
    protected $hidden = [
    ];

    protected $table = 'account_anonymous';

    public $timestamps = false;

    protected $connection = 'Master';
}
