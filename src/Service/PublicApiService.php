<?php

namespace Sdtech\BinanceApiLaravel\Service;

use Ramsey\Uuid\Type\Integer;

/*

*/

class PublicApiService
{
    private $api;

    protected $info = [
        "timeOffset" => 0,
    ];
    protected $charts = []; // /< Websockets chart data

    protected $exchangeInfo = null;

    public function __construct()
    {
        $this->api = new ApiService();
    }

    public function ping() {
        try {
            $data = $this->api->httpRequest("v3/ping");
            return $this->api->sendResponse(200,true,'success',$data);
        } catch(\Exception $e) {
            return $this->api->sendResponse(500,false,$e->getMessage(),[]);
        }
	}

     /**
     * time Gets the server time
     *
     * $time = $api->time();
     *
     * @return array with error message or array with server time key
     * @throws \Exception
     */
	public function time() {
        try {
            $data = $this->api->httpRequest("v3/time");
            $ts = (microtime(true) * 1000) + $this->info['timeOffset'];
            $data['timestamp'] = number_format($ts, 0, '.', '');
            return $this->api->sendResponse(200,true,'success',$data);
        } catch(\Exception $e) {
            return $this->api->sendResponse(500,false,$e->getMessage(),[]);
        }
	}

     /**
     * useServerTime adds the 'useServerTime'=>true to the API request to avoid time errors
     *
     * $api->useServerTime();
     *
     * @return null
     * @throws \Exception
     */
    public function useServerTime()
    {
        $request = $this->api->httpRequest("v3/time");
        if (isset($request['serverTime'])) {
            $this->info['timeOffset'] = $request['serverTime'] - (microtime(true) * 1000);
        }
    }

    /**
     * exchangeInfo -  Gets the complete exchange info, including limits, currency options etc.
     *
     * @link https://binance-docs.github.io/apidocs/spot/en/#exchange-information
     *
     * $info = $api->exchangeInfo();
     * $info = $api->exchangeInfo('BTCUSDT');
     *
     * $arr = array('ATABUSD','BTCUSDT');
     * $info = $api->exchangeInfo($arr);
     *
     * @property int $weight 10
     *
     * @param string|array  $symbols  (optional)  A symbol or an array of symbols, default is empty
     *
     * @return array containing the response
     * @throws \Exception
     */

	public function exchangeInfo($symbols = null) {
        try {
            $data = $this->exchangeInfoDetails($symbols);
            return $this->api->sendResponse(200,true,'success',$data);
        } catch(\Exception $e) {
            return $this->api->sendResponse(500,false,$e->getMessage(),[]);
        }
	}

    /**
     * exchangeInfo -  Gets the complete exchange info, including limits, currency options etc.
     *
     * @link https://binance-docs.github.io/apidocs/spot/en/#exchange-information
     *
     * $info = $api->exchangeInfo();
     * $info = $api->exchangeInfo('BTCUSDT');
     *
     * $arr = array('ATABUSD','BTCUSDT');
     * $info = $api->exchangeInfo($arr);
     *
     * @property int $weight 10
     *
     * @param string|array  $symbols  (optional)  A symbol or an array of symbols, default is empty
     *
     * @return array containing the response
     * @throws \Exception
     */
    public function exchangeInfoDetails($symbols = null)
    {
        if (!$this->exchangeInfo) {
            $arr = array();
            $arr['symbols'] = array();
            $parameters = [];

            if ($symbols) {
                if (gettype($symbols) == "string") {
                    $parameters["symbol"] = $symbols;
                    $arr = $this->api->httpRequest("v3/exchangeInfo", "GET", $parameters);
                }
                if (gettype($symbols) == "array")  {
                    $arr = $this->api->httpRequest('v3/exchangeInfo?symbols=' . '["' . implode('","', $symbols) . '"]');
                }
            } else {
                $arr = $this->api->httpRequest("v3/exchangeInfo");
            }

            $this->exchangeInfo = $arr;
            $this->exchangeInfo['symbols'] = null;

            foreach ($arr['symbols'] as $key => $value) {
                $this->exchangeInfo['symbols'][$value['symbol']] = $value;
            }
        }

        return $this->exchangeInfo;
    }

    /**
     * price get the latest price of a symbol
     *
     * $price = $api->price( "BNBUSDT" );
     * @param STRING symbol mandetory Parameter symbol and symbols cannot be used in combination.
     * @param STRING symbols optional Examples of accepted format for the symbols parameter: ["BTCUSDT","BNBUSDT"]
     *
     * @return array with error message or array with symbol price
     * @throws \Exception
     */
    public function price(string $symbol='BNBUSDT', $symbols=[])
    {
        try {
            $param = [
                'symbol' => $symbol,
            ];
            if (!empty($symbols) && isset($symbols[0])) {
                $param = [
                    'symbols' => $symbols,
                ];
            }
            $ticker = $this->api->httpRequest("v3/ticker/price", "GET", $param);
            return $this->api->sendResponse(200,true,'success',$ticker['price']);
        } catch(\Exception $e) {
            return $this->api->sendResponse(500,false,$e->getMessage(),[]);
        }
    }

     /**
     * prices get all the current prices
     *
     * $ticker = $api->prices();
     *
     * @return array with error message or array of all the currencies prices
     * @throws \Exception
     */

    public function prices()
    {
        try {
            $ticker = $this->priceData($this->api->httpRequest("v3/ticker/price"));
            return $this->api->sendResponse(200,true,'success',$ticker);
        } catch(\Exception $e) {
            return $this->api->sendResponse(500,false,$e->getMessage(),[]);
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

    /**
     * Symbol Order Book Ticker
     * orderBookTicker get all bid/asks prices
     *
     * $ticker = $api->orderBookTicker();
     * @param STRING symbol optional Parameter symbol and symbols cannot be used in combination.
     * @param STRING symbols optional Examples of accepted format for the symbols parameter: ["BTCUSDT","BNBUSDT"]
     *
     *
     * @return array with error message or array of all the book prices
     * @throws \Exception
     */
    public function orderBookTicker($symbol=null,$symbols=[])
    {
        try {
            $param = [];
            if (!empty($symbols) && isset($symbols[0])) {
                $param = [
                    'symbols' => $symbols,
                ];
            } elseif(!empty($symbol)) {
                $param = [
                    'symbol' => $symbol,
                ];
            }
            $ticker = $this->bookPriceData($this->api->httpRequest("v3/ticker/bookTicker", "GET", $param));
            return $this->api->sendResponse(200,true,'success',$ticker);
        } catch(\Exception $e) {
            return $this->api->sendResponse(500,false,$e->getMessage(),[]);
        }
    }


    /**
     * bookPriceData Consolidates Book Prices into an easy to use object
     *
     * $bookPriceData = $this->bookPriceData($array);
     *
     * @param $array array book prices
     * @return array easier format for book prices information
     */
    protected function bookPriceData(array $array)
    {
        $bookprices = [];
        foreach ($array as $obj) {
            $bookprices[$obj['symbol']] = [
                "bid" => $obj['bidPrice'],
                "bids" => $obj['bidQty'],
                "ask" => $obj['askPrice'],
                "asks" => $obj['askQty'],
            ];
        }
        return $bookprices;
    }

    /**
     * priceChange24h get 24hr ticker price change statistics for symbols
     *
     * $prevDay = $api->priceChange24h("BNBBTC");
     *
     * @param $symbol (optional) symbol to get the previous day change for
     * @return array with error message or array of prevDay change
     * @throws \Exception
     */
    public function priceChange24h(string $symbol = null)
    {
        try {
            $additionalData = [];
            if (is_null($symbol) === false) {
                $additionalData = [
                    'symbol' => $symbol,
                ];
            }
            $data = $this->api->httpRequest("v3/ticker/24hr", "GET", $additionalData);
            return $this->api->sendResponse(200,true,'success',$data);
        } catch(\Exception $e) {
            return $this->api->sendResponse(500,false,$e->getMessage(),[]);
        }
    }

    /**
     * aggTrades get Market History / Compressed/Aggregate Trades List
     * Get compressed, aggregate trades. Trades that fill at the time, from the same order, with the same price will have the quantity aggregated.
     *
     * $trades = $api->aggTrades("BNBBTC");
     *
     * @param STRING $symbol (mandetory) the symbol to get the trade information for
     * @param LONG $fromId (optional) id to get aggregate trades from INCLUSIVE.
     * @param LONG $startTime (optional) Timestamp in ms to get aggregate trades from INCLUSIVE.
     * @param LONG $endTime (optional) Timestamp in ms to get aggregate trades until INCLUSIVE.
     * @param INT $limit (optional) Default 100; max 1000.
     * @return array with error message or array of market history
     * @throws \Exception
     */
    public function aggTrades(string $symbol,int $limit=100,$fromId=null,$startTime=null,$endTime=null)
    {
        try {
            $data = $this->tradesData($this->api->httpRequest("v3/aggTrades", "GET", [
                "symbol" => $symbol,
                "limit" => $limit,
                "fromId" => $fromId,
                "startTime" => $startTime,
                "endTime" => $endTime,
            ]));
            return $this->api->sendResponse(200,true,'success',$data);
        } catch(\Exception $e) {
            return $this->api->sendResponse(500,false,$e->getMessage(),[]);
        }
    }

    /**
     * tradesData Convert aggTrades data into easier format
     *
     * $tradesData = $this->tradesData($trades);
     *
     * @param $trades array of trade information
     * @return array easier format for trade information
     */
    protected function tradesData(array $trades)
    {
        $output = [];
        foreach ($trades as $trade) {
            $price = $trade['p'];
            $quantity = $trade['q'];
            $timestamp = $trade['T'];
            $maker = $trade['m'] ? 'true' : 'false';
            $output[] = [
                "price" => $price,
                "quantity" => $quantity,
                "timestamp" => $timestamp,
                "maker" => $maker,
            ];
        }
        return $output;
    }

    /**
     * assetDetail - Fetch details of assets supported on Binance
     *
     * @link https://binance-docs.github.io/apidocs/spot/en/#asset-detail-user_data
     *
     * @property int $weight 1
     *
     * @param string $asset  (optional)  Should be an asset, e.g. BNB or empty to get the full list
     *
     * @return array containing the response
     */
    public function assetDetail($asset = '')
    {
        try {
            $params["sapi"] = true;
            if ($asset != '' && gettype($asset) == 'string')
                $params['asset'] = $asset;
            $arr = $this->api->httpRequest("v1/asset/assetDetail", 'GET', $params, true);
            if (isset($params['asset']))
                return $this->api->sendResponse(200,true,'success',$arr);

            if (!empty($arr['BTC']['withdrawFee'])) {
                $data = array(
                    'success'     => 1,
                    'assetDetail' => $arr,
                    );
                return $this->api->sendResponse(200,true,'success',$data);
            } else {
                $data = array(
                    'success'     => 0,
                    'assetDetail' => array(),
                    );
                return $this->api->sendResponse(200,true,'success',$data);
            }
        } catch(\Exception $e) {
            return $this->api->sendResponse(500,false,$e->getMessage(),[]);
        }
    }

    /**
     * Kline/Candlestick Data
     * candlesticks get the candles for the given intervals
     * 1m,3m,5m,15m,30m,1h,2h,4h,6h,8h,12h,1d,3d,1w,1M
     *
     * $candles = $api->candlesticks("BNBBTC", "5m");
     *
     * @param $symbol string (mandetory) to query
     * @param $interval string (mandetory) to request
     * @param $limit int (optional) limit the amount of candles Default 500; max 1000.
     * @param $startTime string (optional) request candle information starting from here
     * @param $endTime string (optional) request candle information ending here
     * @return array containing the response
     * @throws \Exception
     */
    public function candlesticks(string $symbol, string $interval = "5m", int $limit = 500, $startTime = null, $endTime = null)
    {
        try {
            if (!isset($this->charts[$symbol])) {
                $this->charts[$symbol] = [];
            }

            $opt = [
                "symbol" => $symbol,
                "interval" => $interval,
            ];

            if ($limit) {
                $opt["limit"] = $limit;
            }

            if ($startTime) {
                $opt["startTime"] = $startTime;
            }

            if ($endTime) {
                $opt["endTime"] = $endTime;
            }

            $response = $this->api->httpRequest("v3/klines", "GET", $opt);

            if (is_array($response) === false) {
                return $this->api->sendResponse(400,false,'empty',[]);
            }

            if (count($response) === 0) {
                return $this->api->sendResponse(400,false,"warning: v1/klines returned empty array, usually a blip in the connection or server" . PHP_EOL,[]);
            }

            $ticks = $this->chartData($symbol, $interval, $response);
            $this->charts[$symbol][$interval] = $ticks;

            return $this->api->sendResponse(200,true,'success',$ticks);
        } catch(\Exception $e) {
            return $this->api->sendResponse(500,false,$e->getMessage(),[]);
        }
    }

    /**
     * chartData Convert kline data into object
     *
     * $object = $this->chartData($symbol, $interval, $ticks);
     *
     * @param $symbol string of your currency
     * @param $interval string the time interval
     * @param $ticks array of the canbles array
     * @return array object of the chartdata
     */
    protected function chartData(string $symbol, string $interval, array $ticks)
    {
        if (!isset($this->info[$symbol])) {
            $this->info[$symbol] = [];
        }

        if (!isset($this->info[$symbol][$interval])) {
            $this->info[$symbol][$interval] = [];
        }

        $output = [];
        foreach ($ticks as $tick) {
            list($openTime, $open, $high, $low, $close, $assetVolume, $closeTime, $baseVolume, $trades, $assetBuyVolume, $takerBuyVolume, $ignored) = $tick;
            $output[$openTime] = [
                "open" => $open,
                "high" => $high,
                "low" => $low,
                "close" => $close,
                "volume" => $baseVolume,
                "openTime" => $openTime,
                "closeTime" => $closeTime,
                "assetVolume" => $assetVolume,
                "baseVolume" => $baseVolume,
                "trades" => $trades,
                "assetBuyVolume" => $assetBuyVolume,
                "takerBuyVolume" => $takerBuyVolume,
                "ignored" => $ignored,
            ];
        }

        if (isset($openTime)) {
            $this->info[$symbol][$interval]['firstOpen'] = $openTime;
        }

        return $output;
    }

    /**
     * UIKlines/Candlestick Data
     * candlesticks get the candles for the given intervals
     * uiKlines return modified kline data, optimized for presentation of candlestick charts.
     * 1m,3m,5m,15m,30m,1h,2h,4h,6h,8h,12h,1d,3d,1w,1M
     *
     * $candles = $api->candlesticks("BNBBTC", "5m");
     *
     * @param $symbol string (mandetory) to query
     * @param $interval string (mandetory) to request
     * @param $limit int (optional) limit the amount of candles Default 500; max 1000.
     * @param $startTime string (optional) request candle information starting from here
     * @param $endTime string (optional) request candle information ending here
     * @return array containing the response
     * @throws \Exception
     */
    public function uiKlinesCandlesticks(string $symbol, string $interval = "5m", int $limit = 500, $startTime = null, $endTime = null)
    {
        try {
            if (!isset($this->charts[$symbol])) {
                $this->charts[$symbol] = [];
            }

            $opt = [
                "symbol" => $symbol,
                "interval" => $interval,
            ];

            if ($limit) {
                $opt["limit"] = $limit;
            }

            if ($startTime) {
                $opt["startTime"] = $startTime;
            }

            if ($endTime) {
                $opt["endTime"] = $endTime;
            }

            $response = $this->api->httpRequest("v3/uiKlines", "GET", $opt);

            if (is_array($response) === false) {
                return $this->api->sendResponse(400,false,'empty',[]);
            }

            if (count($response) === 0) {
                return $this->api->sendResponse(400,false,"warning: v1/klines returned empty array, usually a blip in the connection or server" . PHP_EOL,[]);
            }

            return $this->api->sendResponse(200,true,'success',$response);
        } catch(\Exception $e) {
            return $this->api->sendResponse(500,false,$e->getMessage(),[]);
        }
    }

    /**
     * Order Book
     *
     * $data = $this->orderBook($symbol, $limit);
     *
     * @param STRING $symbol string of your currency pair like BNBUSDT
     * @param INT $limit integer optional, Default 100; max 5000. If limit > 5000, then the response will truncate to 5000.
     * @return array of the order book data
     */
    public function orderBook(string $symbol, int $limit=100){
        try {
            $data = $this->api->httpRequest("v3/depth", "GET", [
                "symbol" => $symbol,
                "limit" => $limit
            ]);
            return $this->api->sendResponse(200,true,'success',$data);
        } catch(\Exception $e) {
            return $this->api->sendResponse(500,false,$e->getMessage(),[]);
        }
    }

    /**
     * Recent Trades List
     *
     * $data = $this->recentTrades($symbol, $limit);
     *
     * @param STRING $symbol string of your currency pair like BNBUSDT
     * @param INT $limit integer optional, Default 100; max 1000.
     * @return array of the order book data
     */
    public function recentTrades(string $symbol, int $limit=100){
        try {
            $data = $this->api->httpRequest("v3/trades", "GET", [
                "symbol" => $symbol,
                "limit" => $limit
            ]);
            return $this->api->sendResponse(200,true,'success',$data);
        } catch(\Exception $e) {
            return $this->api->sendResponse(500,false,$e->getMessage(),[]);
        }
    }

    /**
     * Old Trade Lookup (historicalTrades)
     * Get older market trades.
     * $data = $this->historicalTrades($symbol, $limit, $fromId);
     *
     * @param STRING $symbol string of your currency pair like BNBUSDT
     * @param INT $limit integer optional, Default 100; max 1000.
     * @param LONG $fromId integer optional, Trade id to fetch from. Default gets most recent trades..
     * @return array of the order book data
     */
    public function historicalTrades(string $symbol, int $limit=100, $fromId=""){
        try {
            $data = $this->api->httpRequest("v3/historicalTrades", "GET", [
                "symbol" => $symbol,
                "limit" => $limit,
                "fromId" => $fromId
            ]);
            return $this->api->sendResponse(200,true,'success',$data);
        } catch(\Exception $e) {
            return $this->api->sendResponse(500,false,$e->getMessage(),[]);
        }
    }

    /**
     * Trading Day Ticker
     * Price change statistics for a trading day.
     *
     * $prevDay = $api->tradingDayTicker("BNBBTC");
     *
     * @param STRING $symbol (mandetory) Either symbol or symbols must be provided.
     * @param STRING $symbols Examples of accepted format for the symbols parameter:["BTCUSDT","BNBUSDT"]
     * @param ENUM type (optional) Supported values: FULL or MINI.If none provided, the default is FULL.
     * @return array with error message or array of prevDay change
     * @throws \Exception
     */
    public function tradingDayTicker(string $symbol, $type='FULL', $symbols=[])
    {
        try {
            $param = [
                'symbol' => $symbol,
                'type' => $type
            ];
            if (!empty($symbols) && isset($symbols[0])) {
                $param = [
                    'symbols' => $symbols,
                    'type' => $type
                ];
            }
            $data = $this->api->httpRequest("v3/ticker/tradingDay", "GET", $param);
            return $this->api->sendResponse(200,true,'success',$data);
        } catch(\Exception $e) {
            return $this->api->sendResponse(500,false,$e->getMessage(),[]);
        }
    }

    /**
     * Rolling window price change statistics
     * Note: This endpoint is different from the GET /api/v3/ticker/24hr endpoint.
     * The window used to compute statistics will be no more than 59999ms from the requested windowSize.
     * openTime for /api/v3/ticker always starts on a minute, while the closeTime is the current time of the request. As such, the effective window will be up to 59999ms wider than windowSize.
     * E.g. If the closeTime is 1641287867099 (January 04, 2022 09:17:47:099 UTC) , and the windowSize is 1d. the openTime will be: 1641201420000 (January 3, 2022, 09:17:00 UTC)
     *
     * $prevDay = $api->ticker("BNBBTC");
     *
     * @param STRING $symbol (mandetory) Either symbol or symbols must be provided.
     * @param STRING $symbols Examples of accepted format for the symbols parameter:["BTCUSDT","BNBUSDT"]
     * @param ENUM $windowSize (optional) Defaults to 1d if no parameter provided.
     * Supported windowSize values:
     *   1m,2m....59m for minutes
     *   1h, 2h....23h - for hours
     *   1d...7d - for days
     * @param ENUM type (optional) Supported values: FULL or MINI.If none provided, the default is FULL.
     * @return array with error message or array of prevDay change
     * @throws \Exception
     */
    public function ticker(string $symbol, $type='FULL', $symbols=[],$windowSize='1d')
    {
        try {
            $param = [
                'symbol' => $symbol,
                'type' => $type,
                'windowSize' => $windowSize
            ];
            if (!empty($symbols) && isset($symbols[0])) {
                $param = [
                    'symbols' => $symbols,
                    'type' => $type,
                    'windowSize' => $windowSize,
                ];
            }
            $data = $this->api->httpRequest("v3/ticker", "GET", $param);
            return $this->api->sendResponse(200,true,'success',$data);
        } catch(\Exception $e) {
            return $this->api->sendResponse(500,false,$e->getMessage(),[]);
        }
    }

}
