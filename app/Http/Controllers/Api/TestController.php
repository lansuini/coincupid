<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\GuzzleException;

class TestController extends Controller
{
    public function imUserSig(Request $request)
    {
        $api = new \Tencent\TLSSigAPIv2(1400000000, '5bd2850fff3ecb11d7c805251c51ee463a25727bddc2385f3fa8bfee1bb93b5e');
        $sig = $api->genUserSig('xiaojun');
        echo $sig . "\n";
    }

    protected function getUserSig($userId)
    {
        $api = new \Tencent\TLSSigAPIv2(env('IM_APPID'), env('IM_KEY'));
        return $api->genUserSig($userId);
    }

    public function imrestApi(Request $request)
    {
        $args = [];
        $args['contenttype'] = 'json';
        $args['path'] = 'adminapisgp.im.qcloud.com';
        $args['method'] = 'v4/group_open_http_svc/get_appid_group_list';
        $args['sdkappid'] = env('IM_APPID');
        $args['identifier'] = env('IM_ADMIN');
        $args['usersig'] = $this->getUserSig(env('IM_ADMIN'));
        $args['random'] = rand(0, 4294967295);

        $args['data'] = [
            "Limit" => 1000,
            "Next" => 0
        ];

        $client = new Client([
            'timeout'  => env('API_REQUEST_TIME_OUT', 8.0),
            'headers' => [
                'User-Agent' => env('API_REQUEST_NAME', 'IG GAME'),
            ]
        ]);

        $sMethod = 'POST';
        $sParams = ['json' => $args['data']];
        $sUrl = "https://{$args['path']}/{$args['method']}?sdkappid={$args['sdkappid']}&identifier={$args['identifier']}&usersig={$args['usersig']}&random={$args['random']}&contenttype={$args['contenttype']}";
        try {
            $response = $client->request($sMethod, $sUrl, $sParams);
            $sCode = $response->getStatusCode();
            $sResponse = (string) $response->getBody()->getContents();
            $res = json_decode($sResponse, true);
            Log::error('IM info', [$sCode, $res]);
        } catch (GuzzleException $e) {
            $m1 = Psr7\Message::toString($e->getRequest());
            $m2 = $e->getMessage();
            Log::error('IM error', [$m1, $m2]);
        }

        return [$sCode, $res];
    }

    public function imtestfriend(Request $request)
    {
        $tim = new \App\Http\Library\TIM();
        $userid1 = 'A1';
        $userid2 = 'A2';
        $userid3 = 'A3';
        $userid4 = 'A4';
        // $tim->accountImport($userid1, $userid1, '');
        // $tim->accountImport($userid2, $userid2, '');
        // $tim->accountImport($userid4, $userid4, '');
        $tim->friendAdd(['From_Account' => $userid1, 'AddFriendItem' => [
            [
                'To_Account' => $userid4,
                "AddSource" => "AddSource_Type_XXXXXXXX",
            ]
        ]]);
        $res1 = $tim->friendCheck($userid1, $userid2);
        // $res2 = $tim->friendGet($userid1);
        // dd([$res1, $res2]);

        $res3 = $tim->friendCheck($userid1, [$userid2, $userid3, $userid4]);
        dd([$res1, $res3]);
        
    }

    public function imrestApi1(Request $request)
    {
        // $url = 'https://xxxxxx/v4/group_open_http_svc/create_group?sdkappid=88888888&identifier=admin&usersig=xxx&random=99999999&contenttype=json'
        $args = [];
        $args['contenttype'] = 'json';
        $args['path'] = 'adminapisgp.im.qcloud.com';
        $args['method'] = 'v4/group_open_http_svc/create_group';
        $args['sdkappid'] = env('IM_APPID');
        $args['identifier'] = env('IM_ADMIN');
        $args['usersig'] = $this->getUserSig(env('IM_ADMIN'));
        $args['random'] = rand(0, 4294967295);

        $args['data'] = [
            'Owner_Account' => '11111', // 群主的 UserId（选填）
            'Type' => 'Public',
            "Name" => 'TestGroup',
        ];

        $client = new Client([
            'timeout'  => env('API_REQUEST_TIME_OUT', 8.0),
            'headers' => [
                'User-Agent' => env('API_REQUEST_NAME', 'IG GAME'),
            ]
        ]);

        $sMethod = 'POST';
        $sParams = ['json' => $args['data']];
        $sUrl = "https://{$args['path']}/{$args['method']}?sdkappid={$args['sdkappid']}&identifier={$args['identifier']}&usersig={$args['usersig']}&random={$args['random']}&contenttype={$args['contenttype']}";
        try {
            $response = $client->request($sMethod, $sUrl, $sParams);
            $sCode = $response->getStatusCode();
            $sResponse = (string) $response->getBody()->getContents();
            $res = json_decode($sResponse, true);
            Log::error('IM info', [$sCode, $res]);
        } catch (GuzzleException $e) {
            $m1 = Psr7\Message::toString($e->getRequest());
            $m2 = $e->getMessage();
            Log::error('IM error', [$m1, $m2]);
        }

        return [$sCode, $res];
    }

    public function test(Request $request){
        $params = $request->all();
        $encrypted = 'FrWNrKtMb3eCQXoQGMStFA7RlaaHfBIXiGfu4gMZoQa9mbJFWXRiQXMh_nhgePzElu9cMRe9YWGv8jnqhXF2kjhw-gaJ2_eFqTXXQvp9oiH4PRjkgGpvi29XydFRFLIB0YN8sSoq2EuvBVsLL-9wszw6toQKUazbA55tF7MJRtI';
        $encrypted = isset($params['sign']) && !empty($params['sign']) ? $params['sign'] : $encrypted;

        $s = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCGWFUgubNmox/iUASqZubuA1z1tdF08toqt9d7GA/nJ5OUnu6h1tB9fEoCMUPdowNrAnmMffb5HNS7pAfsJHy9md/ErEzGMmYcaEwn/X3m6cJbeEq0M3aohSONcOxs6bHrfSUZtsmK+jK4/p4hjjGwUA+UGYMHGrzPRCN+3ongpwIDAQAB';
        $public_key =  $this->getRsaPublicKey($s);

        $res = $this->getDecry($encrypted,$public_key);
//        $res = base64_decode($s);
        var_dump($res);exit;
    }

    protected function getRsaPublicKey($key){
        return "-----BEGIN PUBLIC KEY-----\n".wordwrap($key, 64, "\n", TRUE)."\n-----END PUBLIC KEY-----";
    }

    private function getDecry($encrypted,$public_key){
        $encrypted = str_replace('-','+',$encrypted);
        $encrypted = str_replace('_','/',$encrypted);
        //解析公钥
//        $res = openssl_pkey_get_public($this->getRsaPublicKey($public_key));
        //密文过长，分段解密
        $crypto = '';
        foreach (str_split(base64_decode($encrypted), 128) as $chunk) {
            openssl_public_decrypt($chunk, $decryptData, $public_key);
            $crypto .= $decryptData;
        }
        return $crypto;
    }

    public function testSettle(){

        $merchantNo = "10000004";
        # 密钥
        $signKey = "9b759040321a408a5c7768b4511287a6";
        $merchantOrderNo = date('YmdHis') . rand(100000, 999999);
        $data = [
            'merchantNo' => $merchantNo,
            'merchantOrderNo' => $merchantOrderNo,
            'merchantReqTime' => date("YmdHis"),
            'orderAmount' => 100.00,
            'tradeSummary' => 'test',
            'bankCode' => 'Globe Gcash',
            'bankName' => '兴宁支行',
            'bankAccountNo' => '6222032007001334680',
            'bankAccountName' => 'helloworld',
            'province' => 'guangdong',
            'city' => 'meizhou',
            'orderReason' => '测试代付',
            'requestIp' => '159.138.86.177',
            'backNoticeUrl' => 'http://cb.luckyp666.com/pay/callback/' . $merchantOrderNo,
            'merchantParam' => 'fuck',
        ];
        $output = $this->getSettlementOrder($data,$signKey,$merchantNo);
        var_dump($output);
    }

    public function testPay(){

        $merchantNo = "10000004";
//        $merchantNo = "10000002";
        # 密钥
        $signKey = "9b759040321a408a5c7768b4511287a6";
//        $signKey = "9b759040321a408a5c7768b4511287a";
        $merchantOrderNo = date('YmdHis') . rand(100000, 999999);
        $params = [
            'merchantNo' => $merchantNo,
            'merchantOrderNo' => $merchantOrderNo,
            'merchantReqTime' => date("YmdHis"),
            'orderAmount' => 50,
            'tradeSummary' => 'test',
            'payModel' => 'Direct',
            'payType' => 'BPIA',
            'cardType' => 'DEBIT',
            'userTerminal' => 'Phone',
            'userIp' => '127.0.0.1',
            'backNoticeUrl' => 'http://cb.xddzfcsz.com/settlement/callback/'.$merchantOrderNo,
            'merchantParam' => 'abc1',
        ];
//        $params = [
//            'merchantNo' => $merchantNo,
//            'merchantOrderNo' => "202304211123171210",
//            'merchantReqTime' => 20230421112317,
//            'orderAmount' => 50.00,
//            'tradeSummary' => 'recharge',
//            'payModel' => 'Direct',
//            'payType' => '711_direct',
//            'bankCode' => '',
//            'cardType' => 'DEBIT',
//            'userTerminal' => 'Phone',
//            'userIp' => '127.0.0.1',
//            'backNoticeUrl' => 'https://api-www.lodislot.com/pay/callback/luckypay',
//            'merchantParam' => 'test',
//        ];
        $output = $this->getPayOrder($params,$signKey);
        var_dump($output);
    }

    public function testBalance(){

        $merchantNo = "10000004";
        # 密钥
        $signKey = "9b759040321a408a5c7768b4511287a6";
        $params = [
            'merchantNo' => $merchantNo,
        ];
        $url = 'http://gate.luckypay.mm'.'/paygateway/query/balance';

        $output = $this->query($params,$signKey,$url);
        var_dump($output);
    }

    public function testQueryPay(){

        $merchantNo = "10000004";
        # 密钥
        $signKey = "9b759040321a408a5c7768b4511287a6";
        $params = [
            'merchantNo' => $merchantNo,
            'merchantOrderNo' => "20230418120628979056",
        ];
        $url = 'http://gate.luckypay.mm'.'/paygateway/query/pay';

        $output = $this->query($params,$signKey,$url);
        var_dump($output);
    }

    public function testQuerySettle(){

        $merchantNo = "10000004";
        # 密钥
        $signKey = "9b759040321a408a5c7768b4511287a6";
        $params = [
            'merchantNo' => $merchantNo,
            'merchantOrderNo' => "20230418123153694729",
        ];
        $url = 'http://gate.luckypay.mm'.'/paygateway/query/settlement';

        $output = $this->query($params,$signKey,$url);
        var_dump($output);
    }

    public function query($param,$signKey,$url)
    {
        $sign = $this->getSign($param,$signKey);
        $param['sign'] = $sign;
        $data = $this->doPost($url, $param);
        return json_decode($data, true);
    }

    public function getPayOrder($param,$signKey)
    {
        $gateway = 'http://gate.luckypay.mm';
        $sign = $this->getSign($param,$signKey);
//        var_dump($sign);
        $param['sign'] = $sign;
        $data = $this->doPost($gateway . '/paygateway/pay', $param);
        return json_decode($data, true);
    }

    public function getSettlementOrder($param,$signKey,$merchantNo)
    {
        $gateway = 'http://gate.luckypay.mm';
        $param['merchantNo'] = $merchantNo;
        $sign = $this->getSign($param,$signKey);
        $param['sign'] = $sign;
        $data = $this->doPost($gateway . '/paygateway/settlement', $param);
        return json_decode($data, true);
    }

    protected function doPost($url, $param)
    {
        $data_string = json_encode($param);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        // 执行后不直接打印出来
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // 设置请求方式为post
        curl_setopt($ch, CURLOPT_POST, true);
        // post的变量
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Content-Length: ' . strlen($data_string)]);
        // 请求头，可以传数组
        // curl_setopt($ch, CURLOPT_HEADER, $header);
        // curl_setopt($ch, CURLOPT_HEADER, 1);
        // 跳过证书检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        // 不从证书中检查SSL加密算法是否存在
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $output = curl_exec($ch);
        curl_close($ch);
        echo PHP_EOL;
        echo $url;
        echo PHP_EOL;
        echo $data_string;
//        print_r($output);
        var_dump($output);

        echo PHP_EOL;
        return $output;
    }

    protected function getSign($param,$signKey)
    {
        $newParam = array_filter($param);
        if (!empty($newParam)) {
            $fields = array_keys($newParam);
            $sortParam = [];
            sort($fields);
            foreach ($fields as $k => $v) {
                $sortParam[] = $v . '=' . $newParam[$v];
            }
            $originalString = implode('&', $sortParam) . $signKey;
        } else {
            $originalString = $signKey;
        }
        return md5($originalString);
    }
}
