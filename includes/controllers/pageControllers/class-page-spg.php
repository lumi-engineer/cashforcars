<?php
if (!defined('ABSPATH')) {
    exit;
}
require_once dirname( __FILE__ ) . '/class-pages-breadcrumbs.php';
class SPGPage extends PagesBreadcrumbs
{

    public function __construct()
    {
        parent::__construct();
        require  CI_INCLUDES . '/templates/backend/home.php';
    }
}

new SPGPage();
