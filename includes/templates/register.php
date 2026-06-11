<?php

/*
Template Name: Copart Login Page
Description: A custom template for the login page.
*/
get_header();
wp_enqueue_script( 'ci-register-js' );
wp_localize_script( 'ci-register-js', 'ajax_object', array(
    'ajax_url' => admin_url( 'admin-ajax.php' ),
    'nonce' => wp_create_nonce( 'ci-register' ),
	'home' => home_url('/offers-home')
));
?>
<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="<?php echo CI_ASSETS?>/css/login.css">
</head>
<body>
<div class="card">
  <div class="container">
  <div class="login-form"> 
	<form id="register-form">
	<div class="container-form">
		<div class="message">
			<small class="error" id="error-message"></small>
			<small class="success" id="error-message"></small>
		</div>
		<label for="name"><b>Name</b></label>
		<input type="text" id="name" placeholder="Enter Name" name="name" required>
		<small class="error" id="name-error">Field cannot be empty.</small>
		<label for="email"><b>Email</b></label>
		<input type="email" id="email" placeholder="Enter Email" name="email" required>
		<small class="error" id="email-error">Field cannot be empty.</small>
		<label for="psw"><b>Password</b></label>
		<input type="password" id="password" placeholder="Enter Password" name="psw" required>
		<small class="error" id="password-error">Field cannot be empty.</small>
		<label for="c_psw"><b>Confirm Password</b></label>
		<input type="password" id="confirm-password" placeholder="Confirm Password" name="c_psw" required>
		<small class="error" id="confirm-password-error">Field cannot be empty.</small>
		<div class="loading">
            <img src="<?php echo CI_ASSETS.'/images/loading.gif'?>">
        </div>
		<button type="submit" class="btn">Register</button>
		<label>
		</label>
		<span class="psw"><a href="/login">Already have an account?</a></span>

	</div>
	</form>
</div>
  </div>
</div>

</body>
</html>
<?php
get_footer();
?>