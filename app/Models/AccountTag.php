<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class AccountTag extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'uid',
        'tag_id',
        'tag_name',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var string[]
     */
    protected $hidden = [
    ];

    protected $table = 'account_tag';

    public $timestamps = true;

    protected $connection = 'Master';

}