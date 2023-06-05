<?php
/**
 * Created by PhpStorm.
 * User: luobinhan
 * Date: 2023/2/16
 * Time: 17:55
 */

namespace App\Http\Controllers\Api;


use App\Models\Account;
use App\Models\AccountInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Ip2location\IP2LocationLaravel\IP2LocationLaravel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
class HomeController extends ApiController
{

    /**
     * 根据起点坐标和终点坐标测距离
     * @param $gpsfrom [起点坐标（经纬度），例如array(118.012951,36.810024)]
     * @param $gpsto [终点坐标]
     * @param bool $km 是否以公里为单位 false:米 true:公里（千里）
     * @param int $decimal 精度  保留小数位数
     */
    public function getDistance($gpsfrom,$gpsto,$km=true,$decimal=2){
        sort($gpsfrom);
        sort($gpsto);
        $EARTH_RANTUS=6370.996;//地球半径数

    }

    //首页新人模块列表
    public function getNewcomerUserList(Request $request){
        $uid = $request->get('uid');
        $params = $request->all();
        Validator::extend('latlong', function ($attribute, $value, $parameters, $validator) {
            //UTF-8汉字字母数字下划线
            $reg = '/^(-)?[0-9]+(\.[0-9]+)?$/';
            //获取字节长度
            $nameLength = strlen($value);
            if ($nameLength >= 1 && $nameLength <= 20 && preg_match($reg, $value)) {
                return true;
            }
            return false;
        });
        $validator = Validator::make($params, [
            'page' => [ 'required','integer', 'between:1,150'],
            'page_size' => ['integer', 'between:10,50'],
            'distance' => [ 'integer', 'between:0,150'],//距离
            'lat' => [ 'string', 'latlong'],//纬度
            'long' => [ 'string', 'latlong'],//经度
            'sex' => [ 'required','integer', 'between:1,3'],//性别
            'constellation' => [ 'string', 'in:Aquarius,Pisces.Aries,Taurus,Gemini,Cancer,Leo,Virgo,Libra,Scorpio,Sagittarius,Capricorn'],//星座
            'age_min' => [ 'integer', 'between:18,80'],//年龄
            'age_max' => [ 'integer', 'between:18,80'],//年龄
            'pop_cert' => [ 'integer', 'in:0,70,80,90,99,100'],//人气认证
        ]);

        if ($validator->fails()) {
            $errorCode = 1070;
            $errorText = 'Data is not legal' . ':' . $validator->errors()->keys()[0] . ' ' . $validator->errors()->first();
            return $this->err($errorCode, $errorText);
        }
        $userInfo = AccountInfo::where('uid',$uid)->first();
        if(!$userInfo){
            return $this->err(404, "User Not Found");
        }
        if(!isset($params['lat']) || empty($params['lat']) || !isset($params['long']) || empty($params['long'])){

            $params['lat'] = $userInfo->lat;
            $params['long'] = $userInfo->long;
        }
        $radius = 250; // km => converted to meter

        $query = AccountInfo::where('uid', '!=', $uid);

        if($userInfo->intention){
            $intentions = array_filter(explode(',',$userInfo->intention));
            if(count($intentions) == 1){
                $query = $query->whereRaw(\DB::raw('FIND_IN_SET('.$intentions[0].',intention)'));
            }else{
                $query = $query->where(function($query) use ($intentions){
                    foreach ($intentions as $intention){
                        $query->orWhereRaw(\DB::raw('FIND_IN_SET('.$intention.',intention)'));
                    }
                });

            }
        }

        if(isset($params['pop_cert']) && is_numeric($params['pop_cert'])){
            $query = $query->where('pop_cert',$params['pop_cert']);
        }

        if(isset($params['sex']) && !empty($params['sex']) && $params['sex'] != 3){
            $query = $query->where('sex',$params['sex']);
        }

        if(isset($params['age_min']) && !empty($params['age_min'])){
            $query = $query->where('age','>=',$params['age_min']);
        }

        if(isset($params['age_max']) && !empty($params['age_max'])){
            $query = $query->where('age','<=',$params['age_max']);
        }

        if(isset($params['constellation']) && !empty($params['constellation'])){
            $query = $query->where('constellation',$params['constellation']);
        }

        if(isset($params['distance']) && is_numeric($params['distance']) && $params['distance'] > 0){
            $radius = $params['distance'];
        }

        $angle_radius = (float)$radius / ( 111 * cos( (float)$params['lat'] ) ); // Every lat|lon degree° is ~ 111Km
        if($params['lat'] && $params['long']){
            if($angle_radius > 0){
                $min_lat = (float)$params['lat'] - (float)$angle_radius;
                $max_lat = (float)$params['lat'] + (float)$angle_radius;
                $min_lon = (float)$params['long'] - (float)$angle_radius;
                $max_lon = (float)$params['long'] + (float)$angle_radius;
            }else{
                $max_lat = (float)$params['lat'] - (float)$angle_radius;
                $min_lat = (float)$params['lat'] + (float)$angle_radius;
                $max_lon = (float)$params['long'] - (float)$angle_radius;
                $min_lon = (float)$params['long'] + (float)$angle_radius;
            }
            $query = $query->whereBetween('lat', [$min_lat, $max_lat])
                ->whereBetween('long', [$min_lon, $max_lon]);
        }
        $page_size = isset($params['page_size']) ? $params['page_size'] : 30;
        $persons = $query->get(['nickname','uid','avatar','age','sex','pop_cert','country','city','height','weight','actived_time','im_userid','facebook','instagram','twitter','line','telegram','lat','long'])
            ->forPage($params['page'],$page_size);;
        $attributes['total'] = $query->count();
        $attributes['page'] = (int)$params['page'];
        $attributes['page_size'] = $page_size;
        $list=[];
        $persons->each(function($person) use ($params,$uid) {
            /* auth user coordinate vs user's coordinates */
            $distance = 999.99;
            if($params['lat'] && $params['long']){
                $point1 = array('lat' => $params['lat'], 'long' => $params['long']);
                $point2 = array('lat' => $person->lat, 'long' => $person->long);
                $distance = $person->getDistanceBetweenPoints($point1['lat'], $point1['long'], $point2['lat'], $point2['long']);
            }
            $person->distance = $distance;
            $lastOnlineTime = Redis::Hget($uid,'last_online_time');
            if(!$lastOnlineTime){
                $lastOnlineTime = strtotime((int)$person->last_login_time);
            }
            $person->last_online_time = $lastOnlineTime;
            $person->actived_time = $person->parseActiveTime($lastOnlineTime);
            $dynamicPictures = DB::table("user_dynamic_pictures")->where('uid',$person->uid)->orderByDesc('created_at')->limit(5)->pluck('picture');
            $person->dynamic_pictures = $dynamicPictures;

        });
        foreach($persons as $key=>$one){
            array_push($list,$one);
        }
        $data['list'] = $list;
        $data['attributes'] = $attributes;
        return $this->succ($data);
    }

    //首页活跃模块列表
    public function getActiveUserList(Request $request){

        $uid = $request->get('uid');
        $params = $request->all();
        Validator::extend('latlong', function ($attribute, $value, $parameters, $validator) {
            //UTF-8汉字字母数字下划线
            $reg = '/^(-)?[0-9]+(\.[0-9]+)?$/';
            //获取字节长度
            $nameLength = strlen($value);
            if ($nameLength >= 1 && $nameLength <= 20 && preg_match($reg, $value)) {
                return true;
            }
            return false;
        });
        $validator = Validator::make($params, [
            'page' => [ 'required','integer', 'between:1,150'],
            'page_size' => ['integer', 'between:10,50'],
            'distance' => [ 'integer', 'between:0,150'],//距离
            'lat' => [ 'string', 'latlong'],//纬度
            'long' => [ 'string', 'latlong'],//经度
            'sex' => [ 'required','integer', 'between:1,3'],//性别
            'constellation' => [ 'string', 'in:Aquarius,Pisces.Aries,Taurus,Gemini,Cancer,Leo,Virgo,Libra,Scorpio,Sagittarius,Capricorn'],//星座
            'age_min' => [ 'integer', 'between:18,80'],//年龄
            'age_max' => [ 'integer', 'between:18,80'],//年龄
            'pop_cert' => [ 'integer', 'in:0,70,80,90,99,100'],//人气认证
        ]);

        if ($validator->fails()) {
            $errorCode = 1070;
            $errorText = 'Data is not legal' . ':' . $validator->errors()->keys()[0] . ' ' . $validator->errors()->first();
            return $this->err($errorCode, $errorText);
        }
        $userInfo = AccountInfo::where('uid',$uid)->first();
        if(!$userInfo){
            return $this->err(404, "User Not Found");
        }
        if(!isset($params['lat']) || empty($params['lat']) || !isset($params['long']) || empty($params['long'])){

            $params['lat'] = $userInfo->lat;
            $params['long'] = $userInfo->long;
        }
        $radius = 250; // km => converted to meter

        $query = AccountInfo::where('uid', '!=', $uid);

        if($userInfo->intention){
            $intentions = array_filter(explode(',',$userInfo->intention));
            if(count($intentions) == 1){
                $query = $query->whereRaw(\DB::raw('FIND_IN_SET('.$intentions[0].',intention)'));
            }else{
                $query = $query->where(function($query) use ($intentions){
                    foreach ($intentions as $intention){
                        $query->orWhereRaw(\DB::raw('FIND_IN_SET('.$intention.',intention)'));
                    }
                });

            }
        }

        if(isset($params['pop_cert']) && is_numeric($params['pop_cert'])){
            $query = $query->where('pop_cert',$params['pop_cert']);
        }

        if(isset($params['sex']) && !empty($params['sex']) && $params['sex'] != 3){
            $query = $query->where('sex',$params['sex']);
        }

        if(isset($params['age_min']) && !empty($params['age_min'])){
            $query = $query->where('age','>=',$params['age_min']);
        }

        if(isset($params['age_max']) && !empty($params['age_max'])){
            $query = $query->where('age','<=',$params['age_max']);
        }

        if(isset($params['constellation']) && !empty($params['constellation'])){
            $query = $query->where('constellation',$params['constellation']);
        }

        if(isset($params['distance']) && is_numeric($params['distance']) && $params['distance'] > 0){
            $radius = $params['distance'];
        }

        $angle_radius = (float)$radius / ( 111 * cos( (float)$params['lat'] ) ); // Every lat|lon degree° is ~ 111Km

        $page_size = isset($params['page_size']) ? $params['page_size'] : 30;

        if($params['lat'] && $params['long']){
            if($angle_radius > 0){
                $min_lat = (float)$params['lat'] - (float)$angle_radius;
                $max_lat = (float)$params['lat'] + (float)$angle_radius;
                $min_lon = (float)$params['long'] - (float)$angle_radius;
                $max_lon = (float)$params['long'] + (float)$angle_radius;
            }else{
                $max_lat = (float)$params['lat'] - (float)$angle_radius;
                $min_lat = (float)$params['lat'] + (float)$angle_radius;
                $max_lon = (float)$params['long'] - (float)$angle_radius;
                $min_lon = (float)$params['long'] + (float)$angle_radius;
            }

            $query = $query->whereBetween('lat', [$min_lat, $max_lat])
                ->whereBetween('long', [$min_lon, $max_lon]);
        }
        $persons = $query->get(['nickname','uid','avatar','age','sex','pop_cert','country','city','height','weight','actived_time','im_userid','facebook','instagram','twitter','line','telegram','lat','long'])
            ->forPage($params['page'],$page_size);

        $attributes['total'] = $query->count();
        $attributes['page'] = (int)$params['page'];
        $attributes['page_size'] = $page_size;
        $list = [];
        $persons->each(function($person) use ($params) {
            /* auth user coordinate vs user's coordinates */
            $distance = 999.99;
            if($params['lat'] && $params['long']){
                $point1 = array('lat' => $params['lat'], 'long' => $params['long']);
                $point2 = array('lat' => $person->lat, 'long' => $person->long);
                $distance = $person->getDistanceBetweenPoints($point1['lat'], $point1['long'], $point2['lat'], $point2['long']);
            }
            $person->distance = $distance;
            $person->actived_time = $person->parseActiveTime($person->actived_time);
            $dynamicPictures = DB::table("user_dynamic_pictures")->where('uid',$person->uid)->orderByDesc('created_at')->limit(5)->pluck('picture');
            $person->dynamic_pictures = $dynamicPictures;

        });
        foreach($persons as $key=>$one){
            array_push($list,$one);
        }
        $data['list'] = $list;
        $data['attributes'] = $attributes;
        return $this->succ($data);
    }

    //首页附近的人模块列表
    public function getNearbyUserList(Request $request){

        $uid = $request->get('uid');
        $params = $request->all();
        Validator::extend('latlong', function ($attribute, $value, $parameters, $validator) {
            //UTF-8汉字字母数字下划线
            $reg = '/^(-)?[0-9]+(\.[0-9]+)?$/';
            //获取字节长度
            $nameLength = strlen($value);
            if ($nameLength >= 1 && $nameLength <= 20 && preg_match($reg, $value)) {
                return true;
            }
            return false;
        });
        $validator = Validator::make($params, [
            'page' => [ 'required','integer', 'between:1,150'],
            'page_size' => ['integer', 'between:10,50'],
            'distance' => [ 'integer', 'between:0,150'],//距离
            'lat' => [ 'string', 'latlong'],//纬度
            'long' => [ 'string', 'latlong'],//经度
            'sex' => [ 'required','integer', 'between:1,3'],//性别
            'constellation' => [ 'string', 'in:Aquarius,Pisces.Aries,Taurus,Gemini,Cancer,Leo,Virgo,Libra,Scorpio,Sagittarius,Capricorn'],//星座
            'age_min' => [ 'integer', 'between:18,80'],//年龄
            'age_max' => [ 'integer', 'between:18,80'],//年龄
            'pop_cert' => [ 'integer', 'in:0,70,80,90,99,100'],//人气认证
        ]);

        if ($validator->fails()) {
            $errorCode = 1070;
            $errorText = 'Data is not legal' . ':' . $validator->errors()->keys()[0] . ' ' . $validator->errors()->first();
            return $this->err($errorCode, $errorText);
        }
        $userInfo = AccountInfo::where('uid',$uid)->first();
        if(!$userInfo){
            return $this->err(404, "User Not Found");
        }
        if(!isset($params['lat']) || empty($params['lat']) || !isset($params['long']) || empty($params['long'])){

            $ip = $request->getClientIp();
            $coordinates = AccountInfo::getCoordinatesByIP($ip);
            if($coordinates){
                $params['lat'] = $coordinates['lat'];
                $params['long'] = $coordinates['long'];
            }else{
                $params['lat'] = $userInfo->lat;
                $params['long'] = $userInfo->long;
            }

        }
        $radius = 250; // km => converted to meter

        $query = AccountInfo::where('uid', '!=', $uid);

        if($userInfo->intention){
            $intentions = array_filter(explode(',',$userInfo->intention));
            if(count($intentions) == 1){
                $query = $query->whereRaw(\DB::raw('FIND_IN_SET('.$intentions[0].',intention)'));
            }else{
                $query = $query->where(function($query) use ($intentions){
                    foreach ($intentions as $intention){
                        $query->orWhereRaw(\DB::raw('FIND_IN_SET('.$intention.',intention)'));
                    }
                });

            }
        }

        if(isset($params['pop_cert']) && is_numeric($params['pop_cert'])){
            $query = $query->where('pop_cert',$params['pop_cert']);
        }

        if(isset($params['sex']) && !empty($params['sex']) && $params['sex'] != 3){
            $query = $query->where('sex',$params['sex']);
        }

        if(isset($params['age_min']) && !empty($params['age_min'])){
            $query = $query->where('age','>=',$params['age_min']);
        }

        if(isset($params['age_max']) && !empty($params['age_max'])){
            $query = $query->where('age','<=',$params['age_max']);
        }

        if(isset($params['constellation']) && !empty($params['constellation'])){
            $query = $query->where('constellation',$params['constellation']);
        }

        if(isset($params['distance']) && is_numeric($params['distance']) && $params['distance'] > 0){
            $radius = $params['distance'];
        }


        if($params['lat'] && $params['long']){
            $angle_radius = (float)$radius / ( 111 * cos( (float)$params['lat'] ) ); // Every lat|lon degree° is ~ 111Km
            if($angle_radius > 0){
                $min_lat = (float)$params['lat'] - (float)$angle_radius;
                $max_lat = (float)$params['lat'] + (float)$angle_radius;
                $min_lon = (float)$params['long'] - (float)$angle_radius;
                $max_lon = (float)$params['long'] + (float)$angle_radius;
            }else{
                $max_lat = (float)$params['lat'] - (float)$angle_radius;
                $min_lat = (float)$params['lat'] + (float)$angle_radius;
                $max_lon = (float)$params['long'] - (float)$angle_radius;
                $min_lon = (float)$params['long'] + (float)$angle_radius;
            }
            $query = $query->whereBetween('lat', [$min_lat, $max_lat])
                ->whereBetween('long', [$min_lon, $max_lon]);
        }
        $page_size = isset($params['page_size']) ? $params['page_size'] : 30;
        $persons = $query->get(['nickname','uid','avatar','age','sex','pop_cert','country','city','height','weight','actived_time','im_userid','facebook','instagram','twitter','line','telegram','lat','long'])
            ->forPage($params['page'],$page_size);
        $attributes['total'] = $query->count();
        $attributes['page'] = (int)$params['page'];
        $attributes['page_size'] = $page_size;
        $list= [];

        $persons->each(function($person) use ($params,$uid) {
            /* auth user coordinate vs user's coordinates */
            $distance = 999.99;
            if($params['lat'] && $params['long']){
                $point1 = array('lat' => $params['lat'], 'long' => $params['long']);
                $point2 = array('lat' => $person->lat, 'long' => $person->long);
                $distance = $person->getDistanceBetweenPoints($point1['lat'], $point1['long'], $point2['lat'], $point2['long']);
            }
            $person->distance = $distance;
            $lastOnlineTime = Redis::Hget($uid,'last_online_time');
            if(!$lastOnlineTime){
                $lastOnlineTime = strtotime((int)$person->last_login_time);
            }
            $person->last_online_time = $lastOnlineTime;
            $person->actived_time = $person->parseActiveTime($lastOnlineTime);
            $dynamicPictures = DB::table("user_dynamic_pictures")->where('uid',$person->uid)->orderByDesc('created_at')->limit(5)->pluck('picture');
            $person->dynamic_pictures = $dynamicPictures;
        });
        foreach($persons as $key=>$one){
            array_push($list,$one);
        }
        $data['list'] = $list;
        $data['attributes'] = $attributes;
        return $this->succ($data);
    }

    //首页查询接口
    public function searchUser(Request $request){

        $uid = $request->get('uid');
        Validator::extend('nickname', function ($attribute, $value, $parameters, $validator) {
            //UTF-8汉字字母数字下划线
            $reg = '/^[\x{4e00}-\x{9fa5}A-Za-z0-9_]+$/u';
            //获取字节长度
            $nameLength = strlen($value);
            if ($nameLength >= 1 && $nameLength <= 21 && preg_match($reg, $value)) {
                return true;
            }
            return false;
        });
        $params = $request->all();
        $validator = Validator::make($params, [
            'nickname' => ['required', 'string','nickname'],
            'page' => [ 'required','integer', 'between:1,150'],
            'page_size' => ['integer', 'between:10,50'],
        ]);
        if ($validator->fails()) {
            $errorCode = 1070;
            $errorText = 'Data is not legal' . ':' . $validator->errors()->keys()[0] . ' ' . $validator->errors()->first();
            return $this->err($errorCode, $errorText);
        }
        $page_size = isset($params['page_size']) ? $params['page_size'] : 30;
        $query = AccountInfo::where('uid', '!=', $uid);
        $users = $query
            ->where('nickname', 'like', '%' . $params['nickname'] . '%')
            ->get(['nickname','uid','avatar','pop_cert','im_userid'])
            ->forPage($params['page'],$page_size);


        $attributes['total'] = $query->count();
        $attributes['page'] = (int)$params['page'];
        $attributes['page_size'] = $page_size;

        $data['list'] = $users;
        $data['attributes'] = $attributes;
        return $this->succ($data);
    }

    public function testIp(Request $request){

        $str = 'eyJpdiI6IkZ1c1NubUlJaEZUeHcvTEFaZkxJb3c9PSIsInZhbHVlIjoiZUJHZWk0QTlTY2N3MkRpTUF2N1dmZ050S2NCOGxjbVc5UUlpU3ExMnhkRT0iLCJtYWMiOiI3OTgxZmY4OTFlM2EwMmE4NDJmNDQyMmM0NTk0NWE2Zjk1ZDE5MTM4NTFkMzI3YTZhYmE1ODg3NjNmNzA0MjY3IiwidGFnIjoiIn0=';
        var_dump(decrypt($str));exit;
        $uid = $request->get('uid');
        $page = isset($params['page']) && !empty($params['page']) && is_numeric($params['page']) ? $params['page'] : 1;
        $page_size = isset($params['page_size']) && !empty($params['page_size']) && is_numeric($params['page_size']) ? $params['page'] : 10;
        $userInfo = AccountInfo::where('uid',$uid)->first();
        $ip = $request->getClientIp();
        $coordinates = AccountInfo::getCoordinatesByIP($ip);

        if($coordinates){
            $params['lat'] = $coordinates['lat'];
            $params['long'] = $coordinates['long'];
        }else{
            $params['lat'] = $userInfo->lat;
            $params['long'] = $userInfo->long;
        }
        $radius = 250; // km => converted to meter

        $query = AccountInfo::where('uid', '!=', $uid);

        if($params['lat'] && $params['long']){
            $angle_radius = (float)$radius / ( 111 * cos( (float)$params['lat'] ) ); // Every lat|lon degree° is ~ 111Km
            if($angle_radius > 0){
                $min_lat = (float)$params['lat'] - (float)$angle_radius;
                $max_lat = (float)$params['lat'] + (float)$angle_radius;
                $min_lon = (float)$params['long'] - (float)$angle_radius;
                $max_lon = (float)$params['long'] + (float)$angle_radius;
            }else{
                $max_lat = (float)$params['lat'] - (float)$angle_radius;
                $min_lat = (float)$params['lat'] + (float)$angle_radius;
                $max_lon = (float)$params['long'] - (float)$angle_radius;
                $min_lon = (float)$params['long'] + (float)$angle_radius;
            }
            $query = $query->whereBetween('lat', [$min_lat, $max_lat])
                ->whereBetween('long', [$min_lon, $max_lon]);
        }
        $getCount = $page * $page_size;

        $count = $query->count();

        if($getCount > $count){

        }

        $persons = $query->get(['nickname','uid','avatar','age','sex','pop_cert','country','city','height','weight','actived_time','im_userid','facebook','instagram','twitter','line','telegram','lat','long'])
            ->forPage($page,$page_size);
        $attributes['total'] = $query->count();
        $attributes['page'] = $page;
        $attributes['page_size'] = $page_size;
        $list= [];

        $persons->each(function($person) use ($params,$uid) {
            /* auth user coordinate vs user's coordinates */
            $point1 = array('lat' => $params['lat'], 'long' => $params['long']);
            $point2 = array('lat' => $person->lat, 'long' => $person->long);
            $distance = 'unknow';
            if($params['lat'] && $params['long']){
                $distance = $person->getDistanceBetweenPoints($point1['lat'], $point1['long'], $point2['lat'], $point2['long']);
            }
            $person->distance = $distance;
            $lastOnlineTime = Redis::Hget($uid,'last_online_time');
            if(!$lastOnlineTime){
                $lastOnlineTime = strtotime((int)$person->last_login_time);
            }
            $person->last_online_time = $lastOnlineTime;
            $person->actived_time = $person->parseActiveTime($lastOnlineTime);
            $dynamicPictures = DB::table("user_dynamic_pictures")->where('uid',$person->uid)->orderByDesc('created_at')->limit(5)->pluck('picture');
            $person->dynamic_pictures = $dynamicPictures;
        });
        foreach($persons as $key=>$one){
            array_push($list,$one);
        }
        $data['list'] = $list;
        $data['attributes'] = $attributes;
        return $this->succ($data);

        //Create a lookup function for display
//        $ipTools = new \IP2Location\IpTools();

// Validate IPv4 address
//        var_dump($ipTools->isIpv4('8.8.8.8'));exit;
            //Try query the geolocation information of 8.8.8.8 IP address
//            $records = (new IP2LocationLaravel)->get('171.96.73.11', 'bin');
//        $ip = $request->getClientIp();
//        $res = AccountInfo::getCoordinatesByIP($ip);
//        print_r($res);exit;
        $path = env('IP2LOCATION_DB_PATH','/data/www/IP2LOCATION-LITE-DB11.BIN');
        $db = new \IP2Location\Database($path, \IP2Location\Database::FILE_IO);

        $records = $db->lookup('171.96.73.11', \IP2Location\Database::ALL);

        echo $db->getDate();


        print_r($records);exit;

            echo 'IP Number             : ' . $records['ipNumber'] . "<br>";
            echo 'IP Version            : ' . $records['ipVersion'] . "<br>";
            echo 'IP Address            : ' . $records['ipAddress'] . "<br>";
            echo 'Country Code          : ' . $records['countryCode'] . "<br>";
            echo 'Country Name          : ' . $records['countryName'] . "<br>";
            echo 'Region Name           : ' . $records['regionName'] . "<br>";
            echo 'City Name             : ' . $records['cityName'] . "<br>";
            echo 'Latitude              : ' . $records['latitude'] . "<br>";
            echo 'Longitude             : ' . $records['longitude'] . "<br>";
            echo 'Area Code             : ' . $records['areaCode'] . "<br>";
            echo 'IDD Code              : ' . $records['iddCode'] . "<br>";
            echo 'Weather Station Code  : ' . $records['weatherStationCode'] . "<br>";
            echo 'Weather Station Name  : ' . $records['weatherStationName'] . "<br>";
            echo 'MCC                   : ' . $records['mcc'] . "<br>";
            echo 'MNC                   : ' . $records['mnc'] . "<br>";
            echo 'Mobile Carrier        : ' . $records['mobileCarrierName'] . "<br>";
            echo 'Usage Type            : ' . $records['usageType'] . "<br>";
            echo 'Elevation             : ' . $records['elevation'] . "<br>";
            echo 'Net Speed             : ' . $records['netSpeed'] . "<br>";
            echo 'Time Zone             : ' . $records['timeZone'] . "<br>";
            echo 'ZIP Code              : ' . $records['zipCode'] . "<br>";
            echo 'Domain Name           : ' . $records['domainName'] . "<br>";
            echo 'ISP Name              : ' . $records['isp'] . "<br>";
            echo 'Address Type          : ' . $records['addressType'] . "<br>";
            echo 'Category              : ' . $records['category'] . "<br>";
    }


}
