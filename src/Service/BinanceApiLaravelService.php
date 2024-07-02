<?php

namespace Sdtech\BinanceApiLaravel\Service;


/*

*/

class BinanceApiLaravelService

{

    private $imgManager;
    public function __construct()
    {
        
    }

    public function testing(){
        return 'ok google';
    }


    private function is_setup() {
        return true;
    }

    private function sendResponse($status,$message = "",$data = [])
    {
        return [
            'success' => $status,
            'message' => $message ? $message : 'Something went wrong',
            'data' => $data
        ];
    }


}
