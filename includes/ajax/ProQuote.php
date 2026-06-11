<?php

namespace CI_Lib\Ajax;
class ProQuote {
    private $auth_token;
    private $access_token_url;
    private $proquote_url;
    private $client_id;
    private $secret;
    private $company_code;
    private $assignment_url;
    function __construct() {
        $this->client_id = 'b2b-importautohaus';
        $this->secret = 'b33591f6be474d958d94e451c0af6fe1';
        $this->access_token_url = "https://b2b.copart.com/employee/oauth/token?grant_type=client_credentials";
        $this->proquote_url = "https://b2b.copart.com/v1/proquote?sellerCompanyCode=QXR6";
        $this->company_code = "QXR6";
        $this->assignment_url = "https://b2b.copart.com/usaps/v2/assignment";
    }
    function generateToken()
    {
        $combined = $this->client_id . ":" . $this->secret;
        $this->auth_token = base64_encode($combined);;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->access_token_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        $headers = array();
        $headers[] = 'Authorization: Basic ' . $this->auth_token;
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        $result = json_decode($result,1);
        if(isset($result['access_token']) && !empty($result['access_token']))
        {
            session_start();
            $_SESSION['token_expiration_time'] = time() + $result['expires_in'];
            $_SESSION['copart_access_token'] = $result['access_token'];
            return $_SESSION['copart_access_token'];
        }
        else
        {
            wp_send_json_error('Invalid request.');
            exit;
        }
    }
    function getToken()
    {
        session_start();
        if((isset($_SESSION['copart_access_token']) && !empty($_SESSION['copart_access_token'])))
        {
            $tokenExpirationTime = $_SESSION['token_expiration_time'];
            if ($tokenExpirationTime <= time()) {
               $token = $this->generateToken();
            }
            else
            {
                $token = $_SESSION['copart_access_token'];
            }
        }
        else
        {
            $token = $this->generateToken();
        }
        return $token;
    }
    function generateRandomString($length = 32) {
        // Define characters that can be used in the random string
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        $maxIndex = strlen($characters) - 1;
    
        for ($i = 0; $i < $length; $i++) {
            // Insert hyphens at the specified positions
            if ($i == 8 || $i == 13 || $i == 18 || $i == 23) {
                $randomString .= '-';
            } else {
                // Choose a random character from the defined set
                $randomString .= $characters[rand(0, $maxIndex)];
            }
        }
    
        return $randomString;
    }

    function generateRequest($data)
    {
        $req_json = array();
        if (isset($data) && !empty($data)) {
            $req_json['transactionId'] = time();
            $req_json['claimNumber'] = $req_json['transactionId'];
            $req_json['adminInfo'] = array();
            $req_json['adminInfo']['sellerCompanyCode'] = $this->company_code;
            $req_json['adminInfo']['vendorCode'] = "";
            $req_json['adminInfo']['officeCode'] = $this->company_code;
            $req_json['vehicleLocationSite'] = array();
            $req_json['vehicleLocationSite']['locationName'] = "Joe's body shop";
            $req_json['vehicleLocationSite']['address'] = array();
            $req_json['vehicleLocationSite']['address']['contact'] = array();
            $req_json['lossInfo'] = array();
            $req_json['vehicleInformation'] = array();
            $req_json['vehicleInformation']['odometerInfo'] = array();
            $req_json['vehicleCondition'] = array();
            $req_json['valuation'] = array();
            $req_json['valuation']['repairCost'] = 0;
            if(isset($data['repair_cost']) && !empty($data['repair_cost']))
            {
                $req_json['valuation']['repairCost'] = $data['repair_cost'];
            }
            foreach ($data as $key => $value) {
                switch ($key) {
                    case 'zip':
                        $req_json['vehicleLocationSite']['address']['contact']['postalCode'] = $value;                 
                        break;
                    case 'loc':
                            $req_json['vehicleLocationSite']['locationName'] = $value;                 
                            break;
                    case 'phone':
                            $req_json['vehicleLocationSite']['phone'] = $value;                 
                            break;
                    case 'damage_location':
                        $req_json['lossInfo']['primaryPointOfImpact'] = $value;
                        break;
                    case 'year':
                        $req_json['vehicleInformation']['year'] = $value;
                        break;
                    case 'make':
                        $req_json['vehicleInformation']['makeCode'] = $value;
                        $req_json['vehicleInformation']['makeDescription'] = $value;
                        break;
                    case 'model':
                        $req_json['vehicleInformation']['model'] = $value;
                        break;
                    case 'vehicle_type':
                        $req_json['vehicleInformation']['vehicleType'] = $value;
                        break;
                    case 'mileage':
                        $req_json['vehicleInformation']['odometerInfo']['odometerReading'] = $value;
                        $req_json['vehicleInformation']['odometerInfo']['odometerBrand'] = "Actual";
                        break;
                    case 'keys':
                        $req_json['vehicleInformation']['hasKeys'] = $value;
                        break;
                    case 'acv':
                        $req_json['valuation']['acv'] = $value;
                        break;
                    case 'drivability_rating':
                        $req_json['vehicleCondition']['drivabilityRating'] = $value;
                        break;
                    case 'sale_title_type':
                        $req_json['vehicleCondition']['titleCategory'] = $value;
                        break;
                    case 'drivable':
                        $req_json['vehicleCondition']['drivable'] = $value;
                        break;
                    case 'start':
                        $req_json['vehicleCondition']['drivabilityRating'] = $value;
                        break;
                    case 'title':
                        $req_json['vehicleCondition']['titleCategory'] = $value;
                        break;
                    case 'damage_type':
                        $req_json['lossInfo']['lossType'] = $value;
                        break;
                    case 'repair_cost':
                        $req_json['valuation']['repairCost'] = $value;
                        break;
                    case 'damage':
                        if ( ! isset( $data['damage_location'] ) || empty( $data['damage_location'] ) ) {
                            $req_json['lossInfo']['primaryPointOfImpact'] = "UK";
                        }
                        $req_json['lossInfo']['lossType'] = "N";
                        break;
                    // Add more cases as needed for other fields
                    default:
                        break;
                }
            }
        }
        error_log('GENERATED REQUEST: ' . json_encode($req_json));
        return $req_json;
    }
    function makePostRequest($token, $data, $assignment = false)
    {
        if($assignment)
        {
            $url = $this->assignment_url;
        }
        else
        {
            $url = $this->proquote_url;
        }
        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS =>$data,
        CURLOPT_HTTPHEADER => array(
            'countryCode: USA',
            'insco: vrom',
            "Authorization: bearer $token",
            'Content-Type: application/json',
        ),
        ));
        $response = curl_exec($curl);
        $http_status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        $response = json_decode($response,1);
        // print_r($response);
        error_log('RAW RESPONSE: ' . json_encode($response));
        $res = array();
        $res['status_code'] = $http_status_code;
        $res['data'] = $response;
        return $res;
    }
    function makePutRequest($token, $data)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => $this->assignment_url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'PUT',
        CURLOPT_POSTFIELDS =>$data,
        CURLOPT_HTTPHEADER => array(
            'countryCode: USA',
            'insco: vrom',
            "Authorization: bearer $token",
            'Content-Type: application/json',
        ),
        ));
        $response = curl_exec($curl);
        $http_status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        $response = json_decode($response,1);
        $res = array();
        $res['status_code'] = $http_status_code;
        $res['data'] = $response;
        return $res;
    }
    function parseCopartResponse($response)
    {
        $res = array();
        if($response['status_code'] == 200)
        {
            $res['success'] = true;
            if(isset($response['data']['proQuote']))
            {
                $res['quote'] = $response['data']['proQuote'];
            }
            else
            {
                $res['success'] = false;
            }
        }
        else
        {
            $res['success'] = false;
        }
        return $res;
    }
    function generateAssignmentRequest($data)
    {
        // Initialize an empty array to store the mapped data
        $mappedData = [];
        // Map array of objects to the desired JSON structure
        foreach ($data as $item) {
            if(isset($item['name']))
            {
            switch ($item['name']) {
                case 'modelYear':
                    $mappedData['vehicleDetails']['modelYear'] = intval($item['value']);
                    break;
                case 'vehicleType':
                    $mappedData['vehicleDetails']['vehicleType'] = $item['value'];
                    break;
                case 'makeDescription':
                    $mappedData['vehicleDetails']['make'] = $item['value'];
                    $mappedData['vehicleDetails']['makeDescription'] = $item['value'];
                    break;
                case 'modelDescription':
                    $mappedData['vehicleDetails']['modelDescription'] = $item['value'];
                    $mappedData['vehicleDetails']['modelName'] = $item['value'];
                    break;
                case 'odometerBrand':
                    $mappedData['vehicleDetails']['odometerBrand'] = $item['value'];
                    break;
                case 'isTrailerAttached':
                    $mappedData['vehicleDetails']['isTrailerAttached'] = $item['value'];
                    break;
                // case 'causeOfLoss':
                //     $mappedData['assignmentDetails']['causeOfLoss'] = $item['value'];
                //     break;
                // case 'primaryDamage':
                //     $mappedData['assignmentDetails']['primaryDamage'] = $item['value'];
                //     break;
                // case 'insured':
                //     $mappedData['assignmentDetails']['pickupRequired'] = "Y";
                //     break;
                // case 'claim':
                //     $mappedData['assignmentDetails']['claimNumber'] = $item['value'];
                //     break;
                case 'addressLine1':
                    $mappedData['vehicleLocation']['address']['addressLine1'] = $item['value'];
                    break;
                case 'city':
                    $mappedData['vehicleLocation']['address']['city'] = $item['value'];
                    break;
                case 'state':
                    $mappedData['vehicleLocation']['address']['state'] = $item['value'];
                    break;
                case 'zipcode':
                    $mappedData['vehicleLocation']['address']['zipcode'] = $item['value'];
                    break;
                case 'country':
                    $mappedData['vehicleLocation']['address']['country'] = $item['value'];
                    break;
                case 'name':
                    $mappedData['vehicleLocation']['type']= "B";
                    $mappedData['vehicleLocation']['name']= $item['value'];
                    break;
                case 'phone':
                    $mappedData['vehicleLocation']['telephone']['type']= "Home";
                    $mappedData['vehicleLocation']['telephone']['countryCode']= "+1";
                    $mappedData['vehicleLocation']['telephone']['number']= $item['value'];
                    break;
                case 'dates':
                    $dates  = $item['value'];
                    $datesArray = explode(',', $dates);
                    $requestedDates = array();
                    if(count($datesArray) > 0)
                    {
                        foreach($datesArray as $date)
                        {
                            $d = array();
                            $d['type'] = 'PickUpDate';
                            $d['date'] = $date;
                            $requestedDates[] = $d;
                        }
                        if(!empty($requestedDates))
                        {
                            $mappedData['assignmentDetails']['dates'] = $requestedDates;
                        }
                    }
                    $mappedData['assignmentDetails']['causeOfLoss'] = "N";
                    $mappedData['assignmentDetails']['primaryDamage'] = "UK";
                    $mappedData['assignmentDetails']['pickupRequired'] = "Y";
                    $mappedData['assignmentDetails']['claimNumber'] = "QXR6";
                    break;
            }
            }
        }

        // Hardcoded values for the remaining fields
        $mappedData['transactionID'] = time();
        $mappedData['currencyCode'] = "USA";
        $mappedData['companyCode'] = $this->company_code;
        $mappedData['assignmentType'] = "V";
        $mappedData['sellerCode'] = $this->company_code;
        $mappedData['sellerTransactionID'] = time();
        $mappedData['stockNumber'] = 0;
        $mappedData['cancellationReason'] = "";
        // Hardcoded "adjusterDetails" section
        $mappedData['adjusterDetails'] = [
            [
                'role' => 'Assignment',
                'adjuster' => [
                    'employee' => [
                        'name' => [
                            'firstName' => 'Robert',
                            'lastName' => 'Marquez'
                        ],
                        'telephone' => [
                            'type' => 'Home',
                            'countryCode' => '+1',
                            'number' => '561-436-4568'
                        ]
                    ]
                ]
            ]
        ];
        return $mappedData;
    }
    function parseAssignmentResponse($response)
    {
        $isValid = false;
        if(isset($response['statusCode']) && $response['statusCode'] == "ACCEPT")
        {
            $isValid = true;
        }
        else
        {
            $isValid = false;
        }
        return $isValid;
    }
}