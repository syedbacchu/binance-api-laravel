<?php

namespace Sdtech\BinanceApiLaravel\Service;


/*

*/

class PublicApiService
{
    private $api;

    public function __construct()
    {
        $this->api = new ApiService();
    }


    /**
     * price get the latest price of a symbol
     *
     * $price = $api->price( "BNBUSDT" );
     *
     * @return array with error message or array with symbol price
     * @throws \Exception
     */
    public function price(string $symbol)
    {
        try {
            $ticker = $this->api->httpRequest("v3/ticker/price", "GET", ["symbol" => $symbol]);
            return $this->api->sendResponse(200,true,'success',$ticker['price']);
        } catch(\Exception $e) {
            return $this->api->sendResponse(400,false,$e->getMessage(),[]);
        }
    }

    public function prices()
    {
        try {
            $ticker = $this->priceData($this->api->httpRequest("v3/ticker/price"));
            return $this->api->sendResponse(200,true,'success',$ticker);
        } catch(\Exception $e) {
            return $this->api->sendResponse(400,false,$e->getMessage(),[]);
        }
    }

    /**
     * priceData Converts Price Data into an easy key/value array
     *
     * $array = $this->priceData($array);
     *
     * @param $array array of prices
     * @return array of key/value pairs
     */
    protected function priceData(array $array)
    {
        $prices = [];
        foreach ($array as $obj) {
            $prices[$obj['symbol']] = $obj['price'];
        }
        return $prices;
    }

}
