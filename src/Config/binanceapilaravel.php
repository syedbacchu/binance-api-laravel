<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Binance Api Requirements
    |--------------------------------------------------------------------------
    |
    | The binance api url
    | api version
    |
    */

    'BINANCE_API_BASE_URL' => env('BINANCE_API_BASE_URL') ?? "",
    'BINANCE_API_ACCESS_TOKEN' => env('BINANCE_API_ACCESS_TOKEN') ?? "",
    'BINANCE_API_SECRET_KEY' => env('BINANCE_API_SECRET_KEY') ?? "",
    'BINANCE_API_MODE' => env('BINANCE_API_MODE') ?? 'test',
];
