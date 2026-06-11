<?php

namespace CI_Lib\Ajax;

class AjaxAction {
    public function __construct() {
        $this->init();
    }

    public static function addAction($tag, $function_to_add, $nonpriv = false, $priority = 10, $accepted_args = 1) {
        add_action( 'wp_ajax_' . $tag, $function_to_add, $priority, $accepted_args );
        if ( $nonpriv ) {
            add_action( 'wp_ajax_nopriv_' . $tag, $function_to_add, $priority, $accepted_args );
        }
        return true;
    }

    public static function init() {
        $ajaxCallbacks = new AjaxCallbacks();
        AjaxAction::addAction('year', [$ajaxCallbacks , 'getYears'], true);
        AjaxAction::addAction('make', [$ajaxCallbacks , 'getMakes'], true);
        AjaxAction::addAction('model', [$ajaxCallbacks , 'getMakeModels'], true);
        AjaxAction::addAction('quote', [$ajaxCallbacks , 'getQuote'], true);
        AjaxAction::addAction('vehicle_type', [$ajaxCallbacks , 'getTypeData'], true);
        AjaxAction::addAction('damage_location', [$ajaxCallbacks , 'getTypeData'], true);
        AjaxAction::addAction('damage_type', [$ajaxCallbacks , 'getTypeData'], true);
        AjaxAction::addAction('zip', [$ajaxCallbacks , 'getZipDetails'], true);
        AjaxAction::addAction('vin', [$ajaxCallbacks , 'decodeVin'], true);
        AjaxAction::addAction('cpi_login', [$ajaxCallbacks , 'login'], true);
        AjaxAction::addAction('cpi_register', [$ajaxCallbacks , 'register'], true);
        AjaxAction::addAction('create', [$ajaxCallbacks , 'createAssignment'], true);
        AjaxAction::addAction('cancel', [$ajaxCallbacks , 'cancelAssignment'], true);
        AjaxAction::addAction('upload', [$ajaxCallbacks , 'uploadImages'], true);
        AjaxAction::addAction('approve', [$ajaxCallbacks , 'approve'], true);
    }
}