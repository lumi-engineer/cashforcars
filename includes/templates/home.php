<?php
ob_start();
/*
Template Name: Copart Home Page
Description: A custom template for the login page.
*/
get_header();
session_start(); // Start or resume the session
if (!isset($_SESSION['ci_name']) && !isset($_SESSION['ci_email'])) {
    // If either 'name' or 'email' session variables are missing, redirect to the login page
    wp_redirect(home_url('/login'));
    exit;
 // Terminate script execution to ensure the redirect is followed\
}
$offerID = $_GET['offer'];
wp_enqueue_script( 'ci-select2-js' );
wp_enqueue_script( 'ci-swal-js' );
wp_enqueue_script( 'ci-home-js' );
wp_localize_script( 'ci-home-js', 'ajax_object', array(
    'ajax_url' => admin_url( 'admin-ajax.php' ),
    'nonce' => wp_create_nonce( 'ci-data' )
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
</style>
<body">
<div class="mb-5">
<?php
wp_enqueue_script( 'ci-bootstrap-js' );
wp_enqueue_style( 'ci-bootstrap-css' );
if(!$offerID)
{
// Connect to the WordPress database

// Get the current page number
$currentPage = isset($_GET['ci-page']) ? abs((int) $_GET['ci-page']) : 1;

// Set the number of items per page
$itemsPerPage = 10;
// Calculate the offset based on the current page
$offset = ($currentPage - 1) * $itemsPerPage;

// Get the total number of items in the table
$email = $_SESSION['ci_email'];
$totalCount = $wpdb->get_var("SELECT COUNT(*) FROM $quotes_data_table WHERE $quotes_table.email = '$email'");
// Get the data for the current page
$results = $wpdb->get_results("SELECT $quotes_table.*, $quotes_data_table.pro_quote FROM $quotes_table JOIN $quotes_data_table ON $quotes_table.id = $quotes_data_table.qid WHERE $quotes_table.email = '$email' LIMIT $offset, $itemsPerPage");
// echo "<pre>" . print_r($results);die;
echo '<div class="container mt-4">';
echo '<div class="card p-5">';
echo '<div class="panel-heading mb-2 row">
<div class="col-md-8"><h3 class="panel-title">My Offers</h3></div>
<div class="col-md-4"><h3 class="panel-title"><a href="' . home_url("/copart-integration") . '" class="btn btn-primary mr-1 ci-btn" style="float:right">Instant Offer</i></a></h3></div>
</div>';
echo '<table class="table table-bordered">';

// Create the table header
echo '<thead>';
echo '<tr>';
echo '<th scope="col">Vehicle</th>';
echo '<th scope="col">Offer Amount</th>';
echo '<th scope="col">Status</th>';
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
            echo '<span class="badge bg-warning">In Review</span';
        }
        elseif ($row->status == 5) {
            echo '<span class="badge bg-success">Title Images Approved</span';
        }
        elseif ($row->status == 6) {
            echo '<span class="badge bg-danger">Title Images Disapproved - Submit again</span';
        }
        elseif ($row->status == 7) {
            echo '<span class="badge bg-danger">Car Images Disapproved - Submit again</span';
        }
        echo '</td>';
        echo '<td>';
        echo '<a href="?offer=' . $row->id . '" class="btn btn-primary mr-1"><i class="far fa-eye"></i></a>';
        echo '</td>';
        echo '</tr>';
    }
}
echo '</tbody>';

echo '</table>';

// Create pagination links
$totalPages = ceil($totalCount / $itemsPerPage);
echo '<nav aria-label="Page navigation">';
echo '<ul class="pagination justify-content-end">';
for ($i = 1; $i <= $totalPages; $i++) {
    $active = ($i == $currentPage) ? ' active' : '';
    echo '<li class="page-item' . $active . '">';
    echo '<a class="page-link" href="' . get_permalink() . '?ci-page=' . $i . '">' . $i . '</a>';
    echo '</li>';
}
echo '</ul>';
echo '</nav>';
echo '</div>';
echo '</div>';
}
else
{
  $query = $wpdb->prepare("SELECT $quotes_table.*, $quotes_data_table.pro_quote FROM $quotes_table JOIN $quotes_data_table ON $quotes_table.id = $quotes_data_table.qid WHERE $quotes_table.email = %s  and $quotes_table.id  = $offerID",$_SESSION['ci_email']);
  $result = $wpdb->get_row($query);
  $request = json_decode($result->data_proquote,1);
  if($result->email != $_SESSION['ci_email'])
  {
    wp_redirect(home_url('/offers-home'));
    exit;
  }
  $request_assignment = json_decode($result->data_assignment,1);
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
    <div class="stepwizard">
        <div class="stepwizard-row setup-panel">
            <div class="stepwizard-step col-xs-6 col-md-6"> 
                <a href="#step-1" type="button" class="btn btn-success btn-circle">1</a>
                <p><small>Vehicle</small></p>
            </div>
            <!-- <div class="stepwizard-step col-xs-4 col-md-4"> 
                <a href="#step-2" type="button" class="btn btn-default btn-circle" disabled="disabled">2</a>
                <p><small>Loss Info</small></p>
            </div> -->
            <div class="stepwizard-step col-xs-6 col-md-6"> 
                <a href="#step-2" type="button" class="btn btn-default btn-circle" disabled="disabled">2</a>
                <p><small>Vehicle Location</small></p>
            </div>
        </div>
    </div>
    
    <form role="form" id="saveForm">
    <div class="loader-container" id="loader-container" style="display:none">
          <div class="loader">
          </div>
          <p class="loading-text">Please wait ...</p>
      </div>
      <?php
    if(isset($result->status) && !empty($result->status) && ($result->status == 1 ||$result->status == 4 || $result->status == 6 || $result->status == 7 ))
    {
?>              <div class="container">
                <!-- Add a hidden container for full-screen preview -->
                <!-- Hidden modal dialog for image preview -->
                <div class="row card p-5">
                <p class="mb-5">Please select the title and car images so that we can review so that you accept our offer.</p>

                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="control-label mb-5">Upload Title Image</label><br>
                            <input type="file" id="images" class="mb-5" name="custom_images[]" accept="image/*">
                            <button class="submit_images" data-id ="title">      Upload Image  </button>                
                        </div>
                    </div>
                    <?php ci_render_image_previews( $result->title_images ); ?>

                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="control-label mb-5">Upload Vehicle Photos</label><br>
                            <input type="file" id="car_images" name="car_images[]" accept="image/*" multiple>
                            <button class="submit_images" data-id ="car">      Upload Images  </button>                
                        </div>
                    </div>
                    <?php ci_render_image_previews( $result->car_images, 'image-preview-container mt-5' ); ?>
                </div>
                    </div>

<?php
    }
else{
?>
        <div class="panel panel-primary setup-content card p-5" id="step-1">
        <p class="mb-5 text-center">Your title has been reviewed you can accept our offer now.</p>
            <div class="panel-heading">
                 <h3 class="panel-title">Vehicle</h3>
            </div>
            <div class="panel-body">
            <div class="form-group text-center">
                    <label for="phone">Ta da! We'd love to buy your vehicle for</label>
                    <div id="result">
                    <h2 data-id="offer_presentation_price" class="_b22f1561 _f09a3dc7 _86719728" style="color: rgb(24, 114, 237);">$<?php echo $result->pro_quote ?></h2>
                </div>
              </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label">Year</label>
                        <select class="select2" id="year-select" style="width: 100%" name="modelYear" required="required" <?php echo $disabled?>>
                          <option selected="selected"><?php echo $request['vehicleInformation']['year']?></option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label">Type</label>
                        <select class="select2" id="vehicle_type-select" style="width: 100%" name="vehicleType" required="required" <?php echo $disabled?>>
                          <option selected="selected" value="<?php echo $result_vehicle_type->code ?>"><?php echo $result_vehicle_type->name ?></option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label">Make</label>
                        <select class="select2" id="make-select" style="width: 100%" name="makeDescription" required="required" <?php echo $disabled?>>
                          <option selected="selected"><?php echo $request['vehicleInformation']['makeCode']?></option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label">Model</label>
                        <select class="select2" id="model-select"  style="width: 100%" name="modelDescription" required="required" <?php echo $disabled?>>
                          <option selected="selected"><?php echo $request['vehicleInformation']['model']?></option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label">Odometer Brand</label>
                        <select class="select2" style="width: 100%" name="odometerBrand" required="required" <?php echo $disabled?>>
                          <option selected="selected">Actual</option>
                          <option selected="selected">Not Actual</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label">Is Trailer Attached ?</label>
                        <select class="select2" id="isTrailerAttached"  style="width: 100%" name="isTrailerAttached" required="required" <?php echo $disabled?>>
                          <option <?php $request_assignment['vehicleDetails']['isTrailerAttached'] == 'Y' ? 'selected' : ''?> value="Y">Yes</option>
                          <option <?php $request_assignment['vehicleDetails']['isTrailerAttached'] == 'N' ? 'selected' : ''?> value="N">No</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="mt-5">
            <button class="btn btn-primary nextBtn float-right ci-btn" type="button" style="float: right;">Next</button>
            <?php
              if(isset($result->status) && ($result->status == 2)){
            ?>
              <button id="cancelBtn" class="btn btn-danger float-left" type="button" >Cancel Offer</button>
            <?php
              }
            ?>
          </div>
        </div>
        </div>
        
        <!-- <div class="panel panel-primary setup-content card p-5" id="step-2">
            <div class="panel-heading">
                 <h3 class="panel-title">Loss Info</h3>
            </div>
            <div class="panel-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label">Loss Type</label>
                        <select class="select2" id="damage_type-select"  style="width: 100%" name="causeOfLoss" required="required" <?php echo $disabled?>>
                          <option selected="selected"><?php echo $request_assignment['assignmentDetails']['causeOfLoss']?></option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label">Loss Location</label>
                        <select class="select2" id="damage_location-select" style="width: 100%" name="primaryDamage" required="required" <?php echo $disabled?>>
                          <option selected="selected"><?php echo $request['lossInfo']['primaryPointOfImpact']?></option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label">Insured</label>
                        <select class="select2" id="insured" style="width: 100%" name="insured" required="required" <?php echo $disabled?>>
                          <option selected="selected" value="Y">Yes</option>
                            <option selected="selected" value="N">No</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label">Claim</label>
                        <input maxlength="200" type="text" name="claim" required="required" class="form-control" placeholder="Claim Number" value="<?php echo $request_assignment['assignmentDetails']['claimNumber']?>" <?php echo $disabled?>/>
                    </div>
                </div>
            </div>
            <div class="mt-5">
            <button class="btn btn-primary nextBtn float-right ci-btn" type="button" style="float: right;">Next</button>
          </div>
            </div>
        </div> -->
        <div class="panel panel-primary setup-content card p-5" id="step-2">
            <div class="panel-heading">
                 <h3 class="panel-title">Vehicle Location</h3>
            </div>
                <div class="panel-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label">Name</label>
                            <input maxlength="100" type="text" required="required" class="form-control" placeholder="Name" name="name" value="<?php echo $request_assignment['vehicleLocation']['name']?>" <?php echo $disabled?> required="required" />
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label">Phone</label>
                            <input maxlength="100" type="text" required="required" class="form-control" placeholder="Phone" name="phone" value="<?php echo $request_assignment['vehicleLocation']['telephone']['number']?>" <?php echo $disabled?> required="required" />
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="control-label">Address</label>
                            <input  type="text" required="required" name="addressLine1" class="form-control" placeholder="Enter Address" value="<?php echo $request_assignment['vehicleLocation']['address']['addressLine1']?>" <?php echo $disabled?> required="required" />
                        </div>
                    </div>
                </div>
                <div class="row">
                  <div class="col-md-6">
                      <div class="form-group">
                          <label class="control-label">City</label>
                          <input maxlength="100" type="text" required="required" class="form-control" placeholder="City" name="city" value="<?php echo $request_assignment['vehicleLocation']['address']['city']?>" <?php echo $disabled?> required="required" />
                      </div>
                  </div>
                  <div class="col-md-6">
                      <div class="form-group">
                          <label class="control-label">State</label>
                          <select class="form-control" name="state" <?php echo $disabled?> required="required" >
                            <option value="">Select State</option>
                            <option <?php echo ($request_assignment['vehicleLocation']['address']['state'] == 'AL') ? 'selected' : ''?> value="AL">Alabama</option>
                            <option <?php echo ($request_assignment['vehicleLocation']['address']['state'] == 'AK') ? 'selected' : ''?> value="AK">Alaska</option>
                            <option <?php echo ($request_assignment['vehicleLocation']['address']['state'] == 'AZ') ? 'selected' : ''?> value="AZ">Arizona</option>
                            <option <?php echo ($request_assignment['vehicleLocation']['address']['state'] == 'AR') ? 'selected' : ''?> value="AR">Arkansas</option>
                            <option <?php echo ($request_assignment['vehicleLocation']['address']['state'] == 'CA') ? 'selected' : ''?> value="CA">California</option>
                            <option <?php echo ($request_assignment['vehicleLocation']['address']['state'] == 'CO') ? 'selected' : ''?> value="CO">Colorado</option>
                            <option <?php echo ($request_assignment['vehicleLocation']['address']['state'] == 'CT') ? 'selected' : ''?> value="CT">Connecticut</option>
                            <option <?php echo ($request_assignment['vehicleLocation']['address']['state'] == 'DE') ? 'selected' : ''?> value="DE">Delaware</option>
                            <option <?php echo ($request_assignment['vehicleLocation']['address']['state'] == 'FL') ? 'selected' : ''?> value="FL">Florida</option>
                            <option <?php echo ($request_assignment['vehicleLocation']['address']['state'] == 'GA') ? 'selected' : ''?> value="GA">Georgia</option>
                            <option <?php echo ($request_assignment['vehicleLocation']['address']['state'] == 'HI') ? 'selected' : ''?> value="HI">Hawaii</option>
                            <option <?php echo ($request_assignment['vehicleLocation']['address']['state'] == 'ID') ? 'selected' : ''?> value="ID">Idaho</option>
                            <option <?php echo ($request_assignment['vehicleLocation']['address']['state'] == 'IL') ? 'selected' : ''?> value="IL">Illinois</option>
                            <option <?php echo ($request_assignment['vehicleLocation']['address']['state'] == 'IN') ? 'selected' : ''?> value="IN">Indiana</option>
                            <option <?php echo ($request_assignment['vehicleLocation']['address']['state'] == 'IA') ? 'selected' : ''?> value="IA">Iowa</option>
                            <option <?php echo ($request_assignment['vehicleLocation']['address']['state'] == 'KS') ? 'selected' : ''?> value="KS">Kansas</option>
                            <option <?php echo ($request_assignment['vehicleLocation']['address']['state'] == 'KY') ? 'selected' : ''?> value="KY">Kentucky</option>
                            <option <?php echo ($request_assignment['vehicleLocation']['address']['state'] == 'LA') ? 'selected' : ''?> value="LA">Louisiana</option>
                            <option <?php echo ($request_assignment['vehicleLocation']['address']['state'] == 'ME') ? 'selected' : ''?> value="ME">Maine</option>
                            <option <?php echo ($request_assignment['vehicleLocation']['address']['state'] == 'MD') ? 'selected' : ''?> value="MD">Maryland</option>
                            <option <?php echo ($request_assignment['vehicleLocation']['address']['state'] == 'MA') ? 'selected' : ''?> value="MA">Massachusetts</option>
                            <option <?php echo ($request_assignment['vehicleLocation']['address']['state'] == 'MI') ? 'selected' : ''?> value="MI">Michigan</option>
                            <option <?php echo ($request_assignment['vehicleLocation']['address']['state'] == 'MN') ? 'selected' : ''?> value="MN">Minnesota</option>
                            <option <?php echo ($request_assignment['vehicleLocation']['address']['state'] == 'MS') ? 'selected' : ''?> value="MS">Mississippi</option>
                            <option <?php echo ($request_assignment['vehicleLocation']['address']['state'] == 'MO') ? 'selected' : ''?> value="MO">Missouri</option>
                            <option <?php echo ($request_assignment['vehicleLocation']['address']['state'] == 'MT') ? 'selected' : ''?> value="MT">Montana</option>
                            <option <?php echo ($request_assignment['vehicleLocation']['address']['state'] == 'NE') ? 'selected' : ''?> value="NE">Nebraska</option>
                            <option <?php echo ($request_assignment['vehicleLocation']['address']['state'] == 'NV') ? 'selected' : ''?> value="NV">Nevada</option>
                            <option <?php echo ($request_assignment['vehicleLocation']['address']['state'] == 'NH') ? 'selected' : ''?> value="NH">New Hampshire</option>
                            <option <?php echo ($request_assignment['vehicleLocation']['address']['state'] == 'NJ') ? 'selected' : ''?> value="NJ">New Jersey</option>
                            <option <?php echo ($request_assignment['vehicleLocation']['address']['state'] == 'NM') ? 'selected' : ''?> value="NM">New Mexico</option>
                            <option <?php echo ($request_assignment['vehicleLocation']['address']['state'] == 'NY') ? 'selected' : ''?> value="NY">New York</option>
                            <option <?php echo ($request_assignment['vehicleLocation']['address']['state'] == 'NC') ? 'selected' : ''?> value="NC">North Carolina</option>
                            <option <?php echo ($request_assignment['vehicleLocation']['address']['state'] == 'ND') ? 'selected' : ''?> value="ND">North Dakota</option>
                            <option <?php echo ($request_assignment['vehicleLocation']['address']['state'] == 'OH') ? 'selected' : ''?> value="OH">Ohio</option>
                            <option <?php echo ($request_assignment['vehicleLocation']['address']['state'] == 'OK') ? 'selected' : ''?> value="OK">Oklahoma</option>
                            <option <?php echo ($request_assignment['vehicleLocation']['address']['state'] == 'OR') ? 'selected' : ''?> value="OR">Oregon</option>
                            <option <?php echo ($request_assignment['vehicleLocation']['address']['state'] == 'PA') ? 'selected' : ''?> value="PA">Pennsylvania</option>
                            <option <?php echo ($request_assignment['vehicleLocation']['address']['state'] == 'RI') ? 'selected' : ''?> value="RI">Rhode Island</option>
                            <option <?php echo ($request_assignment['vehicleLocation']['address']['state'] == 'SC') ? 'selected' : ''?> value="SC">South Carolina</option>
                            <option <?php echo ($request_assignment['vehicleLocation']['address']['state'] == 'SD') ? 'selected' : ''?> value="SD">South Dakota</option>
                            <option <?php echo ($request_assignment['vehicleLocation']['address']['state'] == 'TN') ? 'selected' : ''?> value="TN">Tennessee</option>
                            <option <?php echo ($request_assignment['vehicleLocation']['address']['state'] == 'TX') ? 'selected' : ''?> value="TX">Texas</option>
                            <option <?php echo ($request_assignment['vehicleLocation']['address']['state'] == 'UT') ? 'selected' : ''?> value="UT">Utah</option>
                            <option <?php echo ($request_assignment['vehicleLocation']['address']['state'] == 'VT') ? 'selected' : ''?> value="VT">Vermont</option>
                            <option <?php echo ($request_assignment['vehicleLocation']['address']['state'] == 'VA') ? 'selected' : ''?> value="VA">Virginia</option>
                            <option <?php echo ($request_assignment['vehicleLocation']['address']['state'] == 'WA') ? 'selected' : ''?> value="WA">Washington</option>
                            <option <?php echo ($request_assignment['vehicleLocation']['address']['state'] == 'WV') ? 'selected' : ''?> value="WV">West Virginia</option>
                            <option <?php echo ($request_assignment['vehicleLocation']['address']['state'] == 'WI') ? 'selected' : ''?> value="WI">Wisconsin</option>
                            <option <?php echo ($request_assignment['vehicleLocation']['address']['state'] == 'WY') ? 'selected' : ''?> value="WY">Wyoming</option>
                        </select>

                      </div>
                  </div>
              </div>
              <div class="row">
                  <div class="col-md-6">
                      <div class="form-group">
                          <label class="control-label">Zip Code</label>
                          <input maxlength="100" type="text" required="required" class="form-control" name="zipcode" placeholder="Zip" value="<?php echo $request_assignment['vehicleLocation']['address']['zipcode']?>" <?php echo $disabled?> required="required" />
                      </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                          <label class="control-label">Country</label>
                          <select class="form-control" name="country" <?php echo $disabled?> required="required" >
                            <option value="">Select Country</option>
                            <option <?php echo ($request_assignment['vehicleLocation']['address']['country'] == 'USA') ? 'selected' : ''?> value="USA">United States</option>
                        </select>
                      </div>
                      </div>
                  </div>
                  <div class="row">
                  <div class="col-md-6">
                      <div class="form-group">
                          <label class="control-label">Pickup Dates</label>
                          <?php
                            $dates = array();
                            if(isset($request_assignment['assignmentDetails']['dates']) && !empty(isset($request_assignment['assignmentDetails']['dates'])))
                            {
                                foreach($request_assignment['assignmentDetails']['dates'] as $date)
                                {
                                    $dates[] = $date['date'];
                                }
                            }
                          ?>
`                          <input type="text" id="datepicker" name="dates" class="form-control date" placeholder="Pick the multiple dates for pickup" required="required" value="<?php echo implode($dates)?>" <?php echo $disabled?>>                      
                        </div>
                  </div>
                  </div>
                  <div class="mt-5">
                  <button id="finish" <?php echo $disabled?> class="btn btn-success pull-right" style="float: right;">Accept Offer</button>
          </div>
              </div>
            </div>
        </div>
<?php
}
?>
    </form>
</div>
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
get_footer();
ob_start();

?>
<script>
  jQuery(document).ready(function () {

var navListItems = jQuery('div.setup-panel div a'),
    allWells = jQuery('.setup-content'),
    allNextBtn = jQuery('.nextBtn');

allWells.hide();

navListItems.click(function (e) {
    e.preventDefault();
    var $target = jQuery(jQuery(this).attr('href')),
        $item = jQuery(this);

    if (!$item.hasClass('disabled')) {
        navListItems.removeClass('btn-success').addClass('btn-default');
        $item.addClass('btn-success');
        allWells.hide();
        $target.show();
        $target.find('input:eq(0)').focus();
    }
});

allNextBtn.click(function () {
    var curStep = jQuery(this).closest(".setup-content"),
        curStepBtn = curStep.attr("id"),
        nextStepWizard = jQuery('div.setup-panel div a[href="#' + curStepBtn + '"]').parent().next().children("a"),
        curInputs = curStep.find("input[type='text'],input[type='url'],select"),
        isValid = true;

    jQuery(".form-group").removeClass("has-error");
    for (var i = 0; i < curInputs.length; i++) {
        if (!curInputs[i].validity.valid) {
            isValid = false;
            jQuery(curInputs[i]).closest(".form-group").addClass("has-error");
        }
    }
    if (isValid) nextStepWizard.removeAttr('disabled').trigger('click');
});

jQuery('div.setup-panel div a.btn-success').trigger('click');
});
var selectedDates = 0;

var selectedDates = 0;

// Initialize Datepicker with options
jQuery("#datepicker").datepicker({
    multidate: true,
    startDate: '+1d', // Set the minimum date to tomorrow
    format: 'yyyy-mm-dd'
});
var selectedDates = [];
let dp = jQuery('#datepicker');
dp.on('changeDate', function(e) {

  if (e.dates.length < 4) {
    // store current selections
    selectedDates = e.dates
  } else {
    // reset dates if 4th selected
    dp.data('datepicker').setDates(selectedDates);
    alert('Can only select 3 dates')
  }

});

</script>