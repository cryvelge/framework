<?php

return [
    'main_driver' => env('STORAGE_DEFAULT_DRIVER', 'aliyun'),

    'qiniu' => [
        'access_key' => env('QINIU_ACCESS_KEY'),
        'secret_key' => env('QINIU_SECRET_KEY'),
        'bucket' => env('QINIU_BUCKET'),
        'host' => env('QINIU_HOST'),
        'ssl' => env('QINIU_SSL', false),
    ],
    'aliyun' => [
        'access_key_id' => env('ALIYUN_ACCESS_KEY_ID'),
        'access_key_secret' => env('ALIYUN_ACCESS_KEY_SECRET'),
        'endpoint' => env('ALIYUN_OSS_ENDPOINT'),
        'host' => env('ALIYUN_OSS_HOST'),
        'internal_host' => env('ALIYUN_OSS_INTERNAL_HOST'),
        'bucket' => env('ALIYUN_OSS_BUCKET'),
        'ssl' => env('ALIYUN_OSS_SSL', false),
    ]
];
