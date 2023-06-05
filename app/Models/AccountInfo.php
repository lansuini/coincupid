<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use App\Http\Library\TIM;
class AccountInfo extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'id',
        'uid',
        'nickname',
        'bio',
        'facebook',
        'instagram',
        'twitter',
        'line',
        'telegram',
        'intention',
        'long',
        'lat',
        'country',
        'province',
        'city',
        'client_ip',
        'intention',
        'last_login_ip',
        'last_login_time',
        'last_gps',
        'created',
        'updated',
        'birthday',
        'constellation',
        'educational',
        'annualIncome',
        'weight',
        'height',
        'measurements',
        'avatar',
        'im_userid',
        'sex',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var string[]
     */
    protected $hidden = [
    ];

    protected $table = 'account_info';

    public $timestamps = false;

    protected $connection = 'Master';

    /*
    * 根据生日中的月份和日期来计算所属星座

    *

    * @param int $birth_month

    * @param int $birth_date

    * @return string

    */
    public static function getConstellation($birth_month,$birth_date){

        //判断的时候，为避免出现1和true的疑惑，或是判断语句始终为真的问题，这里统一处理成字符串形式
        $birth_month = strval($birth_month);
        $constellation_name = array(
//            '水瓶座','双鱼座','白羊座','金牛座','双子座','巨蟹座',
//            '狮子座','处女座','天秤座','天蝎座','射手座','摩羯座'
            'Aquarius','Pisces','Aries','Taurus','Gemini','Cancer',
            'Leo','Virgo','Libra','Scorpio','Sagittarius','Capricorn'
        );

        if ($birth_date <= 22) {
            if ('1' !== $birth_month)
            {
                $constellation = $constellation_name[$birth_month-2];
            }else{
                $constellation = $constellation_name[11];
            }
        }else {
            $constellation = $constellation_name[$birth_month-1];
        }
        return $constellation;
    }

    public static function nearby()
    {
        $user = auth()->user();
//        var_dump($user);exit;
        $lat = auth()->user()->lat;
        $lon = auth()->user()->long;
        $radius = 50; // km => converted to meter

        $angle_radius = (float)$radius / ( 111 * cos( (float)$lat ) ); // Every lat|lon degree° is ~ 111Km
        $min_lat = (float)$lat - (float)$angle_radius;
        $max_lat = (float)$lat + (float)$angle_radius;
        $min_lon = (float)$lon - (float)$angle_radius;
        $max_lon = (float)$lon + (float)$angle_radius;

        $persons = self::where('id', '!=', $user->uid)
            ->whereBetween('lat', [$min_lat, $max_lat])
            ->whereBetween('long', [$min_lon, $max_lon])
            ->get();

        $persons->each(function($person) {
            /* auth user coordinate vs user's coordinates */
            $point1 = array('lat' => auth()->user()->latitude, 'long' => auth()->user()->longitude);
            $point2 = array('lat' => $person->latitude, 'long' => $person->longitude);
            $distance = $person->getDistanceBetweenPoints($point1['lat'], $point1['long'], $point2['lat'], $point2['long']);

            $person['distance'] = $distance;
        });

        return $persons;

    }

    public static function getDistanceBetweenPoints($latitude1, $longitude1, $latitude2, $longitude2, $unit = 'km') {

        $theta = $longitude1 - $longitude2;
        $distance = (sin(deg2rad($latitude1)) * sin(deg2rad($latitude2))) + (cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * cos(deg2rad($theta)));
        $distance = acos($distance);
        $distance = rad2deg($distance);
        $distance = $distance * 60 * 1.1515; switch($unit) {
            case 'mi': break; case 'km' : $distance = $distance * 1.609344;
        }
        return round($distance,2);

    }

    public static function parseActiveTime($time)
    {
        if(!$time) return '';
        $now = time();
        $delay = floor(($now - $time)/60);
        if($delay < 3){
            return "just now";
        }elseif($delay < 60){
            return $delay ." minutes ago";
        }elseif($delay < 60 *24){
            $hours = floor($delay/60);
            return $hours ." hours ago";
        }else{
            $days = floor($delay/(60*24));
            return $days ." days ago";
        }
    }

    public static function getCoordinatesByIP($ip){

        if(!$ip) return [];
        $ipTools = new \IP2Location\IpTools();
        if(!$ipTools->isIpv4($ip)) return [];
        $path = env('IP2LOCATION_DB_PATH','');
        if(!$path) return [];
        try{
            $path = env('IP2LOCATION_DB_PATH','/data/www/IP2LOCATION-LITE-DB11.BIN');
            $db = new \IP2Location\Database($path, \IP2Location\Database::FILE_IO);
            $records = $db->lookup($ip, \IP2Location\Database::ALL);
            $Coordinates['lat'] = $records['latitude'];
            $Coordinates['long'] = $records['longitude'];
            return $Coordinates;
        }catch (\Exception $e){
            Log::Error("$ip 获取ip失败：".$e->getMessage());
            return [];
        }
    }

    public static function friendCheckSingle($userid1, $userid2){
        try{
            $res = (new TIM)->friendCheck($userid1, $userid2,"CheckResult_Type_Single");
//            Log::info("friendCheckSingle",$res);
            if(!$res[0]){
                Log::error('friendCheckSingle : '.$res[1]);
                return 0;
            }
            $InfoItem = $res[2]['InfoItem'];
            if(isset($InfoItem[0])){
                return $InfoItem[0]['Relation'] == "CheckResult_Type_AWithB" ? 1 : 0;
            }
            return 0;
        }catch (\Exception $e){
            Log::error('friendCheckSingle : '.$e->getMessage());
            return 0;
        }

    }
}
