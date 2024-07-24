<?php

namespace Sdtech\BinanceApiLaravel\Service;



class BinanceApiLaravelService extends BinanceApiService
{

    private $publicApi;
    private $privateApi;
    private $brokerApi;
    private $socketApi;
    private $spotTradeApi;

    public function __construct()
    {
        $this->publicApi = new PublicApiService();
        $this->privateApi = new PrivateApiService();
        $this->brokerApi = new BrokerApiService();
        $this->socketApi = new WebSocketService();
        $this->spotTradeApi = new SpotTradeApiService();
    }

    public function testing(){
        return 'ok testing';
    }


    /*** binance spot api start ***/

    // test connectivity
    public function _ping() {
		return $this->publicApi->ping();
	}

    // check server time
	public function _time() {
		return $this->publicApi->time();
	}

    // get exchange info
	public function _exchangeInfo($symbol=null) {
		return $this->publicApi->exchangeInfo($symbol);
	}

    // Get latest price of a symbol like BNBUSDT
    public function _price(string $symbol=null, $symbols= [])
    {
        return $this->publicApi->price($symbol,$symbols);
    }

    // Get latest price of all symbols
    public function _prices()
    {
        return $this->publicApi->prices();
    }

    // Get all bid/ask prices
    public function _orderBookTicker($symbol=null,$symbols=[])
    {
        return $this->publicApi->orderBookTicker($symbol,$symbols);
    }

    // Getting 24hr ticker price change statistics for a symbol like BNBUSDT
    // if empty symbol Getting 24hr ticker price change statistics for all symbols
    public function _priceChange24h($symbol=null)
    {
        return $this->publicApi->priceChange24h($symbol);
    }

    // Aggregate Trades List symbol = BNBUSDT, $limit = 500 default
    public function _aggTrades($symbol=null,$limit=500,$fromId=null,$startTime=null,$endTime=null)
    {
        return $this->publicApi->aggTrades($symbol,$limit,$fromId,$startTime,$endTime);
    }

     /**
     * assetDetail - Fetch details of assets supported on Binance
     * @param string $asset  (optional)  Should be an asset, e.g. BNB or empty to get the full list
     */
    public function _assetDetail($asset='')
    {
        return $this->publicApi->assetDetail($asset);
    }

    /**
     *  balances get balances for the account assets
     * @param bool $priceData array of the symbols balances are required for
     */
    public function _balances($priceData=false)
    {
        return $this->privateApi->balances($priceData);
    }

    // account get all information about the api account
    public function _account()
    {
        return $this->privateApi->account();
    }

    // Kline/Candlestick Data
    // Get Kline/candlestick data for a symbol
    // Periods: 1m,3m,5m,15m,30m,1h,2h,4h,6h,8h,12h,1d,3d,1w,1M
    public function _candlesticks(string $symbol, string $interval = "5m", int $limit = 500, $startTime = null, $endTime = null)
    {
        return $this->publicApi->candlesticks($symbol, $interval,$limit, $startTime,$endTime);
    }

     // UIKlines/Candlestick Data
     public function _uiKlinesCandlesticks(string $symbol, string $interval = "5m", int $limit = 500, $startTime = null, $endTime = null)
     {
         return $this->publicApi->uiKlinesCandlesticks($symbol, $interval,$limit, $startTime,$endTime);
     }

    // spot order book
    public function _orderBook($symbol,$limit=100)
    {
        return $this->publicApi->orderBook($symbol,$limit);
    }

    // Recent Trades List
    public function _recentTrades($symbol,$limit=100)
    {
        return $this->publicApi->recentTrades($symbol,$limit);
    }

    // Old Trade Lookup (historicalTrades)
    public function _historicalTrades(string $symbol, int $limit=100, $fromId="")
    {
        return $this->publicApi->historicalTrades($symbol, $limit, $fromId);
    }

    // Trading Day Ticker
    // Price change statistics for a trading day.
    public function _tradingDayTicker(string $symbol, $type='FULL', $symbols=[]){
        return $this->publicApi->tradingDayTicker($symbol, $type, $symbols);
    }

    // Rolling window price change statistics
    public function _ticker(string $symbol, $type='FULL', $symbols=[],$windowSize='1d'){
        return $this->publicApi->ticker($symbol, $type, $symbols, $windowSize);
    }


    /*** Broker api start ***/
    // create broker sub account
    public function _createSubAccount($subAccountString)
    {
        return $this->brokerApi->createSubAccount($subAccountString);
    }

    // sub account list
    public function _subAccountList($subAccountId = null,$page=1,$size=100)
    {
        return $this->brokerApi->subAccountList($subAccountId,$page,$size);
    }

    // enable margin for sub account
    public function _enableMarginForSubAccount($subAccountId,$margin=true)
    {
        return $this->brokerApi->enableMarginForSubAccount($subAccountId,$margin);
    }

    // enable futures for sub account
    public function _enableFutureForSubAccount($subAccountId,$futures=true)
    {
        return $this->brokerApi->enableFutureForSubAccount($subAccountId,$futures);
    }

    // Create Api Key for Sub Account
    public function _createApiKeyForSubAccount($subAccountId,$spotTrade,$marginTrade,$futuresTrade)
    {
        return $this->brokerApi->createApiKeyForSubAccount($subAccountId,$spotTrade,$marginTrade,$futuresTrade);
    }

    // delete Api Key from Sub Account
    public function _deleteApiKeyForSubAccount($subAccountId,$subAccountApiKey)
    {
        return $this->brokerApi->deleteApiKeyForSubAccount($subAccountId,$subAccountApiKey);
    }

    // sub account api key list
    public function _subAccountApiKeyList($subAccountId = null,$page=1,$size=100)
    {
        return $this->brokerApi->subAccountApiKeyList($subAccountId,$page,$size);
    }

    // Change Sub Account Api Permission
    public function _changeApiKeyPermissionForSubAccount($subAccountId,$subAccountApiKey,$spotTrade,$marginTrade,$futuresTrade)
    {
        return $this->brokerApi->changeApiKeyPermissionForSubAccount($subAccountId,$subAccountApiKey,$spotTrade,$marginTrade,$futuresTrade);
    }

    // Change Sub Account Commission
    public function _changeCommissionForSubAccount($subAccountId,$makerCommission,$takerCommission,$marginMakerCommission=0,$marginTakerCommission=0)
    {
        return $this->brokerApi->changeCommissionForSubAccount($subAccountId,$makerCommission,$takerCommission,$marginMakerCommission,$marginTakerCommission);
    }

    // Broker Account Information
    public function _brokerAccountInfo()
    {
        return $this->brokerApi->brokerAccountInfo();
    }

    // Sub Account Transfer（SPOT）
    public function _subAccountTransferSpot($asset, $amount, $fromId = null, $toId = null, $clientTranId = null)
    {
        return $this->brokerApi->subAccountTransferSpot($asset, $amount, $fromId, $toId, $clientTranId);
    }

    // Query Sub Account Transfer History（SPOT)
    public function _subAccountTransferListSpot(
        $fromId = null,
        $toId = null,
        $clientTranId = null,
        $showAllStatus=null,
        $startTime=null,
        $endTime=null,
        $page=1,
        $limit=100,
    )
    {
        return $this->brokerApi->subAccountTransferListSpot(
            $fromId,
            $toId,
            $clientTranId,
            $showAllStatus,
            $startTime,
            $endTime,
            $page,
            $limit,
        );
    }

    // Get Sub Account Deposit History
    public function _subAccountDepositHistory(
        $subAccountId = null,
        $coin = null,
        $status = "",
        $startTime=null,
        $endTime=null,
        $offset=0,
        $limit=100,
    )
    {
        return $this->brokerApi->subAccountDepositHistory(
            $subAccountId,
            $coin,
            $status,
            $startTime,
            $endTime,
            $offset,
            $limit,
        );
    }

    // Query Sub Account Spot Asset info
    public function _subAccountAssetInfoSpot(
        $subAccountId = null,
        $page=1,
        $size=10,
    )
    {
        return $this->brokerApi->subAccountAssetInfoSpot(
            $subAccountId,
            $page,
            $size,
        );
    }

    // Query Subaccount Margin Asset info
    public function _subAccountAssetInfoMargin(
        $subAccountId = null,
        $page=1,
        $size=10,
    )
    {
        return $this->brokerApi->subAccountAssetInfoMargin(
            $subAccountId,
            $page,
            $size,
        );
    }

    // Query Broker Commission Rebate Recent Record（Spot）
    public function _brokerRebateRecentRecord(
        $subAccountId = null,
        $startTime = 7,
        $endTime = null,
        $page=1,
        $size=100,
    )
    {
        return $this->brokerApi->brokerRebateRecentRecord(
            $subAccountId,
            $startTime,
            $endTime,
            $page,
            $size,
        );
    }

    // Get IP Restriction for Sub Account Api Key
    public function _getIpRestrictionForSubAccount(
        $subAccountId,
        $subAccountApiKey
    )
    {
        return $this->brokerApi->getIpRestrictionForSubAccount(
            $subAccountId,
            $subAccountApiKey
        );
    }

    // Delete IP Restriction for Sub Account Api Key
    public function _deleteIpRestrictionForSubAccount(
        $subAccountId,
        $subAccountApiKey,
        $ipAddress
    )
    {
        return $this->brokerApi->deleteIpRestrictionForSubAccount(
            $subAccountId,
            $subAccountApiKey,
            $ipAddress
        );
    }

    // Update IP Restriction for Sub-Account API key (For Master Account)
    public function _updateIpRestrictionForSubAccount(
        $subAccountId,
        $subAccountApiKey,
        $status,
        $ipAddress=""
    )
    {
        return $this->brokerApi->updateIpRestrictionForSubAccount(
            $subAccountId,
            $subAccountApiKey,
            $status,
            $ipAddress
        );
    }

    /*** Broker api end ***/

    /*** Socket api start ***/
    // Get complete realtime chart data via WebSockets
    // @param $symbols = ["BTCUSDT", "EOSBTC"]
    public function _chart($symbols=[],$interval) {
        $$this->socketApi->chart($symbols,$interval, function($socketApi,$symbol, $chart) {
            echo "{$symbol} chart update\n";
            print_r($chart);
            $endpoint = strtolower( $symbol ) . '@kline_' . "15m";
            $socketApi->terminate( $endpoint );
        });
    }


    // depthCache Pulls /depth data and subscribes to @depth WebSocket endpoint
    // @param $symbols = ["BTCUSDT", "EOSBTC"]
    public function _depthCache($symbols=[]) {
        $$this->socketApi->depthCache($symbols, function($socketApi, $symbol, $depth) {
            echo "{$symbol} depth cache update\n";
            $limit = 11; // Show only the closest asks/bids
            $sorted = $socketApi->sortDepth($symbol, $limit);
            $bid = $socketApi->first($sorted['bids']);
            $ask = $socketApi->first($sorted['asks']);
            echo $socketApi->displayDepth($sorted);
            echo "ask: {$ask}\n";
            echo "bid: {$bid}\n";
            $endpoint = strtolower( $symbol ) . '@depthCache';
            $socketApi->terminate( $endpoint );
        });
    }


     // depthCache Pulls /depth data and subscribes to @depth WebSocket endpoint
     // @param $symbols = ["BTCUSDT", "EOSBTC"]
     // @param $interval = "5m"
     public function _kline($symbols=[],$intervals) {
        $$this->socketApi->kline($symbols,$intervals, function($socketApi, $symbol, $chart) {
            var_dump( $chart );
            //echo "{$symbol} ({$interval}) candlestick update\n";
            $interval = $chart->i;
            $tick = $chart->t;
            $open = $chart->o;
            $high = $chart->h;
            $low = $chart->l;
            $close = $chart->c;
            $volume = $chart->q; // +trades buyVolume assetVolume makerVolume
            echo "{$symbol} price: {$close}\t volume: {$volume}\n";

            $endpoint = strtolower( $symbol ) . '@kline_' . "5m";
            $socketApi->terminate( $endpoint );
        });
    }

     // miniTicker Get miniTicker for all symbols
     public function _miniTicker() {
        $count = 0;
        $$this->socketApi->miniTicker( function ( $socketApi, $ticker ) use ( &$count ) {
            print_r( $ticker );
            $count++;
            print $count . "\n";
            if($count > 2) {
               $endpoint = '@miniticker';
               $socketApi->terminate( $endpoint );
            }
        });
    }


    // Trade Updates via WebSocket
     // @param $symbols = ["BTCUSDT", "EOSBTC"]
     public function _trades($symbols) {
        $$this->socketApi->trades($symbols, function($socketApi, $symbol, $trades) {
            echo "{$symbol} trades update".PHP_EOL;
            print_r($trades);
            $endpoint = strtolower( $symbol ) . '@trades';
            $socketApi->terminate( $endpoint );
        });
    }


    public function _userData()
    {
        $balanceUpdate = function($api, $balances) {
            print_r($balances);
            echo "Balance update".PHP_EOL;
        };

        $orderUpdate = function($api, $report) {
            echo "Order update".PHP_EOL;
            print_r($report);
            $price = $report['price'];
            $quantity = $report['quantity'];
            $symbol = $report['symbol'];
            $side = $report['side'];
            $orderType = $report['orderType'];
            $orderId = $report['orderId'];
            $orderStatus = $report['orderStatus'];
            $executionType = $report['orderStatus'];
            if ($executionType == "NEW") {
                if ($executionType == "REJECTED") {
                    echo "Order Failed! Reason: {$report['rejectReason']}".PHP_EOL;
                }
                echo "{$symbol} {$side} {$orderType} ORDER #{$orderId} ({$orderStatus})".PHP_EOL;
                echo "..price: {$price}, quantity: {$quantity}".PHP_EOL;
                return;
            }
            //NEW, CANCELED, REPLACED, REJECTED, TRADE, EXPIRED
            echo "{$symbol} {$side} {$executionType} {$orderType} ORDER #{$orderId}".PHP_EOL;
        };

        $this->socketApi->userData($balanceUpdate, $orderUpdate);
    }

    /**
     * create listen key for userDataStream
     *
     * $api->createListenKeySpot();
     *
     * @return null
     */
    public function _createListenKeySpot()
    {
        return $this->socketApi->createListenKeySpot();
    }

     /**
     * create listen key for userDataStream
     *
     * $api->closeListenKeySpot();
     * @param $listenKey
     *
     * @return null
     */
    public function _closeListenKeySpot($listenKey)
    {
        return $this->socketApi->closeListenKeySpot($listenKey);
    }


    /**** spot trade api start ****/


    /**
     * place spot test order
     *
     */
    public function _placeTestOrderSpot(string $side, string $symbol, $quantity, $price, string $type = "LIMIT", array $flags = [])
    {
        return $this->spotTradeApi->placeTestOrderSpot($side, $symbol, $quantity, $price, $type, $flags);
    }

    /**
     * place order
     */
    public function _placeOrderSpot(string $side, string $symbol, $quantity, $price, string $type = "LIMIT", array $flags = [])
    {
        return $this->spotTradeApi->placeOrderSpot($side, $symbol, $quantity, $price, $type, $flags);
    }

    // Place a buy order test
    public function _buyTestOrder(string $symbol, $quantity, $price, string $type = "LIMIT", array $flags = [])
    {
        return $this->spotTradeApi->buyTestOrder($symbol, $quantity, $price, $type, $flags);
    }

    // Place a LIMIT buy order test
    public function _placeLimitBuyTestOrder(string $symbol, $quantity, $price)
    {
        return $this->spotTradeApi->buyTestOrder($symbol, $quantity, $price);
    }

    // Place a MARKET buy order test
    public function _placeMarketBuyTestOrder(string $symbol, $quantity)
    {
        return $this->spotTradeApi->buyTestOrder($symbol, $quantity, 0, "MARKET");
    }

    // Place a sell order test
    public function _sellTestOrder(string $symbol, $quantity, $price, string $type = "LIMIT", array $flags = [])
    {
        return $this->spotTradeApi->sellTestOrder($symbol, $quantity, $price, $type, $flags);
    }

    // Place a STOP LOSS order
    // When the stop is reached, a stop order becomes a market order
    public function _placeStopLossSellTestOrder(string $symbol, $quantity,$price, $stopPrice)
    {
        return $this->spotTradeApi->sellTestOrder($symbol, $quantity, $price, "STOP_LOSS", ["stopPrice"=>$stopPrice]);
    }

    // Place a take profit order
    // When the stop is reached, a stop order becomes a market order
    public function _placeTakeProfitSellTestOrder(string $symbol, $quantity,$price, $stopPrice)
    {
        return $this->spotTradeApi->sellTestOrder($symbol, $quantity, $price, "TAKE_PROFIT", ["stopPrice"=>$stopPrice]);
    }

    // Place an ICEBERG order
    // Iceberg orders are intended to conceal the true order quantity.
    public function _placeIceBergSellTestOrder(string $symbol, $quantity,$price, $icebergQty)
    {
        return $this->spotTradeApi->sellTestOrder($symbol, $quantity, $price, "LIMIT", ["icebergQty"=>$icebergQty]);
    }

    // place sell LIMIT order
    public function _placeLimitSellTestOrder($symbol,$quantity,$price)
    {
        return $this->spotTradeApi->sellTestOrder($symbol, $quantity, $price);
    }

    // place sell MARKET order
    public function _placeMarketSellTestOrder($symbol,$quantity)
    {
        return $this->spotTradeApi->sellTestOrder($symbol, $quantity, 0, "MARKET");
    }

    // Place a buy order
    public function _buyOrder(string $symbol, $quantity, $price, string $type = "LIMIT", array $flags = [])
    {
        return $this->spotTradeApi->buyOrder($symbol, $quantity, $price, $type, $flags);
    }

    // Place a LIMIT buy order
    public function _placeLimitBuyOrder(string $symbol, $quantity, $price)
    {
        return $this->spotTradeApi->buyOrder($symbol, $quantity, $price);
    }

    // Place a MARKET buy order
    public function _placeMarketBuyOrder(string $symbol, $quantity)
    {
        return $this->spotTradeApi->buyOrder($symbol, $quantity, 0, "MARKET");
    }

    // Place a sell order
    public function _sellOrder(string $symbol, $quantity, $price, string $type = "LIMIT", array $flags = [])
    {
        return $this->spotTradeApi->sellOrder($symbol, $quantity, $price, $type, $flags);
    }

    // Place a STOP LOSS order
    // When the stop is reached, a stop order becomes a market order
    public function _placeStopLossSellOrder(string $symbol, $quantity,$price, $stopPrice)
    {
        return $this->spotTradeApi->sellOrder($symbol, $quantity, $price, "STOP_LOSS", ["stopPrice"=>$stopPrice]);
    }

    // Place a take profit order
    // When the stop is reached, a stop order becomes a market order
    public function _placeTakeProfitSellOrder(string $symbol, $quantity,$price, $stopPrice)
    {
        return $this->spotTradeApi->sellOrder($symbol, $quantity, $price, "TAKE_PROFIT", ["stopPrice"=>$stopPrice]);
    }

    // Place an ICEBERG order
    // Iceberg orders are intended to conceal the true order quantity.
    public function _placeIceBergSellOrder(string $symbol, $quantity,$price, $icebergQty)
    {
        return $this->spotTradeApi->sellOrder($symbol, $quantity, $price, "LIMIT", ["icebergQty"=>$icebergQty]);
    }

    // place sell LIMIT order
    public function _placeLimitSellOrder($symbol,$quantity,$price)
    {
        return $this->spotTradeApi->sellOrder($symbol, $quantity, $price);
    }

    // place sell MARKET order
    public function _placeMarketSellOrder($symbol,$quantity)
    {
        return $this->spotTradeApi->sellOrder($symbol, $quantity, 0, "MARKET");
    }

    // Current Open Orders (USER_DATA)
    public function _openOrders($symbol = null)
    {
        return $this->spotTradeApi->openOrders($symbol);
    }

    // Cancel Order (TRADE)
    public function _cancelOrder(string $symbol, $orderid, $flags = [])
    {
        return $this->spotTradeApi->cancelOrder($symbol, $orderid, $flags);
    }

    // Cancel all Open Orders on a Symbol (TRADE)
    public function _cancelAllOrders(string $symbol)
    {
        return $this->spotTradeApi->cancelOpenOrders($symbol);
    }

    // user trade history (TRADE)
    public function _userTradeHistory(string $symbol)
    {
        return $this->spotTradeApi->userTradeHistory($symbol);
    }

    /**** spot trade api end ****/
}
