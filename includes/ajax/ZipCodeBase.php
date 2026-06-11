<?php
namespace CI_Lib\Ajax;
class ZipCodeBase {
    private $apiKey;
    private $country;
    function __construct() {
        $this->apiKey = "5cd070c0-0d96-11ef-a5aa-913d52ff739b";
        $this->country = "US";
    }
    function getAddress($data)
    {
        $zip = $data['zip'];
        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.zippopotam.us/us/$zip",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($response, 1);
        $response['code'] = $zip;
        return $response;
    }
    function parseResponse($response)
    {
        $res = array();
        if(isset($response[0]['zipcodes'][0]) && !empty($response[0]['zipcodes'][0]))
        {
            $res['success'] = true;
            $data = $response[0]['zipcodes'][0];
            $res['data'] = $data['default_city'] . ", ". $data['state'];
        }
        else
        {
            $res['success'] = false;
        }
        return $res;
    }
    function parseResponseZipCodeBase($response)
    {
        $res = array();
        if (isset($response['places']) && !empty($response['places'])) {
            $place = $response['places'][0];
            $res['success'] = true;
            $res['data'] = $place['place name'] . ", " . $place['state abbreviation'];
        } else {
            $res['success'] = false;
        }
        return $res;
    }
    function getZipDetails($data)
    {
        $response = $this->getAddressSmarty($data);
        if(isset($response[0]['zipcodes'][0]) && !empty($response[0]['zipcodes'][0]))
        {
            $data = $response[0]['zipcodes'][0];
            return $data;
        }
        else
        {
            return false;
        }
    }
    function getAddressSmarty($data)
    {
        $zip = $data['zip'];
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://us-zipcode.api.smarty.com/lookup?zipcode=$zip&auth-id=220f39ad-dcf2-94c6-ce2d-4abaf7fa79b3&auth-token=bT1SgGPtDD0Yb7mkSb4x",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));
        $response = curl_exec($curl);
        $data = json_decode($response, true);
        curl_close($curl);
        return $data;
    }
}