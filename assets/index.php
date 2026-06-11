<?php
if (isset($_GET)) {
    // Convert the PHP array to a JSON string
    $json_data_get = json_encode($_GET);
}
else
{
    $json_data_get = '{}';
}
wp_localize_script( 'ci-front-js', 'ajax_object', array(
    'ajax_url' => admin_url( 'admin-ajax.php' ),
    'nonce' => wp_create_nonce( 'ci-data' )
));
$steps = [
    ['name' => 'year', 'desc' => 'Search for year', 'getData' => true, 'selectable' => true, 'title' => 'Select Year' ],
    ['name' => 'vehicle_type', 'desc' => 'Search for type', 'getData' => true, 'selectable' => true , 'title' => 'Select Vehicle Type'],
    ['name' => 'make', 'desc' => 'Search for make', 'getData' => true, 'selectable' => true , 'title' => 'Select Make'],
    ['name' => 'model', 'desc' => 'Search for model', 'getData' => true, 'selectable' => true , 'title' => 'Select Model'],
    ['name' => 'phone', 'desc' => 'Enter phone', 'getData' => false, 'selectable' => false, 'title' => 'Add Phone'],
    ['name' => 'sale_title_type', 'desc' => 'Select title?', 'getData' => false, 'selectable' => true, 'title' => 'Add Title'],
    ['name' => 'zip', 'desc' => 'Enter zip code', 'getData' => false, 'selectable' => false, 'title' => 'Add Address'],
    ['name' => 'drivability_rating', 'desc' => 'Select start condition?', 'getData' => false, 'selectable' => true, 'title' => 'Select Start'],
    ['name' => 'mileage', 'desc' => 'Enter mileage', 'getData' => false, 'selectable' => false, 'title' => 'Add Mileage'],
    ['name' => 'keys', 'desc' => 'Keys available?', 'getData' => false, 'selectable' => true, 'title' => 'Select Keys'],
    ['name' => 'damage', 'desc' => 'Is there any damage?', 'getData' => false, 'selectable' => true , 'steps' => 3, 'title' => 'Select Damage'],
    ['name' => 'damage_location', 'desc' => 'Select damage location ?', 'getData' => true, 'selectable' => true, 'title' => 'Select Damage Location'],
    // ['name' => 'damage_type', 'desc' => 'Select damage type?', 'getData' => true, 'selectable' => true, 'title' => 'Select Damage Type'],
    // ['name' => 'repair_cost', 'desc' => 'Enter repair cost ?', 'getData' => false, 'selectable' => false, 'title' => 'Add Repair cost'],
    ['name' => 'drivable', 'desc' => 'Is vehicle drivable ?', 'getData' => false, 'selectable' => true, 'title' => 'Select Drivable'],
    ['name' => 'mech', 'desc' => 'Any mechanical issue?', 'getData' => false, 'selectable' => true, 'title' => 'Select Mechanical issue'],
    ['name' => 'lien', 'desc' => 'Select lien ?', 'getData' => false, 'selectable' => true, 'title' => 'Select Lien'],
    ['name' => 'vehicle_photo', 'desc' => 'Upload vehicle and title photos', 'getData' => false, 'selectable' => false, 'inputType' => 'file', 'title' => 'Upload Photos'],
    ['name' => 'email', 'desc' => 'Enter your email to get your quote', 'getData' => false, 'selectable' => false, 'title' => 'Enter Email'],
];
$vins = ['year', 'make', 'model', 'vehicle_type' ];
$jsonString = json_encode($steps);
?>
 
<div id="canvas">
    <div class="header">
        <span id="selected_cat">Year</span> 
        <div class="right flex-column-center">
            <p class="mycar" id="selected_data">My Car</p>
        </div>
    </div>
    <div class="search">
        <?php for ($i = 0; $i < count($steps); $i++) {?>
            <div class="tab <?php echo $steps[$i]['name'];?> <?php $steps[$i]['selectable'] == true ? print 'select' : '';?>">
            <?php 
            if($steps[$i]['selectable'] == false && isset($steps[$i]['inputType']) && $steps[$i]['inputType'] === 'file'){
            ?>
            <div class="title-container">
            <div class="title-searchbar">
                <div class="form-group">
                    <label for="vehicle_photos"><?php echo ucfirst($steps[$i]['desc']); ?></label>
                    <div class="form-group mt-2">
                        <label for="vehicle_photos">Vehicle photos (multiple)</label>
                        <input type="file" class="form-control input vehicle-photo-input" id="vehicle_photos" name="vehicle_photos[]" accept="image/*" multiple>
                        <div class="photo-preview mt-2" id="vehicle_photos_preview"></div>
                    </div>
                    <div class="form-group mt-2">
                        <label for="title_image">Title image (one file)</label>
                        <input type="file" class="form-control input title-photo-input" id="title_image" name="title_image" accept="image/*">
                        <div class="photo-preview mt-2" id="title_image_preview"></div>
                    </div>
                    <small class="photo-hint mt-2">Upload at least one vehicle photo and one title image (max 10MB each).</small>
                    <small class="error <?php echo $steps[$i]['name']; ?> mt-2">Please upload the required photos.</small>
                </div>
            </div>
            </div>
            <?php
            } elseif($steps[$i]['selectable'] == false){
            ?>
            <div class="title-container">
            <div class="title-searchbar">
                <div class="form-group">
                    <label for="<?php echo $steps[$i]['name']; ?>"><?php echo ucfirst($steps[$i]['desc']); ?></label>
                    <input type="text" class="form-control input mt-2" id="<?php echo $steps[$i]['name']; ?>" onkeyup="javascript:handleKeyPress(this)" placeholder="<?php echo $steps[$i]['desc']; ?>">
                    <small class="error <?php echo $steps[$i]['name']; ?> mt-2">Field cannot be empty.</small>
                </div>
            </div>
            </div>
            <?php
            } elseif($steps[$i]['name'] == 'keys' || $steps[$i]['name'] == 'damage'  || $steps[$i]['name'] == 'mech' || $steps[$i]['name'] == 'lien' || $steps[$i]['name'] == 'drivable' ){ ?>
            <div class="title-container">
            <div class="title-searchbar">
                <div class="form-group selectable">
                <label for="<?php echo $steps[$i]['name']; ?>"><?php echo $steps[$i]['desc']; ?></label>
                </div>
            </div>
            </div>
                <div class="data  <?php echo $steps[$i]['name']; ?>" id="<?php echo $steps[$i]['name']; ?>">
                    <ul id="<?php echo $steps[$i]['name']; ?>" class="ui-listbox-list">
                    <?php if(isset($steps[$i]['steps'])){?>
                        <li class="ui-listbox-item" ><span onclick="javascript:addEvent(<?php echo $i; ?>, this, <?php echo $steps[$i]['steps']; ?>)" class="list-group-item" data-value="N">No</span></li>
                    <?php
                    }
                    else{
                    ?>
                        <li class="ui-listbox-item" ><span onclick="javascript:addEvent(<?php echo $i; ?>, this, 1)" class="list-group-item" data-value="N">No</span></li>
                    <?php
                    }
                    ?>
                    <li class="ui-listbox-item" ><span onclick="javascript:addEvent(<?php echo $i; ?>, this, 1)" class="list-group-item" data-value="Y">Yes</span></li>
                    </ul>
                </div>
                <?php
            } elseif( $steps[$i]['name'] == 'sale_title_type'){ ?>
            <div class="title-container">
            <div class="title-searchbar">
                <div class="form-group selectable">
                <label for="<?php echo $steps[$i]['name']; ?>"><?php echo $steps[$i]['desc']; ?></label>
                </div>
            </div>
            </div>
                <div class="data  <?php echo $steps[$i]['name']; ?>" id="<?php echo $steps[$i]['name']; ?>">
                    <ul id="<?php echo $steps[$i]['name']; ?>" class="ui-listbox-list">
                    <li class="ui-listbox-item" ><span onclick="javascript:addEvent(<?php echo $i; ?>, this, 1)" class="list-group-item" data-value="Clean Title">I Have A Clean Title</span></li>
                    <li class="ui-listbox-item" ><span onclick="javascript:addEvent(<?php echo $i; ?>, this, 1)" class="list-group-item" data-value="Salvage Title">I Have A Salvage Title</span></li>
                    <li class="ui-listbox-item" ><span onclick="javascript:addEvent(<?php echo $i; ?>, this, 1)" class="list-group-item" data-value="Salvage Title">I Have A Rebuilt Title</span></li>
                    <li class="ui-listbox-item" ><span onclick="javascript:addEvent(<?php echo $i; ?>, this, 1)" class="list-group-item" data-value="No Title">I Do Not Have A Title</span></li>
                    </ul>
                </div>
                <?php
            } elseif( $steps[$i]['name'] == 'drivability_rating'){ ?>
            <div class="title-container">
            <div class="title-searchbar">
                <div class="form-group selectable">
                <label for="<?php echo $steps[$i]['name']; ?>"><?php echo $steps[$i]['desc']; ?></label>
                </div>
            </div>
            </div>
                <div class="data  <?php echo $steps[$i]['name']; ?>" id="<?php echo $steps[$i]['name']; ?>">
                    <ul id="<?php echo $steps[$i]['name']; ?>" class="ui-listbox-list">
                    <li class="ui-listbox-item" ><span onclick="javascript:addEvent(<?php echo $i; ?>, this, 1)" class="list-group-item" data-value="D">Drives</span></li>
                    <li class="ui-listbox-item" ><span onclick="javascript:addEvent(<?php echo $i; ?>, this, 1)" class="list-group-item" data-value="N">Non Start</span></li>
                    <li class="ui-listbox-item" ><span onclick="javascript:addEvent(<?php echo $i; ?>, this, 1)" class="list-group-item" data-value="S">Starts</span></li>
                    </ul>
                </div>
            
            <?php
            }
            else{
            ?>
                <div class="title-container">
                    <div class="title-searchbar">
                        <input type="text" id="<?php echo $steps[$i]['name']; ?>Input" onkeydown="search(<?php echo $i; ?>)" placeholder="<?php echo $steps[$i]['desc']; ?>" title="Type in a">
                        <small class="error <?php echo $steps[$i]['name']; ?> mt-2">No Data Available for your selection</small>
                        <?php
                            if(in_array($steps[$i]['name'], $vins))
                            {
                        ?>
                            <a class="openModalBtn search-vin-btn text-right">Search with VIN</a>
                        <?php
                            }
                        ?>
                    </div>
                </div>
                <div class="loading  <?php echo $steps[$i]['name']; ?>">
                    <img src="<?php echo CI_ASSETS.'/images/loading.gif'?>">
                </div>
                <div class="data  <?php echo $steps[$i]['name']; ?>" id="<?php echo $steps[$i]['name']; ?>">
                    <ul id="<?php echo $steps[$i]['name']; ?>" class="ui-listbox-list">
                    </ul>
                </div>
        <?php 
            }?>
            </div>
            <?php
    } ?>
    <div class="results " style="display: none;">
        <div class="title-container">
            <div class="title-searchbar">
                <div class="form-group">
                    <label for="phone">Ta da! We'd love to buy your</label>
                    <span id="selected"></span>
                    <span> for</span>
                </div>
            </div>
        </div>
        <div id="result">
            <h1 data-id="offer_presentation_price" class="_b22f1561 _f09a3dc7 _86719728 offer_presentation_price" style="color: rgb(24, 114, 237);"></h1>
            <div style="width:100%;text-align: center;padding:20px">
            <p style="">
                <strong>Please check your email!</strong> We've sent you an offer. 
                To accept it, simply <a href="/register">Sign Up</a> or 
                <a href="/login">Log In</a> using the same email address. 
                You can do this directly from the email or by clicking 
                <a href="/register">here</a>.
            </p>
            </div>
        </div>
        <span id="error">There was an issue getting your quote, please check your information and  try agin.</span>
                <div class="loading" style="text-align: center;">
 
                    <img src="<?php echo CI_ASSETS.'/images/loading.gif'?>">
        </div>
    </div>
    </div>
    <div class="footer">
        <div >
            <button type="button" id="prevBtn" onclick="prev()">Previous</button>
            <button type="button" id="searchVinPhone" class="openModalBtn search-vin-btn-phone">Search with VIN</button>
            <button type="button" style="float:right;" id="nextBtn" onclick="next(1)">Next</button>
            <button type="button" id="prevBtnResults" onclick="prevLast()">Return to last step</button>
            <button type="button" style="float:right;display:none" id="tryBtn" onclick="tryAgain()">Try Again</button>
        </div>
    </div>
    <div id="myModal" class="modal">
  <div class="modal-content">
    <span class="close" id="closeModal">&times;</span>
    <div class="modal-title">Search with VIN</div>
    <div class="modal-text">
      <input type="text" id="searchInput" placeholder="Enter vin number...">
      <div class="loading-modal">
        <img src="<?php echo CI_ASSETS.'/images/loading.gif'?>">
        </div>
      <small class="error error-modal mt-2">Field cannot be empty.</small>
      <button id="searchButton" class="btn">Search</button>
    </div>
  </div>
</div>
</div>
 
<script>
    var steps = <?php echo $jsonString; ?>;
    var selected = <?php echo $json_data_get; ?>;
    
    // Get the modal element and the button that opens it
    const modal = document.getElementById("myModal");
    const openModalBtns = document.querySelectorAll(".openModalBtn");
    const closeModal = document.getElementById("closeModal");
 
    // Function to open the modal
    function openModal() {
    modal.style.display = "block";
    }
 
    // Function to close the modal
    function closeModals() {
    modal.style.display = "none";
    }
 
    // Close the modal when clicking on the close button
    closeModal.addEventListener("click", closeModals);
 
    // Close the modal when clicking outside the modal content
    window.addEventListener("click", function (event) {
    if (event.target === modal) {
        closeModals();
    }
    });
 
    // Open the modal when the button is clicked
    openModalBtns.forEach(function(btn) {
    btn.addEventListener("click", function() {
        // Your code to open the modal goes here
        openModal();
    });
});
    closeModals();
</script>