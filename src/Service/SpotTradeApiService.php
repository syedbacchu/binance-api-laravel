<?php

namespace Sdtech\BinanceApiLaravel\Service;


class SpotTradeApiService
{
    private $api;

    public function __construct()
    {
        $this->api = new ApiService();
    }

    /**
     * order formats the orders before sending them to the curl wrapper function
     * You can call this function directly or use the helper functions
     *
     * @see buy()
     * @see sell()
     * @see marketBuy()
     * @see marketSell() $this->httpRequest( "https://api.binance.com/api/v1/ticker/24hr");
     *
     * @param $side string typically "BUY" or "SELL"
     * @param $symbol string to buy or sell
     * @param $quantity string in the order
     * @param $price string for the order
     * @param $type string is determined by the symbol bu typicall LIMIT, STOP_LOSS_LIMIT etc.
     * @param $flags array additional transaction options
     * @param $test bool whether to test or not, test only validates the query
     * @return array containing the response
     * @throws \Exception
     */
    public function placeTestOrderSpot(string $side, string $symbol, $quantity, $price, string $type = "LIMIT", array $flags = [])
    {
        try {
            $opt = [
                "symbol" => $symbol,
                "side" => $side,
                "type" => $type,
                "quantity" => $quantity,
                "recvWindow" => 60000,
            ];

            // someone has preformated there 8 decimal point double already
            // dont do anything, leave them do whatever they want
            if (gettype($price) !== "string") {
                // for every other type, lets format it appropriately
                $price = number_format($price, 8, '.', '');
            }

            if (is_numeric($quantity) === false) {
                // WPCS: XSS OK.
                return $this->api->sendResponse(400,false,__("warning: quantity expected numeric got ") . gettype($quantity) . PHP_EOL);
            }

            if (is_string($price) === false) {
                // WPCS: XSS OK.
                return $this->api->sendResponse(400,false,__("warning: price expected string got ") . gettype($price) . PHP_EOL);
            }

            if ($type === "LIMIT" || $type === "STOP_LOSS_LIMIT" || $type === "TAKE_PROFIT_LIMIT") {
                $opt["price"] = $price;
                $opt["timeInForce"] = "GTC";
            }

            if ($type === "MARKET" && isset($flags['isQuoteOrder']) && $flags['isQuoteOrder']) {
                unset($opt['quantity']);
                $opt['quoteOrderQty'] = $quantity;
            }

            if (isset($flags['stopPrice'])) {
                $opt['stopPrice'] = $flags['stopPrice'];
            }

            if (isset($flags['icebergQty'])) {
                $opt['icebergQty'] = $flags['icebergQty'];
            }

            if (isset($flags['newOrderRespType'])) {
                $opt['newOrderRespType'] = $flags['newOrderRespType'];
            }

            $data = $this->api->httpRequest("v3/order/test", "POST", $opt, true);

            return $this->api->sendResponse(200,true,__('Order placed successfully'),$data);
        } catch(\Exception $e) {
            return $this->api->sendResponse(500,false,$e->getMessage(),[]);
        }
    }


    /**
     * order formats the orders before sending them to the curl wrapper function
     * You can call this function directly or use the helper functions
     *
     * @see buy()
     * @see sell()
     * @see marketBuy()
     * @see marketSell() $this->httpRequest( "https://api.binance.com/api/v1/ticker/24hr");
     *
     * @param $side string typically "BUY" or "SELL"
     * @param $symbol string to buy or sell
     * @param $quantity string in the order
     * @param $price string for the order
     * @param $type string is determined by the symbol bu typicall LIMIT, STOP_LOSS_LIMIT etc.
     * @param $flags array additional transaction options
     * @param $test bool whether to test or not, test only validates the query
     * @return array containing the response
     * @throws \Exception
     */
    public function placeOrderSpot(string $side, string $symbol, $quantity, $price, string $type = "LIMIT", array $flags = [])
    {
        try {
            $opt = [
                "symbol" => $symbol,
                "side" => $side,
                "type" => $type,
                "quantity" => $quantity,
                "recvWindow" => 60000,
            ];

            // someone has preformated there 8 decimal point double already
            // dont do anything, leave them do whatever they want
            if (gettype($price) !== "string") {
                // for every other type, lets format it appropriately
                $price = number_format($price, 8, '.', '');
            }

            if (is_numeric($quantity) === false) {
                // WPCS: XSS OK.
                return $this->api->sendResponse(400,false,__("warning: quantity expected numeric got ") . gettype($quantity) . PHP_EOL);
            }

            if (is_string($price) === false) {
                // WPCS: XSS OK.
                return $this->api->sendResponse(400,false,__("warning: price expected string got ") . gettype($price) . PHP_EOL);
            }

            if ($type === "LIMIT" || $type === "STOP_LOSS_LIMIT" || $type === "TAKE_PROFIT_LIMIT") {
                $opt["price"] = $price;
                $opt["timeInForce"] = "GTC";
            }

            if ($type === "MARKET" && isset($flags['isQuoteOrder']) && $flags['isQuoteOrder']) {
                unset($opt['quantity']);
                $opt['quoteOrderQty'] = $quantity;
            }

            if (isset($flags['stopPrice'])) {
                $opt['stopPrice'] = $flags['stopPrice'];
            }

            if (isset($flags['icebergQty'])) {
                $opt['icebergQty'] = $flags['icebergQty'];
            }

            if (isset($flags['newOrderRespType'])) {
                $opt['newOrderRespType'] = $flags['newOrderRespType'];
            }

            $data = $this->api->httpRequest("v3/order", "POST", $opt, true);

            return $this->api->sendResponse(200,true,__('Order placed successfully'),$data);
        } catch(\Exception $e) {
            return $this->api->sendResponse(500,false,$e->getMessage(),[]);
        }
    }

    /**
     * buyTestOrder attempts to create a TEST currency order
     *
     * @see buy()
     *
     * @param $symbol string the currency symbol
     * @param $quantity string the quantity required
     * @param $price string price per unit you want to spend
     * @param $type string config
     * @param $flags array config
     * @return array with error message or empty or the order details
     */
    public function buyTestOrder(string $symbol, $quantity, $price, string $type = "LIMIT", array $flags = [])
    {
        return $this->placeTestOrderSpot("BUY", $symbol, $quantity, $price, $type, $flags);
    }

     /**
     * sellTestOrder attempts to create a TEST currency order
     *
     * @see sell()
     *
     * @param $symbol string the currency symbol
     * @param $quantity string the quantity required
     * @param $price string price per unit you want to spend
     * @param $type array config
     * @param $flags array config
     * @return array with error message or empty or the order details
     */
    public function sellTestOrder(string $symbol, $quantity, $price, string $type = "LIMIT", array $flags = [])
    {
        return $this->placeTestOrderSpot("SELL", $symbol, $quantity, $price, $type, $flags);
    }

     /**
     * buy attempts to create a currency order
     * each currency supports a number of order types, such as
     * -LIMIT
     * -MARKET
     * -STOP_LOSS
     * -STOP_LOSS_LIMIT
     * -TAKE_PROFIT
     * -TAKE_PROFIT_LIMIT
     * -LIMIT_MAKER
     *
     * You should check the @see exchangeInfo for each currency to determine
     * what types of orders can be placed against specific pairs
     *
     * $quantity = 1;
     * $price = 0.0005;
     * $order = $api->buy("BNBBTC", $quantity, $price);
     *
     * @param $symbol string the currency symbol
     * @param $quantity string the quantity required
     * @param $price string price per unit you want to spend
     * @param $type string type of order
     * @param $flags array addtional options for order type
     * @return array with error message or the order details
     */
    public function buyOrder(string $symbol, $quantity, $price, string $type = "LIMIT", array $flags = [])
    {
        return $this->placeOrderSpot("BUY", $symbol, $quantity, $price, $type, $flags);
    }


    /**
     * sell attempts to create a currency order
     * each currency supports a number of order types, such as
     * -LIMIT
     * -MARKET
     * -STOP_LOSS
     * -STOP_LOSS_LIMIT
     * -TAKE_PROFIT
     * -TAKE_PROFIT_LIMIT
     * -LIMIT_MAKER
     *
     * You should check the @see exchangeInfo for each currency to determine
     * what types of orders can be placed against specific pairs
     *
     * $quantity = 1;
     * $price = 0.0005;
     * $order = $api->sell("BNBBTC", $quantity, $price);
     *
     * @param $symbol string the currency symbol
     * @param $quantity string the quantity required
     * @param $price string price per unit you want to spend
     * @param $type string type of order
     * @param $flags array addtional options for order type
     * @return array with error message or the order details
     */
    public function sellOrder(string $symbol, $quantity, $price, string $type = "LIMIT", array $flags = [])
    {
        return $this->placeOrderSpot("SELL", $symbol, $quantity, $price, $type, $flags);
    }

    /**
     * Current Open Orders (USER_DATA)
     * openOrders attempts to get open orders for all currencies or a specific currency
     *
     * $allOpenOrders = $api->openOrders();
     * $allBNBOrders = $api->openOrders( "BNBUSDT" );
     * If the symbol is not sent, orders for all symbols will be returned in an array.
     *
     * @param STRING $symbol (optional) string the currency symbol
     * @return array with error message or the order details
     * @throws \Exception
     */
    public function openOrders(string $symbol = null){
        try {
            $params = [];
            if (is_null($symbol) != true) {
                $params = [
                    "symbol" => $symbol,
                ];
            }
            $data = $this->api->httpRequest("v3/openOrders","GET",$params,true);
            return $this->api->sendResponse(200,true,'success',$data);
        } catch(\Exception $e) {
            return $this->api->sendResponse(500,false,$e->getMessage(),[]);
        }
    }

     /**
     * cancel attempts to cancel a currency order
     *
     * $orderid = "123456789";
     * $order = $api->cancelOrder("BNBBTC", $orderid);
     *
     * @param $symbol string the currency symbol
     * @param $orderid string the orderid to cancel
     * @param $flags array of optional options like ["side"=>"sell"]
     * @return array with error message or the order details
     * @throws \Exception
     */

    public function cancelOrder(string $symbol, $orderid, $flags = [])
    {
        try {
            $params = [
                "symbol" => $symbol,
                "orderId" => $orderid,
            ];
            $data = $this->api->httpRequest("v3/order", "DELETE", array_merge($params, $flags), true);
            return $this->api->sendResponse(200,true,'success',$data);
        } catch(\Exception $e) {
            return $this->api->sendResponse(500,false,$e->getMessage(),[]);
        }
    }

     /**
     * Cancel all open orders method
     * $api->cancelOpenOrders( "BNBBTC" );
     * @param $symbol string the currency symbol
     * @return array with error message or the order details
     * @throws \Exception
     */

     public function cancelOpenOrders(string $symbol)
     {
         try {
             $params = [
                "symbol" => $symbol,
             ];

             $data = $this->api->httpRequest("v3/openOrders", "DELETE", $params, true);
             return $this->api->sendResponse(200,true,'success',$data);
         } catch(\Exception $e) {
             return $this->api->sendResponse(500,false,$e->getMessage(),[]);
         }
     }

     /**
     * history Get the complete account trade history for all or a specific currency
     *
     * $BNBHistory = $api->history("BNBBTC");
     * $limitBNBHistory = $api->history("BNBBTC",5);
     * $limitBNBHistoryFromId = $api->history("BNBBTC",5,3);
     *
     * @param $symbol string the currency symbol
     * @param $limit int the amount of orders returned
     * @param $fromTradeId int (optional) return the orders from this order onwards. negative for all
     * @param $startTime int (optional) return the orders from this time onwards. null to ignore
     * @param $endTime int (optional) return the orders from this time backwards. null to ignore
     * @return array with error message or array of orderDetails array
     * @throws \Exception
     */
    public function userTradeHistory(string $symbol, int $limit = 500, int $fromTradeId = -1, int $startTime = null, int $endTime = null)
    {
        try {
            $param = [
                "symbol" => $symbol,
                "limit" => $limit,
            ];
            if ($fromTradeId > 0) {
                $param["fromId"] = $fromTradeId;
            }
            if (isset($startTime)) {
                $param["startTime"] = $startTime;
            }
            if (isset($endTime)) {
                $param["endTime"] = $endTime;
            }

            $response = $this->api->httpRequest("v3/myTrades", "GET", $param, true);
            return $this->api->sendResponse(200,true,'Trade History',$response);
        } catch(\Exception $e) {
            return $this->api->sendResponse(500,false,$e->getMessage(),[]);
        }
    }
}
