<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class ShortMessageLog extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'id',
        'short_messages_id',
        'mobile_area',
        'mobile_number',
        'code',
        'ip',
        'uid',
        'response',
        'is_success',
        'created',
        'is_used',
        'type',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var string[]
     */
    protected $hidden = [];

    protected $table = 'short_message_log';

    protected $connection = 'Master';

    public $timestamps = false;
}


