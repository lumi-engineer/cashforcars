<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class dealer {

	public function __construct() {
		if ( isset( $_GET['page'] ) ) {
			$this->get( $_GET['page'] );
		}
	}
	protected function get( $page ) {
        /**
		 * * This renders the pages acording to the menu options
		 * todo: needs formController to load data of forms
		 * @param page
		 */
		switch ( $page ) {
			case 'copart-integration':
				require dirname( __FILE__ ) . '/pageControllers/class-page-spg.php';
				break;
			default:
                require dirname( __FILE__ ) . '/pageControllers/class-page-spg.php';
		}
	}
}
new dealer();
