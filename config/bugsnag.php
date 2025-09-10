<?php

return [

    /*
    |--------------------------------------------------------------------------
    | API Key
    |--------------------------------------------------------------------------
    |
    | Your BugSnag API Key
    |
    */

    'api_key' => env('BUGSNAG_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | App Type
    |--------------------------------------------------------------------------
    |
    | The type of application
    |
    */

    'app_type' => env('BUGSNAG_APP_TYPE', 'Laravel'),

    /*
    |--------------------------------------------------------------------------
    | App Version
    |--------------------------------------------------------------------------
    |
    | The version of the application
    |
    */

    'app_version' => env('BUGSNAG_APP_VERSION', null),

    /*
    |--------------------------------------------------------------------------
    | Release Stage
    |--------------------------------------------------------------------------
    |
    | Current release stage (e.g. production, staging, development)
    |
    */

    'release_stage' => env('BUGSNAG_RELEASE_STAGE', env('APP_ENV', 'production')),

    /*
    |--------------------------------------------------------------------------
    | Notify Release Stages
    |--------------------------------------------------------------------------
    |
    | Which release stages should send notifications to BugSnag
    |
    */

    'notify_release_stages' => ['production', 'staging', 'local'],

    /*
    |--------------------------------------------------------------------------
    | Filters
    |--------------------------------------------------------------------------
    |
    | Filter out sensitive data from error reports
    |
    */

    'filters' => [
        'password',
        'password_confirmation',
        'cc',
        'card_number',
        'cvv',
        'BUGSNAG_API_KEY',
        'POSTMARK_TOKEN',
        'DB_PASSWORD',
    ],

];