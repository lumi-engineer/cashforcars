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

jQuery(".actionBtn").click(function (e) {
  e.preventDefault();
  var action = jQuery(this).attr('action');
  var type = jQuery(this).attr('data-type');
  console.log(type);
  approve(action, type);
});
function approve(action,type)
{
  startLoader();
  var offerValue = getQueryParam('offer');
  const data = {
    action: 'approve',
    approve:action,
    type:type,
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
        console.log(response);
        showSweet('success', response.data);
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

// Function to get query parameter value by name
function getQueryParam(name) {
  var urlParams = new URLSearchParams(window.location.search);
  return urlParams.get(name);
}