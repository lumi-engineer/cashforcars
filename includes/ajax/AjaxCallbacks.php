<?php
 
namespace CI_Lib\Ajax;
class AjaxCallbacks {
    private $wpdb;
    private $years_table ;
    private $makes_table;
    private $models_table;
    private $types_table_name;
    private $types_data_table_name;
    private $request_response_log_table;
    private $users_table;
    private $quotes_table;
    private $quotes_data_table;
    function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->years_table = $this->wpdb->prefix . 'df_ci_years';
        $this->makes_table = $this->wpdb->prefix . 'df_ci_makes';
        $this->models_table = $this->wpdb->prefix . 'df_ci_models';
        $this->types_table_name = $this->wpdb->prefix . 'df_ci_types';
        $this->types_data_table_name = $this->wpdb->prefix . 'df_ci_types_data';
        $this->request_response_log_table = $this->wpdb->prefix . 'df_ci_request_response_log';
        $this->users_table = $this->wpdb->prefix . 'df_ci_users';
        $this->quotes_table = $this->wpdb->prefix  . 'df_ci_quotes';
        $this->quotes_data_table = $this->wpdb->prefix . 'df_ci_quote_data';
    }
 
    // AJAX handler function 
    function getYears() {
        $table_name_years = $this->years_table;
        $years = $this->wpdb->get_col("SELECT year
        FROM $table_name_years where year < 2026
        ORDER BY year DESC;
        ");
        $response = $this->getResponse($years);
        return $response;
    }
 
    // AJAX handler function to get models for selected make
    function getMakeModels() {
        $table_name_makes = $this->makes_table;
        $table_name_models = $this->models_table;
        $selected_make_name = sanitize_text_field($_POST['make']);
        $selected_year = sanitize_text_field($_POST['year']);
        $selected_make_id = $this->wpdb->get_var(
            $this->wpdb->prepare( "SELECT id FROM $table_name_makes WHERE make = %s", $selected_make_name )
        );
        if (!$selected_make_id) {
            wp_send_json_error('Invalid make.');
            exit;
        }
        $models = $this->wpdb->get_col(
            $this->wpdb->prepare("SELECT model FROM $table_name_models WHERE make_id = %d and year = %s Group By model", $selected_make_id,$selected_year)
        );
        $response = $this->getResponse($models);
        return $response;
    }
 
    // AJAX handler function to get makes
    function getMakes() { 
        $table_name_makes = $this->makes_table;
        $query = "SELECT * FROM $table_name_makes";
        $results = $this->wpdb->get_results($query);
        $makes = array();
        foreach ($results as $row) {
            $make = array();
            $make['key'] = $row->make;
            $make['value'] = $row->description;
            $makes[] = $make;
        }
        $json_data = array(
            'data' => $makes
        );
        return wp_send_json_success($json_data);
    }
 
    // AJAX handler function to get quote (triggered when user submits the wizard — not automatic)
    function getQuote() {
        if ( ! wp_verify_nonce( $_POST['nonce'], 'ci-data' ) ) {
            wp_send_json_error( 'Invalid nonce.' );
            exit;
        }

        $posted_data = $this->parsePostedQuoteData();
        if ( empty( $posted_data ) || ! is_array( $posted_data ) ) {
            wp_send_json_error( array( 'message' => 'Invalid quote data.' ) );
            exit;
        }

        $required = array( 'year', 'make', 'model', 'vehicle_type', 'drivability_rating', 'email' );
        foreach ( $required as $field ) {
            if ( empty( $posted_data[ $field ] ) ) {
                wp_send_json_error( array( 'message' => 'One or more required fields are empty: ' . $field ) );
                exit;
            }
        }

        if ( empty( $_FILES['vehicle_photos']['name'] ) ||
            ( is_array( $_FILES['vehicle_photos']['name'] ) && empty( array_filter( $_FILES['vehicle_photos']['name'] ) ) ) ) {
            wp_send_json_error( array( 'message' => 'Please upload at least one vehicle photo.' ) );
            exit;
        }

        if ( empty( $_FILES['title_image']['name'] ) ) {
            wp_send_json_error( array( 'message' => 'Please upload a title image.' ) );
            exit;
        }

        $pro_quote = new ProQuote();
        $token     = $pro_quote->getToken();
        $req_json  = $pro_quote->generateRequest( $posted_data );
        $quote_id  = $this->insertQuotesData(
            $req_json['transactionId'],
            $req_json['claimNumber'],
            sanitize_email( $posted_data['email'] ),
            json_encode( $req_json )
        );

        $vehicle_images = $this->uploadFiles( 'vehicle_photos' );
        $title_images   = $this->uploadFiles( 'title_image', false );

        if ( ! empty( $vehicle_images ) || ! empty( $title_images ) ) {
            $this->wpdb->update(
                $this->quotes_table,
                array(
                    'images'        => json_encode( $vehicle_images ),
                    'car_images'    => json_encode( $vehicle_images ),
                    'title_images'  => json_encode( $title_images ),
                    'images_status' => CI_Quote_Status::IMAGES_PENDING,
                    'title_review'  => CI_Quote_Status::REVIEW_PENDING,
                    'car_review'    => CI_Quote_Status::REVIEW_PENDING,
                    'status'        => CI_Quote_Status::OFFERED,
                ),
                array( 'id' => $quote_id ),
                array( '%s', '%s', '%s', '%s', '%s', '%s', '%d' ),
                array( '%d' )
            );
        }

        $response = $pro_quote->makePostRequest( $token, json_encode( $req_json ), false );
        error_log( 'COPART FULL RESPONSE: ' . json_encode( $response ) );
        $response['qid'] = $quote_id;

        if ( $this->insertQuotesDataTableFromJSON( $response ) ) {
            $quote   = $response['data'];
            $vehicle = $posted_data['year'] . '-' . $posted_data['make'] . '-' . $posted_data['model'];
            $address = isset( $req_json['vehicleLocationSite']['locationName'] )
                ? $req_json['vehicleLocationSite']['locationName'] . ', ' . $req_json['vehicleLocationSite']['address']['contact']['postalCode']
                : '';
            $phone = isset( $req_json['vehicleLocationSite']['phone'] ) ? $req_json['vehicleLocationSite']['phone'] : '';
            $this->sendQuoteEmail( sanitize_email( $posted_data['email'] ), $vehicle, $quote['proQuote'] );
            $this->sendQuoteEmail( sanitize_email( $posted_data['email'] ), $vehicle, $quote['proQuote'], true, $address, $phone );
            $response['proQuote'] = $quote['proQuote'];
            return wp_send_json_success( $response );
        }

        error_log( 'INSERT FAILED - Response data: ' . json_encode( $response ) );
        return wp_send_json_error( $response );
    }

    private function parsePostedQuoteData() {
        if ( isset( $_POST['data'] ) && is_array( $_POST['data'] ) ) {
            return $_POST['data'];
        }
        if ( isset( $_POST['data'] ) && is_string( $_POST['data'] ) ) {
            $decoded = json_decode( wp_unslash( $_POST['data'] ), true );
            return is_array( $decoded ) ? $decoded : array();
        }

        $skip_fields = array( 'action', 'nonce' );
        $posted_data = array();

        foreach ( $_POST as $key => $value ) {
            if ( in_array( $key, $skip_fields, true ) || is_array( $value ) ) {
                continue;
            }
            $posted_data[ $key ] = sanitize_text_field( wp_unslash( $value ) );
        }

        return $posted_data;
    }

    private function uploadFiles( $field_name, $allow_multiple = true ) {
        if ( empty( $_FILES[ $field_name ]['name'] ) ) {
            return array();
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';

        $uploaded_files = array();
        $files          = $_FILES[ $field_name ];
        $names          = is_array( $files['name'] ) ? $files['name'] : array( $files['name'] );

        foreach ( $names as $key => $image_name ) {
            if ( empty( $image_name ) ) {
                continue;
            }

            $file = array(
                'name'     => is_array( $files['name'] ) ? $files['name'][ $key ] : $files['name'],
                'type'     => is_array( $files['type'] ) ? $files['type'][ $key ] : $files['type'],
                'tmp_name' => is_array( $files['tmp_name'] ) ? $files['tmp_name'][ $key ] : $files['tmp_name'],
                'error'    => is_array( $files['error'] ) ? $files['error'][ $key ] : $files['error'],
                'size'     => is_array( $files['size'] ) ? $files['size'][ $key ] : $files['size'],
            );

            if ( $file['error'] !== UPLOAD_ERR_OK ) {
                continue;
            }

            $image_type = wp_check_filetype( $file['name'] );
            if ( ! $image_type['type'] || strpos( $image_type['type'], 'image/' ) !== 0 ) {
                continue;
            }

            $max_file_size = 10 * 1024 * 1024;
            if ( $file['size'] > $max_file_size ) {
                continue;
            }

            $uploaded_file = wp_handle_upload( $file, array( 'test_form' => false ) );
            if ( isset( $uploaded_file['url'] ) ) {
                $uploaded_files[] = ci_store_image_url( $uploaded_file['url'] );
            }

            if ( ! $allow_multiple ) {
                break;
            }
        }

        return $uploaded_files;
    }
 
    function getTypeData()
    {
        $type    = sanitize_text_field( $_POST['action'] );
        $type_id = $this->getTypeIdByValue( $type );
        if ( ! $type_id ) {
            wp_send_json_error( 'Invalid type.' );
            exit;
        }
        $data = $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT code, name FROM $this->types_data_table_name WHERE type_id = %d ORDER BY name ASC",
                $type_id
            ),
            ARRAY_A
        );
        return $this->getResponseDatabase( $data );
    }
 
    function getResponseDatabase($data)
    {
        $response = array();
        foreach ($data as $row) {
            $arr = array();
            $arr['key'] = $row['code'];
            $arr['value'] = $row['name'];
            $response[] =  $arr;
        }
        $json_data = array(
            'data' => $response
        );
        return wp_send_json_success($json_data);
    }
 
    function getResponse($data)
    {
        $response = array();
        foreach ($data as $value) {
            $arr = array();
            $arr['key'] = $value;
            $arr['value'] = $value;
            $response[] =  $arr;
        }
        $json_data = array(
            'data' => $response
        );
        return wp_send_json_success($json_data);
    }
 
    function getTypeIdByValue($type_value) {
        $type_id = $this->wpdb->get_var(
            $this->wpdb->prepare(
                "SELECT id FROM $this->types_table_name WHERE type_name = %s",
                $type_value
            )
            );
        return $type_id;
    }
 
    function logRequest($request, $response)
    {
        $data = array(
            'timestamp' => current_time('mysql'),
            'request_proquote_payload' => json_encode($request),
            'response_proquote_payload' => json_encode($response),
        );
        $this->wpdb->insert($this->request_response_log_table, $data);
    }
 
    function getZipDetails()
    {
        if ( ! wp_verify_nonce( $_POST['nonce'], 'ci-data' ) ) {
            wp_send_json_error( 'Invalid nonce.' );
            exit;
        }
        $zipcode_base = new ZipCodeBase();
        $details = $zipcode_base->getAddress($_POST);
        $json_data = array(
            'data' => $zipcode_base->parseResponseZipCodeBase($details)
        );
        return wp_send_json_success($json_data);
    }
 
    function decodeVin()
    {
        if ( ! wp_verify_nonce( $_POST['nonce'], 'ci-data' ) ) {
            wp_send_json_error( 'Invalid nonce.' );
            exit;
        }
        $vin_decoder = new VinDecoder();
        $response = $vin_decoder->decodeVin($_POST);
        return wp_send_json_success($response);
    }
 
    function register()
    {
        if ( ! wp_verify_nonce( $_POST['nonce'], 'ci-register' ) ) {
            wp_send_json_error( 'Invalid nonce.' );
            exit;
        }
        $data = $_POST;
        if((isset($data['email']) && !empty($data['email'])) && (isset($data['password']) && !empty($data['password'])))
        {
            $name = sanitize_text_field($data['name']);
            $email = sanitize_email($data['email']);
            $password = password_hash($data['password'], PASSWORD_BCRYPT);
            $user_exists = $this->wpdb->get_var(
                $this->wpdb ->prepare("SELECT COUNT(*) FROM $this->users_table WHERE email = %s", $email)
            );
    
            if ($user_exists > 0) {
                wp_send_json_error( 'This email address is already registered. Please use a different email' );
            } else {
                $this->wpdb->insert(
                    $this->users_table,
                    array(
                        'name' => $name,
                        'email' => $email,
                        'password' => $password
                    )
                );
                session_start();
                $_SESSION['ci_name'] = $name;
                $_SESSION['ci_email'] = $email;
                return wp_send_json_success('Registered Successfully');
            }
        }
        else
        {
            wp_send_json_error( 'Invalid Data.' );
        }
    }
 
    function login()
    {
        if ( ! wp_verify_nonce( $_POST['nonce'], 'ci-login' ) ) {
            wp_send_json_error( 'Invalid nonce.' );
            exit;
        }
        $data = $_POST;
        if((isset($data['email']) && !empty($data['email'])) && (isset($data['password']) && !empty($data['password'])))
        {
            $email = sanitize_text_field($_POST['email']);
            $password = sanitize_text_field($_POST['password']);
            $user = $this->wpdb->get_row(
                $this->wpdb->prepare("SELECT * FROM $this->users_table WHERE email = %s", $email)
            );
            if ($user && password_verify($password, $user->password)) {
                session_start();
                $_SESSION['ci_name'] = $user->name;
                $_SESSION['ci_email'] = $user->email;
                return wp_send_json_success('Login Successfully');
            } else {
                wp_send_json_error( 'Please check credentials.' );
            }
        }
        else
        {
            wp_send_json_error( 'Invalid Data.' );
        }
    }
 
    function insertQuotesData($transaction_id, $claim_number, $email, $data) {
        $this->wpdb->insert(
            $this->quotes_table,
            array(
                'transaction_id' => $transaction_id,
                'claim_number'   => $claim_number,
                'email'          => $email,
                'data_proquote'  => $data,
            )
        );
        $inserted_id = $this->wpdb->insert_id;
        return $inserted_id;
    }
 
    function insertQuotesDataTableFromJSON($response) {
        if (empty($response)) {
            return false;
        }
        $quoteData = $response['data'];
        $quoteData['qid'] = $response['qid'];
        $required_fields = array('qid','quoteId', 'statusCode', 'highQuote', 'lowQuote', 'proQuote', 'numberOfLots');
        foreach ($required_fields as $field) {
            if (!isset($quoteData[$field])) {
                error_log('MISSING FIELD: ' . $field . ' in response: ' . json_encode($quoteData));
                return false;
            }
        }
        $this->wpdb->insert(
            $this->quotes_data_table,
            array(
                'qid'            => $quoteData['qid'],
                'quote_id'       => $quoteData['quoteId'],
                'status_code'    => $quoteData['statusCode'],
                'high_quote'     => $quoteData['highQuote'],
                'low_quote'      => $quoteData['lowQuote'],
                'pro_quote'      => $quoteData['proQuote'],
                'number_of_lots' => $quoteData['numberOfLots'],
            )
        );
        return true;
    }
 
    function createAssignment()
    {
        if ( ! wp_verify_nonce( $_POST['nonce'], 'ci-data' ) ) {
            wp_send_json_error( 'Invalid nonce.' );
            exit;
        }
        $posted_data = $_POST['data'];
        $otherArray = array();
        foreach ($posted_data as $item) {
            $otherArray[$item['name']] = $item['value'];
        }
        if((isset($otherArray['offer']) && !empty($otherArray['offer'])))
        {
            $query = $this->wpdb->prepare("SELECT * FROM $this->quotes_table WHERE id = %d", $otherArray['offer']);
            $result = $this->wpdb->get_row($query);
            if ($result) {
                if ( ! CI_Quote_Status::can_accept_offer( $result ) ) {
                    wp_send_json_error( 'Images must be approved by admin before you can accept this offer.' );
                    exit;
                }
                $result = (array) $result;
                $posted_data['transactionID'] = $result['transaction_id'];
                $pro_quote = new ProQuote();
                $request = $pro_quote->generateAssignmentRequest($posted_data);
                $pro_quote = new ProQuote();
                $token = $pro_quote->getToken();
                $response = $pro_quote->makePostRequest($token, json_encode($request), true);
                if(isset($response['data']['stockNumber']) && !empty($response['data']['stockNumber']))
                {
                    $this->wpdb->update(
                        $this->quotes_table,
                        array('stock_number' => $response['data']['stockNumber']),
                        array('id' => $otherArray['offer']),
                        array('%s'),
                        array('%d')
                    );
                }
                $valid = $pro_quote->parseAssignmentResponse($response['data']);
                if($valid)
                {
                    $this->wpdb->update(
                        $this->quotes_table,
                        array( 'data_assignment' => json_encode( $request ), 'status' => CI_Quote_Status::ACCEPTED ),
                        array('id' => $otherArray['offer']),
                        array('%s'),
                        array('%d')
                    );
                    wp_send_json_success(array('message' => 'Offer Accepted', 'redirect' => home_url()."/offers-home"));
                }
                else
                {
                    wp_send_json_error( 'Could not save your details. Check if given data is valid' );  
                }
            } else {
                wp_send_json_error( 'No matching offer.' );
            }
        }
        else
        {
            wp_send_json_error( 'Invalid Data.' );
        }
    }
 
    function cancelAssignment()
    {
        if ( ! wp_verify_nonce( $_POST['nonce'], 'ci-data' ) ) {
            wp_send_json_error( 'Invalid nonce.' );
            exit;
        }
        $posted_data = $_POST;
        if((isset($posted_data['offer']) && !empty($posted_data['offer'])))
        {
            $sql = $this->wpdb->prepare("SELECT * FROM $this->quotes_table WHERE id = %d", $posted_data['offer']);
            $row = $this->wpdb->get_row($sql, ARRAY_A);
            if(isset($row['stock_number']) && !empty($row['stock_number']))
            {
                $request = json_decode($row['data_assignment'],1);
                $request['cancellationReason'] = "Cancelling the lot";
                $request['stockNumber'] = $row['stock_number'];
                $pro_quote = new ProQuote();
                $token = $pro_quote->getToken();
                $response = $pro_quote->makePutRequest($token, json_encode($request));
                $valid = $pro_quote->parseAssignmentResponse($response['data']);
                if($valid)
                {
                    $this->wpdb->update(
                        $this->quotes_table,
                        array( 'status' => CI_Quote_Status::CANCELED ),
                        array('id' => $posted_data['offer']),
                        array('%s'),
                        array('%d')
                    );
                    wp_send_json_success(array('message' => 'Offer Cancelled', 'redirect' => home_url()."/offers-home"));
                }
                else
                {
                    wp_send_json_error( 'Could cancel the offer.' );  
                }
            }
            else
            {
                wp_send_json_error( 'No Lot.' );
            }
        }
        else
        {
            wp_send_json_error( 'Invalid Data.' );
        }
    }
 
    function sendQuoteEmail($to, $vehicle, $price, $admin = false, $address = false, $phone = false) {
        $template_path = CI_INCLUDES . '/templates/email.php';
        if($admin)
        {
            $to = "Rmar0007@gmail.com";
            $template_path = CI_INCLUDES . '/templates/admin.php';
        }
        $email_template = file_get_contents($template_path);
        if($admin)
        {
            $subject = "New Quote Cashforcarsjunkcarremoval - $to";
        }
        else
        {
            $subject = 'Free Quote';
        }
        $login = home_url() . '/login';
        $register = home_url() . '/register';
        $email_template = str_replace('{{vehicle}}', $vehicle, $email_template);
        $email_template = str_replace('{{price}}', $price, $email_template);
        $email_template = str_replace('{{login}}', $login, $email_template);
        $email_template = str_replace('{{user_email}}', $to, $email_template);
        $email_template = str_replace('{{user_address}}', $address, $email_template);
        $email_template = str_replace('{{user_phone}}', $phone, $email_template);
        $headers = array('Content-Type: text/html; charset=UTF-8');
        wp_mail($to, $subject, $email_template, $headers);
    }
 
    function uploadImages()
    {
        $uploaded_files = array();
        $posted_data = $_POST;
        if (!empty($_FILES['custom_images']['name'])) {
            $files = $_FILES['custom_images'];
            foreach ($files['name'] as $key => $image_name) {
                $file = array(
                    'name'     => $files['name'][$key],
                    'type'     => $files['type'][$key],
                    'tmp_name' => $files['tmp_name'][$key],
                    'error'    => $files['error'][$key],
                    'size'     => $files['size'][$key]
                );
                $image_type = wp_check_filetype($file['name']);
                if (!$image_type['type']) {
                    echo 'Invalid file type for file: ' . $file['name'] . '. Please upload an image.';
                    continue;
                }
                $max_file_size = 10 * 1024 * 1024;
                if ($file['size'] > $max_file_size) {
                    echo 'File size exceeds the maximum allowed limit for file: ' . $file['name'] . '.';
                    continue;
                }
                $upload_overrides = array('test_form' => false);
                $uploaded_file = wp_handle_upload($file, $upload_overrides);
                if (isset($uploaded_file['url'])) {
                    $uploaded_files[] = ci_store_image_url( $uploaded_file['url'] );
                } else {
                    echo 'Failed to upload image: ' . $file['name'];
                }
            }
            $quote_row = $this->wpdb->get_row(
                $this->wpdb->prepare( "SELECT * FROM $this->quotes_table WHERE id = %d", $posted_data['offer'] )
            );

            if ( ! $quote_row || (int) $quote_row->status !== CI_Quote_Status::OFFERED ) {
                wp_send_json_error( 'This offer is no longer available for image upload.' );
                exit;
            }

            $title_review = $quote_row->title_review ?: CI_Quote_Status::REVIEW_NONE;
            $car_review   = $quote_row->car_review ?: CI_Quote_Status::REVIEW_NONE;

            if ( $posted_data['type'] == 'title' ) {
                $nvp = array(
                    'title_images' => json_encode( $uploaded_files ),
                    'title_review' => CI_Quote_Status::REVIEW_PENDING,
                );
                $title_review = CI_Quote_Status::REVIEW_PENDING;
            } else {
                $nvp = array(
                    'car_images' => json_encode( $uploaded_files ),
                    'car_review' => CI_Quote_Status::REVIEW_PENDING,
                );
                $car_review = CI_Quote_Status::REVIEW_PENDING;
            }

            $nvp['status']        = CI_Quote_Status::OFFERED;
            $nvp['images_status'] = CI_Quote_Status::sync_images_status( $title_review, $car_review );

            $this->wpdb->update(
                $this->quotes_table,
                $nvp,
                array( 'id' => $posted_data['offer'] ),
                array( '%s', '%s', '%d', '%s' ),
                array( '%d' )
            );
            if (!empty($uploaded_files)) {
                wp_send_json_success(array('message' => 'Images Uploaded', 'offer' => $posted_data, 'redirect' => home_url()."/offers-home"));
            }
        } else {
            wp_send_json_error( 'No Images to upload.' );
        }
        wp_die();
    }
 
    function approve()
    {
        if ( ! wp_verify_nonce( $_POST['nonce'], 'ci-backend' ) ) {
            wp_send_json_error( 'Invalid nonce.' );
            exit;
        }
        $posted_data = $_POST;
        if ( ( isset( $posted_data['offer'] ) && ! empty( $posted_data['offer'] ) ) && ( isset( $posted_data['approve'] ) && $posted_data['approve'] !== '' ) ) {
            $quote_row = $this->wpdb->get_row(
                $this->wpdb->prepare( "SELECT * FROM $this->quotes_table WHERE id = %d", $posted_data['offer'] )
            );

            if ( ! $quote_row || (int) $quote_row->status !== CI_Quote_Status::OFFERED ) {
                wp_send_json_error( 'Image review is only available for offered quotes.' );
                exit;
            }

            $title_review = $quote_row->title_review ?: CI_Quote_Status::REVIEW_NONE;
            $car_review   = $quote_row->car_review ?: CI_Quote_Status::REVIEW_NONE;
            $is_approve   = $posted_data['approve'] === 'true';
            $type         = isset( $posted_data['type'] ) ? $posted_data['type'] : 'title';

            if ( $type === 'title' ) {
                $title_review = $is_approve ? CI_Quote_Status::REVIEW_APPROVED : CI_Quote_Status::REVIEW_REJECTED;
                $message      = $is_approve ? 'Title images approved.' : 'Title images disapproved.';
            } else {
                $car_review = $is_approve ? CI_Quote_Status::REVIEW_APPROVED : CI_Quote_Status::REVIEW_REJECTED;
                $message    = $is_approve ? 'Vehicle images approved.' : 'Vehicle images disapproved.';
            }

            $images_status = CI_Quote_Status::sync_images_status( $title_review, $car_review );

            $this->wpdb->update(
                $this->quotes_table,
                array(
                    'status'        => CI_Quote_Status::OFFERED,
                    'images_status' => $images_status,
                    'title_review'  => $title_review,
                    'car_review'    => $car_review,
                ),
                array( 'id' => $posted_data['offer'] ),
                array( '%d', '%s', '%s', '%s' ), // status, images_status, title_review, car_review
                array( '%d' )
            );

            if ( $images_status === CI_Quote_Status::IMAGES_APPROVED ) {
                $message .= ' Customer can now accept the offer.';
            }

            wp_send_json_success( $message );
        } else {
            wp_send_json_error( 'Invalid Data.' );
        }
    }
}