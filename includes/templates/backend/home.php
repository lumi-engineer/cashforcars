<?php
ob_start();
/*
Template Name: Copart Backend Home Page
Description: A custom template for the Copart Backend Home Page.
*/
$offerID = $_GET['offer'];
$statusFilter = isset($_GET['status']) ? intval($_GET['status']) :''; // Get the status filter from URL
wp_enqueue_script( 'select2-js' );
wp_enqueue_script( 'ci-swal-js' );
wp_localize_script( 'script-backend-js', 'ajax_object', array(
    'ajax_url' => admin_url( 'admin-ajax.php' ),
    'nonce' => wp_create_nonce( 'ci-backend' )
));
global $wpdb;
$quotes_table = $wpdb->prefix . 'df_ci_quotes';
$quotes_data_table = $wpdb->prefix . 'df_ci_quote_data';
?>
<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="<?php echo CI_ASSETS?>/css/home.css">
<link rel="stylesheet" href="<?php echo CI_ASSETS . '/css/bootstrap-datepicker.css'?>">
<script src="<?php echo CI_ASSETS . '/js/bootstrap-datepicker.js'?>"></script>
</head>
<style>
  body {
    margin-top:30px;
}
.stepwizard-step p {
    margin-top: 0px !important;
    color:#666 !important;
}
.stepwizard-row {
    display: table-row !important;
}
.stepwizard {
    display: table !important;
    width: 100% !important;
    position: relative !important;
}
.stepwizard-step button[disabled] {
    /*opacity: 1 !important;
    filter: alpha(opacity=100) !important;*/
}
.stepwizard .btn.disabled, .stepwizard .btn[disabled], .stepwizard fieldset[disabled] .btn {
    opacity:1 !important;
    color:#bbb !important;
}
.stepwizard-row:before {
    top: 14px !important;
    bottom: 0 !important;
    position: absolute !important;
    content:" ";
    width: 100%!important;
    height: 1px !important;
    background-color: #ccc ;
    z-index: 0;
}
.stepwizard-step {
    display: table-cell !important;
    text-align: center !important;
    position: relative !important;
}
.btn-circle {
    width: 30px;
    height: 30px;
    text-align: center !important;
    padding: 6px 0 !important;
    font-size: 12px !important;
    line-height: 1.428571429 !important;
    border-radius: 15px !important;
}
.mr-1{
  margin-right: 5px;
}
.mt-4{
  margin-top: 20px;
}
.loader-container {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.7); /* Semi-transparent white background */
    backdrop-filter: blur(5px); /* Apply a blur effect to the background */
    display: flex;
    flex-direction: column; /* Adjusted to display text beneath loader */
    justify-content: center;
    align-items: center;
    z-index: 1000; /* Make sure it's above your content */
}
.loader {
    border: 16px solid #f3f3f3;
    border-top: 16px solid #D9EB4E;
    border-radius: 50%;
    width: 80px;
    height: 80px;
    animation: spin 1s linear infinite;
}

.loading-text {
    color: #8c52ff;
    margin-top: 10px; /* Adjust as needed for spacing */
    font-size: 16px;
    font-weight: bold;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
.form-group.has-error label {
  color: red;
}
.ci-btn{
    color: #000 !important;
    background: #D9EB4E !important;
    border: none !important;
    padding-top: 10px !important;
    padding-bottom: 10px !important;
    font-weight: bold !important;
}
#wpfooter{
    display: none !important;
}
</style>
<body">
<div class="mb-5">
<?php
if(!$offerID)
{
// Connect to the WordPress database

// Get the current page number
$currentPage = isset($_GET['ci-page']) ? abs((int) $_GET['ci-page']) : 1;

// Set the number of items per page
$itemsPerPage = 10;
// Calculate the offset based on the current page
$offset = ($currentPage - 1) * $itemsPerPage;


    // If status filter is applied, modify the query accordingly
    $statusQuery = $statusFilter !== '' ? $wpdb->prepare("AND $quotes_table.status = %d", $statusFilter) : '';
    // If status filter is applied, modify the query accordingly
    if ($statusFilter !== '') {
        $totalCount = $wpdb->get_var(
            $wpdb->prepare("SELECT COUNT(*) FROM $quotes_table 
                            JOIN $quotes_data_table ON $quotes_table.id = $quotes_data_table.qid 
                            WHERE $quotes_table.status = %d AND $quotes_data_table.pro_quote IS NOT NULL AND $quotes_data_table.pro_quote != ''", 
                           $statusFilter)
        );
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT $quotes_table.*, $quotes_data_table.pro_quote 
                 FROM $quotes_table 
                 JOIN $quotes_data_table ON $quotes_table.id = $quotes_data_table.qid 
                 WHERE $quotes_table.status = %d 
                 AND $quotes_data_table.pro_quote IS NOT NULL 
                 AND $quotes_data_table.pro_quote != '' 
                 ORDER BY $quotes_table.id DESC 
                 LIMIT %d, %d", 
                 $statusFilter, $offset, $itemsPerPage
            )
        );
    } else {
        $totalCount = $wpdb->get_var(
            "SELECT COUNT(*) FROM $quotes_table 
             JOIN $quotes_data_table ON $quotes_table.id = $quotes_data_table.qid 
             WHERE $quotes_data_table.pro_quote IS NOT NULL AND $quotes_data_table.pro_quote != ''"
        );
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT $quotes_table.*, $quotes_data_table.pro_quote 
                 FROM $quotes_table 
                 JOIN $quotes_data_table ON $quotes_table.id = $quotes_data_table.qid 
                 WHERE $quotes_data_table.pro_quote IS NOT NULL 
                 AND $quotes_data_table.pro_quote != '' 
                 ORDER BY $quotes_table.id DESC 
                 LIMIT %d, %d", 
                 $offset, $itemsPerPage
            )
        );
    }
    
    echo '<div class="container mt-4">';
    echo '<div class="card p-5" style="min-width:100%">';
    echo '<div class="panel-heading mb-2 row">';
    echo '<div class="col-md-8"><h3 class="panel-title">Offers</h3></div>';
    echo '<div class="col-md-4">';
    echo '<form method="get" action="' . esc_url(admin_url('admin.php')) . '">';
    echo '<input type="hidden" name="page" value="copart-integration">'; // Add the page parameter
    echo '<select name="status" class="form-select" onchange="this.form.submit()">';
    echo '<option value="1"' . selected($statusFilter, 1, false) . '>Offered</option>';
    echo '<option value="2"' . selected($statusFilter, 2, false) . '>Accepted</option>';
    echo '<option value="3"' . selected($statusFilter, 3, false) . '>Canceled</option>';
    echo '<option value="4"' . selected($statusFilter, 4, false) . '>Waiting For Review</option>';
    echo '<option value="5"' . selected($statusFilter, 5, false) . '>Title Images Approved</option>';
    echo '<option value="6"' . selected($statusFilter, 6, false) . '>Title Images Disapproved</option>';
    echo '</select>';
    echo '</form>';
    
    echo '</div>';
    echo '</div>';
    echo '<table class="table table-bordered">';
// Create the table header
echo '<thead>';
echo '<tr>';
echo '<th scope="col">Vehicle</th>';
echo '<th scope="col">Offer Amount</th>';
echo '<th scope="col">Status</th>';
echo '<th scope="col">Time</th>';
echo '<th scope="col">Actions</th>';
echo '</tr>';
echo '</thead>';

// Loop through the results and display each row in the table
echo '<tbody>';
foreach ($results as $row) {
    if ((isset($row->data_proquote) && !empty($row->data_proquote)) && (isset($row->pro_quote) && !empty($row->pro_quote))) {
        $request = json_decode($row->data_proquote, 1);
        $quote = $row->pro_quote;

        echo '<tr>';
        echo '<td>' . $request['vehicleInformation']['year'] . " " . $request['vehicleInformation']['makeCode'] . " " . $request['vehicleInformation']['model'] . '</td>';
        echo '<td>$' . $row->pro_quote . '</td>';
        echo '<td>';
        if ($row->status == 1) {
            echo '<span class="badge bg-info text-dark">Offered</span>';
        } elseif ($row->status == 2) {
            echo '
            <span class="badge bg-success">Accepted</span>';
        } elseif ($row->status == 3) {
            echo '<span class="badge bg-danger">Canceled</span';
        }
        elseif ($row->status == 4) {
            echo '<span class="badge bg-warning">Waiting For Review</span';
        }
        elseif ($row->status == 5) {
            echo '<span class="badge bg-success">Title Images Approved</span';
        }
        elseif ($row->status == 6) {
            echo '<span class="badge bg-danger">Title Images Disapproved - Submit again</span';
        }
        echo '</td>';
        echo '<td>' . date('Y-m-d H:i:s', $result->transaction_id) . '</td>';
        echo '<td>';
        echo '<a href="?page=copart-integration&offer=' . $row->id . '" class="btn btn-primary mr-1">View</a>';
        echo '</td>';
        echo '</tr>';
    }
}
echo '</tbody>';

echo '</table>';

// Create pagination links
$totalPages = ceil($totalCount / $itemsPerPage);
$range = 2; // Number of pages to show around the current page
echo '<nav aria-label="Page navigation">';
echo '<ul class="pagination justify-content-end">';

// Show the first page link
if ($currentPage > $range + 1) {
    echo '<li class="page-item">';
    echo '<a class="page-link" href="' . get_permalink() . '?ci-page=1">1</a>';
    echo '</li>';
    if ($currentPage > $range + 2) {
        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
    }
}

// Show the range of pages around the current page
for ($i = max(1, $currentPage - $range); $i <= min($totalPages, $currentPage + $range); $i++) {
    $active = ($i == $currentPage) ? ' active' : '';
    echo '<li class="page-item' . $active . '">';
    echo '<a class="page-link" href="' . get_permalink() . '?page=copart-integration&ci-page=' . $i . '">' . $i . '</a>';
    echo '</li>';
}

// Show the last page link
if ($currentPage < $totalPages - $range) {
    if ($currentPage < $totalPages - $range - 1) {
        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
    }
    echo '<li class="page-item">';
    echo '<a class="page-link" href="' . get_permalink() . '?page=copart-integration&ci-page=' . $totalPages . '">' . $totalPages . '</a>';
    echo '</li>';
}

echo '</ul>';
echo '</nav>';

echo '</div>';
echo '</div>';
}
else
{
    
  $query = $wpdb->prepare("SELECT $quotes_table.*, $quotes_data_table.pro_quote FROM $quotes_table JOIN $quotes_data_table ON $quotes_table.id = $quotes_data_table.qid  and $quotes_table.id  = $offerID");
  $result = $wpdb->get_row($query);
  $request = json_decode($result->data_proquote,1);
  $request_assignment = json_decode($result->data_assignment,1);
  $request = json_decode($result->data_proquote, true);
  $request_assignment = json_decode($result->data_assignment, true);
    $quote_status = $result->status;
  // Extract email, phone, and address if status is 1
  $email = $result->email;
  $address = isset($request['vehicleLocationSite']['locationName']) ? $request['vehicleLocationSite']['locationName'] . ', ' . $request['vehicleLocationSite']['address']['contact']['postalCode'] : 'Address not available';
  $phone = isset($request['vehicleLocationSite']['phone']) ? $request['vehicleLocationSite']['phone'] : 'Phone not available';

  if(isset($request['vehicleInformation']['vehicleType']) && !empty($request['vehicleInformation']['vehicleType']))
  {
    $table_types = $wpdb->prefix . "df_ci_types_data";
    $type = $request['vehicleInformation']['vehicleType'];
    $query_vehicle_type = $wpdb->prepare("SELECT * FROM $table_types WHERE code = '$type' and type_id = 1");
    $result_vehicle_type = $wpdb->get_row($query_vehicle_type);
  }
  $disabled = '';
  if(isset($result->status) && ($result->status == 2 || $result->status == 3))
  {
    $disabled = "disabled";
  }
?>
<div class="container mt-4">
    <div class="row card p-5" style="min-width: 100%;">
        <h5 class="card-title mb-5"> Information</h5>
        
        <?php if($quote_status == 2) {?>
            <h5 class="card-title mb-5">Vehicle and Assignment Information</h5>
        
        <!-- Vehicle Information -->
        <p><strong>Model Year:</strong> 
            <?php echo isset($request_assignment['vehicleDetails']['modelYear']) ? esc_html($request_assignment['vehicleDetails']['modelYear']) : 'Not available'; ?>
        </p>
        <p><strong>Vehicle Type:</strong> 
            <?php echo isset($request_assignment['vehicleDetails']['vehicleType']) ? esc_html($request_assignment['vehicleDetails']['vehicleType']) : 'Not available'; ?>
        </p>
        <p><strong>Make:</strong> 
            <?php echo isset($request_assignment['vehicleDetails']['make']) ? esc_html($request_assignment['vehicleDetails']['make']) : 'Not available'; ?>
        </p>
        <p><strong>Model:</strong> 
            <?php echo isset($request_assignment['vehicleDetails']['model']) ? esc_html($request_assignment['vehicleDetails']['model']) : 'Not available'; ?>
        </p>
        <p><strong>Odometer Brand:</strong> 
            <?php echo isset($request_assignment['vehicleDetails']['odometerBrand']) ? esc_html($request_assignment['vehicleDetails']['odometerBrand']) : 'Not available'; ?>
        </p>
        <p><strong>Odometer Reading:</strong> 
            <?php echo isset($request_assignment['vehicleDetails']['odometerReading']) ? esc_html($request_assignment['vehicleDetails']['odometerReading']) : 'Not available'; ?>
        </p>
        <!-- Customer Information -->
        <h6 class="mt-4">Customer Information</h6>
        <p><strong>Name:</strong> 
            <?php echo isset($request_assignment['vehicleLocation']['name']) ? esc_html($request_assignment['vehicleLocation']['name']) : 'Not available'; ?>
        </p>
        <p><strong>Phone:</strong> 
            <?php 
                echo isset($request_assignment['vehicleLocation']['telephone']['countryCode'], $request_assignment['vehicleLocation']['telephone']['number']) ? 
                esc_html($request_assignment['vehicleLocation']['telephone']['countryCode'] . ' ' . $request_assignment['vehicleLocation']['telephone']['number']) : 
                'Not available'; 
            ?>
        </p>
        <p><strong>Address:</strong> 
            <?php 
                echo isset($request_assignment['vehicleLocation']['address']['addressLine1'], $request_assignment['vehicleLocation']['address']['city'], $request_assignment['vehicleLocation']['address']['state']) ? 
                esc_html($request_assignment['vehicleLocation']['address']['addressLine1'] . ', ' . $request_assignment['vehicleLocation']['address']['city'] . ', ' . $request_assignment['vehicleLocation']['address']['state']) : 
                'Not available'; 
            ?>
        </p>
        <p><strong>Zip Code:</strong> 
            <?php echo isset($request_assignment['vehicleLocation']['address']['zipcode']) ? esc_html($request_assignment['vehicleLocation']['address']['zipcode']) : 'Not available'; ?>
        </p>
        
        <!-- Pickup Dates -->
        <h6 class="mt-4">Pickup Dates</h6>
        <ul>
            <?php 
            if (isset($request_assignment['assignmentDetails']['dates'])) {
                foreach ($request_assignment['assignmentDetails']['dates'] as $date) {
                    if (isset($date['type'], $date['date']) && $date['type'] === 'PickUpDate') {
                        echo '<li>' . esc_html($date['date']) . '</li>';
                    }
                }
            } else {
                echo '<li>Pickup dates not available</li>';
            }
            ?>
        </ul>
        <p><strong>Quote Time:</strong> 
            <?php 
            echo isset($result->transaction_id) ? date('Y-m-d H:i:s', $result->transaction_id) : 'Not available'; 
            ?>
        </p>
        <?php } else { ?>
            <p><strong>Model Year:</strong> 
            <?php echo isset($request['vehicleInformation']['year']) ? esc_html($request['vehicleInformation']['year']) : 'Not available'; ?>
        </p>
        <p><strong>Vehicle Type:</strong> 
            <?php echo isset($request['vehicleInformation']['vehicleType']) ? esc_html($request['vehicleInformation']['vehicleType']) : 'Not available'; ?>
        </p>
        <p><strong>Make:</strong> 
            <?php echo isset($request['vehicleInformation']['makeCode']) ? esc_html($request['vehicleInformation']['makeCode']) : 'Not available'; ?>
        </p>
        <p><strong>Model:</strong> 
            <?php echo isset($request['vehicleInformation']['model']) ? esc_html($request['vehicleInformation']['model']) : 'Not available'; ?>
        </p>
        <p><strong>Odometer Brand:</strong> 
            <?php echo isset($request['vehicleInformation']['odometerInfo']['odometerBrand']) ? esc_html($request['vehicleInformation']['odometerInfo']['odometerBrand']) : 'Not available'; ?>
        </p>
        <p><strong>Odometer Reading:</strong> 
            <?php echo isset($request['vehicleInformation']['odometerInfo']['odometerReading']) ? esc_html($request['vehicleInformation']['odometerInfo']['odometerReading']) : 'Not available'; ?>
        </p>
        <p><strong>Offer Amount:</strong> 
            <?php echo isset($result->pro_quote) ? "$" . esc_html($result->pro_quote) : 'Not available'; ?>
        </p>
            <p><strong>Email:</strong> <?php echo esc_html($email); ?></p>
            <p><strong>Phone:</strong> <?php echo esc_html($phone); ?></p>
            <p><strong>Address:</strong> <?php echo esc_html($address); ?></p>
            <p><strong>Quote Time:</strong> 
    <?php 
    echo isset($result->transaction_id) ? date('Y-m-d H:i:s', $result->transaction_id) : 'Not available'; 
    ?>
</p>
            <?php } ?>

        <a href="?page=copart-integration" class="btn btn-primary mt-3">Back</a>
    </div>
</div>

<div class="container mt-4">
<div class="loader-container" id="loader-container" style="display:none">
          <div class="loader">
          </div>
          <p class="loading-text">Please wait ...</p>
      </div>
      <?php if(in_array($quote_status, [4,5,6,2])) {?>
        <div class="row card p-5" style="min-width: 100%;">
                <h5 class="card-title mb-5">Please approve or disapprove titles.</h5>
                    <?php ci_render_image_previews( $result->title_images ); ?>
                    <div class="flex">
                    <button id="approveBtn"  style="width: min-content;" class="btn btn-danger float-right mt-5 actionBtn" type="button" data-type="title" action="false">Disapprove</button>
                    <button id="disapproveBtn"   style="width: min-content;float:right" class="btn btn-primary float-left mt-5 actionBtn"  data-type="title" type="button" action="true">Approve</button>
                    </div>
                </div>
                <div class="row card p-5 mt-5" style="min-width: 100%;">
                <h5 class="card-title mb-5">Please approve or disapprove car</h5>
                    <?php ci_render_image_previews( $result->car_images ); ?>
                    <div class="flex">
                    <button id="approveBtn"  style="width: min-content;" class="btn btn-danger float-right mt-5 actionBtn" type="button" data-type="car" action="false">Disapprove</button>
                    <button id="disapproveBtn"   style="width: min-content;float:right" class="btn btn-primary float-left mt-5 actionBtn" type="button" data-type="car" action="true">Approve</button>
                    </div>
                    </div>
</div>
<?php } ?>
<?php

}
?>
    </div>
  </div>
</div>
</div>
</body>
</html>
<?php
ob_start();

?>