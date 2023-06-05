<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class WebLogAnalysis extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'id',
        'created',
        'updated',
        'count_date',
        'content',
        'host',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var string[]
     */
    protected $hidden = [
    ];

    protected $table = 'web_log_analysis';
    
    public $timestamps = false;

    protected $connection = 'Master';
}