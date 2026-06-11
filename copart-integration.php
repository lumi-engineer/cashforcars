<?php

/**
 * Plugin Name: Copart Integration
 * Plugin URI: https://brianmarketinggroup.com/
 * Description: Copart Integration.
 * Version: 1.0.1
 * Author: Ali Arshad
 * Author URI: https://brianmarketinggroup.com/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: copart-integration
 */
final class CopartIntegration {

    public $version = '1.0.1';
    public function __construct() {

        $this->define_constants();
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        add_action( 'plugins_loaded', array( $this, 'init_plugin' ) );
        add_filter( 'theme_page_templates', array($this, 'sf_add_page_template_to_dropdown') );
        add_filter( 'template_include', array($this, 'pt_change_page_template'), 99 );
        add_action('admin_menu', array($this, 'add_menu_page' ));
    }
    function sf_add_page_template_to_dropdown( $templates )
    {
        $templates = $this->my_templates();
        return $templates;
    }
    function pt_change_page_template($template)
    {
        global $post;
        $templates = $this->my_templates();
        if(!$post)
        {
            return $template;
        }
        $template_name = get_page_template_slug($post->ID);
        if(isset($templates[$template_name]))
        {
            $template = $template_name;
        }
        return $template;

    }
    function my_templates()
    {
        $temps = [];
        $temps[CI_INCLUDES . '/templates/login.php'] = 'Copart Login Page';
        $temps[CI_INCLUDES . '/templates/register.php'] = 'Copart Register Page';
        $temps[CI_INCLUDES . '/templates/home.php'] = 'Copart Home Page';
        return $temps;
    }
    public function define_constants() {
        define( 'CI_VERSION', $this->version );
        define( 'CI_FILE', __FILE__ );
        define( 'CI_PATH', dirname( CI_FILE ) );
        define( 'CI_URL', plugins_url( '', CI_FILE ) );
        define( 'CI_ASSETS', CI_URL . '/assets' );
        define( 'CI_INCLUDES', CI_PATH . '/includes' );
        define( 'CI_DATA', CI_PATH . '/includes/data' );
    }
    public function activate() {
        require_once CI_INCLUDES . '/install.php';
        new Install();
    }

    function init_plugin() {
        $this->includes();
        $this->init_classes();
	}
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new CopartIntegration();
        }

        return $instance;
    }

    public function includes() {
        require_once CI_INCLUDES . '/helpers/ImageUrl.php';

        if ( $this->is_request( 'frontend' ) ) {
            require_once CI_INCLUDES . '/Assets.php';
            require_once CI_INCLUDES . '/Frontend.php';
        }
        if ( $this->is_request( 'ajax' ) ) {
            require_once CI_INCLUDES . '/ajax/AjaxAction.php';
            require_once CI_INCLUDES . '/ajax/ProQuote.php';
            require_once CI_INCLUDES . '/ajax/ZipCodeBase.php';
            require_once CI_INCLUDES . '/ajax/VinDecoder.php';
            require_once CI_INCLUDES . '/ajax/AjaxCallbacks.php';
        }
        // require dirname( __FILE__ ) . '/ci-ajax.php';
    }
    function handle_routes() {
        // $template = dirname(__DIR__,1) . '/formController.php';
        $template = CI_INCLUDES . '/controllers/dealer.php';
        // if (file_exists($template)) {
        require $template;
        // }
    }
    public function init_classes() {
        if ( $this->is_request( 'frontend' ) ) {
            new CI_Lib\Assets();
            new CI_Lib\Frontend();
            wp_localize_script( 'ci-front-js', 'ajax_object', array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'ci-data' ),
            ) );
        }
        if ( $this->is_request( 'ajax' ) && !isset($_GET['wc-ajax']) ) {
             new CI_Lib\Ajax\AjaxAction();
        }
       
    }
    private function is_request( $type ) {
        switch ( $type ) {
            case 'admin' :
                return is_admin();

            case 'ajax' :
                return defined( 'DOING_AJAX' );

            case 'rest' :
                return defined( 'REST_REQUEST' );

            case 'cron' :
                return defined( 'DOING_CRON' );

            case 'frontend' :
                return ( ! is_admin() ) ;
        }
    }
    // Register a top-level menu page
    function add_menu_page() {

            add_menu_page(
            'Copart Integration',
            'Copart Integration',
            'manage_options',
            'copart-integration',
            array($this, 'handle_routes'),
            'dashicons-admin-generic',
            10
            );
        }
  
}

$exceltotable = CopartIntegration::init();