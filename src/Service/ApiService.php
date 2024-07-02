<?php

namespace Sdtech\BinanceApiLaravel\Service;


/*

*/

class ApiService
{
    protected $base; // /< REST endpoint for the currency exchange
    protected $baseTestnet; // /< Testnet REST endpoint for the currency exchange
    protected $wapi; // /< REST endpoint for the withdrawals
    protected $sapi; // /< REST endpoint for the supporting network API
    protected $fapi; // /< REST endpoint for the futures API
    protected $bapi; // /< REST endpoint for the internal Binance API
    protected $stream; // /< Endpoint for establishing websocket connections
    protected $streamTestnet; // /< Testnet endpoint for establishing websocket connections
    protected $api_key; // /< API key that you created in the binance website member area
    protected $api_secret; // /< API secret that was given to you when you created the api key
    protected $useTestnet; // /< Enable/disable testnet (https://testnet.binance.vision/)
    protected $depthCache = []; // /< Websockets depth cache
    protected $depthQueue = []; // /< Websockets depth queue
    protected $chartQueue = []; // /< Websockets chart queue
    protected $charts = []; // /< Websockets chart data
    protected $curlOpts = []; // /< User defined curl coptions
    protected $info = [
        "timeOffset" => 0,
    ]; // /< Additional connection options
    protected $proxyConf = null; // /< Used for story the proxy configuration
    protected $caOverride = false; // /< set this if you donnot wish to use CA bundle auto download feature
    protected $transfered = 0; // /< This stores the amount of bytes transfered
    protected $requestCount = 0; // /< This stores the amount of API requests
    protected $httpDebug = false; // /< If you enable this, curl will output debugging information
    protected $subscriptions = []; // /< View all websocket subscriptions
    protected $btc_value = 0.00; // /< value of available assets
    protected $btc_total = 0.00;

    // /< value of available onOrder assets

    protected $exchangeInfo = null;
    protected $lastRequest = [];

    protected $xMbxUsedWeight = 0;
    protected $xMbxUsedWeight1m = 0;

    private $imgManager;

    public function __construct()
    {
        $this->setupApiConfig();
    }

    protected function setupApiConfig()
    {
        $this->api_key = config('binanceapilaravel.BINANCE_API_KEY');
        $this->api_secret = config('binanceapilaravel.BINANCE_API_SECRET_KEY');
        $this->useTestnet = config('binanceapilaravel.BINANCE_API_TEST_MODE');

        $this->base = config('binanceapilaravel.BINANCE_API_LIVE_URL');
        $this->baseTestnet = config('binanceapilaravel.BINANCE_API_TESTNET_URL');
        $this->wapi = config('binanceapilaravel.BINANCE_WAPI_URL');
        $this->sapi = config('binanceapilaravel.BINANCE_SAPI_URL');
        $this->fapi = config('binanceapilaravel.BINANCE_FAPI_URL');
        $this->bapi = config('binanceapilaravel.BINANCE_BAPI_URL');
        $this->stream = config('binanceapilaravel.BINANCE_WSS_STREAM_URL');
        $this->streamTestnet = config('binanceapilaravel.BINANCE_WSS_STREAM_TESTNET_URL');
    }

    /**
     * httpRequest curl wrapper for all http api requests.
     * You can't call this function directly, use the helper functions
     *
     * @see prices()  $this->httpRequest( "https://api.binance.com/api/v3/ticker/price");
     *
     * @param $url string the endpoint to query, typically includes query string
     * @param $method string this should be typically GET, POST or DELETE
     * @param $params array addtional options for the request
     * @param $signed bool true or false sign the request with api secret
     * @return array containing the response
     * @throws \Exception
     */
    public function httpRequest(string $url, string $method = "GET", array $params = [], bool $signed = false)
    {
        if (!$this->is_setup()) {
            throw new \Exception("setup error: Api key and access key is missing");
        }

        if (function_exists('curl_init') === false) {
            throw new \Exception("Sorry cURL is not installed!");
        }

        if ($this->caOverride === false) {
            if (file_exists(getcwd() . '/ca.pem') === false) {
                $this->downloadCurlCaBundle();
            }
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_VERBOSE, $this->httpDebug);
        $query = http_build_query($params, '', '&');

        // signed with params
        if ($signed === true) {

            if (empty($this->api_key)) {
                throw new \Exception("signedRequest error: API Key not set!");
            }

            if (empty($this->api_secret)) {
                throw new \Exception("signedRequest error: API Secret not set!");
            }

            $base = $this->getRestEndpoint();
            $ts = (microtime(true) * 1000) + $this->info['timeOffset'];
            $params['timestamp'] = number_format($ts, 0, '.', '');

            if (isset($params['wapi'])) {
                if ($this->useTestnet) {
                    throw new \Exception("wapi endpoints are not available in testnet");
                }
                unset($params['wapi']);
                $base = $this->wapi;
            }

            if (isset($params['sapi'])) {
                if ($this->useTestnet) {
                    throw new \Exception("sapi endpoints are not available in testnet");
                }
                unset($params['sapi']);
                $base = $this->sapi;
            }

            $query = http_build_query($params, '', '&');
            $query = str_replace([ '%40' ], [ '@' ], $query);//if send data type "e-mail" then binance return: [Signature for this request is not valid.]
            $signature = hash_hmac('sha256', $query, $this->api_secret);
            if ($method === "POST") {
                $endpoint = $base . $url;
                $params['signature'] = $signature; // signature needs to be inside BODY
                $query = http_build_query($params, '', '&'); // rebuilding query
            } else {
                $endpoint = $base . $url . '?' . $query . '&signature=' . $signature;
            }

            curl_setopt($curl, CURLOPT_URL, $endpoint);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'X-MBX-APIKEY: ' . $this->api_key,
            ));
        }
        // params so buildquery string and append to url
        elseif (count($params) > 0) {
            curl_setopt($curl, CURLOPT_URL, $this->getRestEndpoint() . $url . '?' . $query);
        }
        // no params so just the base url
        else {
            curl_setopt($curl, CURLOPT_URL, $this->getRestEndpoint() . $url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'X-MBX-APIKEY: ' . $this->api_key,
            ));
        }
        curl_setopt($curl, CURLOPT_USERAGENT, "User-Agent: Mozilla/4.0 (compatible; PHP Binance API)");
        // Post and postfields
        if ($method === "POST") {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $query);
        }
        // Delete Method
        if ($method === "DELETE") {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        }

        // PUT Method
        if ($method === "PUT") {
            curl_setopt($curl, CURLOPT_PUT, true);
        }

        // proxy settings
        if (is_array($this->proxyConf)) {
            curl_setopt($curl, CURLOPT_PROXY, $this->getProxyUriString());
            if (isset($this->proxyConf['user']) && isset($this->proxyConf['pass'])) {
                curl_setopt($curl, CURLOPT_PROXYUSERPWD, $this->proxyConf['user'] . ':' . $this->proxyConf['pass']);
            }
        }
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);

        // set user defined curl opts last for overriding
        foreach ($this->curlOpts as $key => $value) {
            curl_setopt($curl, constant($key), $value);
        }

        if ($this->caOverride === false) {
            if (file_exists(getcwd() . '/ca.pem') === false) {
                $this->downloadCurlCaBundle();
            }
        }

        $output = curl_exec($curl);
        // Check if any error occurred
        if (curl_errno($curl) > 0) {
            // should always output error, not only on httpdebug
            // not outputing errors, hides it from users and ends up with tickets on github
            throw new \Exception('Curl error: ' . curl_error($curl));
        }

        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $header = $this->get_headers_from_curl_response($output);
        $output = substr($output, $header_size);

        curl_close($curl);

        $json = json_decode($output, true);

        $this->lastRequest = [
            'url' => $url,
            'method' => $method,
            'params' => $params,
            'header' => $header,
            'json' => $json
        ];

        if (isset($header['x-mbx-used-weight'])) {
            $this->setXMbxUsedWeight($header['x-mbx-used-weight']);
        }

        if (isset($header['x-mbx-used-weight-1m'])) {
            $this->setXMbxUsedWeight1m($header['x-mbx-used-weight-1m']);
        }

        if (isset($json['msg']) && !empty($json['msg'])) {
            if ( $url != 'v1/system/status' && $url != 'v3/systemStatus.html' && $url != 'v3/accountStatus.html') {
                // should always output error, not only on httpdebug
                // not outputing errors, hides it from users and ends up with tickets on github
                throw new \Exception('signedRequest error: '.print_r($output, true));
            }
        }
        $this->transfered += strlen($output);
        $this->requestCount++;
        return $json;
    }

    protected function setXMbxUsedWeight(int $usedWeight) : void
    {
        $this->xMbxUsedWeight = $usedWeight;
    }

    protected function setXMbxUsedWeight1m(int $usedWeight1m) : void
    {
        $this->xMbxUsedWeight1m = $usedWeight1m;
    }

    private function getRestEndpoint() : string
    {
        return $this->useTestnet ? $this->baseTestnet : $this->base;
    }

    /**
     * Due to ongoing issues with out of date wamp CA bundles
     * This function downloads ca bundle for curl website
     * and uses it as part of the curl options
     */
    protected function downloadCurlCaBundle()
    {
        $output_filename = getcwd() . "/ca.pem";

        if (is_writable(getcwd()) === false) {
            die(getcwd() . ' folder is not writeable, please check your permissions to download CA Certificates, or use $api->caOverride = true;');
        }

        $host = "https://curl.se/ca/cacert.pem";
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $host);
        curl_setopt($curl, CURLOPT_VERBOSE, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

        // proxy settings
        if (is_array($this->proxyConf)) {
            curl_setopt($curl, CURLOPT_PROXY, $this->getProxyUriString());
            if (isset($this->proxyConf['user']) && isset($this->proxyConf['pass'])) {
                curl_setopt($curl, CURLOPT_PROXYUSERPWD, $this->proxyConf['user'] . ':' . $this->proxyConf['pass']);
            }
        }

        $result = curl_exec($curl);
        curl_close($curl);

        if ($result === false) {
            echo "Unable to to download the CA bundle $host" . PHP_EOL;
            return;
        }

        $fp = fopen($output_filename, 'w');

        if ($fp === false) {
            echo "Unable to write $output_filename, please check permissions on folder" . PHP_EOL;
            return;
        }

        fwrite($fp, $result);
        fclose($fp);
    }

    /**
     * getProxyUriString get Uniform Resource Identifier string assocaited with proxy config
     *
     * $balances = $api->getProxyUriString();
     *
     * @return string uri
     */
    public function getProxyUriString()
    {
        $uri = isset($this->proxyConf['proto']) ? $this->proxyConf['proto'] : "http";
        // https://curl.haxx.se/libcurl/c/CURLOPT_PROXY.html
        $supportedProtocols = array(
            'http',
            'https',
            'socks4',
            'socks4a',
            'socks5',
            'socks5h',
        );

        if (in_array($uri, $supportedProtocols) === false) {
            // WPCS: XSS OK.
            echo "Unknown proxy protocol '" . $this->proxyConf['proto'] . "', supported protocols are " . implode(", ", $supportedProtocols) . PHP_EOL;
        }

        $uri .= "://";
        $uri .= isset($this->proxyConf['address']) ? $this->proxyConf['address'] : "localhost";

        if (isset($this->proxyConf['address']) === false) {
            // WPCS: XSS OK.
            echo "warning: proxy address not set defaulting to localhost" . PHP_EOL;
        }

        $uri .= ":";
        $uri .= isset($this->proxyConf['port']) ? $this->proxyConf['port'] : "1080";

        if (isset($this->proxyConf['address']) === false) {
            // WPCS: XSS OK.
            echo "warning: proxy port not set defaulting to 1080" . PHP_EOL;
        }

        return $uri;
    }

    /**
     * Converts the output of the CURL header to an array
     *
     * @param $header string containing the response
     * @return array headers converted to an array
     */
    public function get_headers_from_curl_response(string $header)
    {
        $headers = array();
        $header_text = substr($header, 0, strpos($header, "\r\n\r\n"));

        foreach (explode("\r\n", $header_text) as $i => $line)
            if ($i === 0)
                $headers['http_code'] = $line;
            else {
                list ($key, $value) = explode(': ', $line);
                $headers[$key] = $value;
            }

        return $headers;
    }



    private function is_setup() {
        return (!empty($this->api_key) || !empty($this->api_secret));
    }

    public function sendResponse($status,$success,$message = "",$data = [])
    {
        return [
            'status' => $status,
            'success' => $success,
            'message' => $message ? $message : 'default message',
            'data' => $data
        ];
    }

}
