<?php
return [
    'config' => [
        'credentials' => [
            'aws_access_key_id' => env('AWS_KEY'),
            'aws_secret_key' => env('AWS_SECRET')
        ],
        'version' => env('AWS_VERSION'),
        'region' => env('AWS_REGION')
    ]
];