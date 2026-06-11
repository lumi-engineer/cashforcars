let loading = jQuery('.loading');
let error = jQuery('#error-message');
let success = jQuery('#success-message');
jQuery(document).ready(function($) {
  $('#register-form').on('submit', function(e) {
      e.preventDefault(); // Prevent the form from submitting normally
      // Clear existing error messages
      $('.error').hide();
      
      // Check if the email field is empty
      var email = $('#email').val();
      if (email === '') {
          $('#email-error').show();
      }
      var name = $('#name').val();
      if (name === '') {
          $('#name-error').show();
      } 
      // Check if the password field is empty
      var password = $('#password').val();
      if (password === '') {
          $('#password-error').show();
      }
      
      // Check if the confirm password field is empty
      var confirmPass = $('#confirm-password').val();
      if (confirmPass === '') {
          $('#confirm-password-error').show();
      }
      
      // Additional validation logic (e.g., password matching) can be added here
      
      if (email !== '' && password !== '' && confirmPass !== '') {
        loading.show()
        var data = {
          action: 'cpi_register', // AJAX action name
          nonce: ajax_object.nonce, // Nonce
          name: name,
          email: email,
          password: password,
      };
      error.hide();
      success.hide();
      console.log(data);
        jQuery.ajax({
          url: ajax_object.ajax_url,
          method: 'POST',
          data: data,
          success: function(response) {
            loading.hide();
            if(response.success == true)
            {
              success.text(response.data)
              success.show();
              window.location.href = ajax_object.home;
            }
            else
            {
              error.text(response.data);
              error.show();
            }
            console.log(response);
          },
          error: function(xhr, textStatus, errorThrown) {
            loading.hide();
            console.log(errorThrown);
          }
      });
      }
  });
});
