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

    'BINANCE_API_LIVE_URL' => env('BINANCE_API_LIVE_URL') ? env('BINANCE_API_LIVE_URL') : "https://api.binance.com/api/",
    'BINANCE_API_TESTNET_URL' => env('BINANCE_API_TESTNET_URL') ? env('BINANCE_API_TESTNET_URL') : "https://testnet.binance.vision/api/",
    'BINANCE_WAPI_URL' => env('BINANCE_WAPI_URL') ? env('BINANCE_WAPI_URL') : "https://api.binance.com/wapi/",
    'BINANCE_SAPI_URL' => env('BINANCE_SAPI_URL') ? env('BINANCE_SAPI_URL') : "https://api.binance.com/sapi/",
    'BINANCE_FAPI_URL' => env('BINANCE_FAPI_URL') ? env('BINANCE_FAPI_URL') : "https://fapi.binance.com/",
    'BINANCE_BAPI_URL' => env('BINANCE_BAPI_URL') ? env('BINANCE_BAPI_URL') : "https://www.binance.com/bapi/",
    'BINANCE_WSS_STREAM_URL' => env('BINANCE_WSS_STREAM_URL') ? env('BINANCE_WSS_STREAM_URL') : "wss://stream.binance.com:9443/ws/",
    'BINANCE_WSS_STREAM_TESTNET_URL' => env('BINANCE_WSS_STREAM_TESTNET_URL') ? env('BINANCE_WSS_STREAM_TESTNET_URL') : "wss://testnet.binance.vision/ws/",
    'BINANCE_API_KEY' => env('BINANCE_API_KEY') ? env('BINANCE_API_KEY') : "",
    'BINANCE_API_SECRET_KEY' => env('BINANCE_API_SECRET_KEY') ? env('BINANCE_API_SECRET_KEY') : "",
    'BINANCE_API_TEST_MODE' => env('BINANCE_API_TEST_MODE') ? env('BINANCE_API_TEST_MODE') : false,
];
