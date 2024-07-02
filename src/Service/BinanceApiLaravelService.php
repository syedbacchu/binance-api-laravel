<?php

namespace Sdtech\BinanceApiLaravel\Service;



class BinanceApiLaravelService extends BinanceApiService
{

    private $publicApi;

    public function __construct()
    {
        $this->publicApi = new PublicApiService();
    }

    public function testing(){
        return 'ok testing';
    }

    public function _price(string $symbol)
    {
        return $this->publicApi->price($symbol);
    }

    public function _prices()
    {
        $ser =  new PublicApiService();
        return $this->publicApi->prices();
    }

}
