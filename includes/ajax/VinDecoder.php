<?php

namespace CI_Lib\Ajax;
class VinDecoder {
    function __construct() {
    }
    function decodeVin($data)
    {
        $res = array();
        if(empty($data['vin']))
        {
            $res['success'] = false;
            return $res;
        }
        else
        {
            $apiPrefix = "https://api.vindecoder.eu/3.2";
            $apiKey = "b58f949ec488";   // Your API key
            $secretKey = "64ab8203a8";  // Your secret key
            $id = "decode";
            $vin = mb_strtoupper($data['vin']);
            $controlSum = substr(sha1("$vin|$id|$apiKey|$secretKey"), 0, 10);
            $url = "{$apiPrefix}/{$apiKey}/{$controlSum}/decode/{$vin}.json";
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            if (curl_errno($ch)) {
                echo 'cURL Error: ' . curl_error($ch);
            }
            curl_close($ch);
            $response = json_decode($response,1);
            // Initialize an array to store Make, Model, and Model Year
            if(isset($response['decode']) && !empty($response['decode']))
            {
                $vehicleInfo = [];
                // Access the "decode" values and extract Make, Model, and Model Year
                foreach ($response['decode'] as $decode) {
                    switch ($decode['label']) {
                        case 'Make':
                            $vehicleInfo['make'] = $decode['value'];
                            break;
                        case 'Model':
                            $vehicleInfo['model'] = $decode['value'];
                            break;
                        case 'Model Year':
                            $vehicleInfo['year'] = $decode['value'];
                            break;
                    }
                }
                $vehicleInfo['vehicle_type'] = "V";
                $vehicleInfo['success'] = true;
                return $vehicleInfo;
            }
            else
            {
                $res['success'] = false;
                return $res;
            }
        }
    }
}
