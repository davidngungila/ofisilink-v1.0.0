<?php

return [
    /*
    |--------------------------------------------------------------------------
    | ZKTeco Device Configuration
    |--------------------------------------------------------------------------
    |
    | Default settings for ZKTeco biometric devices
    |
    */

    'ip' => env('ZKTECO_IP', '192.168.100.108'), // Device IP address
    'port' => env('ZKTECO_PORT', 4370),
    'password' => env('ZKTECO_PASSWORD', 0), // Communication Key (0 = no password)
    'device_id' => env('ZKTECO_DEVICE_ID', 6), // Device ID (for multi-device setups)
    
    // Server IP (for Push SDK configuration on device)
    'server_ip' => env('ZKTECO_SERVER_IP', 'live.ofisilink.com'), // Live server domain
    'server_port' => env('ZKTECO_SERVER_PORT', 443), // HTTPS port for live server

    /*
    |--------------------------------------------------------------------------
    | Push SDK Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for ZKTeco Push SDK (ADMS) real-time attendance
    |
    */

    'push_sdk' => [
        'enabled' => env('ZKTECO_PUSH_SDK_ENABLED', true),
        'endpoint' => env('ZKTECO_PUSH_SDK_ENDPOINT', '/iclock/getrequest'),
        'data_endpoint' => env('ZKTECO_PUSH_SDK_DATA_ENDPOINT', '/iclock/cdata'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Device Settings
    |--------------------------------------------------------------------------
    |
    | Additional device configuration
    |
    */

    'timeout' => env('ZKTECO_TIMEOUT', 60), // Timeout for external API calls (in seconds)
    'retry_attempts' => env('ZKTECO_RETRY_ATTEMPTS', 3),
];


