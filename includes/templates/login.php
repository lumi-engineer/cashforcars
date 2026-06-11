<?php

/*
Template Name: Custom Login Page
Description: A custom template for the login page.
*/
get_header();
wp_enqueue_script( 'ci-login-js' );
wp_localize_script( 'ci-login-js', 'ajax_object', array(
    'ajax_url' => admin_url( 'admin-ajax.php' ),
    'nonce' => wp_create_nonce( 'ci-login' ),
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
	<form id='login-form'>
	<div class="container-form">
	<div class="message">
			<small class="error" id="error-message"></small>
			<small class="success" id="error-message"></small>
		</div>
		<label for="uname"><b>Email</b></label>
		<input type="email" id="email" placeholder="Enter Email" name="email" required>
		<small class="error  mt-2">Field cannot be empty.</small>
		<small class="error  mt-2">Field cannot be empty.</small>
		<label for="psw"><b>Password</b></label>
		<input type="password" id="password" placeholder="Enter Password" name="password" required>
		<small class="error  mt-2">Field cannot be empty.</small>
		<div class="loading">
        	<img src="<?php echo CI_ASSETS.'/images/loading.gif'?>">
        </div>
		<button type="submit" class="btn">Login</button>
		<label>
		</label>
		<span class="psw"><a href="/register">Don't have an account?</a></span>

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