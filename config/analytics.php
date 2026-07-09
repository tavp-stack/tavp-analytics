<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Analytics Configuration
    |--------------------------------------------------------------------------
    |
    | Configure tracking, fraud detection, and dashboard settings.
    |
    */

    'enabled' => env('ANALYTICS_ENABLED', true),

    'track_page_views' => env('ANALYTICS_TRACK_PAGEVIEWS', true),

    'track_events' => env('ANALYTICS_TRACK_EVENTS', true),

    'geolocation_enabled' => env('ANALYTICS_GEOLOCATION', true),

    'fraud_detection_enabled' => env('ANALYTICS_FRAUD_DETECTION', true),

    'session_recording_enabled' => env('ANALYTICS_SESSION_RECORDING', false),

    'exclude_paths' => [
        'api/*',
        '_debugbar/*',
        'telescope/*',
        'horizon/*',
        'sanctum/*',
    ],

    'exclude_ips' => [
        '127.0.0.1',
        '::1',
    ],

    'session_duration' => 30, // minutes

    'realtime_window' => 5, // minutes

    'dashboard_path' => '/analytics',

    'dashboard_enabled' => true,

    'api_prefix' => '/api/analytics',

    'fraud' => [
        'bot_detection' => true,
        'velocity_limit' => 100, // requests per window
        'velocity_window' => 60, // seconds
        'anomaly_threshold' => 3.0,
        'block_suspicious' => false,
        'min_score_to_flag' => 0.5,
        'min_score_to_block' => 0.8,
    ],

    'session_recording' => [
        'enabled' => false,
        'max_events' => 10000,
        'record_inputs' => false,
        'record_scroll' => true,
        'record_clicks' => true,
        'record_mouse' => false,
        'record_console' => false,
    ],

    'experiments' => [
        'enabled' => true,
        'cookie_duration' => 30, // days
    ],

    'funnels' => [
        'enabled' => true,
    ],
];
