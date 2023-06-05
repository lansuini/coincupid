<?php
/**
 * Created by PhpStorm.
 * User: luobinhan
 * Date: 2023/2/21
 * Time: 17:54
 */
#app/config/aws.php
return [
    'version' =>'latest',
    'region'  => env('AWS_DEFAULT_REGION', 'ap-southeast-1'),
    'endpoint' => env('AWS_ENDPOINT', 'https://s3.ap-southeast-1.amazonaws.com'),
    'use_path_style_endpoint' =>env('AWS_USE_PATH_STYLE_ENDPOINT', false),
    'credentials' => [
        'key'    => env('AWS_ACCESS_KEY_ID', 'YOUR_AWS_ACCESS_KEY'),
        'secret' => env('AWS_SECRET_ACCESS_KEY', 'YOUR_AWS_SECRET_KEY'),
    ],
    'Ses' => [
        'region' => env('AWS_SES_REGION', 'ap-southeast-1'),
    ],
];
