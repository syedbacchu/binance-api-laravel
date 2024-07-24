<?php

namespace Sdtech\BinanceApiLaravel\Service;

use Ramsey\Uuid\Type\Integer;

/*

*/

class WebSocketService
{
    private $api;
    private $publicApi;

    protected $info = [
        "timeOffset" => 0,
    ];
    protected $charts = []; // /< Websockets chart data
    protected $chartQueue = []; // /< Websockets chart queue
    protected $subscriptions = []; // /< View all websocket subscriptions
    protected $depthQueue = []; // /< Websockets depth queue
    protected $depthCache = []; // /< Websockets depth cache

    protected $exchangeInfo = null;
    protected $listenKey = "";

    public function __construct()
    {
        $this->api = new ApiService();
        $this->publicApi = new PublicApiService();
    }

    /**
     * chart Pulls /kline data and subscribes to @klines WebSocket endpoint
     *
     * $api->chart(["BNBBTC"], "15m", function($api, $symbol, $chart) {
     * echo "{$symbol} chart update\n";
     * print_r($chart);
     * });
     *
     * @param $symbols string required symbols
     * @param $interval string time inteval
     * @param $callback callable closure
     * @param $limit int default 500, maximum 1000
     * @return null
     * @throws \Exception
     */
    public function chart($symbols, string $interval = "30m", callable $callback = null, $limit = 500)
    {
        if (is_null($callback)) {
            return $this->api->sendResponse(400,false,'You must provide a valid callback',[]);
        }
        if (!is_array($symbols)) {
            $symbols = [
                $symbols,
            ];
        }

        $loop = \React\EventLoop\Factory::create();
        $react = new \React\Socket\Connector($loop);
        $connector = new \Ratchet\Client\Connector($loop, $react);
        foreach ($symbols as $symbol) {
            if (!isset($this->charts[$symbol])) {
                $this->charts[$symbol] = [];
            }

            $this->charts[$symbol][$interval] = [];
            if (!isset($this->info[$symbol])) {
                $this->info[$symbol] = [];
            }

            if (!isset($this->info[$symbol][$interval])) {
                $this->info[$symbol][$interval] = [];
            }

            if (!isset($this->chartQueue[$symbol])) {
                $this->chartQueue[$symbol] = [];
            }

            $this->chartQueue[$symbol][$interval] = [];
            $this->info[$symbol][$interval]['firstOpen'] = 0;
            $endpoint = strtolower($symbol) . '@kline_' . $interval;
            $this->subscriptions[$endpoint] = true;
            $connector($this->api->getWsEndpoint() . $endpoint)->then(function ($ws) use ($callback, $symbol, $loop, $endpoint, $interval) {
                $ws->on('message', function ($data) use ($ws, $loop, $callback, $endpoint) {
                    if ($this->subscriptions[$endpoint] === false) {
                        //$this->subscriptions[$endpoint] = null;
                        $loop->stop();
                        return; //return $ws->close();
                    }
                    $json = json_decode($data);
                    $chart = $json->k;
                    $symbol = $json->s;
                    $interval = $chart->i;
                    $this->chartHandler($symbol, $interval, $json);
                    call_user_func($callback, $this, $symbol, $this->charts[$symbol][$interval]);
                });
                $ws->on('close', function ($code = null, $reason = null) use ($symbol, $loop, $interval) {
                    // WPCS: XSS OK.
                    echo "chart({$symbol},{$interval}) WebSocket Connection closed! ({$code} - {$reason})" . PHP_EOL;
                    $loop->stop();
                });
            }, function ($e) use ($loop, $symbol, $interval) {
                // WPCS: XSS OK.
                echo "chart({$symbol},{$interval})) Could not connect: {$e->getMessage()}" . PHP_EOL;
                $loop->stop();
            });
            $this->publicApi->candlesticks($symbol, $interval, $limit);
            foreach ($this->chartQueue[$symbol][$interval] as $json) {
                $this->chartHandler($symbol, $interval, $json);
            }
            $this->chartQueue[$symbol][$interval] = [];
            call_user_func($callback, $this, $symbol, $this->charts[$symbol][$interval]);
        }
        $loop->run();
    }


     /**
     * chartHandler For WebSocket Chart Cache
     *
     * $this->chartHandler($symbol, $interval, $json);
     *
     * @param $symbol string to sort
     * @param $interval string time
     * @param \stdClass $json object time
     * @return null
     */
    protected function chartHandler(string $symbol, string $interval, \stdClass $json)
    {
        if (!$this->info[$symbol][$interval]['firstOpen']) { // Wait for /kline to finish loading
            $this->chartQueue[$symbol][$interval][] = $json;
            return;
        }
        $chart = $json->k;
        $symbol = $json->s;
        $interval = $chart->i;
        $tick = $chart->t;
        if ($tick < $this->info[$symbol][$interval]['firstOpen']) {
            return;
        }
        // Filter out of sync data
        $open = $chart->o;
        $high = $chart->h;
        $low = $chart->l;
        $close = $chart->c;
        $volume = $chart->q; // +trades buyVolume assetVolume makerVolume
        $this->charts[$symbol][$interval][$tick] = [
            "open" => $open,
            "high" => $high,
            "low" => $low,
            "close" => $close,
            "volume" => $volume,
        ];
    }

    /**
     * depthCache Pulls /depth data and subscribes to @depth WebSocket endpoint
     * Maintains a local Depth Cache in sync via lastUpdateId.
     * See depth() and depthHandler()
     *
     * $api->depthCache(["BNBBTC"], function($api, $symbol, $depth) {
     * echo "{$symbol} depth cache update".PHP_EOL;
     * //print_r($depth); // Print all depth data
     * $limit = 11; // Show only the closest asks/bids
     * $sorted = $api->sortDepth($symbol, $limit);
     * $bid = $api->first($sorted['bids']);
     * $ask = $api->first($sorted['asks']);
     * echo $api->displayDepth($sorted);
     * echo "ask: {$ask}".PHP_EOL;
     * echo "bid: {$bid}".PHP_EOL;
     * });
     *
     * @param $symbol string optional array of symbols
     * @param $callback callable closure
     * @return null
     */
    public function depthCache($symbols, callable $callback)
    {
        if (!is_array($symbols)) {
            $symbols = [
                $symbols,
            ];
        }

        $loop = \React\EventLoop\Factory::create();
        $react = new \React\Socket\Connector($loop);
        $connector = new \Ratchet\Client\Connector($loop, $react);
        foreach ($symbols as $symbol) {
            if (!isset($this->info[$symbol])) {
                $this->info[$symbol] = [];
            }

            if (!isset($this->depthQueue[$symbol])) {
                $this->depthQueue[$symbol] = [];
            }

            if (!isset($this->depthCache[$symbol])) {
                $this->depthCache[$symbol] = [
                    "bids" => [],
                    "asks" => [],
                ];
            }

            $this->info[$symbol]['firstUpdate'] = 0;
            $endpoint = strtolower($symbol) . '@depthCache';
            $this->subscriptions[$endpoint] = true;

            $connector($this->api->getWsEndpoint() . strtolower($symbol) . '@depth')->then(function ($ws) use ($callback, $symbol, $loop, $endpoint) {
                $ws->on('message', function ($data) use ($ws, $callback, $loop, $endpoint) {
                    if ($this->subscriptions[$endpoint] === false) {
                        //$this->subscriptions[$endpoint] = null;
                        $loop->stop();
                        return; //return $ws->close();
                    }
                    $json = json_decode($data, true);
                    $symbol = $json['s'];
                    if (intval($this->info[$symbol]['firstUpdate']) === 0) {
                        $this->depthQueue[$symbol][] = $json;
                        return;
                    }
                    $this->depthHandler($json);
                    call_user_func($callback, $this, $symbol, $this->depthCache[$symbol]);
                });
                $ws->on('close', function ($code = null, $reason = null) use ($symbol, $loop) {
                    // WPCS: XSS OK.
                    echo "depthCache({$symbol}) WebSocket Connection closed! ({$code} - {$reason})" . PHP_EOL;
                    $loop->stop();
                });
            }, function ($e) use ($loop, $symbol) {
                // WPCS: XSS OK.
                echo "depthCache({$symbol})) Could not connect: {$e->getMessage()}" . PHP_EOL;
                $loop->stop();
            });
            $this->depth($symbol, 100);
            foreach ($this->depthQueue[$symbol] as $data) {
                $this->depthHandler($data);
            }
            $this->depthQueue[$symbol] = [];
            call_user_func($callback, $this, $symbol, $this->depthCache[$symbol]);
        }
        $loop->run();
    }


    /*
     * WebSockets
     */

    /**
     * depthHandler For WebSocket Depth Cache
     *
     * $this->depthHandler($json);
     *
     * @param $json array of depth bids and asks
     * @return null
     */
    protected function depthHandler(array $json)
    {
        $symbol = $json['s'];
        if ($json['u'] <= $this->info[$symbol]['firstUpdate']) {
            return;
        }

        foreach ($json['b'] as $bid) {
            $this->depthCache[$symbol]['bids'][$bid[0]] = $bid[1];
            if ($bid[1] == "0.00000000") {
                unset($this->depthCache[$symbol]['bids'][$bid[0]]);
            }
        }
        foreach ($json['a'] as $ask) {
            $this->depthCache[$symbol]['asks'][$ask[0]] = $ask[1];
            if ($ask[1] == "0.00000000") {
                unset($this->depthCache[$symbol]['asks'][$ask[0]]);
            }
        }
    }

    /**
     * depth get Market depth
     *
     * $depth = $api->depth("ETHBTC");
     *
     * @param $symbol string the symbol to get the depth information for
     * @param $limit int set limition for number of market depth data
     * @return array with error message or array of market depth
     * @throws \Exception
     */
    public function depth(string $symbol, int $limit = 100)
    {
        if (is_int($limit) === false) {
            $limit = 100;
        }

        if (isset($symbol) === false || is_string($symbol) === false) {
            // WPCS: XSS OK.
            echo "asset: expected bool false, " . gettype($symbol) . " given" . PHP_EOL;
        }
        $json = $this->api->httpRequest("v1/depth", "GET", [
            "symbol" => $symbol,
            "limit" => $limit,
        ]);
        if (isset($this->info[$symbol]) === false) {
            $this->info[$symbol] = [];
        }
        $this->info[$symbol]['firstUpdate'] = $json['lastUpdateId'];
        return $this->depthData($symbol, $json);
    }


    /**
     * depthData Formats depth data for nice display
     *
     * $array = $this->depthData($symbol, $json);
     *
     * @param $symbol string to display
     * @param $json array of the depth infomration
     * @return array of the depth information
     */
    protected function depthData(string $symbol, array $json)
    {
        $bids = $asks = [];
        foreach ($json['bids'] as $obj) {
            $bids[$obj[0]] = $obj[1];
        }
        foreach ($json['asks'] as $obj) {
            $asks[$obj[0]] = $obj[1];
        }
        return $this->depthCache[$symbol] = [
            "bids" => $bids,
            "asks" => $asks,
        ];
    }


    /**
     * kline Subscribes to @klines WebSocket endpoint for latest chart data only
     *
     * $api->kline(["BNBBTC"], "15m", function($api, $symbol, $chart) {
     * echo "{$symbol} chart update\n";
     * print_r($chart);
     * });
     *
     * @param $symbols string required symbols
     * @param $interval string time inteval
     * @param $callback callable closure
     * @return null
     * @throws \Exception
     */
    public function kline($symbols, string $interval = "30m", callable $callback = null)
    {
        if (is_null($callback)) {
            return $this->api->sendResponse(400,false,'You must provide a valid callback',[]);
        }
        if (!is_array($symbols)) {
            $symbols = [
                $symbols,
            ];
        }

        $loop = \React\EventLoop\Factory::create();
        $react = new \React\Socket\Connector($loop);
        $connector = new \Ratchet\Client\Connector($loop, $react);
        foreach ($symbols as $symbol) {
            $endpoint = strtolower($symbol) . '@kline_' . $interval;
            $this->subscriptions[$endpoint] = true;
            $connector($this->api->getWsEndpoint() . $endpoint)->then(function ($ws) use ($callback, $symbol, $loop, $endpoint, $interval) {
                $ws->on('message', function ($data) use ($ws, $loop, $callback, $endpoint) {
                    if ($this->subscriptions[$endpoint] === false) {
                        $loop->stop();
                        return;
                    }
                    $json = json_decode($data);
                    $chart = $json->k;
                    $symbol = $json->s;
                    $interval = $chart->i;
                    call_user_func($callback, $this, $symbol, $chart);
                });
                $ws->on('close', function ($code = null, $reason = null) use ($symbol, $loop, $interval) {
                    // WPCS: XSS OK.
                    echo "kline({$symbol},{$interval}) WebSocket Connection closed! ({$code} - {$reason})" . PHP_EOL;
                    $loop->stop();
                });
            }, function ($e) use ($loop, $symbol, $interval) {
                // WPCS: XSS OK.
                echo "kline({$symbol},{$interval})) Could not connect: {$e->getMessage()}" . PHP_EOL;
                $loop->stop();
            });
        }
        $loop->run();
    }

    /**
     * miniTicker Get miniTicker for all symbols
     *
     * $api->miniTicker(function($api, $ticker) {
     * print_r($ticker);
     * });
     *
     * @param $callback callable function closer that takes 2 arguments, $pai and $ticker data
     * @return null
     */
    public function miniTicker(callable $callback)
    {
        $endpoint = '@miniticker';
        $this->subscriptions[$endpoint] = true;

        // @codeCoverageIgnoreStart
        // phpunit can't cover async function
        \Ratchet\Client\connect($this->api->getWsEndpoint() . '!miniTicker@arr')->then(function ($ws) use ($callback, $endpoint) {
            $ws->on('message', function ($data) use ($ws, $callback, $endpoint) {
                if ($this->subscriptions[$endpoint] === false) {
                    //$this->subscriptions[$endpoint] = null;
                    $ws->close();
                    return; //return $ws->close();
                }
                $json = json_decode($data, true);
                $markets = [];
                foreach ($json as $obj) {
                    $markets[] = [
                        "symbol" => $obj['s'],
                        "close" => $obj['c'],
                        "open" => $obj['o'],
                        "high" => $obj['h'],
                        "low" => $obj['l'],
                        "volume" => $obj['v'],
                        "quoteVolume" => $obj['q'],
                        "eventTime" => $obj['E'],
                    ];
                }
                call_user_func($callback, $this, $markets);
            });
            $ws->on('close', function ($code = null, $reason = null) {
                // WPCS: XSS OK.
                echo "miniticker: WebSocket Connection closed! ({$code} - {$reason})" . PHP_EOL;
            });
        }, function ($e) {
            // WPCS: XSS OK.
            echo "miniticker: Could not connect: {$e->getMessage()}" . PHP_EOL;
        });
        // @codeCoverageIgnoreEnd
    }


    /**
     * trades Trades WebSocket Endpoint
     *
     * $api->trades(["BNBBTC"], function($api, $symbol, $trades) {
     * echo "{$symbol} trades update".PHP_EOL;
     * print_r($trades);
     * });
     *
     * @param $symbols
     * @param $callback callable closure
     * @return null
     */
    public function trades($symbols, callable $callback)
    {
        if (!is_array($symbols)) {
            $symbols = [
                $symbols,
            ];
        }

        $loop = \React\EventLoop\Factory::create();
        $react = new \React\Socket\Connector($loop);
        $connector = new \Ratchet\Client\Connector($loop, $react);
        foreach ($symbols as $symbol) {
            if (!isset($this->info[$symbol])) {
                $this->info[$symbol] = [];
            }

            // $this->info[$symbol]['tradesCallback'] = $callback;

            $endpoint = strtolower($symbol) . '@trades';
            $this->subscriptions[$endpoint] = true;

            $connector($this->api->getWsEndpoint() . strtolower($symbol) . '@aggTrade')->then(function ($ws) use ($callback, $symbol, $loop, $endpoint) {
                $ws->on('message', function ($data) use ($ws, $callback, $loop, $endpoint) {
                    if ($this->subscriptions[$endpoint] === false) {
                        //$this->subscriptions[$endpoint] = null;
                        $loop->stop();
                        return; //return $ws->close();
                    }
                    $json = json_decode($data, true);
                    $symbol = $json['s'];
                    $price = $json['p'];
                    $quantity = $json['q'];
                    $timestamp = $json['T'];
                    $maker = $json['m'] ? 'true' : 'false';
                    $trades = [
                        "price" => $price,
                        "quantity" => $quantity,
                        "timestamp" => $timestamp,
                        "maker" => $maker,
                    ];
                    // $this->info[$symbol]['tradesCallback']($this, $symbol, $trades);
                    call_user_func($callback, $this, $symbol, $trades);
                });
                $ws->on('close', function ($code = null, $reason = null) use ($symbol, $loop) {
                    // WPCS: XSS OK.
                    echo "trades({$symbol}) WebSocket Connection closed! ({$code} - {$reason})" . PHP_EOL;
                    $loop->stop();
                });
            }, function ($e) use ($loop, $symbol) {
                // WPCS: XSS OK.
                echo "trades({$symbol}) Could not connect: {$e->getMessage()}" . PHP_EOL;
                $loop->stop();
            });
        }
        $loop->run();
    }


    /**
     * userData Issues userDataStream token and keepalive, subscribes to userData WebSocket
     *
     * $balance_update = function($api, $balances) {
     * print_r($balances);
     * echo "Balance update".PHP_EOL;
     * };
     *
     * $order_update = function($api, $report) {
     * echo "Order update".PHP_EOL;
     * print_r($report);
     * $price = $report['price'];
     * $quantity = $report['quantity'];
     * $symbol = $report['symbol'];
     * $side = $report['side'];
     * $orderType = $report['orderType'];
     * $orderId = $report['orderId'];
     * $orderStatus = $report['orderStatus'];
     * $executionType = $report['orderStatus'];
     * if( $executionType == "NEW" ) {
     * if( $executionType == "REJECTED" ) {
     * echo "Order Failed! Reason: {$report['rejectReason']}".PHP_EOL;
     * }
     * echo "{$symbol} {$side} {$orderType} ORDER #{$orderId} ({$orderStatus})".PHP_EOL;
     * echo "..price: {$price}, quantity: {$quantity}".PHP_EOL;
     * return;
     * }
     *
     * //NEW, CANCELED, REPLACED, REJECTED, TRADE, EXPIRED
     * echo "{$symbol} {$side} {$executionType} {$orderType} ORDER #{$orderId}".PHP_EOL;
     * };
     * $api->userData($balance_update, $order_update);
     *
     * @param $balance_callback callable function
     * @param bool $execution_callback callable function
     * @return null
     * @throws \Exception
     */
    public function userData(&$balance_callback, &$execution_callback = false)
    {
        $response = $this->api->httpRequest("v1/userDataStream", "POST", []);
        $this->listenKey = $response['listenKey'];
        $this->info['balanceCallback'] = $balance_callback;
        $this->info['executionCallback'] = $execution_callback;

        $this->subscriptions['@userdata'] = true;

        $loop = \React\EventLoop\Factory::create();
        $loop->addPeriodicTimer(30*60, function () {
            $listenKey = $this->listenKey;
            $this->api->httpRequest("v1/userDataStream?listenKey={$listenKey}", "PUT", []);
        });
        $connector = new \Ratchet\Client\Connector($loop);

        // @codeCoverageIgnoreStart
        // phpunit can't cover async function
        $connector($this->api->getWsEndpoint() . $this->listenKey)->then(function ($ws) {
            $ws->on('message', function ($data) use ($ws) {
                if ($this->subscriptions['@userdata'] === false) {
                    //$this->subscriptions[$endpoint] = null;
                    $ws->close();
                    return; //return $ws->close();
                }
                $json = json_decode($data);
                $type = $json->e;
                if ($type === "outboundAccountPosition") {
                    $balances = $this->balanceHandler($json->B);
                    $this->info['balanceCallback']($this, $balances);
                } elseif ($type === "executionReport") {
                    $report = $this->executionHandler($json);
                    if ($this->info['executionCallback']) {
                        $this->info['executionCallback']($this, $report);
                    }
                }
            });
            $ws->on('close', function ($code = null, $reason = null) {
                // WPCS: XSS OK.
                echo "userData: WebSocket Connection closed! ({$code} - {$reason})" . PHP_EOL;
            });
        }, function ($e) {
            // WPCS: XSS OK.
            echo "userData: Could not connect: {$e->getMessage()}" . PHP_EOL;
        });

        $loop->run();
    }

    /**
     * create listen key for userDataStream
     *
     * $api->createListenKeySpot();
     *
     * @return null
     */
    public function createListenKeySpot()
    {
        $data = $this->api->httpRequest("v3/userDataStream", "POST", []);
        return $this->api->sendResponse(200,true,'success', $data);
    }

    /**
     * close listen key for userDataStream
     *
     * $api->createListenKeySpot();
     *
     * @return null
     */
    public function closeListenKeySpot($listenKey)
    {
        $data = $this->api->httpRequest("v3/userDataStream", "DELETE", ["listenKey"=>$listenKey]);
        return $this->api->sendResponse(200,true,'success', $data);
    }

    /**
     * keepAlive Keep-alive function for userDataStream
     *
     * $api->keepAlive();
     *
     * @return null
     */
    public function keepAlive($listenKey)
    {
        $loop = \React\EventLoop\Factory::create();
        $loop->addPeriodicTimer(30, function () use($listenKey) {
            // $listenKey = $this->listenKey;
            $this->api->httpRequest("v3/userDataStream", "PUT", ["listenKey"=>$listenKey]);
        });
        $loop->run();
    }


    /**
     * balanceHandler Convert balance WebSocket data into array
     *
     * $data = $this->balanceHandler( $json );
     *
     * @param $json array data to convert
     * @return array
     */
    protected function balanceHandler(array $json)
    {
        $balances = [];
        foreach ($json as $item) {
            $asset = $item->a;
            $available = $item->f;
            $onOrder = $item->l;
            $balances[$asset] = [
                "available" => $available,
                "onOrder" => $onOrder,
            ];
        }
        return $balances;
    }

    /**
     * tickerStreamHandler Convert WebSocket trade execution into array
     *
     * $data = $this->executionHandler( $json );
     *
     * @param \stdClass $json object data to convert
     * @return array
     */
    protected function executionHandler(\stdClass $json)
    {
        return [
            "symbol" => $json->s,
            "side" => $json->S,
            "orderType" => $json->o,
            "quantity" => $json->q,
            "price" => $json->p,
            "executionType" => $json->x,
            "orderStatus" => $json->X,
            "rejectReason" => $json->r,
            "orderId" => $json->i,
            "clientOrderId" => $json->c,
            "orderTime" => $json->T,
            "eventTime" => $json->E,
        ];
    }


}
