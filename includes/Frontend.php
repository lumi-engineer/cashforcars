<?php
namespace CI_Lib;
/**
 * Frontend Pages Handler
 */
class Frontend {

	public function __construct() {
		add_shortcode( 'copart-integration', array( $this, 'render_frontend' ) );
	}

	/**
	 * Render frontend app
	 *
	 * @param  array $atts
	 * @param  string $content
	 *
	 * @return string
	 */
	public function render_frontend( $atts, $content = '' ) {
		wp_enqueue_script( 'ci-front-js' );
		wp_enqueue_style('ci-style-css');
        $html_rendered = $this->get_rendered_html(CI_PATH . '/assets/index.php');
		return ( $html_rendered );
	}
    function get_rendered_html($path)
    {
        ob_start();
        include($path);
        $var=ob_get_contents();
        ob_end_clean();
        return $var;
    }
}
