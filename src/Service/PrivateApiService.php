<?php

namespace Sdtech\BinanceApiLaravel\Service;

/*

*/

class PrivateApiService
{
    private $api;
    protected $btc_value = 0.00; // /< value of available assets
    protected $btc_total = 0.00;


    public function __construct()
    {
        $this->api = new ApiService();
    }

     /**
     * balances get balances for the account assets
     *
     * $balances = $api->balances($ticker);
     *
     * @param bool $priceData array of the symbols balances are required for
     * @return array with error message or array of balances
     * @throws \Exception
     */
    public function balances($priceData = false)
    {
        try {
            if (is_array($priceData) === false) {
                $priceData = false;
            }

            $account = $this->api->httpRequest("v3/account", "GET", [], true);

            if (is_array($account) === false) {
                return $this->api->sendResponse(400,true,"Error: unable to fetch your account details" . PHP_EOL);
            }

            if (isset($account['balances']) === false || empty($account['balances'])) {
                return $this->api->sendResponse(400,true,"Error: your balances were empty or unset" . PHP_EOL);
            }

            $data = $this->balanceData($account, $priceData);
            return $this->api->sendResponse(200,true,'success',$data);
        } catch(\Exception $e) {
            return $this->api->sendResponse(500,false,$e->getMessage(),[]);
        }

    }

    /**
     * balanceData Converts all your balances into a nice array
     * If priceData is passed from $api->prices() it will add btcValue & btcTotal to each symbol
     * This function sets $btc_value which is your estimated BTC value of all assets combined and $btc_total which includes amount on order
     *
     * $candles = $api->candlesticks("BNBBTC", "5m");
     *
     * @param $array array of your balances
     * @param $priceData array of prices
     * @return array containing the response
     */
    protected function balanceData(array $array, $priceData)
    {
        $balances = [];

        if (is_array($priceData)) {
            $btc_value = $btc_total = 0.00;
        }

        if (empty($array) || empty($array['balances'])) {
            // WPCS: XSS OK.
            echo "balanceData error: Please make sure your system time is synchronized: call \$api->useServerTime() before this function" . PHP_EOL;
            echo "ERROR: Invalid request. Please double check your API keys and permissions." . PHP_EOL;
            return [];
        }

        foreach ($array['balances'] as $obj) {
            $asset = $obj['asset'];
            $balances[$asset] = [
                "available" => $obj['free'],
                "onOrder" => $obj['locked'],
                "btcValue" => 0.00000000,
                "btcTotal" => 0.00000000,
            ];

            if (is_array($priceData) === false) {
                continue;
            }

            if ($obj['free'] + $obj['locked'] < 0.00000001) {
                continue;
            }

            if ($asset === 'BTC') {
                $balances[$asset]['btcValue'] = $obj['free'];
                $balances[$asset]['btcTotal'] = $obj['free'] + $obj['locked'];
                $btc_value += $obj['free'];
                $btc_total += $obj['free'] + $obj['locked'];
                continue;
            } elseif ($asset === 'USDT' || $asset === 'USDC' || $asset === 'PAX' || $asset === 'BUSD') {
                $btcValue = $obj['free'] / $priceData['BTCUSDT'];
                $btcTotal = ($obj['free'] + $obj['locked']) / $priceData['BTCUSDT'];
                $balances[$asset]['btcValue'] = $btcValue;
                $balances[$asset]['btcTotal'] = $btcTotal;
                $btc_value += $btcValue;
                $btc_total += $btcTotal;
                continue;
            }

            $symbol = $asset . 'BTC';

            if ($symbol === 'BTCUSDT') {
                $btcValue = number_format($obj['free'] / $priceData['BTCUSDT'], 8, '.', '');
                $btcTotal = number_format(($obj['free'] + $obj['locked']) / $priceData['BTCUSDT'], 8, '.', '');
            } elseif (isset($priceData[$symbol]) === false) {
                $btcValue = $btcTotal = 0;
            } else {
                $btcValue = number_format($obj['free'] * $priceData[$symbol], 8, '.', '');
                $btcTotal = number_format(($obj['free'] + $obj['locked']) * $priceData[$symbol], 8, '.', '');
            }

            $balances[$asset]['btcValue'] = $btcValue;
            $balances[$asset]['btcTotal'] = $btcTotal;
            $btc_value += $btcValue;
            $btc_total += $btcTotal;
        }
        if (is_array($priceData)) {
            uasort($balances, function ($opA, $opB) {
                if ($opA == $opB)
                    return 0;
                return ($opA['btcValue'] < $opB['btcValue']) ? 1 : -1;
            });
            $this->btc_value = $btc_value;
            $this->btc_total = $btc_total;
        }
        return $balances;
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
                return $arr;

            if (!empty($arr['BTC']['withdrawFee'])) {
                return array(
                    'success'     => 1,
                    'assetDetail' => $arr,
                    );
            } else {
                return array(
                    'success'     => 0,
                    'assetDetail' => array(),
                    );

            }
            // return $this->api->sendResponse(200,true,'success',$data);
        } catch(\Exception $e) {
            return $this->api->sendResponse(500,false,$e->getMessage(),[]);
        }

    }

    /**
     * account get all information about the api account
     *
     * $account = $api->account();
     *
     * @return array with error message or array of all the account information
     * @throws \Exception
     */
    public function account()
    {
        try {
            $data = $this->api->httpRequest("v3/account", "GET", [], true);
            return $this->api->sendResponse(200,true,'success',$data);
        } catch(\Exception $e) {
            return $this->api->sendResponse(500,false,$e->getMessage(),[]);
        }
    }


}
