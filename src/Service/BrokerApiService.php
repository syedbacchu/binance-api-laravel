<?php

namespace Sdtech\BinanceApiLaravel\Service;

/*

*/

class BrokerApiService
{
    private $api;
    protected $btc_value = 0.00; // /< value of available assets
    protected $btc_total = 0.00;


    public function __construct()
    {
        $this->api = new ApiService();
    }


    /**
     * create new sub account
     *
     * $account = $api->createSubAccount();
     *
     * @param string $subAccountString is the account name
     * @return array with error message or array of all the account information
     * @throws \Exception
     */
    public function createSubAccount($subAccountString)
    {
        try {
            $params = [
                "tag" => $subAccountString,
                "sapi" => true,
            ];

            $data = $this->api->httpRequest("v1/broker/subAccount", "POST", $params, true);
            return $this->api->sendResponse(200,true,'success',$data);
        } catch(\Exception $e) {
            return $this->api->sendResponse(500,false,$e->getMessage(),[]);
        }
    }

    /**
     * account get all information about the api account
     *
     * $account = $api->subAccountList();
     *
     * @param string $subAccountId sub account id optional
     * @param LONG $page default 1
     * @param LONG $size default 100
     * @return array with error message or array of all the account information
     * @throws \Exception
     */
    public function subAccountList($subAccountId = null,$page=1,$size=100)
    {
        try {
            $params = [
                "sapi" => true,
                'page' => $page,
                'size' => $size,
            ];
            if(!empty($subAccountId)){
                $params['subAccountId'] = $subAccountId;
            }
            $data = $this->api->httpRequest("v1/broker/subAccount", "GET", $params, true);
            return $this->api->sendResponse(200,true,'success',$data);
        } catch(\Exception $e) {
            return $this->api->sendResponse(500,false,$e->getMessage(),[]);
        }
    }

    /**
     * enable margin trade for sub account
     *
     * $account = $api->enableMarginForSubAccount();
     *
     * @param string $subAccountId is the account account id
     * @return array with error message or array of all the account information
     * @throws \Exception
     */
    public function enableMarginForSubAccount($subAccountId,$margin=true)
    {
        try {
            $params = [
                "subAccountId" => $subAccountId,
                "margin" => $margin,
                "sapi" => true,
            ];

            $data = $this->api->httpRequest("v1/broker/subAccount/margin", "POST", $params, true);
            return $this->api->sendResponse(200,true,'success',$data);
        } catch(\Exception $e) {
            return $this->api->sendResponse(500,false,$e->getMessage(),[]);
        }
    }

    /**
     * enable future trade for sub account
     *
     * $account = $api->enableFutureForSubAccount();
     *
     * @param string $subAccountId is the account account id
     * @return array with error message or array of all the account information
     * @throws \Exception
     */
    public function enableFutureForSubAccount($subAccountId,$futures)
    {
        try {
            $params = [
                "subAccountId" => $subAccountId,
                "futures" => $futures,
                "sapi" => true,
            ];

            $data = $this->api->httpRequest("v1/broker/subAccount/futures", "POST", $params, true);
            return $this->api->sendResponse(200,true,'success',$data);
        } catch(\Exception $e) {
            return $this->api->sendResponse(500,false,$e->getMessage(),[]);
        }
    }

    /**
     * Create Api Key for Sub Account
     *
     * $account = $api->createApiKeyForSubAccount();
     *
     * @param STRING $subAccountId is the account account id
     * @param ENUM $spotTrade true or false
     * @param ENUM $marginTrade true or false
     * @param ENUM $futuresTrade true or false
     * @return array with error message or array of all the account information
     * @throws \Exception
     */
    public function createApiKeyForSubAccount($subAccountId,$spotTrade,$marginTrade,$futuresTrade)
    {
        try {
            $params = [
                "subAccountId" => $subAccountId,
                "canTrade" => $spotTrade,
                "marginTrade" => $marginTrade,
                "futuresTrade" => $futuresTrade,
                "sapi" => true,
            ];

            $data = $this->api->httpRequest("v1/broker/subAccountApi", "POST", $params, true);
            return $this->api->sendResponse(200,true,'success',$data);
        } catch(\Exception $e) {
            return $this->api->sendResponse(500,false,$e->getMessage(),[]);
        }
    }

    /**
     * Delete Sub Account Api Key
     *
     * $account = $api->deleteApiKeyForSubAccount();
     *
     * @param STRING $subAccountId is the account account id
     * @param ENUM $subAccountApiKey the api key
     * @return array with error message or array of all the account information
     * @throws \Exception
     */
    public function deleteApiKeyForSubAccount($subAccountId,$subAccountApiKey)
    {
        try {
            $params = [
                "subAccountId" => $subAccountId,
                "subAccountApiKey" => $subAccountApiKey,
                "sapi" => true,
            ];

            $data = $this->api->httpRequest("v1/broker/subAccountApi", "DELETE", $params, true);
            return $this->api->sendResponse(200,true,'success',$data);
        } catch(\Exception $e) {
            return $this->api->sendResponse(500,false,$e->getMessage(),[]);
        }
    }

    /**
     * Query Sub Account Api Key
     *
     * $account = $api->subAccountApiKeyList();
     *
     * @param STRING $subAccountId sub account id mandetory
     * @param STRING $subAccountApiKey optional
     * @param LONG $page default 1
     * @param LONG $size default 100 max 500
     * @return array with error message or array of all the account information
     * @throws \Exception
     */
    public function subAccountApiKeyList($subAccountId,$subAccountApiKey=null,$page=1,$size=100)
    {
        try {
            $params = [
                "subAccountId" => $subAccountId,
                "sapi" => true,
                'page' => $page,
                'size' => $size,
            ];
            if(!empty($subAccountApiKey)){
                $params['subAccountApiKey'] = $subAccountApiKey;
            }
            $data = $this->api->httpRequest("v1/broker/subAccountApi", "GET", $params, true);
            return $this->api->sendResponse(200,true,'success',$data);
        } catch(\Exception $e) {
            return $this->api->sendResponse(500,false,$e->getMessage(),[]);
        }
    }

    /**
     * Change Sub Account Api Permission
     *
     * $account = $api->changeApiKeyPermissionForSubAccount();
     *
     * @param STRING $subAccountId is the account id
     * @param STRING $subAccountApiKey api key
     * @param ENUM $spotTrade true or false
     * @param ENUM $marginTrade true or false
     * @param ENUM $futuresTrade true or false
     * @return array with error message or array of all the account information
     * @throws \Exception
     */
    public function changeApiKeyPermissionForSubAccount($subAccountId,$subAccountApiKey,$spotTrade,$marginTrade,$futuresTrade)
    {
        try {
            $params = [
                "subAccountId" => $subAccountId,
                "subAccountApiKey" => $subAccountApiKey,
                "canTrade" => $spotTrade,
                "marginTrade" => $marginTrade,
                "futuresTrade" => $futuresTrade,
                "sapi" => true,
            ];

            $data = $this->api->httpRequest("v1/broker/subAccountApi", "POST", $params, true);
            return $this->api->sendResponse(200,true,'success',$data);
        } catch(\Exception $e) {
            return $this->api->sendResponse(500,false,$e->getMessage(),[]);
        }
    }

    /**
     * Change Sub Account Commission
     *
     * $account = $api->changeCommissionForSubAccount();
     *
     * @param STRING $subAccountId the account id mandetory
     * @param FLOAT $makerCommission mandetory
     * @param FLOAT $takerCommission mandetory
     * @param FLOAT $marginMakerCommission optional
     * @param FLOAT $marginTakerCommission optional
     * @return array with error message or array of all the account information
     * @throws \Exception
     */
    public function changeCommissionForSubAccount($subAccountId,$makerCommission,$takerCommission,$marginMakerCommission=0,$marginTakerCommission=0)
    {
        try {
            $params = [
                "subAccountId" => $subAccountId,
                "makerCommission" => $makerCommission,
                "takerCommission" => $takerCommission,
                "marginMakerCommission" => $marginMakerCommission,
                "marginTakerCommission" => $marginTakerCommission,
                "sapi" => true,
            ];

            $data = $this->api->httpRequest("v1/broker/subAccountApi/commission", "POST", $params, true);
            return $this->api->sendResponse(200,true,'success',$data);
        } catch(\Exception $e) {
            return $this->api->sendResponse(500,false,$e->getMessage(),[]);
        }
    }

    /**
     * Broker Account Information
     *
     * $account = $api->brokerAccountInfo();
     *
     *
     * @return array with error message or array of all the account information
     * @throws \Exception
     */
    public function brokerAccountInfo()
    {
        try {
            $params = [
                "sapi" => true,
            ];

            $data = $this->api->httpRequest("v1/broker/info", "GET", $params, true);
            return $this->api->sendResponse(200,true,'success',$data);
        } catch(\Exception $e) {
            return $this->api->sendResponse(500,false,$e->getMessage(),[]);
        }
    }

    /**
     * Sub Account Transfer（SPOT）
     * You need to enable "internal transfer" option for the api key which requests this endpoint.
     *
     * $account = $api->subAccountTransferSpot();
     *
     * @param STRING $fromId (optional) Transfer from master account if fromId not sent.
     * @param STRING $toId (optional) Transfer to master account if toId not sent.
     * @param STRING $clientTranId (optional) client transfer id, must be unique. The max length is 32 characters
     * @param STRING $asset mandetory
     * @param DECIMAL $amount mandetory
     *
     * @return array with error message or array of all the information
     * @throws \Exception
     */
    public function subAccountTransferSpot(
        $asset, $amount, $fromId = null, $toId = null, $clientTranId = null
        )
    {
        try {
            $params = [
                "sapi" => true,
                $asset => $asset,
                $amount => $amount,
                $fromId => $fromId,
                $toId => $toId,
                $clientTranId => $clientTranId,
            ];

            $data = $this->api->httpRequest("v1/broker/transfer", "POST", $params, true);
            return $this->api->sendResponse(200,true,'success',$data);
        } catch(\Exception $e) {
            return $this->api->sendResponse(500,false,$e->getMessage(),[]);
        }
    }

    /**
     * Query Sub Account Transfer History（SPOT)
     * If showAllStatus is true, the status in response will show four types: INIT,PROCESS,SUCCESS,FAILURE.
     * If showAllStatus is false, the status in response will show three types: INIT,PROCESS,SUCCESS.
     * Either fromId or toId must be sent. Return fromId equal master account by default.
     * Both startTime and endTime are provided: If it exceeds, the endTime will be re-calculated 100 days after the startTime.
     * Neither startTime nor endTime are provided: Calculate 100 days before today.
     * endTime is not provided: Calculate 100 days after startTime.
     * startTime is not provided: Calculate 100 days before endTime.

     *
     * $account = $api->subAccountTransferListSpot();
     *
     * @param STRING $fromId (optional)
     * @param STRING $toId (optional)
     * @param STRING $clientTranId (optional) client transfer id
     * @param ENUM $showAllStatus (optional) true or false, default: false
     * @param LONG $startTime (optional)
     * @param LONG $endTime (optional)
     * @param INT $page (optional)
     * @param INT $limit (optional)  default 100, max 500
     *
     * @return array with error message or array of all the information
     * @throws \Exception
     */
    public function subAccountTransferListSpot(
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
        try {
            $params = [
                "sapi" => true,
                $fromId => $fromId,
                $toId => $toId,
                $clientTranId => $clientTranId,
                $showAllStatus => $showAllStatus,
                $startTime => $startTime,
                $endTime => $endTime,
                $page => $page,
                $limit => $limit,
            ];

            $data = $this->api->httpRequest("v1/broker/transfer", "GET", $params, true);
            return $this->api->sendResponse(200,true,'success',$data);
        } catch(\Exception $e) {
            return $this->api->sendResponse(500,false,$e->getMessage(),[]);
        }
    }

    /**
     * Get Sub Account Deposit History
     * The query time period must be less than 7 days( default as the recent 7 days).

     *
     * $account = $api->subAccountTransferListSpot();
     *
     * @param STRING $subAccountId (optional)
     * @param STRING $coin (optional)
     * @param INT $status (optional) 0(0:pending,6: credited but cannot withdraw, 1:success)
     * @param LONG $startTime (optional)
     * @param LONG $endTime (optional)
     * @param INT $offset (optional) Default：0
     * @param INT $limit (optional)  default 100
     *
     * @return array with error message or array of all the information
     * @throws \Exception
     */
    public function subAccountDepositHistory(
        $subAccountId = null,
        $coin = null,
        $status = "",
        $startTime=null,
        $endTime=null,
        $offset=0,
        $limit=100,
        )
    {
        try {
            $params = [
                "sapi" => true,
                $subAccountId => $subAccountId,
                $coin => $coin,
                $status => $status,
                $startTime => $startTime,
                $endTime => $endTime,
                $offset => $offset,
                $limit => $limit,
            ];

            $data = $this->api->httpRequest("v1/broker/subAccount/depositHist", "GET", $params, true);
            return $this->api->sendResponse(200,true,'success',$data);
        } catch(\Exception $e) {
            return $this->api->sendResponse(500,false,$e->getMessage(),[]);
        }
    }

    /**
     * Query Sub Account Spot Asset info
     * If subaccountId is not sent, the size must be sent
     * Requests per UID are limited to 60 requests per minute
     *
     * $account = $api->subAccountAssetInfoSpot();
     *
     * @param STRING $subAccountId (optional)
     * @param LONG $page (optional) Default：1
     * @param LONG $size (optional)  default 10, max 20
     *
     * @return array with error message or array of all the information
     * @throws \Exception
     */
    public function subAccountAssetInfoSpot(
        $subAccountId = null,
        $page=1,
        $size=10,
        )
    {
        try {
            $params = [
                "sapi" => true,
                $subAccountId => $subAccountId,
                $page => $page,
                $size => $size,
            ];

            $data = $this->api->httpRequest("v1/broker/subAccount/spotSummary", "GET", $params, true);
            return $this->api->sendResponse(200,true,'success',$data);
        } catch(\Exception $e) {
            return $this->api->sendResponse(500,false,$e->getMessage(),[]);
        }
    }

    /**
     * Query Subaccount Margin Asset info
     * If subaccountId is not sent, the size must be sent
     *
     * $account = $api->subAccountAssetInfoMargin();
     *
     * @param STRING $subAccountId (optional)
     * @param LONG $page (optional) Default：1
     * @param LONG $size (optional)  default 10, max 20
     *
     * @return array with error message or array of all the information
     * @throws \Exception
     */
    public function subAccountAssetInfoMargin(
        $subAccountId = null,
        $page=1,
        $size=10,
        )
    {
        try {
            $params = [
                "sapi" => true,
                $subAccountId => $subAccountId,
                $page => $page,
                $size => $size,
            ];

            $data = $this->api->httpRequest("v1/broker/subAccount/marginSummary", "GET", $params, true);
            return $this->api->sendResponse(200,true,'success',$data);
        } catch(\Exception $e) {
            return $this->api->sendResponse(500,false,$e->getMessage(),[]);
        }
    }

    /**
     * Query Broker Commission Rebate Recent Record（Spot）
     * The query time period must be less than 7 days (default as the recent 7 days).
     *
     * $account = $api->brokerRebateRecentRecord();
     *
     * @param STRING $subAccountId (optional)
     * @param LONG $startTime (optional) Default: 7 days from current timestamp
     * @param LONG $endTime (optional) Default: present timestamp
     * @param LONG $page (optional) Default：1
     * @param LONG $size (optional)  default 100, max 500
     *
     * @return array with error message or array of all the information
     * @throws \Exception
     */
    public function brokerRebateRecentRecord(
        $subAccountId = null,
        $startTime = 7,
        $endTime = null,
        $page=1,
        $size=100,
        )
    {
        try {
            $params = [
                "sapi" => true,
                $subAccountId => $subAccountId,
                $startTime => $startTime,
                $endTime => $endTime,
                $page => $page,
                $size => $size,
            ];

            $data = $this->api->httpRequest("v1/broker/rebate/recentRecord", "GET", $params, true);
            return $this->api->sendResponse(200,true,'success',$data);
        } catch(\Exception $e) {
            return $this->api->sendResponse(500,false,$e->getMessage(),[]);
        }
    }

    /**
     * Get IP Restriction for Sub Account Api Key
     *
     * $account = $api->getIpRestrictionForSubAccount();
     *
     * @param STRING $subAccountId (mandetory)
     * @param STRING $subAccountApiKey (mandetory)
     *
     * @return array with error message or array of all the information
     * @throws \Exception
     */
    public function getIpRestrictionForSubAccount(
        $subAccountId,
        $subAccountApiKey
        )
    {
        try {
            $params = [
                "sapi" => true,
                $subAccountId => $subAccountId,
                $subAccountApiKey => $subAccountApiKey
            ];

            $data = $this->api->httpRequest("v1/broker/subAccountApi/ipRestriction", "GET", $params, true);
            return $this->api->sendResponse(200,true,'success',$data);
        } catch(\Exception $e) {
            return $this->api->sendResponse(500,false,$e->getMessage(),[]);
        }
    }

    /**
     * Delete IP Restriction for Sub Account Api Key
     *
     * $account = $api->deleteIpRestrictionForSubAccount();
     *
     * @param STRING $subAccountId (mandetory)
     * @param STRING $subAccountApiKey (mandetory)
     * @param STRING $ipAddress (mandetory)
     *
     * @return array with error message or array of all the information
     * @throws \Exception
     */
    public function deleteIpRestrictionForSubAccount(
        $subAccountId,
        $subAccountApiKey,
        $ipAddress
        )
    {
        try {
            $params = [
                "sapi" => true,
                $subAccountId => $subAccountId,
                $subAccountApiKey => $subAccountApiKey,
                $ipAddress => $ipAddress,
            ];

            $data = $this->api->httpRequest("v1/broker/subAccountApi/ipRestriction/ipList", "DELETE", $params, true);
            return $this->api->sendResponse(200,true,'success',$data);
        } catch(\Exception $e) {
            return $this->api->sendResponse(500,false,$e->getMessage(),[]);
        }
    }


    /**
     * Update IP Restriction for Sub-Account API key (For Master Account)
     *
     * $account = $api->deleteIpRestrictionForSubAccount();
     *
     * @param STRING $subAccountId (mandetory)
     * @param STRING $subAccountApiKey (mandetory)
     * @param STRING $status (mandetory)  IP Restriction status. 1 = IP Unrestricted. 2 = Restrict access to trusted IPs only.
     * @param STRING $ipAddress (optional) Insert static IP in batch, separated by commas.
     *
     * @return array with error message or array of all the information
     * @throws \Exception
     */
    public function updateIpRestrictionForSubAccount(
        $subAccountId,
        $subAccountApiKey,
        $status,
        $ipAddress
        )
    {
        try {
            $params = [
                "sapi" => true,
                $subAccountId => $subAccountId,
                $subAccountApiKey => $subAccountApiKey,
                $status => $status,
                $ipAddress => $ipAddress,
            ];

            $data = $this->api->httpRequest("v1/broker/subAccountApi/ipRestriction", "POST", $params, true);
            return $this->api->sendResponse(200,true,'success',$data);
        } catch(\Exception $e) {
            return $this->api->sendResponse(500,false,$e->getMessage(),[]);
        }
    }
}
