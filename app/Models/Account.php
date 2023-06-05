<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redis;
use Hashids\Hashids;
use Carbon\Carbon;
use App\Http\Library\Code2latlon;
use App\Http\Library\Country2locale;
use Illuminate\Support\Facades\Log;

class Account extends Model
{
    protected $primaryKey = 'uid';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'uid',
        'username',
        'vip_level',
        'gold',
        'password',
        'worldname',
        'contact',
        'created',
        'is_lock',
        'err_login_cnt',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var string[]
     */
    protected $hidden = [];

    protected $table = 'account';

    protected $connection = 'Master';

    public $timestamps = false;

    public static function getAndSetToken($uid)
    {
        $hashids = new Hashids(env('SERVER_HASH_IDS_SALT'), 32, env('SERVER_HASH_IDS_STR_TABLE'));
        $hid = $hashids->encode($uid);
        $redis = Redis::connection();
        $uniqueString = md5(microtime() . rand(0, 99999999));
        $redis->setex('web_player_token:' . $uid, 30 * 86400, $uniqueString);
        $token = $hid . $uniqueString;
        return $token;
    }

    public static function delToken($uid)
    {
        $redis = Redis::connection();
        $redis->del('web_player_token:' . $uid);
    }

    public static function getUidByToken($token)
    {
        $redis = Redis::connection();
        $hashids = new Hashids(env('SERVER_HASH_IDS_SALT'), 32, env('SERVER_HASH_IDS_STR_TABLE'));
        $uidStr = substr($token, 0, 32);
        $tokenStr = substr($token, 32, 32);
        $uid = $hashids->decode($uidStr);
        $uid = !empty($uid) ? current($uid) : 0;

        if ($uid > 0) {
            $uniqueString = $redis->get('web_player_token:' . $uid);
            if ($uniqueString != $tokenStr) {
                return 0;
            }
        }

        return $uid;
    }

    //创建平台用户
    public static function createRobotUser($country = 'US', $sex = 'male', $ageStart = 18, $ageEnd = 100, $heightStart = 155, $heightEnd = 200, $weightStart = 40, $weightEnd = 110)
    {
        $country = strtoupper($country);
        try {

            $locale = (new Country2locale)->country2locale($country);
            if (strpos($locale, ',') != false) {
                $locales = explode(',', $locale);
                $locale = $locales[0];
            }
            $fakerProviderAddress = '\Faker\Provider\\' . $locale . '\Address';
            $fakerProviderPhoneNumber = '\Faker\Provider\\' . $locale . '\PhoneNumber';
            //            echo $fakerProviderPhoneNumber . PHP_EOL;
            if (!class_exists($fakerProviderAddress)) {
                echo 'class not exists : ' . $fakerProviderAddress;
                exit;
            }

            if (!class_exists($fakerProviderPhoneNumber)) {
                echo 'class not exists : ' . $fakerProviderPhoneNumber;
                exit;
            }
            $faker = \Faker\Factory::create(config('app.faker_locale'));
            $faker->addProvider(new $fakerProviderPhoneNumber($faker));
            $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
            $mobileNumber = $faker->mobileNumber;
            //            echo $faker->password(8,12);
            $swissNumberProto = $phoneUtil->parse($mobileNumber, $country);
            //            var_dump($swissNumberProto->getCountryCode());
            //            var_dump($swissNumberProto->getNationalNumber());
            //            echo $phoneUtil->format($swissNumberProto, \libphonenumber\PhoneNumberFormat::E164);

            $faker->addProvider(new $fakerProviderAddress($faker));
            $account = new Account();
            $account->username = $swissNumberProto->getCountryCode() . $swissNumberProto->getNationalNumber();
            $account->account_type = mt_rand(1, 2);
            $account->vip_level = mt_rand(0, 3);
            $account->contact = $faker->password(8, 12);
            $account->password = encrypt($account->contact);
            //            $account->save();
            //            print_r($account);

            $accountMobile = new AccountMobile();
            //            $accountMobile->uid = $account->uid;
            $accountMobile->uid = 1000;
            $accountMobile->mobile_area = $swissNumberProto->getCountryCode();
            $accountMobile->mobile_number = $swissNumberProto->getNationalNumber();
            //            print_r($accountMobile);
            //            $accountMobile->save();

            $avatar_url = 'https://im-project.s3.ap-southeast-1.amazonaws.com/system/';
            $avatar_rand = mt_rand(1, 40);
            $avatar_dir = 'meinv';
            $accountInfo = new AccountInfo();
            //            $accountInfo->uid = $account->uid;
            $accountInfo->uid = 1000;
            $accountInfo->nickname = $faker->name($sex);
            $accountInfo->verified = 1;
            $accountInfo->country = $country;
            $accountInfo->province = $faker->state;
            $accountInfo->city = $faker->city();
            if ($sex == 'male') {
                $accountInfo->sex = 1;
                $avatar_dir = 'shuaige';
            } elseif ($sex == 'female') {
                $accountInfo->sex = 2;
                $avatar_dir = 'meinv';
            } else {
                $accountInfo->sex = 3;
            }
            if ($avatar_rand <= 10) {
                $avatar_url = $avatar_url . $avatar_dir . '/00' . $avatar_rand . '.jpg';
            } else {
                $avatar_url = $avatar_url . $avatar_dir . '/0' . $avatar_rand . '.jpg';
            }

            $accountInfo->age = mt_rand($ageStart, $ageEnd);
            $accountInfo->avatar = $avatar_url;

            $code2latlon = new Code2latlon;
            $res = $code2latlon->get($country);
            $accountInfo->lat = $res[0];
            $accountInfo->long = $res[1];

            $birthMonth = mt_rand(1, 12);
            $birthDay = mt_rand(1, 30);
            if ($birthMonth == 2) {
                $birthDay = mt_rand(1, 28);
            }
            $age = mt_rand($ageStart, $ageEnd);
            $birth = Carbon::parse("-$age year");
            $birth->month = $birthMonth;
            $birth->day = $birthDay;
            $accountInfo->constellation = AccountInfo::getConstellation($birthMonth, $birthDay); //星座
            $accountInfo->birthday = $birth->year . '-' . $birth->month . '-' . $birth->day;
            $pop_sert = [0, 10, 20, 30, 40, 50, 60, 70, 80, 90, 99];
            $rand_key = array_rand($pop_sert, 1);
            $rand_pop_sert = $pop_sert[$rand_key];
            $accountInfo->pop_sert = $rand_pop_sert;

            if ($sex == 'female' && $heightEnd > 180) {
                $heightEnd = 180;
            }
            if ($sex == 'male' && $heightStart < 170) {
                $heightStart = 170;
            }
            $accountInfo->height = mt_rand($heightStart, $heightEnd);
            if ($sex == 'female' && $weightEnd > 75) {
                $weightEnd = 75;
            }
            if ($sex == 'male' && $weightStart < 65) {
                $weightStart = 65;
            }
            $accountInfo->weight = mt_rand($weightStart, $weightEnd);
            $accountInfo->intention = '1,2,3,4,5';

            $socials = ['facebook', 'instagram', 'twitter', 'line', 'telegram'];
            $socials_rand = mt_rand(1, 5);
            $socials_rands = array_rand($socials, $socials_rand);
            print_r($socials_rands);
            if (is_array($socials_rands)) {
                foreach ($socials_rands as $key => $rand) {
                    $accountInfo->{$socials[$rand]} = $faker->email();
                    //                    echo $socials[$rand];
                }
            } else {
                //                echo $socials[$socials_rands];
                $accountInfo->{$socials[$socials_rands]} = $faker->email();
            }
            var_dump($accountInfo);
            exit;

            echo $faker->city();
            exit;

            $faker->addProvider(new $fakerProviderPhoneNumber($faker));
            $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
            $mobileNumber = $faker->mobileNumber;
            echo $mobileNumber . PHP_EOL;
            $swissNumberProto = $phoneUtil->parse($mobileNumber, $country);
            var_dump($swissNumberProto->getCountryCode());
            var_dump($swissNumberProto->getNationalNumber());
            echo $phoneUtil->format($swissNumberProto, \libphonenumber\PhoneNumberFormat::E164);
            exit;
            echo $faker->name($sex) . PHP_EOL;
            echo $faker->e164PhoneNumber() . PHP_EOL;
            echo $faker->city() . PHP_EOL;
            echo $locale;
            exit;

            print_r($res);
            exit;
        } catch (\Exception $e) {
            Log::error('createRobotUser->' . $e->getMessage());
            exit;
        }

        $accountInfo = new AccountInfo();

        $faker->addProvider(new \Faker\Provider\zh_CN\Address($faker));
        $faker->addProvider(new \Faker\Provider\en_US\PhoneNumber($faker));
        $faker->addProvider(new \Faker\Provider\th_TH\PhoneNumber($faker));
        echo $faker->name($sex) . PHP_EOL;
        //        $faker = app('Faker');
        echo $faker->country . PHP_EOL;
        echo $faker->latitude() . PHP_EOL;
        echo $faker->longitude() . PHP_EOL;
        echo $faker->city . PHP_EOL;
        //        echo $faker->cityPrefix .PHP_EOL;
        //        echo $faker->realText .PHP_EOL;
        echo $faker->state . PHP_EOL;
        echo $faker->imageUrl(640, 480, 'cats', true, 'Faker', true) . PHP_EOL;

        print_r($faker->localCoordinates());
        exit;
        echo $faker->name;
        exit;

        if (!in_array($sex, [1, 2, 3])) {
            $sex = mt_rand(1, 3);
        }


        $birthMonth = mt_rand(1, 12);
        $birthDay = mt_rand(1, 30);
        if ($birthMonth == 2) {
            $birthDay = mt_rand(1, 28);
        }
        $birth = Carbon::parse("-$age year");
        $birth->month = $birthMonth;
        $birth->day = $birthDay;
        $constellation = AccountInfo::getConstellation($birthMonth, $birthDay); //星座
        //计算生日

        $height = mt_rand($heightStart, $heightEnd);
        $weight = mt_rand($weightStart, $weightEnd);
        $intention = mt_rand(1, 5);
    }

    public static function test()
    {
        $params = [
            'lat' => 13.75,
            'long' => 100.516667,
        ];
        //获取50公里内坐标范围
        $distance = mt_rand(1, 100);
        $res = (new self())->location_range($params['long'], $params['lat'], $distance);
        $lat = (new self())->float_rand($res['lat_start'], $res['lat_end']);
        $lon = (new self())->float_rand($res['lng_start'], $res['lng_end']);

        //        echo AccountInfo::getDistanceBetweenPoints($params['lat'],$params['long'],$lat,$lon);exit;
        var_dump($lat, $lon);
        exit;
        $radius = 50;
        $angle_radius = (float)$radius / (111 * cos((float)$params['lat'])); // Every lat|lon degree° is ~ 111Km
        $min_lat = (float)$params['lat'] - (float)$angle_radius;
        $max_lat = (float)$params['lat'] + (float)$angle_radius;
        $min_lon = (float)$params['long'] - (float)$angle_radius;
        $max_lon = (float)$params['long'] + (float)$angle_radius;
        $lat = (new self())->float_rand($min_lat, $max_lat);
        $lon = (new self())->float_rand($min_lon, $max_lon);
        echo (new self())->distance($params['lat'], $params['long'], $lat, $lon, 'k');
        exit;
        print_r([$lat, $lon]);
    }

    public function float_rand($Min, $Max)
    {
        if ($Min > $Max) {
            $min = $Max;
            $max = $Min;
        } else {
            $min = $Min;
            $max = $Max;
        }
        $randomfloat = $min + mt_rand() / mt_getrandmax() * ($max - $min);
        $randomfloat = round($randomfloat, 10);

        return $randomfloat;
    }

    public function distance($lat1, $lon1, $lat2, $lon2, $unit)
    {

        $theta = $lon1 - $lon2;

        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));

        $dist = acos($dist);

        $dist = rad2deg($dist);

        $miles = $dist * 60 * 1.1515;

        $unit = strtoupper($unit);

        if ($unit == "K") {

            return ($miles * 1.609344);
        } else if ($unit == "N") {

            return ($miles * 0.8684);
        } else {

            return $miles;
        }
    }

    /**
     * @desc 根据传入的经纬度，和距离范围，返回所有在距离范围内的经纬度的取值范围
     * @param $lng              坐标点经度
     * @param $lat              坐标点纬度
     * @param $distance         范围直径单位：km
     * @return array
     */
    public function location_range($lng, $lat, $distance = 50)
    {

        //不传默认1km

        $earthRadius = 6378.137; //单位km,地球的直径
        $d_lng =  2 * asin(sin($distance / (2 * $earthRadius)) / cos(deg2rad($lat)));
        $d_lng = rad2deg($d_lng);
        $d_lat = $distance / $earthRadius;
        $d_lat = rad2deg($d_lat);

        $info = [
            'lat_start' => sprintf("%.6f", $lat - $d_lat), //纬度开始
            'lat_end' => sprintf("%.6f", $lat + $d_lat), //纬度结束
            'lng_start' => sprintf("%.6f", $lng - $d_lng), //纬度开始
            'lng_end' => sprintf("%.6f", $lng + $d_lng), //纬度结束
            'distance' => $distance * 1000 //限制的距离
        ];

        //开始跟结束不能乱，BETWEEN查询必须从小到大
        return $info;
    }

    public function getDeleteAccountStatus($uid) {
        return [
            'account_safe_status' => 1,
            'account_balance_is_zero' => 1,
            'account_vip_is_cancel' => 1,
            'account_party_is_over' => 1,
            'account_game_is_over' => 1,
            'account_shop_order_is_over' => 1,
        ];
    }

}
