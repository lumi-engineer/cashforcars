// Define an object to store selected data
const datajson = {};
// Cache select elements and initialize Select2
const year_select = jQuery('#year-select').select2({
  dropdownAutoWidth: true,
  dropdownHeight: 400
});
const make_select = jQuery('#make-select').select2();
const model_select = jQuery('#model-select').select2();
const type_select = jQuery('#vehicle_type-select').select2();
const submitButton = document.getElementById("submit-ci-search");
// submitButton.style.cursor = "pointer";
// Get the element by class name
const select2SingleSelection = document.querySelector('.select2-container .select2-selection--single');
// Set the height to 50px
select2SingleSelection.style.height = '50px';
// Function to fetch data via AJAX
function getData(step) {
  const select = jQuery(`#${step}-select`);
  const data = {
    action: step,
  };

  if (step === 'model') {
    data['make'] = datajson.makeSel;
    data['year'] = datajson.yearSel;
  }
  jQuery.ajax({
    type: 'POST',
    url: `${window.location.origin}/wp-admin/admin-ajax.php`,
    data: data,
    success: function (response) {
      console.log(step, data, response);
      if (step === 'model') {
        select.empty();
      }
      const initialData = response.data.data;
      initialData.forEach(function (item) {
        select.append(new Option(item.value, item.key));
      });

      // Enable the 'make' select when 'year' is selected
      if (step === 'year') {
        make_select.prop('disabled', false);
        type_select.prop('disabled', false);
        model_select.prop('disabled', true);
      }
      select.trigger('change');
      // Attach an event handler for select2:select event
      select.on('change', function (e) {
        console.log(e);
        const selectedValue = jQuery(this).val();
        datajson[step+'Sel'] = selectedValue;
        console.log('Selected value:', selectedValue);

        if (step === 'make') {
          model_select.prop('disabled', false);
          getData('model');
        }
      });
    },
    error: function (xhr, status, error) {
      console.error('AJAX Error:', error);
    },
  });
}

// Initial setup on document ready
jQuery(document).ready(function ($) {

  // Disable initially
  make_select.prop('disabled', true);
  model_select.prop('disabled', true);

  // Load data
  getData('year');
  getData('vehicle_type');
  getData('make');


  // ✅ Prevent form submit
  $('#instant-offer').on('submit', function (e) {

    e.preventDefault(); // STOP FORM SUBMIT

    var year = $('#year-select').val();
    var vehicle_type = $('#vehicle_type-select').val();
    var make = $('#make-select').val();
    var model = $('#model-select').val();

    const requiredFields = {
      year,
      vehicle_type,
      make,
      model
    };

    // Check required
    for (const field in requiredFields) {
      if (!requiredFields[field]) {
        alert(field + " is required.");
        return false;
      }
    }

    const vehicleObject = {
      yearSel: year,
      vehicle_typeSel: vehicle_type,
      makeSel: make,
      modelSel: model
    };

    const queryParams = new URLSearchParams(vehicleObject);

    const currentURL = window.location.href;
    const urlObject = new URL(currentURL);

    const domain = urlObject.origin; // better than hostname
    const path = urlObject.pathname;

    const newURL =
      domain +
      "/copart-integration/?" +
      queryParams.toString();

    window.location.href = newURL;

  });

});
function capitalizeFirstLetter(string) {
      // Split the string into an array at each underscore
      const words = string.split('_');

      // Capitalize the first letter of each word and join them back together
      const capitalizedWords = words.map(word =>
          word.charAt(0).toUpperCase() + word.slice(1).toUpperCase()
      );
  
      // Join the words with spaces to remove underscores
      return capitalizedWords.join(' ');
}
function checkFields(obj, fields) {
  var isValid = true;
  for (const field of fields) {
      if (!((field+'Sel') in obj)) {
          alert(`Field '${field}' is missing in the value.`);
          isValid =  false;
      }
  }
  return isValid;
}

