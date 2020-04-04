<?php

namespace App\CashFree;

use Illuminate\Support\Facades\Log;

/**
 * Undocumented class
 * 
 */
class CashFreeAPI
{
    private static $_CLIENTID = "CF3883E223DZFWU94Y2IM"; //"3883d7088cd8513fec84a62e3883"; //"3883d7088cd8513fec84a62e3883";//"CF395CAVV2FR1ZV82E2U";
    private static $_CLIENTSECRETID = "d6a53cb7fd6a696da02453b464a5a758b0e47d8a"; //"583cbd5816953d87edd1bb2df42431a65a17de14"; //"583cbd5816953d87edd1bb2df42431a65a17de14";//"9a18e3e38efd56a89ea3a8fa81a7bce0453db29c";
    private static $_url = "https://ces-gamma.cashfree.com"; //"https://ces-gamma.cashfree.com"; //"https://test.cashfree.com";//"https://test.cashfree.com";
    private $_returnURL = "http://127.0.0.1:8000/order/payment";
    private $_notifyURL = "http://127.0.0.1:8000/order/payment";
    private static $_isToken = "Use Token";
    private static $_token;
    private static $_isXKeys = "Use Keys";

    function __construct()
    { }

    /**
     * @param $endpoint
     * @param array $params
     * @return object
     */
    private static function makeRequest($method = 'GET', $endpoint, $toUse, $params = array())
    {
        //$apiEndpoint = "https://test.cashfree.com";
        $opUrl = self::$_url . $endpoint;
        //dd(self::$_isXKeys);

        // $params['appId'] = "$this->_CLIENTID";
        // $params['secretKey'] = "$this->_CLIENTSECRETID";

        $timeout = 60;

        /*$request_string = "";
        foreach($params as $key=>$value) {
            $request_string .= $key.'='.rawurlencode($value).'&';
        }*/

        //echo $opUrl.$request_string;

        $payload = json_encode($params);

        $ch = curl_init();
        if ($toUse == self::$_isXKeys) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Cache-Control: no-cache', 'Content-Type: application/json', 'X-Client-Id: ' . self::$_CLIENTID, 'X-Client-Secret: ' . self::$_CLIENTSECRETID));
        } else if ($toUse == self::$_isToken) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Cache-Control: no-cache', 'Content-Type: application/json', 'Content-Length: ' . strlen($payload), 'Authorization: Bearer ' . self::$_token));
        }

        curl_setopt($ch, CURLOPT_URL, "$opUrl?");

        if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, count($params));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        $curl_result = curl_exec($ch);
        Log::error("CURL Error: " . curl_error($ch));
        curl_close($ch);

        return json_decode($curl_result);
    }

    // public function authenticateKeys()
    // {
    //     $result = $this->makeRequest("/ces/v1/authorize", $this->_isXKeys);
    //     if ($result->status == "SUCCESS") {
    //         $this->_token = $result->data->token;
    //     }
    //     //echo json_encode($result);
    //     //return $this->_token;
    // }

    public static function authenticateKeys()
    {
        $result = self::makeRequest('POST', "/ces/v1/authorize", self::$_isXKeys);
        Log::info(print_r($result, true));
        if ($result->status == "SUCCESS") {
            self::$_token = $result->data->token;
        }
    }

    public static function retreive($vendorId)
    {
        $result = self::makeRequest('GET', "/ces/v1/getVendor/$vendorId", self::$_isToken);
        if ($result == null)
            return null;
        if ($result->status == "SUCCESS" && $result->message == "Vendor Details") {
            return $result->data;
        } else {
            return null;
        }
    }

    public function verifyToken()
    {
        $result = $this->makeRequest("/ces/v1/verifyToken", $this->_isToken);
        //{"status":"SUCCESS","subCode":"200","message":"Token is valid"}
        if ($result->status == "SUCCESS" && $result->message == "Token is valid") {
            return true;
        }
        return false;
    }

    public function addVendor($vendor)
    {
        //prepare params from vendor
        $params = array(
            'vendorId' => $vendor['vID'],
            'name' => $vendor['name'],
            'phone' => $vendor['phone'],
            'email' => $vendor['email'],
            'commission' => $vendor['commission'],
            'bankAccount' => $vendor['bankAccount'],
            'accountHolder' => $vendor['accountHolder'],
            'ifsc' => $vendor['ifsc'],
            'address1' => $vendor['address1'],
            'address2' => $vendor['address2'],
            'city' => $vendor['city'],
            'state' => $vendor['state'],
            'pincode' => $vendor['pincode'],
        );
        //$params = array('request'=>json_encode($param));
        $result = $this->makeRequest("/ces/v1/addVendor", $this->_isToken, $params);
        return json_encode($result);
    }
}

// $api = new CashFreeAPI();
// $api->authenticateKeys();
// if($api->verifyToken()) {
//     echo "Yes. You have access to the api now.<p>";
//     echo $api->addVendor(array('vID'=>'VEN_TEST001', 'name'=>'Test One', 'email'=>'johndoe_1@example.com', 'phone'=>919023927432, 'bankAccount'=>667940560596, 'accountHolder'=>'Binoya Thakur', 'ifsc'=>'ICIC0000041', 'address1'=>'25, Ratan Society', 'address2'=>'Bandra Warangal', 'city'=>'Warangal', 'state'=>'Maharashtra', 'pincode'=>560071));
// }
