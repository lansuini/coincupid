<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShortMessages extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'id',
        'name',
        'configs',
        'symbol',
        'created',
        'updated',
        'is_enabled',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var string[]
     */
    protected $hidden = [];

    protected $table = 'short_messages';

    protected $connection = 'Master';

    public $timestamps = false;

    public function sendCaptcha($mobileArea, $mobileNumber, $code)
    {
        $d = $this->getShortMessage();
        $symbol = $d['symbol'];
        $func = 'sendCaptchaBy' . $symbol;
        $return = $this->$func($mobileArea, $mobileNumber, $d['configs'], $code);
        return array_merge(['short_messages_id' => $d['id'], 'symbol' => $d['symbol']], $return);
    }

    public function getShortMessage()
    {
        $res = self::where('is_enabled', 1)->get()->toArray();
        if (empty($res)) {
            return ['symbol' => 'DEV', 'configs' => '{"TEMPLATE" : "your code is {code}"}', 'id' => 0];
        } else {
            $total = count($res);
            $single = (array) $res[rand(0, $total - 1)];
            return $single;
        }
    }

    /**
     * $config = {"TEMPLATE" : "your code is {code}", "TELEGRAM_ROBOT_TOKEN": "5858687505:AAHmua2-T4v33Ay98MYKiSQk2qNitBpiFhk", "TELEGRAM_ROBOT_CHAT_ID": "-650654893"}
     */
    protected function sendCaptchaByDEV($mobileArea, $mobileNumber, $config, &$code)
    {
        // $code = 8888;
        $isSuccess = 1;
        $response = ["error" => ""];
        $config = json_decode($config, true);
        $message = sprintf($config['TEMPLATE'], $code);
        if (!isset($config['TEMPLATE'])) {
            $isSuccess = 0;
            $response = ["error" => "template not defined"];
        }

        if ($isSuccess && !empty($config['TELEGRAM_ROBOT_TOKEN']) && !empty($config['TELEGRAM_ROBOT_CHAT_ID'])) {
            \Artisan::queue('SendMessage2', [
                'text' => $message,
                'telegram_robot_token' => $config['TELEGRAM_ROBOT_TOKEN'],
                'telegram_robot_chat_id' => $config['TELEGRAM_ROBOT_CHAT_ID'],
            ])->onConnection('redis')->onQueue('ShortMessage');
        }

        return ["is_success" => $isSuccess, "response" => $response];
    }

    /**
     * $config = {"TEMPLATE" : "your code is {code}", "KEY": "", "SECRET": ""}
     */
    protected function sendCaptchaByAWS($mobileArea, $mobileNumber, $config, $code)
    {
        $isSuccess = 1;
        $response = null;
        $config = json_decode($config, true);
        $awsConfig = [
            // 'profile' => 'default',
            'region' => $config["REGION"] || 'ap-southeast-1',
            'version' => '2010-03-31',
            'credentials' => [
                'key'   =>  $config["KEY"],
                'secret' => $config["SECRET"],
            ],
        ];

        $SnSclient = new \Aws\Sns\SnsClient($awsConfig);
        $message = sprintf($config['TEMPLATE'], $code);
        $messageConfig = [
            'Message' => $message,
            'PhoneNumber' => $mobileArea . $mobileNumber,
        ];

        try {
            $result = $SnSclient->publish($messageConfig);
            $response = (array) $result;
        } catch (\Aws\Exception\AwsException $e) {
            $isSuccess = 0;
            $response = $e->getMessage();
        }

        return ["is_success" => $isSuccess, "response" => $response];
    }
}
