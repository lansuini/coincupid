<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
class AccountConfig extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'uid',
        'hideTrace',
        'hideNewVisitorRemind',
        'hideNoticeContent',
        'hideOnlineStatus',
        'hideLocation',
        'hideRankingList',
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

    protected $table = 'account_config';

    protected $primaryKey = 'uid';

    public $timestamps = false;

    protected $connection = 'Master';


}
