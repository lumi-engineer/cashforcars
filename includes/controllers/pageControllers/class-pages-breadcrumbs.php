<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * * Main class for all pages, the page classes inherit this class
 * todo: here must be enqueue most of the js and css
 */

class PagesBreadcrumbs {
	public function __construct() {
		if ( is_admin() ) {
			wp_register_style('ci-bootstrap-style-css', CI_ASSETS . '/css/bootstrap.min.css');
            wp_enqueue_style('ci-bootstrap-style-css');
            wp_register_script('ci-backend-bootstrap-js', CI_ASSETS . '/js/bootstrap.bundle.min.js', array('jquery'), '4.5.2', true);
            wp_enqueue_script('ci-backend-bootstrap-js');
			wp_register_script('ci-backend-swal-js', CI_ASSETS . '/js/swal.min.js', array('jquery'), '4.5.2', true);
            wp_enqueue_script('ci-backend-swal-js');
			wp_register_script('ci-image-url-js', CI_ASSETS . '/js/ci-image-url.js', array(), CI_VERSION, true);
            wp_enqueue_script('ci-image-url-js');
			wp_register_script('script-backend-js', CI_ASSETS . '/backend/js/script.js', array('jquery', 'ci-image-url-js'), CI_VERSION, true);
            wp_enqueue_script('script-backend-js');
		}
	}
}
