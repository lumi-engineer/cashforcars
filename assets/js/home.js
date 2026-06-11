
// Define an object to store selected data
const datajson2 = {};
// Cache select elements and initialize Select2
const year_select2 = jQuery('#year-select').select2({
  dropdownAutoWidth: true,
  dropdownHeight: 400,
  width: 'resolve' 
});
const make_select2 = jQuery('#make-select').select2({
  width: 'resolve' 
});
const model_select2 = jQuery('#model-select').select2({
  width: 'resolve' 
});
const type_select2 = jQuery('#vehicle_type-select').select2(
  {
    width: 'resolve' 
  }
);
const title_select2 = jQuery('#title-select').select2(
  {
    width: 'resolve' 
  }
);
const damage_type_select2 = jQuery('#damage_type-select').select2(
  {
    width: 'resolve' 
  }
);
const damage_loc_select2 = jQuery('#damage_location-select').select2(
  {
    width: 'resolve' 
  }
);
jQuery(document).ready(function() {
  jQuery('select.select2').select2();
});
// // Get the element by class name
// const select2SingleSelection = document.querySelector('.select2-container .select2-selection--single');
// // Set the height to 50px
// select2SingleSelection.style.height = '40px';
// select2SingleSelection.style.width = '100%';
// Function to fetch data via AJAX
function getData(step) {
  const select = jQuery(`#${step}-select`);
  const data = {
    action: step,
    nonce: ajax_object.nonce,
  };

  if (step === 'model') {
    data['make'] = datajson2.makeSel;
    data['year'] = datajson2.yearSel;
  }
  jQuery.ajax({
    type: 'POST',
    url: ajax_object.ajax_url,
    data: data,
    success: function (response) {
      console.log(response);
      const initialData = response.data.data;
      initialData.forEach(function (item) {
        select.append(new Option(item.value, item.key));
      });

      // Enable the 'make' select when 'year' is selected
      if (step === 'year') {
        make_select2.prop('disabled', false);
        type_select2.prop('disabled', false);
        model_select2.prop('disabled', true);
      }
      select.trigger('change');
      // Attach an event handler for select2:select event
      select.on('select2:select', function (e) {
        const selectedValue = e.params.data.id;
        datajson2[step+'Sel'] = selectedValue;
        console.log('Selected value:', selectedValue);

        if (step === 'make') {
          model_select2.prop('disabled', false);
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
  // Disable 'make' and 'model' initially
  make_select2.prop('disabled', true);
  model_select2.prop('disabled', true);

  // Trigger data loading for 'year' and 'make' when the document is ready
  getData('year');
  getData('vehicle_type');
  getData('make');
  getData('damage_type');
  getData('damage_location');
  $("#finish").click(function (e) {
    e.preventDefault();
    // Perform actions when the "Finish!" button is clicked
    console.log("Finish button clicked!");
    
    var curForm = jQuery('#saveForm');
    // Serialize the form data
    var formData = curForm.serializeArray();
    // Get the value of the "offer" parameter from the URL
    var offerValue = getQueryParam('offer');

    // If "offer" parameter exists, append it to the dataArray
    if (offerValue !== null) {
      formData.push({ "name": "offer", "value": offerValue });
    }

    // Log or post formData to the server
    console.log(formData);
    validateZip(formData);
});
$("#cancelBtn").click(function (e) {
  e.preventDefault();
  cancel();
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
  for (const field of fields) {
      if (!((field+'Sel') in obj)) {
          alert(`Field '${field}' is missing in the object.`);
          return false;
      }
  }
}
// Function to get query parameter value by name
function getQueryParam(name) {
  var urlParams = new URLSearchParams(window.location.search);
  return urlParams.get(name);
}
function create(formData)
{
    startLoader();
    const data = {
      action: 'create',
      nonce: ajax_object.nonce,
      data:formData
    };
    jQuery.ajax({
      type: 'POST',
      url: ajax_object.ajax_url,
      data: data,
      success: function (response) {
        stopLoader();
        if(response.success == true)
        {
          showSweet('success', response.data.message, response.data.redirect);
        }
        else
        {
          showSweet('error', response.data)
        }
      },
      error: function (xhr, status, error) {
        stopLoader();
        console.error('AJAX Error:', error);
      },
    });
}
function cancel()
{
  startLoader();
  var offerValue = getQueryParam('offer');
  const data = {
    action: 'cancel',
    nonce: ajax_object.nonce,
    offer:offerValue
  };
  jQuery.ajax({
    type: 'POST',
    url: ajax_object.ajax_url,
    data: data,
    success: function (response) {
      console.log(response);
      stopLoader();
      if(response.success == true)
      {
        showSweet('success', response.data.message, response.data.redirect);
      }
      else
      {
        showSweet('error', response.data);
      }
    },
    error: function (xhr, status, error) {
      stopLoader();
      console.error('AJAX Error:', error);
    },
  });
}
function showSweet(type, message, redirect=false) {
  Swal.fire({
    icon: type === 'success' ? 'success' : 'error',
    text: message
  }).then(function() {
    if(redirect)
    {
      window.location = redirect;
    }
});
}
// Function to start the loader
function startLoader() {
  jQuery('#loader-container').show();
  jQuery('body').css('overflow', 'hidden'); // Prevent scrolling
}

// Function to stop the loader
function stopLoader() {
  jQuery('#loader-container').hide();
  jQuery('body').css('overflow', 'auto'); // Restore scrolling
}
 function validateZip(formData) {
  let isValid = true;
  startLoader();
  var data = {
      action: 'zip', // AJAX action name
      nonce: ajax_object.nonce // Nonce
  };
  data['zip'] = jQuery("input[name='zipcode']").val();
  jQuery.ajax({
      url: ajax_object.ajax_url,
      method: 'POST',
      data: data,
      success: function(response) {
        console.log(response);
        stopLoader();
        if(response.data.data.success == false)
        {
          showSweet('error', "Zip not valid.");
          return false;
        }
        else
        {
          create(formData);
        }
      },
      error: function(xhr, textStatus, errorThrown) {
        stopLoader();
          console.error('AJAX Error:', errorThrown);
          data_years.show();
      }
  });
  return isValid;
}
function ciOpenImagePreview(imageUrl) {
  var normalizedUrl = window.ciNormalizeImageUrl ? window.ciNormalizeImageUrl(imageUrl) : imageUrl;

  if (typeof Swal !== 'undefined') {
    Swal.fire({
      imageUrl: normalizedUrl,
      imageAlt: 'Preview Image',
      showCloseButton: true,
      width: 'auto'
    });
    return;
  }

  window.open(normalizedUrl, '_blank', 'noopener,noreferrer');
}

jQuery(document).on('click keypress', '.preview-image', function (e) {
  if (e.type === 'keypress' && e.which !== 13 && e.which !== 32) {
    return;
  }

  e.preventDefault();
  var imageUrl = jQuery(this).attr('data-ci-image') || jQuery(this).attr('src');
  ciOpenImagePreview(imageUrl);
});

jQuery(document).ready(function () {
  if (window.ciApplyImageUrlNormalization) {
    window.ciApplyImageUrlNormalization();
  }
});

jQuery('.submit_images').click(function (e) {
  e.preventDefault();
  startLoader();
  var type = jQuery(this).attr('data-id');
  var files;

  if (type == 'title') {
    files = jQuery('#images')[0].files;
    if (files.length !== 1) {
      showSweet('error', 'Please upload exactly one title image.', false);
      stopLoader();
      return;
    }
  } else {
    files = jQuery('#car_images')[0].files;
    if (files.length === 0) {
      showSweet('error', 'No vehicle photos selected for upload.', false);
      stopLoader();
      return;
    }
  }

  var formData = new FormData();
  formData.append('action', 'upload');
  formData.append('nonce', ajax_object.nonce);

  for (var i = 0; i < files.length; i++) {
    formData.append('custom_images[]', files[i]);
  }

  var offerValue = getQueryParam('offer');
  if (offerValue !== null) {
    formData.append('offer', offerValue);
  } else {
    formData.append('offer', 1);
  }

  formData.append('type', type);

  jQuery.ajax({
      url: ajax_object.ajax_url,
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      success: function(response) {
          // Handle successful upload
          if(response.success == true)
          {
            showSweet('success', response.data.message, response.data.redirect);
          }
          else
          {
            showSweet('error', response.data)
          }
          stopLoader();
      },
      error: function(xhr, status, error) {
        stopLoader();
          // Handle upload error
          console.error('Error uploading images:', error);
      }
  });
});
